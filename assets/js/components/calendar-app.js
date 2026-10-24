// assets/js/components/calendar-app.js
document.addEventListener('DOMContentLoaded', () => {
    const closureForm = document.getElementById('closure-form');
    if (closureForm) {
        closureForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(closureForm);

            fetch('../api/toggle_closure.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(err => console.error('Error toggling closure:', err));
        });
    }
});