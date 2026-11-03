<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit();
}

$fields = ['csrf_token', 'full_name', 'username', 'contact_info', 'password', 'confirm_password'];
foreach ($fields as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ../register.php?error=invalid');
        exit();
    }
}

if (!app_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: ../register.php?error=invalid');
    exit();
}

$name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$contactInfo = trim($_POST['contact_info'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (
    $name === '' ||
    $username === '' ||
    $contactInfo === '' ||
    strlen($name) > 150 ||
    strlen($username) > 50 ||
    strlen($contactInfo) > 100 ||
    strlen($password) < 8 ||
    strlen($password) > 72 ||
    $password !== $confirmPassword
) {
    header('Location: ../register.php?error=invalid');
    exit();
}

try {
    $pdo->beginTransaction();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $hashedPassword, 'student']);

    $stmtPatient = $pdo->prepare(
        'INSERT INTO patients (name, student_id_or_staff_id, contact_info) VALUES (?, ?, ?)'
    );
    $stmtPatient->execute([$name, $username, $contactInfo]);

    $pdo->commit();
    header('Location: ../login.php?message=Registration%20successful.%20Please%20sign%20in.');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e->getCode() === '23000') {
        header('Location: ../register.php?error=duplicate');
        exit();
    }

    error_log('Student registration failed: ' . $e->getMessage());
    header('Location: ../register.php?error=error');
    exit();
}
