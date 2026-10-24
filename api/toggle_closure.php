<?php
// api/toggle_closure.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['closure_date'] ?? '';
    $reason = trim($_POST['reason'] ?? 'Clinic Holiday / Closure');

    if (!empty($date)) {
        // Check if already closed
        $check = $pdo->prepare("SELECT closure_id FROM clinic_closures WHERE closure_date = :date");
        $check->execute(['date' => $date]);

        if ($check->rowCount() > 0) {
            // Remove closure if it exists (re-open clinic)
            $del = $pdo->prepare("DELETE FROM clinic_closures WHERE closure_date = :date");
            $del->execute(['date' => $date]);
            echo json_encode(['success' => true, 'status' => 'opened', 'message' => 'Clinic date re-opened successfully.']);
        } else {
            // Add closure
            $ins = $pdo->prepare("INSERT INTO clinic_closures (closure_date, reason) VALUES (:date, :reason)");
            $ins->execute(['date' => $date, 'reason' => $reason]);
            echo json_encode(['success' => true, 'status' => 'closed', 'message' => 'Clinic date marked as closed.']);
        }
        exit();
    }
}
echo json_encode(['success' => false, 'message' => 'Invalid date provided.']);
?>