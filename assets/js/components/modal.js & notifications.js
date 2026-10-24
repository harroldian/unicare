// assets/js/components/modal.js
function openModal(modalId) { document.getElementById(modalId)?.classList.add('active'); }
function closeModal(modalId) { document.getElementById(modalId)?.classList.remove('active'); }

// assets/js/components/notifications.js
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'alert-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}