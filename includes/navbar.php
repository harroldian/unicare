<?php
// includes/navbar.php
$username = $_SESSION['username'] ?? 'User';
$role = ucfirst($_SESSION['role'] ?? 'Guest');
?>
<header class="unicare-navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" class="sidebar-toggle-btn" aria-label="Toggle Navigation">☰</button>
        <span class="navbar-brand-title">Unicare University Clinic</span>
    </div>

    <div class="navbar-right">
        <!-- Light / Dark Mode Toggle Button -->
        <button id="theme-toggle-btn" class="theme-btn" title="Toggle Light/Dark Mode">
            <span id="theme-icon">🌙</span>
        </button>

        <div class="user-profile-dropdown">
            <span class="user-badge"><?= htmlspecialchars($role); ?></span>
            <span class="username-display"><?= htmlspecialchars($_SESSION['full_name'] ?? $username); ?></span>
            <a href="../logout.php" class="logout-btn" title="Sign Out">Logout</a>
        </div>
    </div>
</header>