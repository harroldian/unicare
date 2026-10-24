<?php
// api/fetch_queue.php
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    // 1. Fetch currently "In Consultation" (Now Serving) tickets
    $serving_stmt = $pdo->prepare("
        SELECT q.queue_number, p.full_name, p.school_id 
        FROM queue q
        JOIN appointments a ON q.appointment_id = a.appointment_id
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE DATE(q.updated_at) = CURDATE() AND q.status = 'in_consultation'
        ORDER BY q.updated_at DESC LIMIT 1
    ");
    $serving_stmt->execute();
    $now_serving = $serving_stmt->fetch();

    // 2. Fetch upcoming "Waiting" tickets
    $waiting_stmt = $pdo->prepare("
        SELECT q.queue_number 
        FROM queue q
        WHERE DATE(q.updated_at) = CURDATE() AND q.status = 'waiting'
        ORDER BY q.queue_number ASC LIMIT 6
    ");
    $waiting_stmt->execute();
    $waiting_tickets = $waiting_stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success'      => true,
        'now_serving'  => $now_serving ? $now_serving['queue_number'] : '---',
        'patient_name' => $now_serving ? $now_serving['full_name'] : 'None',
        'next_tickets' => $waiting_tickets
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>