<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account | Unicare University Clinic</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="clinic-page center-page register-page">
<main class="card">
    <header class="clinic-header">
        <?php
        $pageHeading = 'Create your clinic account';
        $pageIntro = 'Create your student account to access university clinic services.';
        require __DIR__ . '/includes/header.php';
        ?>
    </header>

    <section class="form-content">
        <?php if (isset($_GET['error']) && is_string($_GET['error'])): ?>
            <?php
            $registrationError = $_GET['error'] === 'duplicate'
                ? 'That university ID is already registered.'
                : ($_GET['error'] === 'error'
                    ? 'Registration could not be completed. Please try again.'
                    : 'Check your details and make sure your passwords match and contain at least 8 characters.');
            ?>
            <div class="error-msg" role="alert"><?= app_escape($registrationError); ?></div>
        <?php endif; ?>

        <form action="actions/register_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="full_name">Full name <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" name="full_name" id="full_name" required maxlength="150" autocomplete="name" placeholder="Enter your name as it appears on your university ID">
                </div>

                <div class="form-group">
                    <label for="username">University ID <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" name="username" id="username" required maxlength="50" autocomplete="username" placeholder="Your student ID">
                </div>

                <div class="form-group">
                    <label for="affiliation">University affiliation</label>
                    <input type="text" id="affiliation" value="Student" readonly>
                </div>

                <div class="form-group full-width">
                    <label for="contact_info">Email or phone number <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" name="contact_info" id="contact_info" required maxlength="100" autocomplete="email" placeholder="How the clinic can contact you">
                </div>

                <div class="form-group">
                    <label for="password">Create password <span class="required" aria-hidden="true">*</span></label>
                    <input type="password" name="password" id="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="At least 8 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm password <span class="required" aria-hidden="true">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Enter it again">
                </div>
            </div>

            <p class="help-text">Use your official student ID so the clinic can identify your account.</p>
            <button type="submit">Create clinic account</button>
        </form>

        <p class="login-link">Already registered? <a href="login.php">Sign in</a></p>
        <p class="privacy-note">Your registration details are used to set up your university clinic account.</p>
    </section>
</main>
<?php $assetPath = 'assets/'; require __DIR__ . '/includes/footer.php'; ?>
