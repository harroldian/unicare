<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Security check: Ensure user is logged in and is an admin
app_require_role(['admin'], '../login.php');

$accountStatus = is_string($_GET['account_status'] ?? null) ? $_GET['account_status'] : '';
$closureStatus = is_string($_GET['closure_status'] ?? null) ? $_GET['closure_status'] : '';
$userStatus = is_string($_GET['user_status'] ?? null) ? $_GET['user_status'] : '';
$pageSize = 50;
$usersPage = app_page_number('users_page');
$closuresPage = app_page_number('closures_page');
$appointmentsPage = app_page_number('appointments_page');

// Fetch stats & lists
// 1. Fetch Users
$stmtUsers = $pdo->prepare(
    'SELECT user_id, username, role FROM users ORDER BY user_id DESC LIMIT :page_limit OFFSET :page_offset'
);
$stmtUsers->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtUsers->bindValue(':page_offset', app_page_offset($usersPage, $pageSize), PDO::PARAM_INT);
$stmtUsers->execute();
$users = $stmtUsers->fetchAll();
$usersHasNext = app_trim_extra_page_row($users, $pageSize);

// 2. Fetch Clinic Closures
$stmtClosures = $pdo->prepare(
    'SELECT closure_id, closure_date, reason, created_at FROM clinic_closure '
    . 'ORDER BY closure_date DESC, closure_id DESC LIMIT :page_limit OFFSET :page_offset'
);
$stmtClosures->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtClosures->bindValue(':page_offset', app_page_offset($closuresPage, $pageSize), PDO::PARAM_INT);
$stmtClosures->execute();
$closures = $stmtClosures->fetchAll();
$closuresHasNext = app_trim_extra_page_row($closures, $pageSize);

