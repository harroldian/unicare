<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Unicare Healthcare Portal'; ?></title>
    
    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/components/navbar.css">
    <link rel="stylesheet" href="../assets/css/components/sidebar.css">
    <link rel="stylesheet" href="../assets/css/components/alerts.css">
    <link rel="stylesheet" href="../assets/css/components/modal.css">
    
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?= $extra_css; ?>">
    <?php endif; ?>

    <!-- Inline script to prevent theme flash -->
    <script>
        const savedTheme = localStorage.getItem('unicare_theme') || '<?= $_SESSION['theme'] ?? 'light'; ?>';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
            document.addEventListener('DOMContentLoaded', () => document.body.classList.add('dark-mode'));
        }
    </script>
</head>
<body>