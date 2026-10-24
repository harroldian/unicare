<?php
// api/update_queue.php
require_once '../includes/auth_check.php';
check_role(['doctor', 'nurse', 'admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queue_id = $_POST['queue_id'] ?? null;
    $status   = $_POST['status'] ?? '';

    if ($queue_id && in_array($status, ['waiting', 'in_consultation', 'completed', 'skipped'])) {
        $stmt = $pdo->prepare("UPDATE queue SET status = :status WHERE queue_id = :qid");
        if ($stmt->execute(['status' => $status, 'qid' => $queue_id])) {
            echo json_encode(['success' => true, 'message' => 'Queue status updated successfully.']);
            exit();
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid parameters provided.']);
}
?>