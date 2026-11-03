<?php
require_once __DIR__ . '/includes/session.php';

$destinations = [
    'student' => 'student/dashboard.php',
    'staff' => 'university_staff/dashboard.php',
    'doctor' => 'clinic_staff/dashboard.php',
    'admin' => 'admin/dashboard.php',
];

$role = $_SESSION['role'] ?? '';
if (isset($_SESSION['user_id'], $destinations[$role])) {
    header('Location: ' . $destinations[$role]);
} else {
    header('Location: login.php');
}
exit();
