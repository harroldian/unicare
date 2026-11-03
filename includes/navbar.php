<header class="app-topbar">
    <button class="sidebar-toggle" type="button" aria-label="Toggle navigation" aria-controls="app-sidebar" aria-expanded="false" data-sidebar-toggle>
        <span></span><span></span><span></span>
    </button>
    <div class="topbar-page"><?= app_escape($topbarTitle ?? 'University Clinic'); ?></div>
    <span class="topbar-account"><?= app_escape($_SESSION['display_name'] ?? $_SESSION['username'] ?? ''); ?></span>
</header>
