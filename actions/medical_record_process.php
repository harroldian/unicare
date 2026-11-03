<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['doctor'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../clinic_staff/dashboard.php');
    exit();
}

$operation = $_POST['operation'] ?? null;
if (
    !is_string($operation) ||
    !in_array($operation, ['create', 'delete'], true) ||
    !app_verify_csrf_token($_POST['csrf_token'] ?? null)
) {
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

if ($operation === 'delete') {
    $recordId = $_POST['record_id'] ?? null;
    if (!is_string($recordId) || !ctype_digit($recordId)) {
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmtRecord = $pdo->prepare(
            'SELECT appointment_id FROM medical_records WHERE record_id = ? FOR UPDATE'
        );
        $stmtRecord->execute([(int) $recordId]);
        $record = $stmtRecord->fetch();
        if (!$record) {
            $pdo->rollBack();
            header('Location: ../clinic_staff/dashboard.php?error=1');
            exit();
        }

        $stmtDelete = $pdo->prepare('DELETE FROM medical_records WHERE record_id = ?');
        $stmtDelete->execute([(int) $recordId]);

        $stmtAppointment = $pdo->prepare(
            "UPDATE appointments SET status = 'Confirmed' WHERE appointment_id = ? AND status = 'Completed'"
        );
        $stmtAppointment->execute([$record['appointment_id']]);

        $pdo->commit();
        header('Location: ../clinic_staff/dashboard.php?status=deleted');
        exit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Medical record deletion failed: ' . $e->getMessage());
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }
}

$fields = [
    'appointment_id',
    'blood_pressure',
    'temperature',
    'weight',
    'diagnosis',
    'prescription',
    'notes'
];
foreach ($fields as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }
}

$appointmentId = $_POST['appointment_id'] ?? '';
$bloodPressure = trim($_POST['blood_pressure'] ?? '');
$temperature = trim($_POST['temperature'] ?? '');
$weight = trim($_POST['weight'] ?? '');
$diagnosis = trim($_POST['diagnosis'] ?? '');
$prescription = trim($_POST['prescription'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (
    !ctype_digit($appointmentId) ||
    $diagnosis === '' ||
    $prescription === '' ||
    strlen($diagnosis) > 10000 ||
    strlen($prescription) > 10000 ||
    strlen($notes) > 10000 ||
    strlen($bloodPressure) > 20 ||
    ($bloodPressure !== '' && !preg_match('/^\d{2,3}\/\d{2,3}$/', $bloodPressure)) ||
    ($temperature !== '' && (!is_numeric($temperature) || (float) $temperature < 25 || (float) $temperature > 45)) ||
    ($weight !== '' && (!is_numeric($weight) || (float) $weight < 0.01 || (float) $weight > 999.99))
) {
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmtAppointment = $pdo->prepare(
        'SELECT patient_id, doctor_id, status FROM appointments WHERE appointment_id = ? FOR UPDATE'
    );
    $stmtAppointment->execute([(int) $appointmentId]);
    $appointment = $stmtAppointment->fetch();
    if (!$appointment || $appointment['status'] !== 'Confirmed') {
        $pdo->rollBack();
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }

    $stmtExisting = $pdo->prepare(
        'SELECT record_id FROM medical_records WHERE appointment_id = ? LIMIT 1'
    );
    $stmtExisting->execute([(int) $appointmentId]);
    if ($stmtExisting->fetch()) {
        $pdo->rollBack();
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }

    $stmtInsert = $pdo->prepare("
        INSERT INTO medical_records
            (appointment_id, patient_id, doctor_id, diagnosis, prescription, notes, blood_pressure, temperature, weight)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([
        (int) $appointmentId,
        $appointment['patient_id'],
        $appointment['doctor_id'],
        $diagnosis,
        $prescription,
        $notes === '' ? null : $notes,
        $bloodPressure === '' ? null : $bloodPressure,
        $temperature === '' ? null : $temperature,
        $weight === '' ? null : $weight
    ]);

    $stmtCompleteAppointment = $pdo->prepare(
        "UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?"
    );
    $stmtCompleteAppointment->execute([(int) $appointmentId]);

    $stmtCompleteQueue = $pdo->prepare(
        "UPDATE queue SET status = 'Completed' WHERE appointment_id = ?"
    );
    $stmtCompleteQueue->execute([(int) $appointmentId]);

    $pdo->commit();
    header('Location: ../clinic_staff/dashboard.php?status=record');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Medical record creation failed: ' . $e->getMessage());
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}
