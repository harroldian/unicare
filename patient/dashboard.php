<?php
// patient/dashboard.php
require_once '../includes/auth_check.php';
check_role(['patient']);
require_once '../config/db.php';

$patient_id = $_SESSION['patient_id'];

// Fetch latest active appointment
$stmt = $pdo->prepare("
    SELECT a.*, q.queue_number, q.status AS queue_status, d.full_name AS doctor_name 
    FROM appointments a 
    LEFT JOIN queue q ON a.appointment_id = q.appointment_id 
    LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = :pid AND a.status IN ('pending', 'approved')
    ORDER BY a.appointment_date DESC LIMIT 1
");
$stmt->execute(['pid' => $patient_id]);
$active_appointment = $stmt->fetch();

$page_title = "Patient Dashboard - Unicare";
$extra_css = "../assets/css/dashboards/patient.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Patient Portal Overview</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']); ?>! Manage your clinic appointments and queue status here.</p>
    </div>

    <div class="patient-grid">
        <div class="card">
            <h3>Active Appointment Status</h3>
            <?php if ($active_appointment): ?>
                <div class="appointment-card-info">
                    <p><strong>Date:</strong> <?= $active_appointment['appointment_date']; ?> at <?= $active_appointment['appointment_time']; ?></p>
                    <p><strong>Reason:</strong> <?= htmlspecialchars($active_appointment['reason']); ?></p>
                    <p><strong>Status:</strong> <span class="badge status-<?= $active_appointment['status']; ?>"><?= ucfirst($active_appointment['status']); ?></span></p>
                    <?php if ($active_appointment['queue_number']): ?>
                        <p><strong>Queue Ticket #:</strong> <span class="queue-num">#<?= $active_appointment['queue_number']; ?></span></p>
                        <p><strong>Queue State:</strong> <?= ucfirst($active_appointment['queue_status']); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>You have no active appointments right now.</p>
                <a href="book_appointment.php" class="btn btn-primary" style="margin-top: 15px;">Book New Appointment</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <div class="action-links" style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <a href="book_appointment.php" class="btn btn-primary">📅 Book Clinic Appointment</a>
                <a href="live_queue.php" class="btn btn-secondary">⏳ View Live Waiting Queue</a>
                <a href="history.php" class="btn btn-secondary">📜 View Medical History</a>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>