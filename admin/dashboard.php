<?php
// admin/dashboard.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

// Fetch quick summary statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$today_appts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$pending_queue = $pdo->query("SELECT COUNT(*) FROM queue WHERE status = 'waiting'")->fetchColumn();

$page_title = "Admin Dashboard - Unicare";
$extra_css = "../assets/css/dashboards/admin.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Administrator Control Center</h1>
        <p>Welcome back, Admin. Here is today's clinic summary.</p>
    </div>

    <!-- Metrics Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <h3>Total Users</h3>
            <p class="metric-value"><?= $total_users; ?></p>
        </div>
        <div class="metric-card">
            <h3>Registered Patients</h3>
            <p class="metric-value"><?= $total_patients; ?></p>
        </div>
        <div class="metric-card">
            <h3>Today's Appointments</h3>
            <p class="metric-value"><?= $today_appts; ?></p>
        </div>
        <div class="metric-card">
            <h3>Patients Waiting</h3>
            <p class="metric-value"><?= $pending_queue; ?></p>
        </div>
    </div>

    <div class="admin-quick-actions">
        <h2>Quick Management</h2>
        <div class="action-buttons">
            <a href="user_management.php" class="btn btn-primary">Manage User Accounts & Passwords</a>
            <a href="calendar_management.php" class="btn btn-secondary">Set Clinic Closures</a>
            <a href="medical_records.php" class="btn btn-secondary">View All Medical Records</a>
        </div>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>