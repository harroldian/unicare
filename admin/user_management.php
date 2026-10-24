<?php
// admin/user_management.php
require_once '../includes/auth_check.php';
check_role(['admin']);
require_once '../config/db.php';

$success = '';
$error = '';

// Handle creation of universitystaff / nurse /doctor/admin accounts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staff'])) {
    $username       = trim($_POST['username'] ?? '');
    $full_name      = trim($_POST['full_name'] ?? '');
    $role           = $_POST['role'] ?? 'doctor';
    $password       = $_POST['password'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');

    if (!empty($username) && !empty($full_name) && !empty($password) && in_array($role, ['admin', 'doctor', 'nurse', 'university staff'])) {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $u_stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:uname, :pass, :role)");
            $u_stmt->execute(['uname' => $username, 'pass' => $hash, 'role' => $role]);
            $user_id = $pdo->lastInsertId();

            if ($role === 'doctor') {
                $d_stmt = $pdo->prepare("INSERT INTO doctors (user_id, full_name, specialization) VALUES (:uid, :name, :spec)");
                $d_stmt->execute(['uid' => $user_id, 'name' => $full_name, 'spec' => $specialization]);
            }

            $pdo->commit();
            $success = "Successfully created new " . ucfirst($role) . " account for " . htmlspecialchars($full_name) . "!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to create account. Username or ID may already exist.";
        }
    } else {
        $error = "Please fill in all required fields properly.";
    }
}

$page_title = "User Management - Unicare Admin";
$extra_css = "../assets/css/dashboards/admin.css";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="dashboard-header">
        <h1>User Account Management</h1>
        <p>Manage system users, create clinic staff accounts, and handle credentials.</p>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error; ?></div><?php endif; ?>

    <!-- Create Staff Account Section -->
    <div class="card" style="margin-bottom: 30px;">
        <h3>Register New: University Staff / Nurse / Doctor / Admin Account</h3>
        <form action="user_management.php" method="POST" class="auth-form grid-form" style="margin-top: 15px;">
            <input type="hidden" name="create_staff" value="1">
            <div class="form-group">
                <label>Username / Staff ID</label>
                <input type="text" name="username" required placeholder="e.g. dr.cruz or nurse_ana">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required placeholder="e.g. Dr. Maria Cruz">
            </div>
            <div class="form-group">
                <label>System Role</label>
                <select name="role" required class="form-control" style="width:100%; padding:10px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-primary);">
                    <option value="university staff">University Staff</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="form-group">
                <label>Specialization (Doctors/Nurse) / faculty (University Staff)</label>
                </label>
                <input type="text" name="specialization" placeholder="e.g. General Practitioner">
            </div>
            <div class="form-group span-2">
                <label>Temporary Password</label>
                <input type="password" name="password" required placeholder="Set temporary password">
            </div>
            <div class="form-group span-2">
                <button type="submit" class="btn btn-primary">Create Staff Account</button>
            </div>
        </form>
    </div>

    <!-- Existing Users Table -->
    <div class="card">
        <h3>All Registered User Accounts</h3>
        <div class="search-bar-container" style="margin: 15px 0;">
            <input type="text" id="user-search-input" placeholder="Search accounts by username or role..." class="search-input">
        </div>
        <div class="table-responsive">
            <table class="unicare-table" id="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Theme</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr id="user-row-<?= $row['user_id']; ?>">
                        <td><?= $row['user_id']; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><span class="badge status-<?= $row['role']; ?>"><?= ucfirst($row['role']); ?></span></td>
                        <td><?= ucfirst($row['theme_preference']); ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td>
                            <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?= $row['user_id']; ?>)" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
                            <?php else: ?>
                                <span style="color: var(--text-secondary); font-size: 0.85rem;">(Current User)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user account? This action cannot be undone.")) {
        fetch('../api/delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'user_id=' + userId
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                document.getElementById('user-row-' + userId).remove();
            }
        })
        .catch(err => console.error('Error deleting user:', err));
    }
}
</script>

<?php include '../includes/footer.php'; ?>