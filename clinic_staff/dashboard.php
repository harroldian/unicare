<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

app_require_role(['doctor'], '../login.php');

$pageSize = 50;
$appointmentsPage = app_page_number('appointments_page');
$queuePage = app_page_number('queue_page');
$medicalRecordsPage = app_page_number('medical_records_page');

$stmtAppointments = $pdo->prepare("
    SELECT a.appointment_id, p.patient_id, p.name AS patient_name, p.student_id_or_staff_id,
           d.doctor_id, d.name AS doctor_name, a.date, a.time, a.status,
           mr.record_id AS medical_record_id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    LEFT JOIN medical_records mr ON mr.appointment_id = a.appointment_id
    ORDER BY a.date DESC, a.time DESC, a.appointment_id DESC
    LIMIT :page_limit OFFSET :page_offset
");
$stmtAppointments->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtAppointments->bindValue(':page_offset', app_page_offset($appointmentsPage, $pageSize), PDO::PARAM_INT);
$stmtAppointments->execute();
$appointments = $stmtAppointments->fetchAll();
$appointmentsHasNext = app_trim_extra_page_row($appointments, $pageSize);

$stmtQueue = $pdo->prepare("
    SELECT q.queue_id, q.position, q.status, a.date, a.time,
           p.name AS patient_name, d.name AS doctor_name
    FROM queue q
    JOIN appointments a ON q.appointment_id = a.appointment_id
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    ORDER BY CASE WHEN q.status = 'Completed' THEN 1 ELSE 0 END, q.position ASC, q.queue_id DESC
    LIMIT :page_limit OFFSET :page_offset
");
$stmtQueue->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtQueue->bindValue(':page_offset', app_page_offset($queuePage, $pageSize), PDO::PARAM_INT);
$stmtQueue->execute();
$queueList = $stmtQueue->fetchAll();
$queueHasNext = app_trim_extra_page_row($queueList, $pageSize);

$stmtRecords = $pdo->prepare("
    SELECT mr.record_id, p.name AS patient_name, d.name AS doctor_name,
           mr.diagnosis, mr.prescription, mr.blood_pressure, mr.temperature,
           mr.weight, mr.created_at
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.patient_id
    JOIN doctors d ON mr.doctor_id = d.doctor_id
    ORDER BY mr.created_at DESC, mr.record_id DESC
    LIMIT :page_limit OFFSET :page_offset
");
$stmtRecords->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtRecords->bindValue(':page_offset', app_page_offset($medicalRecordsPage, $pageSize), PDO::PARAM_INT);
$stmtRecords->execute();
$medicalRecords = $stmtRecords->fetchAll();
$medicalRecordsHasNext = app_trim_extra_page_row($medicalRecords, $pageSize);

$staffStatus = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$statusMessage = [
    'appointment' => 'Appointment status updated.',
    'queue' => 'Queue status updated.',
    'record' => 'Medical record saved.',
    'deleted' => 'Medical record deleted.'
][$staffStatus] ?? '';
$errorMessage = isset($_GET['error']) ? 'The requested action could not be completed. Check the appointment details and try again.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Staff Dashboard | Unicare University Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="clinic-page dashboard-page">
<?php $topbarTitle = 'Clinic staff dashboard'; require __DIR__ . '/../includes/sidebar.php'; ?>

<main class="container">
    <div class="page-heading">
        <h1>Clinic staff dashboard</h1>
        <p>Manage appointment requests, monitor the live queue, and record patient visits.</p>
    </div>

    <?php if ($statusMessage !== ''): ?>
        <div class="notice success" role="status"><?= app_escape($statusMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="notice" role="alert"><?= app_escape($errorMessage); ?></div>
    <?php endif; ?>

    <section class="section-card" id="appointments">
        <h2 class="section-heading">Patient appointments</h2>
        <p class="section-description">Review requests and confirm or cancel pending appointments.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Appointment</th>
                        <th>Patient</th>
                        <th>Clinic staff</th>
                        <th>Date and time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>#<?= (int) $appointment['appointment_id']; ?></td>
                                <td><?= app_escape($appointment['patient_name']); ?> (<?= app_escape($appointment['student_id_or_staff_id']); ?>)</td>
                                <td><?= app_escape($appointment['doctor_name']); ?></td>
                                <td><?= app_escape($appointment['date']); ?> <?= app_escape(substr($appointment['time'], 0, 5)); ?></td>
                                <td><?= app_escape($appointment['status']); ?></td>
                                <td>
                                    <?php if ($appointment['status'] === 'Pending'): ?>
                                        <div class="table-actions">
                                            <form action="../actions/update_appointment.php" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                                                <input type="hidden" name="appointment_id" value="<?= (int) $appointment['appointment_id']; ?>">
                                                <input type="hidden" name="status" value="Confirmed">
                                                <button type="submit">Confirm</button>
                                            </form>
                                            <form action="../actions/update_appointment.php" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                                                <input type="hidden" name="appointment_id" value="<?= (int) $appointment['appointment_id']; ?>">
                                                <input type="hidden" name="status" value="Cancelled">
                                                <button type="submit" class="button-danger">Cancel</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="help-text"><?= $appointment['medical_record_id'] ? 'Visit recorded' : 'No action needed'; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty-cell">No appointments have been requested.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php app_render_pagination('appointments_page', $appointmentsPage, $appointmentsPage > 1, $appointmentsHasNext); ?>
    </section>

    <section class="section-card" id="queue">
        <h2 class="section-heading">Live queue</h2>
        <p class="section-description">Track patients currently waiting for clinic care.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Queue</th>
                        <th>Position</th>
                        <th>Patient</th>
                        <th>Clinic staff</th>
                        <th>Appointment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($queueList): ?>
                        <?php foreach ($queueList as $queueItem): ?>
                            <tr>
                                <td>#<?= (int) $queueItem['queue_id']; ?></td>
                                <td>#<?= (int) $queueItem['position']; ?></td>
                                <td><?= app_escape($queueItem['patient_name']); ?></td>
                                <td><?= app_escape($queueItem['doctor_name']); ?></td>
                                <td><?= app_escape($queueItem['date']); ?> <?= app_escape(substr($queueItem['time'], 0, 5)); ?></td>
                                <td><?= app_escape($queueItem['status']); ?></td>
                                <td>
                                    <?php if ($queueItem['status'] !== 'Completed'): ?>
                                        <form action="../actions/queue_process.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                                            <input type="hidden" name="queue_id" value="<?= (int) $queueItem['queue_id']; ?>">
                                            <input type="hidden" name="status" value="Completed">
                                            <button type="submit">Mark completed</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="help-text">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty-cell">No queue tickets are active.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php app_render_pagination('queue_page', $queuePage, $queuePage > 1, $queueHasNext); ?>
    </section>

    <section class="section-card" id="record-entry">
        <h2 class="section-heading">Record a patient visit</h2>
        <p class="section-description">Save a diagnosis and treatment for a confirmed appointment.</p>
        <form action="../actions/medical_record_process.php" method="POST" class="form-grid record-form">
            <input type="hidden" name="operation" value="create">
            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
            <div class="form-group">
                <label for="appointment_id">Confirmed appointment</label>
                <select name="appointment_id" id="appointment_id" required>
                    <option value="">Select an appointment</option>
                    <?php foreach ($appointments as $appointment): ?>
                        <?php if ($appointment['status'] === 'Confirmed' && !$appointment['medical_record_id']): ?>
                            <option value="<?= (int) $appointment['appointment_id']; ?>">
                                #<?= (int) $appointment['appointment_id']; ?> — <?= app_escape($appointment['patient_name']); ?>, <?= app_escape($appointment['date']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="blood_pressure">Blood pressure</label>
                <input type="text" name="blood_pressure" id="blood_pressure" maxlength="20" placeholder="120/80">
            </div>
            <div class="form-group">
                <label for="temperature">Temperature (°C)</label>
                <input type="number" name="temperature" id="temperature" min="25" max="45" step="0.1" placeholder="36.5">
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" name="weight" id="weight" min="0.01" max="999.99" step="0.01" placeholder="65.00">
            </div>
            <div class="form-group full-width">
                <label for="diagnosis">Diagnosis</label>
                <textarea name="diagnosis" id="diagnosis" maxlength="10000" required></textarea>
            </div>
            <div class="form-group full-width">
                <label for="prescription">Prescription / treatment</label>
                <textarea name="prescription" id="prescription" maxlength="10000" required></textarea>
            </div>
            <div class="form-group full-width">
                <label for="notes">Additional notes</label>
                <textarea name="notes" id="notes" maxlength="10000"></textarea>
            </div>
            <div class="full-width">
                <button type="submit">Save medical record</button>
            </div>
        </form>
    </section>

    <section class="section-card" id="medical-records">
        <h2 class="section-heading">Medical record history</h2>
        <p class="section-description">Review saved visit records and vitals.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Record</th>
                        <th>Patient</th>
                        <th>Clinic staff</th>
                        <th>Vitals</th>
                        <th>Diagnosis and treatment</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($medicalRecords): ?>
                        <?php foreach ($medicalRecords as $record): ?>
                            <tr>
                                <td>#<?= (int) $record['record_id']; ?></td>
                                <td><?= app_escape($record['patient_name']); ?></td>
                                <td><?= app_escape($record['doctor_name']); ?></td>
                                <td>
                                    BP: <?= app_escape($record['blood_pressure'] ?? '—'); ?><br>
                                    Temp: <?= app_escape($record['temperature'] ?? '—'); ?> °C<br>
                                    Weight: <?= app_escape($record['weight'] ?? '—'); ?> kg
                                </td>
                                <td><strong>Diagnosis:</strong> <?= nl2br(app_escape($record['diagnosis'])); ?><br><strong>Treatment:</strong> <?= nl2br(app_escape($record['prescription'])); ?></td>
                                <td><?= app_escape($record['created_at']); ?></td>
                                <td>
                                    <form action="../actions/medical_record_process.php" method="POST" data-confirm="Delete this medical record? The appointment will return to confirmed status.">
                                        <input type="hidden" name="operation" value="delete">
                                        <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                                        <input type="hidden" name="record_id" value="<?= (int) $record['record_id']; ?>">
                                        <button type="submit" class="button-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty-cell">No medical records have been saved.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php app_render_pagination('medical_records_page', $medicalRecordsPage, $medicalRecordsPage > 1, $medicalRecordsHasNext); ?>
    </section>
</main>
    </div>
</div>
<?php $assetPath = '../assets/'; require __DIR__ . '/../includes/footer.php'; ?>
