<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$bookingPage = ($_SESSION['role'] ?? '') === 'staff'
    ? '../university_staff/book_appointment.php'
    : '../student/book_appointment.php';
app_require_role(['student', 'staff'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $bookingPage);
    exit();
}

foreach (['csrf_token', 'doctor_id', 'date', 'time'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ' . $bookingPage . '?status=invalid');
        exit();
    }
}

if (!app_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: ' . $bookingPage . '?status=invalid');
    exit();
}

$doctorId = filter_var($_POST['doctor_id'] ?? '', FILTER_VALIDATE_INT);
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
$dateErrors = DateTimeImmutable::getLastErrors();
$validDate = $dateValue !== false
    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
    && $dateValue->format('Y-m-d') === $date
    && $date >= date('Y-m-d');

if (!$doctorId || $doctorId < 1 || !$validDate || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
    header('Location: ' . $bookingPage . '?status=invalid');
    exit();
}

$stmtPatient = $pdo->prepare('SELECT patient_id FROM patients WHERE student_id_or_staff_id = ?');
$stmtPatient->execute([$_SESSION['username']]);
$patient = $stmtPatient->fetch();
if (!$patient) {
    header('Location: ' . $bookingPage . '?status=patient');
    exit();
}

$stmtDoctor = $pdo->prepare('SELECT doctor_id FROM doctors WHERE doctor_id = ?');
$stmtDoctor->execute([$doctorId]);
if (!$stmtDoctor->fetch()) {
    header('Location: ' . $bookingPage . '?status=invalid');
    exit();
}

$stmtClosure = $pdo->prepare('SELECT reason FROM clinic_closure WHERE closure_date = ? LIMIT 1');
$stmtClosure->execute([$date]);
if ($stmtClosure->fetch()) {
    header('Location: ' . $bookingPage . '?status=closed');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmtSlot = $pdo->prepare("
        SELECT appointment_id
        FROM appointments
        WHERE doctor_id = ? AND date = ? AND time = ? AND status IN ('Pending', 'Confirmed')
        LIMIT 1
        FOR UPDATE
    ");
    $stmtSlot->execute([$doctorId, $date, $time]);
    if ($stmtSlot->fetch()) {
        $pdo->rollBack();
        header('Location: ' . $bookingPage . '?status=unavailable');
        exit();
    }

    $stmtInsert = $pdo->prepare(
        "INSERT INTO appointments (patient_id, doctor_id, date, time, status) VALUES (?, ?, ?, ?, 'Pending')"
    );
    $stmtInsert->execute([$patient['patient_id'], $doctorId, $date, $time]);

    $pdo->commit();
    $dashboardPage = ($_SESSION['role'] ?? '') === 'staff'
        ? '../university_staff/dashboard.php'
        : '../student/dashboard.php';
    header('Location: ' . $dashboardPage . '?booking=success');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Appointment booking failed: ' . $e->getMessage());
    header('Location: ' . $bookingPage . '?status=error');
    exit();
}
