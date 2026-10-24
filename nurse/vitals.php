<?php
// nurse/vitals.php
require_once '../includes/auth_check.php';
check_role(['nurse']);
require_once '../config/db.php';

$appointment_id = $_GET['appointment_id'] ?? null;
if (!$appointment_id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch Appointment Details
$stmt = $pdo->prepare("
    SELECT a.*, p.full_name, p.school_id, p.patient_id 
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE a.appointment_id = :aid
");
$stmt->execute(['aid' => $appointment_id]);
$appt = $stmt->fetch();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bp    = trim($_POST['blood_pressure'] ?? '');
    $temp  = trim($_POST['temperature'] ?? '');
    $weight= trim($_POST['weight'] ?? '');

    // Check if record already exists or insert initial vitals
    $rec_stmt = $pdo->prepare("SELECT record_id FROM medical_records WHERE appointment_id = :aid");
    $rec_stmt->execute(['aid' => $appointment_id]);
    $rec = $rec_stmt->fetch();

    if ($rec) {
        $up = $pdo->prepare("UPDATE medical_records SET blood_pressure = :bp, temperature = :temp, weight = :weight WHERE appointment_id = :aid");
        $up->execute(['bp' => $bp, 'temp' => $temp, 'weight' => $weight, 'aid' => $appointment_id]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO medical_records (appointment_id, patient_id, doctor_id, blood_pressure, temperature, weight) 
            VALUES (:aid, :pid, 1, :bp, :temp, :weight)
        ");
        $ins->execute([
            'aid'  => $appointment_id,
            'pid'  => $appt['patient_id'],
            'bp'   => $bp,
            'temp' => $temp,
            'weight' => $weight
        ]);
    }
    $success = "Vitals logged successfully!";
}

$page_title = "Record Vitals - Unicare";
$extra_css = "../assets/css/dashboards/nurse.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Patient Triage - Record Vital Signs</h1>
        <p>Patient Name: <strong><?= htmlspecialchars($appt['full_name']); ?></strong> (<?= htmlspecialchars($appt['school_id']); ?>)</p>
    </div>

    <div class="card" style="max-width: 500px;">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 15px;">Return to Queue Control</a>
        <?php else: ?>

        <form action="vitals.php?appointment_id=<?= $appointment_id; ?>" method="POST" class="auth-form">
            <div class="form-group">
                <label for="blood_pressure">Blood Pressure (mmHg)</label>
                <input type="text" id="blood_pressure" name="blood_pressure" placeholder="e.g. 120/80" required>
            </div>

            <div class="form-group">
                <label for="temperature">Body Temperature (°C)</label>
                <input type="number" step="0.1" id="temperature" name="temperature" placeholder="e.g. 36.6" required>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" step="0.1" id="weight" name="weight" placeholder="e.g. 65.5" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Vital Signs</button>
        </form>

        <?php endif; ?>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>