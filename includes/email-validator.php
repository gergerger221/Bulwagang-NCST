<?php
/**
 * Email Validation Library
 * Validates email format, domain existence, and deliverability
 */

/**
 * Validate email format using PHP filter and additional checks
 */
function validateEmailFormat($email) {
    // Basic format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'level' => 'format',
            'message' => 'Invalid email format'
        ];
    }
    
    // Additional format checks
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return [
            'valid' => false,
            'level' => 'format',
            'message' => 'Invalid email structure'
        ];
    }
    
    $localPart = $parts[0];
    $domain = $parts[1];
    
    // Check email length
    if (strlen($email) > 254) {
        return [
            'valid' => false,
            'level' => 'format',
            'message' => 'Email address too long'
        ];
    }
    
    // Check local part length
    if (strlen($localPart) > 64) {
        return [
            'valid' => false,
            'level' => 'format',
            'message' => 'Email username too long'
        ];
    }
    
    // Check for valid domain format
    if (strpos($domain, '.') === false) {
        return [
            'valid' => false,
            'level' => 'format',
            'message' => 'Invalid domain format'
        ];
    }
    
    return [
        'valid' => true,
        'level' => 'format',
        'message' => 'Valid email format'
    ];
}

/**
 * Validate domain existence and mail server availability
 */
function validateEmailDomain($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return [
            'valid' => false,
            'level' => 'domain',
            'message' => 'Invalid email format'
        ];
    }
    
    $domain = $parts[1];
    
    // Check if domain exists (DNS A or AAAA record)
    if (!checkdnsrr($domain, 'A') && !checkdnsrr($domain, 'AAAA')) {
        return [
            'valid' => false,
            'level' => 'domain',
            'message' => 'Domain does not exist'
        ];
    }
    
    // Check if domain accepts email (MX record)
    if (!checkdnsrr($domain, 'MX')) {
        // Some domains use A record for mail
        if (!checkdnsrr($domain, 'A')) {
            return [
                'valid' => false,
                'level' => 'domain',
                'message' => 'Domain does not accept email'
            ];
        }
    }
    
    // Get MX records for additional info
    $mxRecords = [];
    $mxWeights = [];
    if (getmxrr($domain, $mxRecords, $mxWeights)) {
        return [
            'valid' => true,
            'level' => 'domain',
            'message' => 'Domain accepts email',
            'mx_records' => array_slice($mxRecords, 0, 3) // First 3 MX records
        ];
    }
    
    return [
        'valid' => true,
        'level' => 'domain',
        'message' => 'Domain appears valid'
    ];
}

/**
 * Check for disposable/temporary email providers
 */
function checkDisposableEmail($email) {
    $parts = explode('@', $email);
    $domain = strtolower($parts[1]);
    
    // Common disposable email domains
    $disposableDomains = [
        '10minutemail.com', '10minutemail.net', 'tempmail.org', 'guerrillamail.com',
        'mailinator.com', 'throwaway.email', 'temp-mail.org', 'getnada.com',
        'maildrop.cc', 'sharklasers.com', 'yopmail.com', 'mohmal.com',
        'emailondeck.com', 'fakeinbox.com', 'spamgourmet.com', 'trashmail.com'
    ];
    
    if (in_array($domain, $disposableDomains)) {
        return [
            'valid' => false,
            'level' => 'policy',
            'message' => 'Disposable email addresses are not allowed'
        ];
    }
    
    return [
        'valid' => true,
        'level' => 'policy',
        'message' => 'Email provider accepted'
    ];
}

/**
 * SMTP verification to check if email actually exists
 * Note: This is more intensive and may be blocked by some servers
 */
function verifyEmailSMTP($email, $timeout = 10) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return [
            'valid' => false,
            'level' => 'smtp',
            'message' => 'Invalid email format'
        ];
    }
    
    $user = $parts[0];
    $domain = $parts[1];
    
    // Get MX records
    $mxRecords = [];
    $mxWeights = [];
    if (!getmxrr($domain, $mxRecords, $mxWeights)) {
        // Try A record as fallback
        if (!checkdnsrr($domain, 'A')) {
            return [
                'valid' => false,
                'level' => 'smtp',
                'message' => 'No mail servers found'
            ];
        }
        $mxRecords = [$domain];
        $mxWeights = [10];
    }
    
    // Sort MX records by priority (lower weight = higher priority)
    array_multisort($mxWeights, $mxRecords);
    
    // Try to connect to mail servers
    foreach (array_slice($mxRecords, 0, 3) as $mxRecord) { // Try first 3 MX records
        $result = testSMTPConnection($mxRecord, $email, $timeout);
        if ($result['tested']) {
            return $result;
        }
    }
    
    return [
        'valid' => null,
        'level' => 'smtp',
        'message' => 'Could not verify email existence (servers unavailable)'
    ];
}

