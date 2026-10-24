<?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unicare - University Clinic Management System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <style>
        .hero-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
            background: var(--bg-primary);
        }
        .hero-section h1 { font-size: 3rem; color: var(--accent-blue); margin-bottom: 15px; }
        .hero-section p { font-size: 1.2rem; color: var(--text-secondary); max-width: 600px; margin-bottom: 30px; }
        .hero-actions { display: flex; gap: 15px; }
    </style>
</head>
<body>
    <div class="hero-section">
        <h1>Unicare Healthcare Portal</h1>
        <p>University Clinic Appointment, Scheduling, and Queue Management System for students, faculty, and medical staff.</p>
        <div class="hero-actions">
            <a href="login.php" class="btn btn-primary" style="padding: 12px 24px; text-decoration:none;">Sign In</a>
            <a href="register.php" class="btn btn-secondary" style="padding: 12px 24px; text-decoration:none;">Register as Patient</a>
            <a href="public_queue.php" class="btn btn-secondary" style="padding: 12px 24px; text-decoration:none;" target="_blank">Live Queue Screen</a>
        </div>
    </div>
    <script src="assets/js/theme-toggle.js"></script>
</body>
</html>