<?php
// doctor/consultation.php
require_once '../includes/auth_check.php';
check_role(['doctor']);
require_once '../config/db.php';

$appointment_id = $_GET['appointment_id'] ?? null;
if (!$appointment_id) {
    header("Location: dashboard.php");
    exit();
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis    = trim($_POST['diagnosis'] ?? '');
    $prescription = trim($_POST['prescription'] ?? '');

    $stmt = $pdo->prepare("
        UPDATE medical_records 
        SET diagnosis = :diag, prescription = :pres 
        WHERE appointment_id = :aid
    ");
    $stmt->execute([
        'diag' => $diagnosis,
        'pres' => $prescription,
        'aid'  => $appointment_id
    ]);

    // Update appointment status to completed
    $up_app = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = :aid");
    $up_app->execute(['aid' => $appointment_id]);

    // Update queue status to completed
    $up_q = $pdo->prepare("UPDATE queue SET status = 'completed' WHERE appointment_id = :aid");
    $up_q->execute(['aid' => $appointment_id]);

    $success = "Consultation notes saved successfully!";
}

// Fetch record details
$stmt = $pdo->prepare("
    SELECT m.*, p.full_name, p.school_id, a.reason 
    FROM medical_records m
    JOIN patients p ON m.patient_id = p.patient_id
    JOIN appointments a ON m.appointment_id = a.appointment_id
    WHERE m.appointment_id = :aid
");
$stmt->execute(['aid' => $appointment_id]);
$record = $stmt->fetch();

$page_title = "Consultation Workspace - Unicare";
$extra_css = "../assets/css/dashboards/doctor.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Patient Consultation Workspace</h1>
        <p>Patient: <strong><?= htmlspecialchars($record['full_name'] ?? 'Patient'); ?></strong> (<?= htmlspecialchars($record['school_id'] ?? ''); ?>)</p>
    </div>

    <div class="card" style="max-width: 700px;">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 15px;">Return to Doctor Queue</a>
        <?php else: ?>

        <div class="patient-info-summary">
            <div><strong>Vitals - BP:</strong> <?= $record['blood_pressure'] ?? 'N/A'; ?></div>
            <div><strong>Temp:</strong> <?= $record['temperature'] ? $record['temperature'] . ' °C' : 'N/A'; ?></div>
            <div><strong>Weight:</strong> <?= $record['weight'] ? $record['weight'] . ' kg' : 'N/A'; ?></div>
            <div><strong>Chief Complaint:</strong> <?= htmlspecialchars($record['reason'] ?? ''); ?></div>
        </div>

        <form action="consultation.php?appointment_id=<?= $appointment_id; ?>" method="POST" class="auth-form">
            <div class="form-group">
                <label for="diagnosis">Clinical Diagnosis</label>
                <textarea id="diagnosis" name="diagnosis" rows="4" required placeholder="Enter clinical assessment and diagnosis..."><?= htmlspecialchars($record['diagnosis'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="prescription">Prescription / Treatment Plan</label>
                <textarea id="prescription" name="prescription" rows="3" placeholder="Enter prescribed medication or care instructions..."><?= htmlspecialchars($record['prescription'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Complete Consultation & Save Record</button>
        </form>

        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>