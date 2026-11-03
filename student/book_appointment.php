<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role($bookingRoles ?? ['student'], '../login.php');

$stmt = $pdo->query('SELECT doctor_id, name, specialization FROM doctors ORDER BY name');
$doctors = $stmt->fetchAll();
$bookingStatus = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$bookingMessage = [
    'closed' => 'The clinic is closed on that date. Please choose another day.',
    'unavailable' => 'That time is already booked for this doctor. Please choose another time.',
    'invalid' => 'Check the doctor, date, and time, then try again.',
    'error' => 'Your appointment could not be booked. Please try again.',
    'patient' => 'Your patient profile was not found. Please contact the clinic.'
][$bookingStatus] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment | Unicare University Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="clinic-page dashboard-page">
<?php $topbarTitle = 'Book an appointment'; require __DIR__ . '/../includes/sidebar.php'; ?>
<main class="dashboard-main booking-shell">
<div class="card">
    <header class="clinic-header">
        <?php
        $pageHeading = 'Book an appointment';
        $pageIntro = 'Choose a clinic staff member and a time that works for you.';
        require __DIR__ . '/../includes/header.php';
        ?>
    </header>

    <section class="form-content">
        <?php if ($bookingMessage !== ''): ?>
            <div class="<?= $bookingStatus === 'error' || $bookingStatus === 'invalid' || $bookingStatus === 'patient' ? 'error-msg' : 'notice' ?>" role="alert">
                <?= app_escape($bookingMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (count($doctors) === 0): ?>
            <div class="notice" role="status">No clinic staff are available for booking yet. Please check back later.</div>
        <?php else: ?>
            <form action="../actions/appointment_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                <div class="form-group">
                    <label for="doctor_id">Clinic staff</label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">Select clinic staff</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= (int) $doctor['doctor_id']; ?>">
                                <?= app_escape($doctor['name']); ?> (<?= app_escape($doctor['specialization']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Appointment date</label>
                    <input type="date" name="date" id="date" required min="<?= app_escape(date('Y-m-d')); ?>">
                </div>
                <div class="form-group">
                    <label for="time">Appointment time</label>
                    <input type="time" name="time" id="time" required>
                </div>
                <button type="submit" class="button-block">Request appointment</button>
            </form>
        <?php endif; ?>

        <p class="link-row"><a href="dashboard.php">Return to dashboard</a></p>
    </section>
</div>
    </main>
</div>
    </div>
<?php $assetPath = '../assets/'; require __DIR__ . '/../includes/footer.php'; ?>
