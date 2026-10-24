<?php
// admin/calendar_management.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

$page_title = "Calendar Management - Admin";
$extra_css = "../assets/css/dashboards/admin.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>Clinic Calendar & Closures</h1>
        <p>Manage holidays, weekends, or emergency closure dates that block patient appointment booking.</p>
    </div>

    <div class="closure-container card">
        <h3>Toggle Clinic Closure Date</h3>
        <form id="closure-form" class="closure-form">
            <div class="form-group">
                <label for="closure_date">Select Date</label>
                <input type="date" id="closure_date" name="closure_date" required min="<?= date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="reason">Reason / Holiday Name</label>
                <input type="text" id="reason" name="reason" placeholder="e.g., University Foundation Day">
            </div>
            <button type="submit" class="btn btn-primary">Toggle Date Status</button>
        </form>
    </div>

    <div class="table-responsive" style="margin-top: 30px;">
        <h3>Current Clinic Closures</h3>
        <table class="unicare-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Set At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $closures = $pdo->query("SELECT * FROM clinic_closures ORDER BY closure_date ASC");
                while ($c = $closures->fetch()):
                ?>
                <tr>
                    <td><?= $c['closure_date']; ?></td>
                    <td><?= htmlspecialchars($c['reason']); ?></td>
                    <td><?= $c['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>