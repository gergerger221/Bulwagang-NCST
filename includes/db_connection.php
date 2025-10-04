<?php
// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bulwagan_db";

try {
    // Create connection using PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Function to get all audition submissions
function getAllAuditions($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                first_name,
                last_name,
                email,
                phone,
                category,
                details,
                status,
                submission_date,
                created_at
            FROM pending_audition 
            ORDER BY submission_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Error fetching auditions: " . $e->getMessage());
        return [];
    }
}

// Function to update audition status
function updateAuditionStatus($pdo, $id, $status) {
    try {
        $stmt = $pdo->prepare("
            UPDATE pending_audition 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error updating audition status: " . $e->getMessage());
        return false;
    }
}

// Function to delete audition
function deleteAudition($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM pending_audition WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error deleting audition: " . $e->getMessage());
        return false;
    }
}
?>
