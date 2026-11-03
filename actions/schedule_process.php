<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['admin'], '../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$operation = $_POST['operation'] ?? null;
if (
    !is_string($operation) ||
    !in_array($operation, ['add', 'delete'], true) ||
    !app_verify_csrf_token($_POST['csrf_token'] ?? null)
) {
    header('Location: ../admin/dashboard.php?closure_status=invalid');
    exit();
}

if ($operation === 'delete') {
    $closureId = $_POST['closure_id'] ?? null;
    if (!is_string($closureId) || !ctype_digit($closureId)) {
        header('Location: ../admin/dashboard.php?closure_status=invalid');
        exit();
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM clinic_closure WHERE closure_id = ?');
        $stmt->execute([(int) $closureId]);
        header($stmt->rowCount() === 1
            ? 'Location: ../admin/dashboard.php?closure_status=deleted'
            : 'Location: ../admin/dashboard.php?closure_status=invalid');
        exit();
    } catch (PDOException $e) {
        error_log('Clinic closure deletion failed: ' . $e->getMessage());
        header('Location: ../admin/dashboard.php?closure_status=error');
        exit();
    }
}

foreach (['closure_date', 'reason'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        header('Location: ../admin/dashboard.php?closure_status=invalid');
        exit();
    }
}

$date = trim($_POST['closure_date'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
$dateErrors = DateTimeImmutable::getLastErrors();
$validDate = $dateValue !== false
    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
    && $dateValue->format('Y-m-d') === $date;

if (!$validDate || $reason === '' || strlen($reason) > 255) {
    header('Location: ../admin/dashboard.php?closure_status=invalid');
    exit();
}

try {
    $stmtExisting = $pdo->prepare(
        'SELECT closure_id FROM clinic_closure WHERE closure_date = ? LIMIT 1'
    );
    $stmtExisting->execute([$date]);
    if ($stmtExisting->fetch()) {
        header('Location: ../admin/dashboard.php?closure_status=duplicate');
        exit();
    }

    $stmt = $pdo->prepare(
        'INSERT INTO clinic_closure (closure_date, reason) VALUES (?, ?)'
    );
    $stmt->execute([$date, $reason]);
    header('Location: ../admin/dashboard.php?closure_status=created');
    exit();
} catch (PDOException $e) {
    error_log('Clinic closure creation failed: ' . $e->getMessage());
    header('Location: ../admin/dashboard.php?closure_status=error');
    exit();
}
