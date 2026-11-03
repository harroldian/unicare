<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['doctor'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../clinic_staff/dashboard.php');
    exit();
}

$queueId = $_POST['queue_id'] ?? null;
if (
    !app_verify_csrf_token($_POST['csrf_token'] ?? null) ||
    !is_string($queueId) ||
    !ctype_digit($queueId)
) {
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

try {
    $stmt = $pdo->prepare(
        "UPDATE queue SET status = 'Completed' WHERE queue_id = ? AND status != 'Completed'"
    );
    $stmt->execute([(int) $queueId]);
} catch (PDOException $e) {
    error_log('Queue status update failed: ' . $e->getMessage());
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

if ($stmt->rowCount() !== 1) {
    header('Location: ../clinic_staff/dashboard.php?error=1');
    exit();
}

header('Location: ../clinic_staff/dashboard.php?status=queue');
exit();
