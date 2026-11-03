<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['doctor'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../clinic_staff/dashboard.php');
    exit();
}

$appointmentId = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? null;
if (
    !app_verify_csrf_token($_POST['csrf_token'] ?? null) ||
    !is_string($appointmentId) ||
    !ctype_digit($appointmentId) ||
    !is_string($status) ||
    !in_array($status, ['Confirmed', 'Cancelled'], true)
) {
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmtAppointment = $pdo->prepare(
        'SELECT status FROM appointments WHERE appointment_id = ? FOR UPDATE'
    );
    $stmtAppointment->execute([(int) $appointmentId]);
    $appointment = $stmtAppointment->fetch();
    if (!$appointment || $appointment['status'] !== 'Pending') {
        $pdo->rollBack();
        header('Location: ../clinic_staff/dashboard.php?error=1');
        exit();
    }

    $stmtUpdate = $pdo->prepare(
        'UPDATE appointments SET status = ? WHERE appointment_id = ?'
    );
    $stmtUpdate->execute([$status, (int) $appointmentId]);

    if ($status === 'Confirmed') {
        $stmtPosition = $pdo->query('SELECT COALESCE(MAX(position), 0) + 1 FROM queue');
        $nextPosition = (int) $stmtPosition->fetchColumn();
        $stmtQueue = $pdo->prepare(
            "INSERT INTO queue (appointment_id, position, status) VALUES (?, ?, 'Waiting')"
        );
        $stmtQueue->execute([(int) $appointmentId, $nextPosition]);
    }

    $pdo->commit();
    header('Location: ../clinic_staff/dashboard.php?status=appointment');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Appointment status update failed: ' . $e->getMessage());
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}
