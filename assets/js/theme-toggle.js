// assets/js/theme-toggle.js

document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('theme-toggle-btn');
    const themeIcon = document.getElementById('theme-icon');
    const body = document.body;

    // Check localStorage preference on load
    const savedTheme = localStorage.getItem('unicare_theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        if (themeIcon) themeIcon.textContent = '☀️';
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            const newTheme = isDark ? 'dark' : 'light';

            localStorage.setItem('unicare_theme', newTheme);
            if (themeIcon) themeIcon.textContent = isDark ? '☀️' : '🌙';

            // Optional: Persist user choice to DB via AJAX if logged in
            fetch('../api/save_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + newTheme
            }).catch(err => console.log('Theme sync error:', err));
        });
    }
});