<?php
// register.php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $school_id      = trim($_POST['school_id'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $user_type      = $_POST['user_type'] ?? 'student';
    $username       = trim($_POST['username'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm_pass   = $_POST['confirm_password'] ?? '';

    // Basic Validations
    if ($password !== $confirm_pass) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if username, school_id, or email already exists
        $check_stmt = $pdo->prepare("
            SELECT u.username, p.school_id, p.email 
            FROM users u 
            LEFT JOIN patients p ON u.user_id = p.user_id 
            WHERE u.username = :uname OR p.school_id = :sid OR p.email = :email
        ");
        $check_stmt->execute([
            'uname' => $username,
            'sid'   => $school_id,
            'email' => $email
        ]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Username, School ID, or Email already registered!";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert User Credentials
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $user_stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'patient')");
                $user_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password
                ]);
                $user_id = $pdo->lastInsertId();

                // 2. Insert Patient Details
                $patient_stmt = $pdo->prepare("
                    INSERT INTO patients (user_id, school_id, full_name, user_type, contact_number, email) 
                    VALUES (:user_id, :school_id, :full_name, :user_type, :contact_number, :email)
                ");
                $patient_stmt->execute([
                    'user_id'        => $user_id,
                    'school_id'      => $school_id,
                    'full_name'      => $full_name,
                    'user_type'      => $user_type,
                    'contact_number' => $contact_number,
                    'email'          => $email
                ]);

                $pdo->commit();
                $success = "Account registered successfully! You can now <a href='login.php'>Login</a>.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Unicare</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card wide">
            <div class="auth-header">
                <h2>Create Unicare Account</h2>
                <p>Register as Student Patient</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php else: ?>

            <form action="register.php" method="POST" class="auth-form grid-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label for="school_id">Student ID Number</label>
                    <input type="text" id="school_id" name="school_id" required placeholder="2026-10492">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="student@university.edu">
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" required placeholder="09123456789">
                </div>

                <div class="form-group">
                    <label for="user_type">Account Classification</label>
                    <input type="text" id="student" name="student" required placeholder="student">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="johndoe">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="At least 6 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                </div>

                <div class="form-submit span-2">
                    <button type="submit" class="btn btn-primary full-width">Register Account</button>
                </div>
            </form>

            <?php endif; ?>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>