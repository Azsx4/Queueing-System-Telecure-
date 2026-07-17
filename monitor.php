<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Queue Monitor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/monitor.css">
    <script src="assets/js/theme.js"></script>

</head>

<body>

<div class="monitor-wrapper">

    <!-- Header -->

    <header class="monitor-header">

        <div class="clinic-name">

            <h1>TELECURE</h1>

            <span>MEDICAL AND DIAGNOSTIC CENTER</span>

        </div>

        <div class="clock-area">

            <div id="clock"></div>

            <div id="date"></div>

        </div>

    </header>

    <!-- Active Reception -->

    <section>

        <h2 class="section-title">
            NOW SERVING
        </h2>

        <div class="row g-4" id="activeReceptionContainer">

  
        </div>

    </section>

    <!-- Upcoming -->

    <section class="mt-5">

        <h2 class="section-title">

            UPCOMING QUEUES

        </h2>

        <div class="upcoming-grid" id="upcomingContainer">

        </div>

    </section>

    <!-- Footer -->

    <footer>

        Please prepare when your queue number is near.

    </footer>

</div>

<script src="assets/js/monitor.js"></script>

</body>

</html>