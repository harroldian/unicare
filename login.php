<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Unicare University Clinic</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="clinic-page center-page login-page">
<main class="card">
    <header class="clinic-header">
        <?php
        $pageHeading = 'Welcome back';
        $pageIntro = 'Sign in to access your university clinic account.';
        require __DIR__ . '/includes/header.php';
        ?>
    </header>

    <section class="form-content">
        <?php if (isset($_GET['error']) && is_string($_GET['error'])): ?>
            <div class="error-msg" role="alert"><?= app_escape($_GET['error']); ?></div>
        <?php elseif (isset($_GET['message']) && is_string($_GET['message'])): ?>
            <div class="success-msg" role="status"><?= app_escape($_GET['message']); ?></div>
        <?php endif; ?>

        <form action="actions/login_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
            <div class="form-group">
                <label for="username">University ID</label>
                <input type="text" name="username" id="username" required maxlength="100" autocomplete="username" placeholder="Enter your student or staff ID">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>

            <button type="submit">Sign in to clinic account</button>
        </form>

        <p class="register-link">New to Unicare? <a href="register.php">Create an account</a></p>
        <p class="privacy-note">Sign in with the university ID used when you registered.</p>
    </section>
</main>
<?php $assetPath = 'assets/'; require __DIR__ . '/includes/footer.php'; ?>
