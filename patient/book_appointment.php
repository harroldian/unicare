<?php
// patient/book_appointment.php
require_once '../includes/auth_check.php';
check_role(['patient']);
require_once '../config/db.php';

$patient_id = $_SESSION['patient_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date   = $_POST['appointment_date'] ?? '';
    $time   = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!empty($date) && !empty($time) && !empty($reason)) {
        // 1. Check if date is blocked in clinic_closures
        $closure_stmt = $pdo->prepare("SELECT * FROM clinic_closures WHERE closure_date = :date");
        $closure_stmt->execute(['date' => $date]);

        if ($closure_stmt->rowCount() > 0) {
            $error = "Selected date is a clinic closure or holiday. Please choose another date.";
        } else {
            // 2. Insert appointment
            $stmt = $pdo->prepare("
                INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason, status) 
                VALUES (:pid, :adate, :atime, :reason, 'pending')
            ");
            $stmt->execute([
                'pid'    => $patient_id,
                'adate'  => $date,
                'atime'  => $time,
                'reason' => $reason
            ]);
            $success = "Appointment booked successfully! Waiting for clinic approval.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$page_title = "Book Appointment - Unicare";
$extra_css = "../assets/css/dashboards/patient.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Book Clinic Appointment</h1>
        <p>Schedule your check-in with the university clinic medical team.</p>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success; ?></div>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 15px;">Return to Dashboard</a>
        <?php else: ?>

        <form action="book_appointment.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="appointment_date">Appointment Date</label>
                <input type="date" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="appointment_time">Preferred Time Slot</label>
                <select name="appointment_time" id="appointment_time" required>
                    <option value="">-- Select Time --</option>
                    <option value="08:30:00">08:30 AM</option>
                    <option value="09:30:00">09:30 AM</option>
                    <option value="10:30:00">10:30 AM</option>
                    <option value="13:00:00">01:00 PM</option>
                    <option value="14:00:00">02:00 PM</option>
                    <option value="15:00:00">03:00 PM</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reason">Reason for Visit / Symptoms</label>
                <textarea id="reason" name="reason" rows="4" required placeholder="Describe your symptoms or reason for visit..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Booking Request</button>
        </form>

        <?php endif; ?>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>