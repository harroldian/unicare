<?php
// forgot_password.php
session_start();
require_once 'config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Verify email exists in patients or doctors table
        $stmt = $pdo->prepare("SELECT email FROM patients WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token
            $token_stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
            $token_stmt->execute([
                'email'   => $email,
                'token'   => $token,
                'expires' => $expires_at
            ]);

            // Direct link output for demo/local environment
            $reset_link = "reset_password.php?token=" . $token;
            $message = "Password reset link generated! <br><a href='$reset_link' class='btn-link'>Click here to reset your password</a>";
        } else {
            $error = "No account found with that email address.";
        }
    } else {
        $error = "Please enter your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Unicare</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Forgot Password?</h2>
                <p>Enter your registered email to receive a password reset link.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= $message; ?></div>
            <?php else: ?>

            <form action="forgot_password.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="registered@university.edu">
                </div>
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>

            <?php endif; ?>

            <div class="auth-footer">
                <p>Back to <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>