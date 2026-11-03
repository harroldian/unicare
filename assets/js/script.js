(() => {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.getElementById('app-sidebar');
    const sidebarScrim = document.querySelector('[data-sidebar-close]');

    if (sidebarToggle && sidebar) {
        const mobileQuery = window.matchMedia('(max-width: 860px)');
        const setMobileOpen = (isOpen) => {
            document.body.classList.toggle('sidebar-open', isOpen);
            sidebarToggle.setAttribute('aria-expanded', String(isOpen));
        };
        const closeMobileSidebar = () => setMobileOpen(false);
        const updateViewportState = () => {
            if (mobileQuery.matches) {
                closeMobileSidebar();
            } else {
                document.body.classList.remove('sidebar-open');
                sidebarToggle.setAttribute(
                    'aria-expanded',
                    String(!document.body.classList.contains('sidebar-collapsed'))
                );
            }
        };

        sidebarToggle.addEventListener('click', () => {
            if (mobileQuery.matches) {
                setMobileOpen(!document.body.classList.contains('sidebar-open'));
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                sidebarToggle.setAttribute(
                    'aria-expanded',
                    String(!document.body.classList.contains('sidebar-collapsed'))
                );
            }
        });

        sidebarScrim?.addEventListener('click', closeMobileSidebar);
        sidebar.querySelectorAll('.sidebar-link').forEach((link) => {
            link.addEventListener('click', closeMobileSidebar);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMobileSidebar();
            }
        });
        mobileQuery.addEventListener('change', updateViewportState);
        updateViewportState();

        const updateActiveLink = () => {
            const currentUrl = new URL(window.location.href);
            sidebar.querySelectorAll('.sidebar-nav .sidebar-link').forEach((link) => {
                const linkUrl = new URL(link.href);
                const active = linkUrl.pathname === currentUrl.pathname
                    && linkUrl.hash === currentUrl.hash;
                link.classList.toggle('is-active', active);
                if (active) {
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };
        window.addEventListener('hashchange', updateActiveLink);
        updateActiveLink();
    }

    const roleSelect = document.getElementById('account_role');

    if (roleSelect) {
        const fields = {
            name: document.querySelector('[data-account-field="name"]'),
            contact: document.querySelector('[data-account-field="contact"]'),
            specialization: document.querySelector('[data-account-field="specialization"]'),
            schedule: document.querySelector('[data-account-field="schedule"]')
        };
        const nameInput = document.getElementById('account_name');
        const usernameInput = document.getElementById('account_username');
        const contactInput = document.getElementById('account_contact');
        const specializationInput = document.getElementById('account_specialization');
        const scheduleInput = document.getElementById('account_schedule');

        const updateAccountFields = () => {
            const role = roleSelect.value;
            const isUniversityStaff = role === 'staff';
            const isClinicStaff = role === 'doctor';

            fields.name.hidden = role === 'admin' || role === '';
            fields.contact.hidden = !isUniversityStaff;
            fields.specialization.hidden = !isClinicStaff;
            fields.schedule.hidden = !isClinicStaff;

            nameInput.required = role !== '' && role !== 'admin';
            usernameInput.maxLength = isUniversityStaff ? 50 : 100;
            contactInput.required = isUniversityStaff;
            specializationInput.required = isClinicStaff;
            scheduleInput.required = isClinicStaff;
        };

        roleSelect.addEventListener('change', updateAccountFields);
        updateAccountFields();
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        const message = form.dataset.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]').forEach((button) => {
            button.disabled = true;
            button.classList.add('is-loading');
        });
        if (event.submitter instanceof HTMLButtonElement) {
            event.submitter.classList.add('is-loading');
        }
    });
})();
