<?php
// api/save_theme.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light';
    if (in_array($theme, ['light', 'dark'])) {
        $_SESSION['theme'] = $theme;

        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("UPDATE users SET theme_preference = :theme WHERE user_id = :uid");
            $stmt->execute(['theme' => $theme, 'uid' => $_SESSION['user_id']]);
        }

        echo json_encode(['success' => true]);
        exit();
    }
}
echo json_encode(['success' => false]);
?>