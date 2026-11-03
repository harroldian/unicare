<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role($dashboardRoles ?? ['student'], '../login.php');

$username = $_SESSION['username'];

$stmtPatient = $pdo->prepare(
    'SELECT patient_id, name FROM patients WHERE student_id_or_staff_id = ?'
);
$stmtPatient->execute([$username]);
$patient = $stmtPatient->fetch();
$patientId = $patient ? (int) $patient['patient_id'] : 0;
$pageSize = 50;
$appointmentsPage = app_page_number('appointments_page');
$appointmentsOffset = app_page_offset($appointmentsPage, $pageSize);

$stmtAppointments = $pdo->prepare("
    SELECT a.appointment_id, d.name AS doctor_name, d.specialization, a.date, a.time, a.status
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = :patient_id
    ORDER BY a.date DESC, a.time DESC, a.appointment_id DESC
    LIMIT :page_limit OFFSET :page_offset
");
$stmtAppointments->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
$stmtAppointments->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtAppointments->bindValue(':page_offset', $appointmentsOffset, PDO::PARAM_INT);
$stmtAppointments->execute();
$appointments = $stmtAppointments->fetchAll();
$appointmentsHasNext = app_trim_extra_page_row($appointments, $pageSize);

$stmtQueue = $pdo->prepare("
    SELECT q.queue_id, q.position, q.status, a.date, a.time, d.name AS doctor_name
    FROM queue q
    JOIN appointments a ON q.appointment_id = a.appointment_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ? AND q.status != 'Completed'
    ORDER BY q.queue_id DESC
    LIMIT 1
");
$stmtQueue->execute([$patientId]);
$activeQueue = $stmtQueue->fetch();
$bookingUrl = ($_SESSION['role'] ?? '') === 'staff'
    ? '../university_staff/book_appointment.php'
    : 'book_appointment.php';
$dashboardTitle = ($_SESSION['role'] ?? '') === 'staff'
    ? 'University staff dashboard'
    : 'Student dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= app_escape($dashboardTitle); ?> | Unicare University Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="clinic-page dashboard-page">
<?php $topbarTitle = $dashboardTitle; require __DIR__ . '/../includes/sidebar.php'; ?>

<main class="container">
    <div class="page-heading">
        <h1>Your clinic dashboard</h1>
        <p>Manage your appointments and follow your place in the clinic queue.</p>
    </div>

    <?php if (isset($_GET['booking']) && $_GET['booking'] === 'success'): ?>
        <div class="notice success" role="status">Your appointment request was submitted and is pending clinic review.</div>
    <?php endif; ?>

    <?php if (!$patient): ?>
        <div class="notice" role="alert">Your patient profile could not be found. Please contact the clinic for assistance.</div>
    <?php endif; ?>

    <section class="section-card quick-card">
        <div>
            <h2>Need to see a doctor?</h2>
            <p>Request a clinic appointment by choosing a staff member and an available time.</p>
        </div>
        <a href="<?= app_escape($bookingUrl); ?>" class="button">Book an appointment</a>
    </section>

    <section class="section-card" id="queue">
        <h2 class="section-heading">Live queue status</h2>
        <p class="section-description">Your current place in line when you have an active visit.</p>
        <?php if ($activeQueue): ?>
            <div class="queue-box">
                <p><strong>Status:</strong> Your visit with <?= app_escape($activeQueue['doctor_name']); ?> is <?= app_escape(strtolower($activeQueue['status'])); ?>.</p>
                <p><strong>Queue position:</strong> #<?= (int) $activeQueue['position']; ?></p>
                <p><strong>Scheduled:</strong> <?= app_escape($activeQueue['date']); ?> at <?= app_escape(substr($activeQueue['time'], 0, 5)); ?></p>
            </div>
        <?php else: ?>
            <p class="help-text queue-empty">You have no active queue tickets at the moment.</p>
        <?php endif; ?>
    </section>

    <section class="section-card" id="appointments">
        <h2 class="section-heading">My appointment history</h2>
        <p class="section-description">A record of your requested and completed clinic visits.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Appointment</th>
                        <th>Clinic staff</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>#<?= (int) $appointment['appointment_id']; ?></td>
                                <td><?= app_escape($appointment['doctor_name']); ?></td>
                                <td><?= app_escape($appointment['specialization']); ?></td>
                                <td><?= app_escape($appointment['date']); ?></td>
                                <td><?= app_escape(substr($appointment['time'], 0, 5)); ?></td>
                                <td><?= app_escape($appointment['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty-cell">You have not booked any appointments yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php app_render_pagination('appointments_page', $appointmentsPage, $appointmentsPage > 1, $appointmentsHasNext); ?>
    </section>
</main>
    </div>
</div>
<?php $assetPath = '../assets/'; require __DIR__ . '/../includes/footer.php'; ?>
