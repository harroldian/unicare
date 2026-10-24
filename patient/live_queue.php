<?php
// patient/live_queue.php
require_once '../includes/auth_check.php';
check_role(['patient']);
require_once '../config/db.php';

$patient_id = $_SESSION['patient_id'];

// Fetch patient's active queue ticket for today
$stmt = $pdo->prepare("
    SELECT q.*, a.appointment_date 
    FROM queue q
    JOIN appointments a ON q.appointment_id = a.appointment_id
    WHERE a.patient_id = :pid AND DATE(q.updated_at) = CURDATE()
");
$stmt->execute(['pid' => $patient_id]);
$my_ticket = $stmt->fetch();

$page_title = "Live Queue Status - Unicare";
$extra_css = "../assets/css/dashboards/patient.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Live Queue Tracker</h1>
        <p>Monitor real-time clinic queue updates and live serving tickets.</p>
    </div>

    <div class="card" style="text-align: center; max-width: 500px; margin: 0 auto;">
        <h3>Your Ticket Number Today</h3>
        <?php if ($my_ticket): ?>
            <span class="queue-num" style="font-size: 5rem;">#<?= $my_ticket['queue_number']; ?></span>
            <p>Current Queue Status: <span class="badge status-<?= $my_ticket['status']; ?>"><?= ucfirst($my_ticket['status']); ?></span></p>
        <?php else: ?>
            <p style="margin: 20px 0; color: var(--text-secondary);">You do not have an active queue ticket checked in for today.</p>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>