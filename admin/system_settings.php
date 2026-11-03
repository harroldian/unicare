<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['admin'], '../login.php');

$tables = ['users', 'patients', 'doctors', 'appointments', 'queue', 'clinic_closure', 'medical_records'];
$connectionError = '';
$existingTables = [];

try {
    $stmt = $pdo->query('SHOW TABLES');
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $connectionError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostics | Unicare University Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="clinic-page dashboard-page">
<?php $topbarTitle = 'System settings'; require __DIR__ . '/../includes/sidebar.php'; ?>
<main class="dashboard-main settings-shell">
<div class="card diagnostic-card">
    <header class="clinic-header">
        <?php
        $pageHeading = 'System diagnostics';
        $pageIntro = 'Check the database connection and required clinic tables.';
        require __DIR__ . '/../includes/header.php';
        ?>
    </header>

    <section class="form-content">
        <?php if ($connectionError === ''): ?>
            <div class="notice success" role="status">Database connection successful.</div>
            <h2 class="section-heading">Database tables</h2>
            <ul class="diagnostic-list">
                <?php foreach ($tables as $table): ?>
                    <?php $exists = in_array($table, $existingTables, true); ?>
                    <li class="<?= $exists ? 'is-success' : 'is-error'; ?>">
                        <?= $exists ? '✓' : '×'; ?>
                        Table <strong><?= app_escape($table); ?></strong>
                        <?= $exists ? 'exists.' : 'is missing.'; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="error-msg" role="alert">
                Database check failed: <?= app_escape($connectionError); ?>
            </div>
        <?php endif; ?>
        <p class="link-row"><a href="dashboard.php">Return to dashboard</a></p>
    </section>
</div>
    </main>
</div>
    </div>
<?php $assetPath = '../assets/'; require __DIR__ . '/../includes/footer.php'; ?>
