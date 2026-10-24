<?php
// reset_password.php
session_start();
require_once 'config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die("Invalid or missing reset token.");
}

// Verify Token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() ORDER BY reset_id DESC LIMIT 1");
$stmt->execute(['token' => $token]);
$reset_req = $stmt->fetch();

if (!$reset_req) {
    $error = "This password reset token is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset_req) {
    $new_password     = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update User Password
        $update_stmt = $pdo->prepare("
            UPDATE users u
            JOIN patients p ON u.user_id = p.user_id
            SET u.password = :password
            WHERE p.email = :email
        ");
        $update_stmt->execute([
            'password' => $hashed_password,
            'email'    => $reset_req['email']
        ]);

        // Clear used tokens
        $del_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $del_stmt->execute(['email' => $reset_req['email']]);

        $success = "Your password has been successfully reset! You can now <a href='login.php'>Login</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Unicare</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Reset Your Password</h2>
                <p>Create a new strong password for your account.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php elseif ($reset_req): ?>

            <form action="reset_password.php?token=<?= htmlspecialchars($token); ?>" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter new password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>