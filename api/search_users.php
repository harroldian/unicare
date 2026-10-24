<?php
// api/search_users.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM users WHERE username LIKE :q OR role LIKE :q ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%$query%"]);
$users = $stmt->fetchAll();

echo json_encode($users);
?>