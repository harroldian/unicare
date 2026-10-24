<?php
// includes/sidebar.php
$role = $_SESSION['role'] ?? '';
?>
<aside class="unicare-sidebar" id="unicare-sidebar">
    <div class="sidebar-logo">
        <h2>Unicare</h2>
        <small>Clinic Portal</small>
    </div>

    <ul class="sidebar-menu">
        <?php if ($role === 'admin'): ?>
            <li><a href="dashboard.php" class="menu-link">📊 Dashboard Overview</a></li>
            <li><a href="user_management.php" class="menu-link">👥 User Accounts & Reset</a></li>
            <li><a href="medical_records.php" class="menu-link">📁 Medical Records Audit</a></li>
            <li><a href="calendar_management.php" class="menu-link">📅 Clinic Closures</a></li>

        <?php elseif ($role === 'doctor'): ?>
            <li><a href="dashboard.php" class="menu-link">🩺 Doctor Queue & Schedule</a></li>
            <li><a href="consultation.php" class="menu-link">📝 Active Consultation</a></li>
            <li><a href="medical_records.php" class="menu-link">🔍 Patient Record Search</a></li>

        <?php elseif ($role === 'nurse'): ?>
            <li><a href="dashboard.php" class="menu-link">📋 Queue Station</a></li>
            <li><a href="patient_lookup.php" class="menu-link">🔍 Patient Lookup & Vitals</a></li>

        <?php elseif ($role === 'patient'): ?>
            <li><a href="dashboard.php" class="menu-link">🏠 My Dashboard</a></li>
            <li><a href="book_appointment.php" class="menu-link">📅 Book Appointment</a></li>
            <li><a href="live_queue.php" class="menu-link">⏳ Live Queue Status</a></li>
            <li><a href="history.php" class="menu-link">📜 My Medical History</a></li>
        <?php endif; ?>
    </ul>
</aside>