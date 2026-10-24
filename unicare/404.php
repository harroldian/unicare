<?php
// 404.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found - Unicare</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .error-container h1 { font-size: 5rem; color: var(--accent-blue); margin: 0; }
        .error-container p { font-size: 1.2rem; color: var(--text-secondary); margin: 15px 0 25px 0; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for does not exist or has been moved.</p>
        <a href="login.php" class="btn btn-primary" style="text-decoration:none; padding:10px 20px;">Return to Portal</a>
    </div>
</body>
</html>