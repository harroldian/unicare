<?php
// nurse/dashboard.php
require_once '../includes/auth_check.php';
check_role(['nurse']);
require_once '../config/db.php';

// Handle Queue State Updates or Direct Walk-in Check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'check_in') {
        $appointment_id = $_POST['appointment_id'];
        
        // Generate Next Sequential Queue Number for Today
        $q_stmt = $pdo->query("SELECT MAX(queue_number) AS max_num FROM queue WHERE DATE(updated_at) = CURDATE()");
        $max_row = $q_stmt->fetch();
        $next_num = ($max_row['max_num'] ?? 0) + 1;

        // Insert into Queue
        $ins_q = $pdo->prepare("INSERT INTO queue (appointment_id, queue_number, status) VALUES (:aid, :qnum, 'waiting') ON DUPLICATE KEY UPDATE status='waiting'");
        $ins_q->execute(['aid' => $appointment_id, 'qnum' => $next_num]);

        // Update Appointment Status
        $up_app = $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE appointment_id = :aid");
        $up_app->execute(['aid' => $appointment_id]);
    }
}

$page_title = "Nurse Queue Station - Unicare";
$extra_css = "../assets/css/dashboards/nurse.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Nurse Queue Control & Triage Station</h1>
        <p>Check in arriving patients, manage queue tickets, and record patient vitals.</p>
    </div>

    <div class="nurse-actions-bar" style="margin-bottom: 20px;">
        <a href="patient_lookup.php" class="btn btn-primary">🔍 Patient Medical Record Search</a>
    </div>

    <!-- Incoming Appointments for Today -->
    <div class="card">
        <h3>Today's Patient Schedule (<?= date('Y-m-d'); ?>)</h3>
        <div class="table-responsive">
            <table class="unicare-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>School ID</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Queue #</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT a.*, p.full_name, p.school_id, q.queue_number, q.status AS queue_status 
                        FROM appointments a
                        JOIN patients p ON a.patient_id = p.patient_id
                        LEFT JOIN queue q ON a.appointment_id = q.appointment_id
                        WHERE a.appointment_date = CURDATE()
                        ORDER BY a.appointment_time ASC
                    ");
                    $stmt->execute();
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?= date('h:i A', strtotime($row['appointment_time'])); ?></td>
                        <td><?= htmlspecialchars($row['full_name']); ?></td>
                        <td><?= htmlspecialchars($row['school_id']); ?></td>
                        <td><?= htmlspecialchars($row['reason']); ?></td>
                        <td><span class="badge status-<?= $row['status']; ?>"><?= ucfirst($row['status']); ?></span></td>
                        <td><?= $row['queue_number'] ? '#' . $row['queue_number'] : 'Not Checked In'; ?></td>
                        <td>
                            <?php if (!$row['queue_number']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="check_in">
                                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Check In Patient</button>
                                </form>
                            <?php else: ?>
                                <a href="vitals.php?appointment_id=<?= $row['appointment_id']; ?>" class="btn btn-sm btn-primary">Log Vitals</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>