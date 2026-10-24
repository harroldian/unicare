// assets/js/components/notifications.js

/**
 * Display a floating toast notification banner on the screen.
 * @param {string} message - The message to display.
 * @param {string} type - The notification type ('success', 'error', 'info'). Default is 'info'.
 */
function showToast(message, type = 'info') {
    // Check if a toast container already exists, if not create one
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(container);
    }

    // Create toast item element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    // Background color based on type
    let bgColor = '#0284c7'; // default blue
    if (type === 'success') bgColor = '#10b981';
    if (type === 'error') bgColor = '#ef4444';

    toast.style.cssText = `
        background-color: ${bgColor};
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        font-size: 0.9rem;
        font-weight: 500;
        opacity: 0;
        transform: translateY(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        max-width: 320px;
        word-break: break-word;
    `;
    toast.textContent = message;

    container.appendChild(toast);

    // Trigger smooth entrance animation
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    // Automatically remove toast after 4 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}