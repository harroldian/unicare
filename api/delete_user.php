<?php
// api/delete_user.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id) {
        // Prevent admin from deleting their own currently logged-in account
        if ($user_id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own active administrator account.']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :uid");
        if ($stmt->execute(['uid' => $user_id])) {
            echo json_encode(['success' => true, 'message' => 'User account successfully deleted.']);
            exit();
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid user ID provided.']);
}
?>