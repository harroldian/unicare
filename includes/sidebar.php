<?php
$sidebarMenus = [
    'student' => [
        ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5z M9 21v-7h6v7'],
        ['label' => 'Book Appointment', 'href' => 'book_appointment.php', 'icon' => 'M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1z M12 12v5m-2.5-2.5h5'],
        ['label' => 'My Appointments', 'href' => 'dashboard.php#appointments', 'icon' => 'M5 4h14v16H5z M8 8h8m-8 4h8m-8 4h5'],
        ['label' => 'Queue Status', 'href' => 'dashboard.php#queue', 'icon' => 'M4 6h16M4 12h10M4 18h6 M17 15l3 3-3 3'],
    ],
    'staff' => [
        ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5z M9 21v-7h6v7'],
        ['label' => 'Book Appointment', 'href' => 'book_appointment.php', 'icon' => 'M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1z M12 12v5m-2.5-2.5h5'],
        ['label' => 'My Appointments', 'href' => 'dashboard.php#appointments', 'icon' => 'M5 4h14v16H5z M8 8h8m-8 4h8m-8 4h5'],
        ['label' => 'Queue Status', 'href' => 'dashboard.php#queue', 'icon' => 'M4 6h16M4 12h10M4 18h6 M17 15l3 3-3 3'],
    ],
    'doctor' => [
        ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5z M9 21v-7h6v7'],
        ['label' => 'Appointments', 'href' => 'dashboard.php#appointments', 'icon' => 'M5 4h14v16H5z M8 8h8m-8 4h8m-8 4h5'],
        ['label' => 'Manage Queue', 'href' => 'dashboard.php#queue', 'icon' => 'M4 6h16M4 12h10M4 18h6 M17 15l3 3-3 3'],
        ['label' => 'Medical Records', 'href' => 'dashboard.php#medical-records', 'icon' => 'M7 3h10v18H7z M10 8h4m-4 4h4m-4 4h4'],
    ],
    'admin' => [
        ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5z M9 21v-7h6v7'],
        ['label' => 'Manage Users', 'href' => 'dashboard.php#users', 'icon' => 'M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20z M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z M17 11a3 3 0 1 0-1-5.8 M18 14a4 4 0 0 1 3 3.9V20'],
        ['label' => 'System Settings', 'href' => 'system_settings.php', 'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.7 2.9-.2-.1a1.7 1.7 0 0 0-1.9.3l-.1.1h-3.4l-.1-.2a1.7 1.7 0 0 0-1.6-1l-.2.1-2.9-1.7.1-.2a1.7 1.7 0 0 0-.3-1.9l-.1-.1v-3.4l.2-.1a1.7 1.7 0 0 0 1-1.6l-.1-.2 1.7-2.9.2.1a1.7 1.7 0 0 0 1.9-.3l.1-.1h3.4l.1.2a1.7 1.7 0 0 0 1.6 1l.2-.1 2.9 1.7-.1.2a1.7 1.7 0 0 0 .3 1.9l.1.1v3.4z'],
    ],
];

$sidebarRole = $_SESSION['role'] ?? '';
if (!isset($sidebarMenus[$sidebarRole])) {
    app_require_role(['student', 'staff', 'doctor', 'admin'], '../login.php');
}
app_require_role([$sidebarRole], '../login.php');

$sidebarContext = [
    'student' => ['folder' => 'student', 'roleName' => 'Student'],
    'staff' => ['folder' => 'university_staff', 'roleName' => 'University staff'],
    'doctor' => ['folder' => 'clinic_staff', 'roleName' => 'Clinic staff'],
    'admin' => ['folder' => 'admin', 'roleName' => 'Administrator'],
][$sidebarRole];

$sidebarCurrentPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$sidebarCurrentFile = basename($sidebarCurrentPath);
$sidebarCurrentFolder = basename(dirname($sidebarCurrentPath));
$sidebarBaseUrl = '../' . $sidebarContext['folder'] . '/';
?>
<div class="app-layout">
    <aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
        <a class="sidebar-brand" href="<?= app_escape($sidebarBaseUrl . 'dashboard.php'); ?>">
            <span class="brand-mark" aria-hidden="true">+</span>
            <span class="sidebar-brand-copy">
                <span class="brand-name">Unicare</span>
                <span class="brand-subtitle">University Clinic</span>
            </span>
        </a>

        <div class="sidebar-user">
            <span class="sidebar-avatar" aria-hidden="true"><?= app_escape(strtoupper(substr($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'U', 0, 1))); ?></span>
            <span class="sidebar-user-copy">
                <span class="sidebar-user-name"><?= app_escape($_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                <span class="sidebar-user-role"><?= app_escape($sidebarContext['roleName']); ?></span>
            </span>
        </div>

        <nav class="sidebar-nav">
            <span class="sidebar-section-label">MENU</span>
            <?php foreach ($sidebarMenus[$sidebarRole] as $menuItem): ?>
                <?php
                $menuParts = explode('#', $menuItem['href'], 2);
                $menuFile = $menuParts[0];
                $menuHash = $menuParts[1] ?? '';
                $menuFolder = $sidebarContext['folder'];
                $isActive = $sidebarCurrentFolder === $menuFolder
                    && $sidebarCurrentFile === basename($menuFile)
                    && $menuHash === '';
                $menuHref = $sidebarBaseUrl . $menuItem['href'];
                ?>
                <a class="sidebar-link<?= $isActive ? ' is-active' : ''; ?>"
                   href="<?= app_escape($menuHref); ?>"
                   title="<?= app_escape($menuItem['label']); ?>"
                   <?= $isActive ? 'aria-current="page"' : ''; ?>>
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <?php foreach (explode(' M', $menuItem['icon']) as $pathIndex => $pathData): ?>
                            <path d="<?= app_escape(($pathIndex > 0 ? 'M' : '') . $pathData); ?>"></path>
                        <?php endforeach; ?>
                    </svg>
                    <span class="sidebar-link-label"><?= app_escape($menuItem['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <form action="../logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= app_escape(app_csrf_token()); ?>">
                <button class="sidebar-link sidebar-logout" type="submit" title="Log out">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10 17l5-5-5-5m5 5H3"></path>
                        <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"></path>
                    </svg>
                    <span class="sidebar-link-label">Log out</span>
                </button>
            </form>
        </div>
    </aside>
    <div class="sidebar-scrim" data-sidebar-close></div>
    <div class="app-main">
        <?php require __DIR__ . '/navbar.php'; ?>
