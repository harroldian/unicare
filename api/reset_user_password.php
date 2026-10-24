<?php
// api/reset_user_password.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $new_pass = $_POST['new_password'] ?? '';

    if ($user_id && !empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE user_id = :id");
        if ($stmt->execute(['pass' => $hashed, 'id' => $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Password successfully reset.']);
            exit();
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}
?>