// 3. Fetch All Appointments for Audit
$stmtAppointments = $pdo->prepare("
    SELECT a.appointment_id, p.name AS patient_name, d.name AS doctor_name, a.date, a.time, a.status 
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    ORDER BY a.date DESC, a.time DESC, a.appointment_id DESC
    LIMIT :page_limit OFFSET :page_offset
");
$stmtAppointments->bindValue(':page_limit', $pageSize + 1, PDO::PARAM_INT);
$stmtAppointments->bindValue(':page_offset', app_page_offset($appointmentsPage, $pageSize), PDO::PARAM_INT);
$stmtAppointments->execute();
$appointments = $stmtAppointments->fetchAll();
$appointmentsHasNext = app_trim_extra_page_row($appointments, $pageSize);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Unicare University Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="clinic-page dashboard-page admin-dashboard">

<?php $topbarTitle = 'Administration'; require __DIR__ . '/../includes/sidebar.php'; ?>

<div class="container">
    <div class="page-heading">
        <h1>Clinic administration</h1>
        <p>Manage clinic closures, user accounts, and appointment activity.</p>
        <a href="system_settings.php" class="button">System diagnostics</a>
    </div>

    <?php if ($closureStatus !== ''): ?>
        <?php
        $closureMessages = [
            'created' => ['Clinic closure added.', false],
            'deleted' => ['Clinic closure removed.', false],
            'duplicate' => ['A closure is already recorded for that date.', true],
            'invalid' => ['Check the closure date and reason, then try again.', true],
            'error' => ['The clinic closure could not be saved.', true]
        ];
        $closureNotice = $closureMessages[$closureStatus] ?? null;
        ?>
        <?php if ($closureNotice): ?>
            <div class="notice <?= $closureNotice[1] ? '' : 'success' ?>" role="<?= $closureNotice[1] ? 'alert' : 'status' ?>"><?= app_escape($closureNotice[0]); ?></div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($userStatus !== ''): ?>
        <?php
        $userMessages = [
            'deleted' => ['User account deleted.', false],
            'missing' => ['That account no longer exists.', true],
            'invalid' => ['The account could not be deleted. You cannot delete your own account.', true],
            'error' => ['The account could not be deleted.', true]
        ];
        $userNotice = $userMessages[$userStatus] ?? null;
        ?>
        <?php if ($userNotice): ?>
            <div class="notice <?= $userNotice[1] ? '' : 'success' ?>" role="<?= $userNotice[1] ? 'alert' : 'status' ?>"><?= app_escape($userNotice[0]); ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <section class="section-card" id="account-creation">
        <h2 class="section-heading">Create an account</h2>
        <p class="section-description">Admins can create university staff, clinic staff, and administrator accounts. Student accounts are created through public registration.</p>

        <?php if ($accountStatus === 'created'): ?>
            <div class="notice success" role="status">Account created successfully.</div>
        <?php elseif ($accountStatus === 'duplicate'): ?>
            <div class="notice" role="alert">That username or ID is already in use.</div>
        <?php elseif ($accountStatus === 'invalid'): ?>
            <div class="notice" role="alert">Please check the account details and try again.</div>
        <?php elseif ($accountStatus === 'error'): ?>
            <div class="notice" role="alert">The account could not be created. Please try again.</div>
        <?php endif; ?>

        <form action="../actions/user_process.php" method="POST" class="account-form" id="create-account-form">
            <input type="hidden" name="operation" value="create">
            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
            <div class="form-group">
                <label for="account_role">Account type</label>
                <select name="role" id="account_role" required>
                    <option value="">Select account type</option>
                    <option value="staff">University staff</option>
                    <option value="doctor">Clinic staff</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="form-group" data-account-field="name">
                <label for="account_name">Full name</label>
                <input type="text" name="full_name" id="account_name" maxlength="150" autocomplete="name">
            </div>
            <div class="form-group" data-account-field="username">
                <label for="account_username">Username / ID</label>
                <input type="text" name="username" id="account_username" maxlength="100" required autocomplete="username">
            </div>
            <div class="form-group" data-account-field="contact">
                <label for="account_contact">Email or phone</label>
                <input type="text" name="contact_info" id="account_contact" maxlength="100" autocomplete="email">
            </div>
            <div class="form-group" data-account-field="specialization" hidden>
                <label for="account_specialization">Specialization</label>
                <input type="text" name="specialization" id="account_specialization" maxlength="100">
            </div>
            <div class="form-group full-width" data-account-field="schedule" hidden>
                <label for="account_schedule">Schedule / availability</label>
                <textarea name="schedule" id="account_schedule" maxlength="2000" placeholder="For example: Monday-Friday, 9:00 AM-5:00 PM"></textarea>
                <p class="form-help">Enter the clinic staff member's availability.</p>
            </div>
            <div class="form-group">
                <label for="account_password">Temporary password</label>
                <input type="password" name="password" id="account_password" required minlength="8" maxlength="72" autocomplete="new-password">
                <p class="form-help">Must be at least 8 characters.</p>
            </div>
            <div class="form-group">
                <label for="account_confirm_password">Confirm password</label>
                <input type="password" name="confirm_password" id="account_confirm_password" required minlength="8" maxlength="72" autocomplete="new-password">
            </div>
            <div class="form-group full-width">
                <button type="submit">Create account</button>
            </div>
        </form>
    </section>

    <section class="section-card" id="closures">
        <h2 class="section-heading">Clinic closures and holidays</h2>
        <p class="section-description">Add closure dates to prevent appointments from being booked when the clinic is unavailable.</p>
    
    <form action="../actions/schedule_process.php" method="POST" class="closure-form">
        <input type="hidden" name="operation" value="add">
        <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
        <input type="date" name="closure_date" required>
        <input type="text" name="reason" placeholder="Reason (e.g., National holiday, maintenance)" required>
        <button type="submit">Add closure date</button>
    </form>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Closure date</th>
                <th>Reason</th>
                <th>Created at</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($closures) > 0): ?>
            <?php foreach ($closures as $c): ?>
                <tr>
                    <td><?= app_escape($c['closure_date']); ?></td>
                    <td><?= app_escape($c['reason']); ?></td>
                    <td><?= app_escape($c['created_at']); ?></td>
                    <td>
                        <form action="../actions/schedule_process.php" method="POST" data-confirm="Remove this clinic closure?">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                            <input type="hidden" name="closure_id" value="<?= (int) $c['closure_id']; ?>">
                            <button type="submit" class="button-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" class="empty-cell">No clinic closures recorded.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php app_render_pagination('closures_page', $closuresPage, $closuresPage > 1, $closuresHasNext); ?>
    </section>

    <section class="section-card" id="users">
    <h2 class="section-heading">User and account management</h2>
    <p class="section-description">Review registered accounts and remove accounts when necessary.</p>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username / ID</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['user_id']; ?></td>
                <td><?= app_escape($u['username']); ?></td>
                <td><span class="role-label"><?= app_escape($u['role']); ?></span></td>
                <td>
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                        <form action="../actions/user_process.php" method="POST" data-confirm="Delete this user account?">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                            <input type="hidden" name="user_id" value="<?= (int) $u['user_id']; ?>">
                            <button type="submit" class="button-danger">Delete</button>
                        </form>
                    <?php else: ?>
                        <span class="current-user">Current user</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php app_render_pagination('users_page', $usersPage, $usersPage > 1, $usersHasNext); ?>
    </section>

    <section class="section-card">
    <h2 class="section-heading">Appointment history</h2>
    <p class="section-description">Review clinic appointments and their current status.</p>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($appointments) > 0): ?>
            <?php foreach ($appointments as $app): ?>
                <tr>
                    <td><?= $app['appointment_id']; ?></td>
                    <td><?= app_escape($app['patient_name']); ?></td>
                    <td><?= app_escape($app['doctor_name']); ?></td>
                    <td><?= app_escape($app['date']); ?></td>
                    <td><?= app_escape($app['time']); ?></td>
                    <td><strong><?= app_escape($app['status']); ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="empty-cell">No appointments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php app_render_pagination('appointments_page', $appointmentsPage, $appointmentsPage > 1, $appointmentsHasNext); ?>
    </section>
</div>
    </div>
</div>
<?php $assetPath = '../assets/'; require __DIR__ . '/../includes/footer.php'; ?>