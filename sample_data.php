<?php
// Sample data insertion script for testing the admin dashboard
require_once 'includes/db_connection.php';

// Sample audition data
$sample_auditions = [
    [
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'maria.santos@email.com',
        'phone' => '09123456789',
        'category' => 'singer',
        'details' => 'I have been singing for 8 years and specialize in pop and ballad songs. I have performed in local events and school competitions.',
        'status' => 'pending'
    ],
    [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan.delacruz@email.com',
        'phone' => '09234567890',
        'category' => 'dancer',
        'details' => 'Contemporary and hip-hop dancer with 5 years of experience. Member of our school dance troupe.',
        'status' => 'approved'
    ],
    [
        'first_name' => 'Anna',
        'last_name' => 'Garcia',
        'email' => 'anna.garcia@email.com',
        'phone' => '09345678901',
        'category' => 'solo-musician',
        'details' => 'Guitar player specializing in acoustic and classical music. Can also play piano.',
        'status' => 'pending'
    ],
    [
        'first_name' => 'Miguel',
        'last_name' => 'Rodriguez',
        'email' => 'miguel.rodriguez@email.com',
        'phone' => '09456789012',
        'category' => 'band',
        'details' => 'Lead vocalist and guitarist of "The Echoes" band. We play rock and alternative music.',
        'status' => 'rejected'
    ],
    [
        'first_name' => 'Sofia',
        'last_name' => 'Reyes',
        'email' => 'sofia.reyes@email.com',
        'phone' => '09567890123',
        'category' => 'singer',
        'details' => 'R&B and soul singer. I write my own songs and have been performing since high school.',
        'status' => 'pending'
    ],
    [
        'first_name' => 'Carlos',
        'last_name' => 'Mendoza',
        'email' => 'carlos.mendoza@email.com',
        'phone' => '09678901234',
        'category' => 'dancer',
        'details' => 'Breakdancer and choreographer. I teach dance classes on weekends.',
        'status' => 'approved'
    ],
    [
        'first_name' => 'Isabella',
        'last_name' => 'Torres',
        'email' => 'isabella.torres@email.com',
        'phone' => '09789012345',
        'category' => 'solo-musician',
        'details' => 'Violinist with classical training. Also play in the school orchestra.',
        'status' => 'pending'
    ],
    [
        'first_name' => 'Diego',
        'last_name' => 'Morales',
        'email' => 'diego.morales@email.com',
        'phone' => '09890123456',
        'category' => 'band',
        'details' => 'Drummer for "Rhythm Nation" band. We specialize in funk and jazz fusion.',
        'status' => 'pending'
    ]
];

try {
    // Prepare the insert statement
    $stmt = $pdo->prepare("
        INSERT INTO pending_audition 
        (first_name, last_name, email, phone, category, details, status, submission_date) 
        VALUES 
        (:first_name, :last_name, :email, :phone, :category, :details, :status, :submission_date)
    ");

    $inserted_count = 0;
    
    foreach ($sample_auditions as $audition) {
        // Add random submission dates within the last 30 days
        $random_days = rand(0, 30);
        $random_hours = rand(0, 23);
        $random_minutes = rand(0, 59);
        $submission_date = date('Y-m-d H:i:s', strtotime("-{$random_days} days -{$random_hours} hours -{$random_minutes} minutes"));
        
        $audition['submission_date'] = $submission_date;
        
        // Execute the statement
        if ($stmt->execute($audition)) {
            $inserted_count++;
            echo "✓ Inserted: {$audition['first_name']} {$audition['last_name']} - {$audition['category']}<br>";
        } else {
            echo "✗ Failed to insert: {$audition['first_name']} {$audition['last_name']}<br>";
        }
    }
    
    echo "<br><strong>Successfully inserted {$inserted_count} sample auditions!</strong><br>";
    echo "<br><a href='admin.php' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Admin Dashboard</a>";
    
} catch(PDOException $e) {
    echo "Error inserting sample data: " . $e->getMessage();
}
?>
