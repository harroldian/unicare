<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

foreach (['csrf_token', 'username', 'password'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ../login.php?error=Invalid%20login%20request.');
        exit();
    }
}

if (!app_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    header('Location: ../login.php?error=Your%20session%20expired.%20Please%20try%20again.');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    header('Location: ../login.php?error=Enter%20your%20university%20ID%20and%20password.');
    exit();
}

$stmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.password, u.role, p.name AS patient_name
    FROM users u
    LEFT JOIN patients p ON p.student_id_or_staff_id = u.username
    WHERE u.username = ?
");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ../login.php?error=Invalid%20university%20ID%20or%20password.');
    exit();
}

if (!in_array($user['role'], ['admin', 'doctor', 'student', 'staff'], true)) {
    header('Location: ../login.php?error=This%20account%20does%20not%20have%20access.');
    exit();
}
session_regenerate_id(true);
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['display_name'] = $user['username'];

if (in_array($user['role'], ['student', 'staff'], true)) {
    $_SESSION['display_name'] = $user['patient_name'];
}

switch ($user['role']) {
    case 'admin':
        header('Location: ../admin/dashboard.php');
        break;
    case 'doctor':
        header('Location: ../clinic_staff/dashboard.php');
        break;
    case 'student':
        header('Location: ../student/dashboard.php');
        break;
    case 'staff':
        header('Location: ../university_staff/dashboard.php');
        break;
}
exit();
