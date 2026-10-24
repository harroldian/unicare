<?php
// patient/history.php
require_once '../includes/auth_check.php';
check_role(['patient']);
require_once '../config/db.php';

$patient_id = $_SESSION['patient_id'];

$stmt = $pdo->prepare("
    SELECT m.*, d.full_name AS doctor_name, a.appointment_date 
    FROM medical_records m
    JOIN appointments a ON m.appointment_id = a.appointment_id
    LEFT JOIN doctors d ON m.doctor_id = d.doctor_id
    WHERE m.patient_id = :pid
    ORDER BY m.created_at DESC
");
$stmt->execute(['pid' => $patient_id]);
$records = $stmt->fetchAll();

$page_title = "Medical History - Unicare";
$extra_css = "../assets/css/dashboards/patient.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>My Medical History</h1>
        <p>View your past consultations, vital signs, diagnoses, and prescriptions.</p>
    </div>

    <div class="table-responsive">
        <table class="unicare-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>BP</th>
                    <th>Temp (°C)</th>
                    <th>Weight (kg)</th>
                    <th>Diagnosis</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): foreach ($records as $r): ?>
                <tr>
                    <td><?= $r['appointment_date']; ?></td>
                    <td><?= htmlspecialchars($r['doctor_name'] ?? 'Clinic Doctor'); ?></td>
                    <td><?= htmlspecialchars($r['blood_pressure'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($r['temperature'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($r['weight'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($r['diagnosis'] ?? 'Pending / None'); ?></td>
                    <td><?= htmlspecialchars($r['prescription'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align: center;">No medical history records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>