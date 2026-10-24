<?php
// login.php
session_start();
require_once 'config/db.php';

// Redirection helper mapped for patients, students, and university staff
function redirectToDashboard($role) {
    $role = strtolower(trim($role));
    
    // Both students and university staff use the patient module
    if (in_array($role, ['patient', 'staff', 'faculty', 'student']) || empty($role)) {
        $target_role = 'patient';
    } else {
        $valid_roles = ['admin', 'doctor', 'nurse'];
        $target_role = in_array($role, $valid_roles) ? $role : 'patient';
    }
    
    header("Location: " . $target_role . "/dashboard.php");
    exit();
}

// If already logged in, redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectToDashboard($_SESSION['role']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (!empty($identifier) && !empty($password)) {
        $stmt = $pdo->prepare("
            SELECT u.*, p.patient_id, p.full_name AS patient_name, p.user_type 
            FROM users u
            LEFT JOIN patients p ON u.user_id = p.user_id
            WHERE u.username = :uname OR p.school_id = :sid
        ");
        $stmt->execute([
            'uname' => $identifier,
            'sid'   => $identifier
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['theme']   = $user['theme_preference'];

            // Store session details based on classification
            $normalized_role = strtolower(trim($user['role']));
            if (in_array($normalized_role, ['patient', 'staff', 'faculty', 'student'])) {
                $_SESSION['patient_id'] = $user['patient_id'] ?? null;
                $_SESSION['full_name']  = $user['patient_name'] ?? $user['username'];
                $_SESSION['user_type']  = $user['user_type'] ?? 'student';
            } else {
                $_SESSION['full_name']  = ucfirst($user['role']) . ' User';
            }

            redirectToDashboard($user['role']);
        } else {
            $error = "Invalid credentials. Please check your username/ID and password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Unicare Clinic System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/components/alerts.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome to Unicare</h2>
                <p>University Clinic Management Portal</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="identifier">Username or School / Staff ID</label>
                    <input type="text" id="identifier" name="identifier" required placeholder="e.g. 2023-12345 or staff_username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <div class="form-actions">
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register Here</a>
            </div>
        </div>
    </div>
    <script src="assets/js/theme-toggle.js"></script>
</body>
</html>