<?php
// Audition form submission handler
header('Content-Type: application/json');

// Enable error logging for debugging
error_log("=== AUDITION FORM SUBMISSION ===");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Include database connection and email validator
require_once 'includes/db_connection.php';
require_once 'includes/email-validator.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email', 'phone', 'category', 'birthDate', 'gender', 'studentLevel'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Sanitize and validate input data
$first_name    = trim($_POST['firstName']);
$last_name     = trim($_POST['lastName']);
$email         = trim($_POST['email']);
$phone         = trim($_POST['phone']);
$category      = trim($_POST['category']);
$birth_date    = isset($_POST['birthDate']) ? trim($_POST['birthDate']) : null; // YYYY-MM-DD
$gender        = isset($_POST['gender']) ? trim($_POST['gender']) : null;
$student_level = isset($_POST['studentLevel']) ? trim($_POST['studentLevel']) : 'other';
$shs_strand    = isset($_POST['shsStrand']) ? trim($_POST['shsStrand']) : null;
$college_course= isset($_POST['collegeCourse']) ? trim($_POST['collegeCourse']) : null;
$details       = isset($_POST['details']) ? trim($_POST['details']) : '';

// Comprehensive email validation
$emailValidation = validateAuditionEmail($email);
if (!$emailValidation['valid']) {
    echo json_encode([
        'success' => false,
        'message' => 'Email validation failed: ' . $emailValidation['message'],
        'field' => 'email'
    ]);
    exit;
}

// Check if email already exists in database
try {
    $stmt = $pdo->prepare("SELECT id FROM pending_audition WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'An audition with this email address has already been submitted.',
            'field' => 'email'
        ]);
        exit;
    }
} catch(PDOException $e) {
    error_log("Database error checking email: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    exit;
}

// Validate category
$valid_categories = ['singer', 'dancer', 'solo-musician', 'band'];
if (!in_array($category, $valid_categories)) {
    echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
    exit;
}

// Validate gender
$valid_genders = ['male','female','other','prefer_not_to_say'];
if ($gender && !in_array($gender, $valid_genders)) {
    echo json_encode(['success' => false, 'message' => 'Invalid gender value']);
    exit;
}

// Validate student level and conditional fields
$valid_levels = ['shs','college','other'];
if (!in_array($student_level, $valid_levels)) {
    echo json_encode(['success' => false, 'message' => 'Invalid student level value']);
    exit;
}

$valid_strands = [
    'ABM','HUMSS','STEM','Culinary Arts (TVL-HE)','Hotel & Restaurant Services (TVL-HE)'
];

$valid_courses = [
 'ACT','AOM','AB Comm','BSA','BS Arch','BSBA-FM','BSBA-MM','BSBA-OM','BSCrim','BSCA','BSCpE','BSCS','BSECE','BSEntrep','BSHM','BSIE','BSISM','BSIT','BSMA','BSOA','BSPsy','BSPA','BSREM','BSTM','BSEd-Eng','BSEd-Fil','BSEd-Math','BSEd-SS'
];

if ($student_level === 'shs') {
    if (!$shs_strand || !in_array($shs_strand, $valid_strands)) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid SHS strand']);
        exit;
    }
    $college_course = null; // ensure only relevant field is set
}

if ($student_level === 'college') {
    if (!$college_course || !in_array($college_course, $valid_courses)) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid college course']);
        exit;
    }
    $shs_strand = null; // ensure only relevant field is set
}

// Validate birth date (basic YYYY-MM-DD check)
if ($birth_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid birth date']);
    exit;
}

// Check for duplicate email (optional - you can remove this if you want to allow duplicates)
try {
    $check_stmt = $pdo->prepare("SELECT id FROM pending_audition WHERE email = :email");
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'An audition with this email address has already been submitted.'
        ]);
        exit;
    }
} catch(PDOException $e) {
    error_log("Error checking duplicate email: " . $e->getMessage());
}

// Insert audition data
try {
    $stmt = $pdo->prepare("
        INSERT INTO pending_audition 
        (first_name, last_name, email, phone, category, details, birth_date, gender, student_level, shs_strand, college_course, status, submission_date) 
        VALUES 
        (:first_name, :last_name, :email, :phone, :category, :details, :birth_date, :gender, :student_level, :shs_strand, :college_course, 'pending', NOW())
    ");
    
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':details', $details);
    $stmt->bindParam(':birth_date', $birth_date);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':student_level', $student_level);
    $stmt->bindParam(':shs_strand', $shs_strand);
    $stmt->bindParam(':college_course', $college_course);
    
    if ($stmt->execute()) {
        $audition_id = $pdo->lastInsertId();
        
        // Log successful submission
        error_log("New audition submitted: ID {$audition_id}, Name: {$first_name} {$last_name}, Email: {$email}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Your audition form has been submitted successfully!',
            'audition_id' => $audition_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to submit audition. Please try again.'
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Database error during audition submission: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
}
?>
