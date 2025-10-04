<?php
/**
 * Email Sender Class
 * Handles sending emails for account notifications
 */

require_once __DIR__ . '/config.php';

class EmailSender {
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    
    public function __construct() {
        // Load from config constants
        $this->fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@bulwagang-ncst.com';
        $this->fromName  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Bulwagang NCST';
        $this->smtpHost  = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->smtpPort  = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $this->smtpUsername = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->smtpPassword = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    }
    
    /**
     * Send email using PHP's mail() function (for development)
     */
    public function sendEmail($to, $subject, $bodyHtml, $bodyText = null) {
        // Prefer SMTP when configured
        if (!empty($this->smtpHost) && !empty($this->smtpUsername) && !empty($this->smtpPassword)) {
            return $this->sendEmailSMTP($to, $subject, $bodyHtml, $bodyText);
        }
        // Fallback to log (useful on localhost)
        return $this->logEmail($to, $subject, $bodyHtml, $bodyText);
    }
    
    /**
     * Check if we're in development environment
     */
    private function isDevEnvironment() {
        return (
            isset($_SERVER['HTTP_HOST']) && (
                $_SERVER['HTTP_HOST'] === 'localhost' || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'xampp') !== false
            )
        );
    }
    
    /**
     * Log email for development/testing (instead of actually sending)
     */
    private function logEmail($to, $subject, $bodyHtml, $bodyText) {
        try {
            $logDir = dirname(__DIR__) . '/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/emails.log';
            $timestamp = date('Y-m-d H:i:s');
            
            $logContent = "
=====================================
EMAIL LOG - $timestamp
=====================================
To: $to
Subject: $subject

HTML Body:
$bodyHtml

" . ($bodyText ? "Text Body:\n$bodyText\n" : "") . "
=====================================

";
            
            file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
            
            // Also create individual email files for easy viewing
            $emailFile = $logDir . '/email_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $to) . '.html';
            file_put_contents($emailFile, $bodyHtml);
            
            error_log("Email logged for development: $to - Check $emailFile");
            return true;
            
        } catch (Exception $e) {
            error_log("Email logging error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using SMTP (for production)
     */
    public function sendEmailSMTP($to, $subject, $bodyHtml, $bodyText = null) {
        $port = $this->smtpPort ?: 587;
        $timeout = 30;

        $fp = @stream_socket_client("tcp://{$this->smtpHost}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            error_log("SMTP connect error: $errno $errstr");
            return $this->logEmail($to, $subject, $bodyHtml, $bodyText);
        }
        stream_set_timeout($fp, $timeout);

        $read = function() use ($fp) {
            $data = '';
            while ($str = fgets($fp, 515)) {
                $data .= $str;
                // 4th char '-' means multi-line continues
                if (isset($str[3]) && $str[3] === ' ') break;
            }
            return $data;
        };

        $expect = function($resp, $codes) {
            foreach ((array)$codes as $code) {
                if (strpos($resp, (string)$code) === 0) return true;
            }
            return false;
        };

        $write = function($cmd) use ($fp) {
            fwrite($fp, $cmd . "\r\n");
        };

        $resp = $read(); // 220 greeting
        if (!$expect($resp, 220)) { fclose($fp); return false; }

        $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        $write("EHLO $host");
        $resp = $read();
        if (!$expect($resp, 250)) { fclose($fp); return false; }

        // STARTTLS
        $write('STARTTLS');
        $resp = $read();
        if (!$expect($resp, 220)) { fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }

        // EHLO again over TLS
        $write("EHLO $host");
        $resp = $read();
        if (!$expect($resp, 250)) { fclose($fp); return false; }

        // AUTH LOGIN
        $write('AUTH LOGIN');
        $resp = $read();
        if (!$expect($resp, 334)) { fclose($fp); return false; }
        $write(base64_encode($this->smtpUsername));
        $resp = $read();
        if (!$expect($resp, 334)) { fclose($fp); return false; }
        $write(base64_encode($this->smtpPassword));
        $resp = $read();
        if (!$expect($resp, 235)) { fclose($fp); return false; }

        // MAIL FROM, RCPT TO, DATA
        $from = $this->fromEmail;
        $write("MAIL FROM:<$from>");
        $resp = $read();
        if (!$expect($resp, 250)) { fclose($fp); return false; }

        $write("RCPT TO:<$to>");
        $resp = $read();
        if (!$expect($resp, [250, 251])) { fclose($fp); return false; }

        $write('DATA');
        $resp = $read();
        if (!$expect($resp, 354)) { fclose($fp); return false; }

        // Build MIME message (multipart/alternative)
        $boundary = 'bndry_' . bin2hex(random_bytes(8));
        $headers = [];
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>';
        $headers[] = 'Reply-To: ' . $this->fromEmail;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $headers[] = 'Subject: ' . $subject;
        $headers[] = 'To: ' . $to;

        $lines = [];
        $lines[] = implode("\r\n", $headers);
        $lines[] = '';
        if ($bodyText) {
            $lines[] = '--' . $boundary;
            $lines[] = 'Content-Type: text/plain; charset=UTF-8';
            $lines[] = 'Content-Transfer-Encoding: 8bit';
            $lines[] = '';
            $lines[] = $bodyText;
            $lines[] = '';
        }
        $lines[] = '--' . $boundary;
        $lines[] = 'Content-Type: text/html; charset=UTF-8';
        $lines[] = 'Content-Transfer-Encoding: 8bit';
        $lines[] = '';
        $lines[] = $bodyHtml;
        $lines[] = '';
        $lines[] = '--' . $boundary . '--';
        $lines[] = '';
        $message = implode("\r\n", $lines);

        // Send data and end with '.'
        fwrite($fp, $message . "\r\n.\r\n");
        $resp = $read();
        if (!$expect($resp, 250)) { fclose($fp); return false; }

        $write('QUIT');
        $read();
        fclose($fp);
        return true;
    }
    
    /**
     * Validate email address
     */
    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Send test email
     */
    public function sendTestEmail($to) {
        $subject = 'Test Email from Bulwagang NCST';
        $bodyHtml = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2>Test Email</h2>
            <p>This is a test email from the Bulwagang NCST system.</p>
            <p>If you received this email, the email system is working correctly.</p>
            <p>Timestamp: ' . date('Y-m-d H:i:s') . '</p>
        </body>
        </html>';
        
        $bodyText = 'Test Email - This is a test email from the Bulwagang NCST system. Timestamp: ' . date('Y-m-d H:i:s');
        
        return $this->sendEmail($to, $subject, $bodyHtml, $bodyText);
    }
}
?>
