<?php
// api/fetch_slots.php
require_once '../config/db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');

// 1. Check if date is a clinic closure
$closure = $pdo->prepare("SELECT * FROM clinic_closures WHERE closure_date = :date");
$closure->execute(['date' => $date]);

if ($closure->rowCount() > 0) {
    echo json_encode(['success' => true, 'closed' => true, 'slots' => []]);
    exit();
}

// 2. Default Clinic Time Slots
$all_slots = ['08:30:00', '09:30:00', '10:30:00', '13:00:00', '14:00:00', '15:00:00'];

// 3. Fetch already booked slots for this date
$booked = $pdo->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = :date AND status != 'cancelled'");
$booked->execute(['date' => $date]);
$booked_times = $booked->fetchAll(PDO::FETCH_COLUMN);

$available_slots = array_diff($all_slots, $booked_times);

echo json_encode([
    'success' => true, 
    'closed' => false, 
    'slots' => array_values($available_slots)
]);
?>