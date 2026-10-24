<?php
// api/search_records.php
require_once '../includes/auth_check.php';
check_role(['doctor', 'nurse', 'admin']);
require_once '../config/db.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

$sql = "
    SELECT m.*, p.full_name, p.school_id, a.appointment_date 
    FROM medical_records m
    JOIN patients p ON m.patient_id = p.patient_id
    JOIN appointments a ON m.appointment_id = a.appointment_id
    WHERE p.full_name LIKE :q OR p.school_id LIKE :q OR m.diagnosis LIKE :q
    ORDER BY m.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%$query%"]);
$records = $stmt->fetchAll();

echo json_encode($records);
?>