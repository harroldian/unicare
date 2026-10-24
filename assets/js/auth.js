// assets/js/auth.js
document.addEventListener('DOMContentLoaded', () => {
    const regForm = document.querySelector('.register-form');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                e.preventDefault();
                alert('Passwords do not match. Please verify.');
            }
        });
    }
});