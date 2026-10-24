// assets/js/components/queue-poll.js
function initQueuePoller(callback) {
    setInterval(() => {
        fetch('../api/fetch_queue.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && typeof callback === 'function') {
                    callback(data);
                }
            })
            .catch(err => console.error('Queue poll error:', err));
    }, 3000);
}