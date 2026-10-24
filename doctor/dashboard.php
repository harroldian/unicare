<?php
// doctor/dashboard.php
require_once '../includes/auth_check.php';
check_role(['doctor']);
require_once '../config/db.php';

$page_title = "Doctor Workspace - Unicare";
$extra_css = "../assets/css/dashboards/doctor.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Doctor Clinical Station</h1>
        <p>Manage daily consultations and record clinical diagnoses.</p>
    </div>

    <div style="margin-bottom: 20px;">
        <a href="medical_records.php" class="btn btn-primary">🔍 Search Medical Record Archive</a>
    </div>

    <div class="card">
        <h3>Active Consultation Queue</h3>
        <div class="table-responsive">
            <table class="unicare-table">
                <thead>
                    <tr>
                        <th>Queue #</th>
                        <th>Patient Name</th>
                        <th>Reason for Visit</th>
                        <th>BP</th>
                        <th>Temp</th>
                        <th>Weight</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT q.queue_id, q.queue_number, q.status AS queue_status, a.appointment_id, p.full_name, m.blood_pressure, m.temperature, m.weight 
                        FROM queue q
                        JOIN appointments a ON q.appointment_id = a.appointment_id
                        JOIN patients p ON a.patient_id = p.patient_id
                        LEFT JOIN medical_records m ON a.appointment_id = m.appointment_id
                        WHERE DATE(q.updated_at) = CURDATE() AND q.status IN ('waiting', 'in_consultation')
                        ORDER BY q.queue_number ASC
                    ");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><strong>#<?= $row['queue_number']; ?></strong></td>
                        <td><?= htmlspecialchars($row['full_name']); ?></td>
                        <td><?= htmlspecialchars($row['blood_pressure'] ?? 'Not Logged'); ?></td>
                        <td><?= $row['blood_pressure'] ?? '-'; ?></td>
                        <td><?= $row['temperature'] ? $row['temperature'] . ' °C' : '-'; ?></td>
                        <td><?= $row['weight'] ? $row['weight'] . ' kg' : '-'; ?></td>
                        <td>
                            <a href="consultation.php?appointment_id=<?= $row['appointment_id']; ?>" class="btn btn-sm btn-primary">Start Consultation</a>
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