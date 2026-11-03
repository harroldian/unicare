<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['admin'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$operation = $_POST['operation'] ?? null;
if (
    !is_string($operation) ||
    !in_array($operation, ['create', 'delete'], true) ||
    !app_verify_csrf_token($_POST['csrf_token'] ?? null)
) {
    header('Location: ../admin/dashboard.php?user_status=invalid');
    exit();
}

if ($operation === 'delete') {
    $userId = $_POST['user_id'] ?? null;
    if (
        !is_string($userId) ||
        !ctype_digit($userId) ||
        (int) $userId === (int) $_SESSION['user_id']
    ) {
        header('Location: ../admin/dashboard.php?user_status=invalid');
        exit();
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([(int) $userId]);
        header($stmt->rowCount() === 1
            ? 'Location: ../admin/dashboard.php?user_status=deleted'
            : 'Location: ../admin/dashboard.php?user_status=missing');
        exit();
    } catch (PDOException $e) {
        error_log('User account deletion failed: ' . $e->getMessage());
        header('Location: ../admin/dashboard.php?user_status=error');
        exit();
    }
}

$postFields = [
    'role',
    'username',
    'full_name',
    'contact_info',
    'specialization',
    'schedule',
    'password',
    'confirm_password'
];
foreach ($postFields as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ../admin/dashboard.php?account_status=invalid');
        exit();
    }
}

$role = trim($_POST['role'] ?? '');
$username = trim($_POST['username'] ?? '');
$name = trim($_POST['full_name'] ?? '');
$contactInfo = trim($_POST['contact_info'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$schedule = trim($_POST['schedule'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (
    !in_array($role, ['staff', 'doctor', 'admin'], true) ||
    $username === '' ||
    strlen($username) > 100 ||
    strlen($password) < 8 ||
    strlen($password) > 72 ||
    $password !== $confirmPassword
) {
    header('Location: ../admin/dashboard.php?account_status=invalid');
    exit();
}

if ($role === 'staff' && (
    $name === '' ||
    $contactInfo === '' ||
    strlen($username) > 50 ||
    strlen($name) > 150 ||
    strlen($contactInfo) > 100
)) {
    header('Location: ../admin/dashboard.php?account_status=invalid');
    exit();
}

if ($role === 'doctor' && (
    $name === '' ||
    $specialization === '' ||
    $schedule === '' ||
    strlen($name) > 150 ||
    strlen($specialization) > 100 ||
    strlen($schedule) > 2000
)) {
    header('Location: ../admin/dashboard.php?account_status=invalid');
    exit();
}

try {
    $pdo->beginTransaction();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $hashedPassword, $role]);

    if ($role === 'staff') {
        $stmtPatient = $pdo->prepare(
            'INSERT INTO patients (name, student_id_or_staff_id, contact_info) VALUES (?, ?, ?)'
        );
        $stmtPatient->execute([$name, $username, $contactInfo]);
    } elseif ($role === 'doctor') {
        $stmtDoctor = $pdo->prepare(
            'INSERT INTO doctors (name, specialization, schedule) VALUES (?, ?, ?)'
        );
        $stmtDoctor->execute([$name, $specialization, $schedule]);
    }

    $pdo->commit();
    header('Location: ../admin/dashboard.php?account_status=created');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e->getCode() === '23000') {
        header('Location: ../admin/dashboard.php?account_status=duplicate');
        exit();
    }

    error_log('Admin account creation failed: ' . $e->getMessage());
    header('Location: ../admin/dashboard.php?account_status=error');
    exit();
}
