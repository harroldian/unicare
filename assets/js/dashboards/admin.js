// assets/js/dashboards/admin.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('user-search-input');
    const usersTableBody = document.querySelector('#users-table tbody');
    const resetModal = document.getElementById('resetModal');
    const resetForm = document.getElementById('reset-pwd-form');
    const closeModalBtn = document.getElementById('close-modal');

    // 1. Live AJAX Search Engine for Admin User Accounts
    if (searchInput && usersTableBody) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();

            fetch(`../api/search_users.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(users => {
                    usersTableBody.innerHTML = '';

                    if (users.length === 0) {
                        usersTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No accounts found matching search.</td></tr>';
                        return;
                    }

                    users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.user_id}</td>
                            <td>${escapeHtml(user.username)}</td>
                            <td><span class="badge role-${user.role}">${capitalize(user.role)}</span></td>
                            <td>${capitalize(user.theme_preference)}</td>
                            <td>${user.created_at}</td>
                            <td>
                                <button class="btn btn-sm btn-warning reset-pwd-btn" data-userid="${user.user_id}" data-username="${escapeHtml(user.username)}">
                                    Reset Password
                                </button>
                            </td>
                        `;
                        usersTableBody.appendChild(row);
                    });

                    attachResetListeners();
                })
                .catch(err => console.error('Error fetching search results:', err));
        });
    }

    // 2. Modal Popup Management for Password Reset
    function attachResetListeners() {
        document.querySelectorAll('.reset-pwd-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.getAttribute('data-userid');
                const username = btn.getAttribute('data-username');

                document.getElementById('reset-user-id').value = userId;
                document.getElementById('modal-username').textContent = username;
                resetModal.classList.add('active');
            });
        });
    }

    if (closeModalBtn && resetModal) {
        closeModalBtn.addEventListener('click', () => {
            resetModal.classList.remove('active');
        });
    }

    // 3. Submit Admin Password Reset Request via AJAX
    if (resetForm) {
        resetForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(resetForm);

            fetch('../api/reset_user_password.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    resetModal.classList.remove('active');
                    resetForm.reset();
                }
            })
            .catch(err => console.error('Error resetting password:', err));
        });
    }

    // Initial listener binding
    attachResetListeners();

    // Helper functions
    function escapeHtml(text) {
        return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }
});