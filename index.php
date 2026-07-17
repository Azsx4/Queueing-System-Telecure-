<?php
include 'database/config.php';

$today = date('Y-m-d');
$currentServing = $conn->query("
SELECT queue_number
FROM queues
WHERE status='called'
AND queue_date='$today'
ORDER BY called_at DESC
LIMIT 1
")->fetch_assoc();



if(isset($_GET['id']))
{
    $id = intval($_GET['id']);

    $result = $conn->query("
    SELECT *
    FROM queues
    WHERE id='$id'
    ");
}
else
{
    $result = $conn->query("
    SELECT *
    FROM queues
    WHERE status='pending'
    ORDER BY id DESC
    LIMIT 1
    ");
}

$current = null;

if($result->num_rows > 0){
    $current = $result->fetch_assoc();
}

$total = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>    
    <meta charset="UTF-8">
    <title>Generate Queue Number</title>


<link rel="stylesheet" href="assets/css/theme.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/kiosk.css">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script
src="assets/js/theme.js">
</script>


</head>


<body>

<?php include 'components/sidebar.php'; ?>

<?php include 'components/header.php'; ?>

<div class="main-content">

                            <button class="action-icon history queue-action action-history" type="button" title="History" onclick="showQueueCompletedHistory('all')">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="action-label">History</span>
                        </button>
<div class="kiosk-container">

<?php if(!$current): ?>

<div class="kiosk-card">

    <h2 id="welcome-text">
       Welcome!
    </h2>

    <p>
        Please press the button
        to get your queue number.
    </p>
    <form 
    action="actions/generate.php"
    method="POST">  
        <button id="prt-btn" class="kiosk-icon">
            <svg id="prt-icon"xmlns="http://www.w3.org/2000/svg" width="85" height="85" fill="currentColor" class="bi bi-ticket-detailed-fill" viewBox="0 0 16 16">
            <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6zm4 1a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5m0 5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5M4 8a1 1 0 0 0 1 1h6a1 1 0 1 0 0-2H5a1 1 0 0 0-1 1"/>
            </svg>
        </button>
    </form>
</div>

<?php else: ?>

<div class="kiosk-card">

    <div class="text-success mb-3">

        <i class="bi bi-check-circle-fill"></i>

        Queue Number Issued

    </div>

    <div class="queue-display">

        <?= str_pad(
            $current['queue_number'],
            3,
            '0',
            STR_PAD_LEFT
        ); ?>

    </div>

    <div class="info-box">

        <h5>
            Estimated Waiting Time
        </h5>

        <h3>
            10 - 15 mins
        </h3>

        <hr>

        <h6>
            Currently Serving
        </h6>

        <h3>

            <?= $currentServing
            ? str_pad(
                $currentServing['queue_number'],
                3,
                '0',
                STR_PAD_LEFT
            )
            : "---"; ?>

        </h3>

    </div>

    <div class="mt-4">

        <a
        href="kiosk/print.php?id=<?= $current['id'] ?>"
        class="btn btn-primary">

            Print Ticket

        </a>

        <a
        href="index.php"
        class="btn btn-outline-primary">

            New Number

        </a>

    </div>

</div>

<?php endif; ?>

</div>
</div>

<!-- Queue History Modal -->
<div class="modal fade" id="queueHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="queueHistoryTitle"></h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="queueHistoryContent"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/index.js"></script>

</body>
</html>