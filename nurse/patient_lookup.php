<?php
// nurse/patient_lookup.php
require_once '../includes/auth_check.php';
check_role(['nurse']);
require_once '../config/db.php';

$page_title = "Patient Record Lookup - Unicare";
$extra_css = "../assets/css/dashboards/nurse.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Patient Medical Record Lookup</h1>
        <p>Search patient archives and past clinic visits instantly.</p>
    </div>

    <div class="search-bar-container">
        <input type="text" id="record-search-input" placeholder="Search by patient name or school ID..." class="search-input">
    </div>

    <div class="table-responsive">
        <table class="unicare-table" id="records-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>School ID</th>
                    <th>Patient Name</th>
                    <th>Blood Pressure</th>
                    <th>Temperature</th>
                    <th>Diagnosis</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
                    SELECT m.*, p.full_name, p.school_id, a.appointment_date 
                    FROM medical_records m
                    JOIN patients p ON m.patient_id = p.patient_id
                    JOIN appointments a ON m.appointment_id = a.appointment_id
                    ORDER BY m.created_at DESC LIMIT 20
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $row['appointment_date']; ?></td>
                    <td><?= htmlspecialchars($row['school_id']); ?></td>
                    <td><?= htmlspecialchars($row['full_name']); ?></td>
                    <td><?= htmlspecialchars($row['blood_pressure'] ?? '-'); ?></td>
                    <td><?= $row['temperature'] ? $row['temperature'] . '°C' : '-'; ?></td>
                    <td><?= htmlspecialchars($row['diagnosis'] ?? 'Pending'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>