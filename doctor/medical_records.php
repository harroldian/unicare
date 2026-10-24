<?php
// admin/medical_records.php & doctor/medical_records.php
require_once '../includes/auth_check.php';
check_role(['admin', 'doctor']);
require_once '../config/db.php';

$page_title = "Medical Records Audit - Unicare";
$extra_css = "../assets/css/dashboards/admin.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Clinic Medical Records Archive</h1>
        <p>Comprehensive audit log of all patient consultations, diagnoses, and vitals records.</p>
    </div>

    <div class="search-bar-container">
        <input type="text" id="record-search-input" placeholder="Search by patient name, school ID, or diagnosis..." class="search-input">
    </div>

    <div class="table-responsive">
        <table class="unicare-table" id="records-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient Name</th>
                    <th>School ID</th>
                    <th>BP / Temp / Weight</th>
                    <th>Diagnosis</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT m.*, p.full_name, p.school_id, a.appointment_date 
                    FROM medical_records m
                    JOIN patients p ON m.patient_id = p.patient_id
                    JOIN appointments a ON m.appointment_id = a.appointment_id
                    ORDER BY m.created_at DESC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $row['appointment_date']; ?></td>
                    <td><?= htmlspecialchars($row['full_name']); ?></td>
                    <td><?= htmlspecialchars($row['school_id']); ?></td>
                    <td><?= htmlspecialchars($row['blood_pressure'] ?? '-'); ?> / <?= $row['temperature'] ? $row['temperature'] . '°C' : '-'; ?></td>
                    <td><?= htmlspecialchars($row['diagnosis'] ?? 'Pending'); ?></td>
                    <td><?= htmlspecialchars($row['prescription'] ?? '-'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>