/**
 * Test SMTP connection to verify email
 */
function testSMTPConnection($mxRecord, $email, $timeout = 10) {
    $socket = @fsockopen($mxRecord, 25, $errno, $errstr, $timeout);
    
    if (!$socket) {
        return [
            'tested' => false,
            'message' => "Could not connect to {$mxRecord}"
        ];
    }
    
    try {
        // Read initial response
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return [
                'tested' => false,
                'message' => 'Mail server not ready'
            ];
        }
        
        // HELO command
        fputs($socket, "HELO bulwagang-ncst.local\r\n");
        $response = fgets($socket, 1024);
        
        // MAIL FROM command
        fputs($socket, "MAIL FROM: <noreply@bulwagang-ncst.local>\r\n");
        $response = fgets($socket, 1024);
        
        // RCPT TO command - this checks if the email exists
        fputs($socket, "RCPT TO: <{$email}>\r\n");
        $response = fgets($socket, 1024);
        
        // QUIT command
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        // Analyze response
        $responseCode = substr($response, 0, 3);
        
        if ($responseCode === '250') {
            return [
                'tested' => true,
                'valid' => true,
                'level' => 'smtp',
                'message' => 'Email address exists and can receive mail'
            ];
        } elseif (in_array($responseCode, ['550', '551', '553', '554'])) {
            return [
                'tested' => true,
                'valid' => false,
                'level' => 'smtp',
                'message' => 'Email address does not exist'
            ];
        } else {
            return [
                'tested' => true,
                'valid' => null,
                'level' => 'smtp',
                'message' => 'Could not determine email existence (server policy)'
            ];
        }
        
    } catch (Exception $e) {
        fclose($socket);
        return [
            'tested' => false,
            'message' => 'SMTP test failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Comprehensive email validation for audition forms
 */
function validateAuditionEmail($email) {
    $results = [];
    
    // Step 1: Format validation
    $formatResult = validateEmailFormat($email);
    $results[] = $formatResult;
    if (!$formatResult['valid']) {
        return [
            'valid' => false,
            'message' => $formatResult['message'],
            'details' => $results
        ];
    }
    
    // Step 2: Domain validation
    $domainResult = validateEmailDomain($email);
    $results[] = $domainResult;
    if (!$domainResult['valid']) {
        return [
            'valid' => false,
            'message' => $domainResult['message'],
            'details' => $results
        ];
    }
    
    // Step 3: Check for disposable emails
    $disposableResult = checkDisposableEmail($email);
    $results[] = $disposableResult;
    if (!$disposableResult['valid']) {
        return [
            'valid' => false,
            'message' => $disposableResult['message'],
            'details' => $results
        ];
    }
    
    // Step 4: SMTP verification (optional, can be disabled for performance)
    $enableSMTPCheck = true; // Set to false to disable SMTP verification
    
    if ($enableSMTPCheck) {
        $smtpResult = verifyEmailSMTP($email, 5); // 5 second timeout
        $results[] = $smtpResult;
        
        if (isset($smtpResult['valid']) && $smtpResult['valid'] === false) {
            return [
                'valid' => false,
                'message' => $smtpResult['message'],
                'details' => $results
            ];
        }
    }
    
    return [
        'valid' => true,
        'message' => 'Email address is valid and deliverable',
        'details' => $results
    ];
}

/**
 * Quick email validation (format + domain only)
 * Use this for better performance when SMTP check is not needed
 */
function validateEmailQuick($email) {
    // Format validation
    $formatResult = validateEmailFormat($email);
    if (!$formatResult['valid']) {
        return $formatResult;
    }
    
    // Domain validation
    $domainResult = validateEmailDomain($email);
    if (!$domainResult['valid']) {
        return $domainResult;
    }
    
    // Disposable email check
    $disposableResult = checkDisposableEmail($email);
    if (!$disposableResult['valid']) {
        return $disposableResult;
    }
    
    return [
        'valid' => true,
        'message' => 'Email appears valid and deliverable'
    ];
}
?>
