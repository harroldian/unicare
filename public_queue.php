<?php
// public_queue.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Clinic Queue Display - Unicare</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/components/queue-display.css">
    <style>
        /* Standalone High-Contrast Styling for TV Display */
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 95vh;
            overflow: hidden;
        }

        .tv-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #334155;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .tv-header h1 {
            margin: 0;
            font-size: 2.2rem;
            color: #38bdf8;
        }

        .live-clock {
            font-size: 1.8rem;
            font-weight: bold;
            color: #94a3b8;
        }

        .display-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            flex-grow: 1;
        }

        .card-serving {
            background-color: #1e293b;
            border: 3px solid #38bdf8;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .card-serving h2 {
            font-size: 2.5rem;
            color: #94a3b8;
            margin: 0 0 10px 0;
        }

        .ticket-huge {
            font-size: 7.5rem;
            font-weight: 900;
            color: #38bdf8;
            margin: 0;
            line-height: 1;
        }

        .patient-name-display {
            font-size: 2rem;
            color: #f8fafc;
            margin-top: 20px;
        }

        .card-next {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 25px;
            display: flex;
            flex-direction: column;
        }

        .card-next h2 {
            font-size: 2rem;
            color: #94a3b8;
            margin-top: 0;
            border-bottom: 1px solid #334155;
            padding-bottom: 10px;
        }

        .tickets-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .next-ticket-box {
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: #f8fafc;
        }
    </style>
</head>
<body>

    <div class="tv-header">
        <h1>🏥 Unicare University Clinic — Live Waiting Queue</h1>
        <div class="live-clock" id="live-clock">00:00:00 AM</div>
    </div>

    <div class="display-grid">
        <!-- Now Serving Box -->
        <div class="card-serving">
            <h2>NOW SERVING</h2>
            <p class="ticket-huge" id="now-serving-ticket">#---</p>
            <p class="patient-name-display" id="now-serving-name">Please wait for your ticket call</p>
        </div>

        <!-- Up Next Box -->
        <div class="card-next">
            <h2>UP NEXT IN QUEUE</h2>
            <div class="tickets-list" id="next-tickets-container">
                <div class="next-ticket-box">#---</div>
            </div>
        </div>
    </div>

    <script>
        // Live Clock Implementation
        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // AJAX Polling Engine for Live Queue Updates
        let currentServing = null;

        function fetchLiveQueue() {
            fetch('api/fetch_queue.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update Now Serving
                        const servingTicket = data.now_serving !== '---' ? '#' + data.now_serving : '#---';
                        document.getElementById('now-serving-ticket').textContent = servingTicket;
                        document.getElementById('now-serving-name').textContent = data.patient_name;

                        // Render Upcoming Tickets
                        const container = document.getElementById('next-tickets-container');
                        container.innerHTML = '';

                        if (data.next_tickets.length > 0) {
                            data.next_tickets.forEach(ticketNum => {
                                const box = document.createElement('div');
                                box.className = 'next-ticket-box';
                                box.textContent = '#' + ticketNum;
                                container.appendChild(box);
                            });
                        } else {
                            container.innerHTML = '<div class="next-ticket-box" style="grid-column: span 2;">No upcoming tickets</div>';
                        }
                    }
                })
                .catch(err => console.error('Error fetching queue:', err));
        }

        // Poll API every 3 seconds
        setInterval(fetchLiveQueue, 3000);
        fetchLiveQueue();
    </script>
</body>
</html>