// assets/js/dashboards/patient.js
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');

    if (dateInput && timeSelect) {
        dateInput.addEventListener('change', (e) => {
            const selectedDate = e.target.value;
            fetch(`../api/fetch_slots.php?date=${selectedDate}`)
                .then(res => res.json())
                .then(data => {
                    timeSelect.innerHTML = '<option value="">-- Select Time --</option>';
                    if (data.closed) {
                        alert('Selected date is a clinic holiday or closure.');
                        dateInput.value = '';
                        return;
                    }
                    if (data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const opt = document.createElement('option');
                            opt.value = slot;
                            opt.textContent = new Date('1970-01-01T' + slot + 'Z').toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            timeSelect.appendChild(opt);
                        });
                    }
                })
                .catch(err => console.error('Error fetching time slots:', err));
        });
    }
});