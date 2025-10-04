<?php
// Set page-specific variables
$current_page = 'admin';
$nav_type = 'bootstrap';

// Include database connection
require_once 'includes/db_connection.php';

// Check if user is logged in and has admin/moderator access
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: login.php?error=Please login to access the admin panel');
    exit;
}

// Check if user has admin or moderator role
$allowedRoles = ['admin', 'moderator'];
if (!in_array($_SESSION['user_role'], $allowedRoles)) {
    header('Location: view.php?error=Access denied. Admin or moderator access required.');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$currentUserName = $_SESSION['user_name'];
$currentUserRole = $_SESSION['user_role'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'update_status':
            $id = (int) $_POST['id'];
            $status = $_POST['status'];
            $success = updateAuditionStatus($pdo, $id, $status);
            echo json_encode(['success' => $success]);
            exit;

        case 'delete_audition':
            $id = (int) $_POST['id'];
            $success = deleteAudition($pdo, $id);
            echo json_encode(['success' => $success]);
            exit;

        case 'get_auditions':
            $auditions = getAllAuditions($pdo);
            echo json_encode(['data' => $auditions]);
            exit;
    }
}

// Get audition data for initial page load
$auditions = getAllAuditions($pdo);

// Calculate statistics
$total_auditions = count($auditions);
$pending_count = count(array_filter($auditions, function ($a) {
    return $a['status'] === 'pending';
}));
$approved_count = count(array_filter($auditions, function ($a) {
    return $a['status'] === 'approved';
}));
$rejected_count = count(array_filter($auditions, function ($a) {
    return $a['status'] === 'rejected';
}));

// Include head component
include 'includes/head.php';
?>


<!-- Main Content -->
<div class="container-fluid py-4">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Manage audition submissions and track applications</p>
            <div class="user-info" style="margin-top: 15px;">
                <span class="role-badge role-<?php echo $currentUserRole; ?>">
                    <?php
                    $roleIcons = ['admin' => '🔐', 'moderator' => '👮‍♀️'];
                    echo $roleIcons[$currentUserRole] . ' ' . ucfirst($currentUserRole);
                    ?>
                </span>
                <span style="margin-left: 10px; color: #666;">
                    Welcome, <?php echo htmlspecialchars($currentUserName); ?>
                </span>
                <a href="home.php" class="refresh-btn" style="margin-left: 12px; text-decoration: none;">
                    ← Back to Home
                </a>
                <a href="api/logout.php" style="margin-left: 12px; color: #dc3545; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <h3><?php echo $total_auditions; ?></h3>
                    <p>Total Auditions</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Review</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <h3><?php echo $approved_count; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <h3><?php echo $rejected_count; ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <!-- Auditions Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Audition Submissions</h2>
                <button class="refresh-btn" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <div class="table-responsive">
                <table id="auditionsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditions as $audition): ?>
                            <tr data-id="<?php echo $audition['id']; ?>">
                                <td><?php echo $audition['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($audition['first_name'] . ' ' . $audition['last_name']); ?></strong>
                                    <?php if (!empty($audition['details'])): ?>
                                        <br><small class="text-muted"
                                            title="<?php echo htmlspecialchars($audition['details']); ?>">
                                            <?php echo htmlspecialchars(substr($audition['details'], 0, 50)) . (strlen($audition['details']) > 50 ? '...' : ''); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($audition['email']); ?>">
                                        <?php echo htmlspecialchars($audition['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($audition['phone']); ?>">
                                        <?php echo htmlspecialchars($audition['phone']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="category-badge category-<?php echo $audition['category']; ?>">
                                        <?php echo ucwords(str_replace('-', ' ', $audition['category'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $audition['status']; ?>">
                                        <?php echo ucfirst($audition['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('M j, Y', strtotime($audition['submission_date'])); ?><br>
                                        <?php echo date('g:i A', strtotime($audition['submission_date'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($audition['status'] !== 'approved'): ?>
                                            <button class="btn-action btn-approve"
                                                onclick="updateStatus(<?php echo $audition['id']; ?>, 'approved')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($audition['status'] !== 'rejected'): ?>
                                            <button class="btn-action btn-reject"
                                                onclick="updateStatus(<?php echo $audition['id']; ?>, 'rejected')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn-action btn-delete"
                                            onclick="deleteAudition(<?php echo $audition['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer component
include 'includes/footer.php';
?>