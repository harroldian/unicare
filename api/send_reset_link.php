<?php
// api/send_reset_link.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT email FROM patients WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $token_stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
            $token_stmt->execute([
                'email'   => $email,
                'token'   => $token,
                'expires' => $expires_at
            ]);

            $reset_link = "reset_password.php?token=" . $token;
            echo json_encode([
                'success' => true, 
                'message' => 'Reset link generated successfully.', 
                'reset_link' => $reset_link
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Email address not found in system records.']);
            exit();
        }
    }
    echo json_encode(['success' => false, 'message' => 'Email field cannot be blank.']);
}
?>