<?php

include 'database/config.php';

$today = date('Y-m-d');

$allCount = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
")->fetch_assoc()['total'];

$waitingTab = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE status='waiting'
AND queue_date='$today'
")->fetch_assoc()['total'];

$calledTab = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE status='called'
AND queue_date='$today'
")->fetch_assoc()['total'];

$doneTab = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE status='done'
AND queue_date='$today'
")->fetch_assoc()['total'];

$cancelTab = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE status='cancelled'
AND queue_date='$today'
")->fetch_assoc()['total'];


$limit = 20;

$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$countSql = "
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
";

//if($statusFilter != '')
//{
//  $countSql .= "
//   AND status='$statusFilter'
//";
//}

$totalRows =
    $conn->query($countSql)
        ->fetch_assoc()['total'];

$totalPages =
    ceil($totalRows / $limit);

$date = isset($_GET['date'])
? $_GET['date']
: date('Y-m-d');

$history = $conn->query("
SELECT *
FROM queues
WHERE queue_date='$date'
AND status <> 'archived'
ORDER BY queue_number DESC
");

$totalToday = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$date'
AND status <> 'archived'
")->fetch_assoc()['total'];

$statusFilter = $_GET['status'] ?? '';

$sql = "
SELECT *
FROM queues
WHERE queue_date = '$today'
";

if ($statusFilter != '') {
    $sql .= " AND status='$statusFilter'";
}

$sql .= "
ORDER BY queue_number ASC
LIMIT $offset, $limit
";

$queues = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/theme.css">
<link href="assets/css/styles.css" rel="stylesheet">
<link href="assets/css/reception.css" rel="stylesheet">
<script
src="assets/js/theme.js">
</script>

</head>

<body>

<?php include 'components/sidebar.php'; ?>
<?php include 'components/header.php'; ?>
<div class="container mt-4">
<div class="main-content">

<form method="GET">

<div class="row mb-2">

<div class="col-md-2">

<input
type="date"
name="date"
class="form-control"
value="<?= $date ?>">

</div>

<div class="col-md-2">

<button
class="btn btn-primary">
Search
</button>

</div>

</div>

</form>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>Queue</th>
<th>Status</th>
<th>Issued</th>
<th>Called</th>
<th>Completed</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = $history->fetch_assoc()): ?>

<tr>

<td>

<?= str_pad(
$row['queue_number'],
3,
'0',
STR_PAD_LEFT
); ?>

</td>

<td>

<?php

$statusClass = match($row['status']){

'done' => 'success',

'called' => 'primary',

'waiting' => 'warning',

'cancelled' => 'danger',

default => 'secondary'

};

?>

<span class="badge bg-<?= $statusClass; ?>">

<?= strtoupper($row['status']); ?>

</span>

</td>

<td>

<?= $row['issued_at']; ?>

</td>

<td>

<?= $row['called_at']
? $row['called_at']
: '-'; ?>

</td>

<td>

<?= $row['completed_at']
? $row['completed_at']
: '-'; ?>

</td>

<td>

<a
class="btn btn-sm btn-primary"
href="reprint.php?id=<?= $row['id']; ?>" target="_blank"> 
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
</svg>
</a>
<a
class="btn btn-danger btn-sm"
onclick="archiveQueue(<?= $row['id']; ?>)">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
  <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
</svg>

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>
<div class="alert alert-info">

Total Queue Records:
<strong><?= $totalToday ?></strong>

</div>
</div>
</div>
<script>

function archiveQueue(id)
{
    // Confirmation first
    if(!confirm(
        "Are you sure you want to delete this queue record?\n\nThis action will remove it from the History list."
    ))
    {
        return;
    }

    // Ask for password
    let password = prompt(
        "Enter IT Admin Password:"
    );

    if(password == null || password == "")
    {
        return;
    }

    let form = document.createElement("form");
    form.method = "POST";
    form.action = "archive_queue.php";

    let idInput = document.createElement("input");
    idInput.type = "hidden";
    idInput.name = "id";
    idInput.value = id;

    let passInput = document.createElement("input");
    passInput.type = "hidden";
    passInput.name = "password";
    passInput.value = password;

    form.appendChild(idInput);
    form.appendChild(passInput);

    document.body.appendChild(form);
    form.submit();
}

</script>

               <!-- Search -->

                <div class="queue-toolbar mb-4">

                    <div class="search-box">
                        <div class="position-relative">
                            <input
                                type="text"
                                id="queueSearch"
                                class="form-control rounded-pill search-bar pe-5"
                                placeholder="Search"
                                aria-label="Search">
                            <button class="btn position-absolute top-50 end-0 translate-middle-y" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>


                    <div class="queue-toolbar">

                        <div class="status-tabs">

                            <a
                                href="reception.php"
                                class="status-tab <?= $statusFilter == '' ? 'active' : '' ?>">
                                All (<?= $allCount ?>)
                            </a>

                            <a
                                href="reception.php?status=waiting"
                                class="status-tab <?= $statusFilter == 'waiting' ? 'active' : '' ?>">
                                Waiting (<?= $waitingTab ?>)
                            </a>


                            <a
                                href="reception.php?status=called"
                                class="status-tab <?= $statusFilter == 'called' ? 'active' : '' ?>">
                                Called (<?= $calledTab ?>)
                            </a>

                            <a
                                href="reception.php?status=done"
                                class="status-tab <?= $statusFilter == 'done' ? 'active' : '' ?>">
                                Completed(<?= $doneTab ?>)
                            </a>

                            <a
                                href="reception.php?status=cancelled"
                                class="status-tab <?= $statusFilter == 'cancelled' ? 'active' : '' ?>">
                                Cancelled(<?= $cancelTab ?>)
                            </a>

                        </div>

                    </div>

                </div>

                <!-- Queue Cards -->

                <div class="queue-grid" id="queueContainer">
                    <?php while ($row = $queues->fetch_assoc()): ?>
                        <div
                            class="queue-mini-card queue-item<?= ($current && $row['id'] == $current['id']) ? ' current' : '' ?>" data-id="<?= $row['id']; ?>" data-status="<?= $row['status']; ?>"
                            data-queue="<?= str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?>">


                            <?php
                            /*
$statusClass = match($row['status']) {

    'waiting' => 'warning',
    'called' => 'primary',
    'done' => 'success',
    'cancelled' => 'danger',
    default => 'secondary'
};
*/
                            ?>



                            <div class="queue-mini-number">

                                <?= str_pad(
                                    $row['queue_number'],
                                    3,
                                    '0',
                                    STR_PAD_LEFT
                                ); ?>

                            </div>

                            <div class="queue-status"><!-- bg-<? //= $statusClass; ?> -->

                                <?= strtoupper($row['status']); ?>

                            </div>

                            <div class="queue-time">

                                <?= $row['issued_at']; ?>

                            </div>

                            <div class="queue-actions">

                                <a
                                    class="btn btn-primary btn-sm"
                                    href="#"
                                    onclick="callQueue(event,
    <?= $row['id']; ?>,
    '<?= str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?>'
    )">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-megaphone" viewBox="0 0 16 16">
                                        <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0v-.214c-2.162-1.241-4.49-1.843-6.912-2.083l.405 2.712A1 1 0 0 1 5.51 15.1h-.548a1 1 0 0 1-.916-.599l-1.85-3.49-.202-.003A2.014 2.014 0 0 1 0 9V7a2.02 2.02 0 0 1 1.992-2.013 75 75 0 0 0 2.483-.075c3.043-.154 6.148-.849 8.525-2.199zm1 0v11a.5.5 0 0 0 1 0v-11a.5.5 0 0 0-1 0m-1 1.35c-2.344 1.205-5.209 1.842-8 2.033v4.233q.27.015.537.036c2.568.189 5.093.744 7.463 1.993zm-9 6.215v-4.13a95 95 0 0 1-1.992.052A1.02 1.02 0 0 0 1 7v2c0 .55.448 1.002 1.006 1.009A61 61 0 0 1 4 10.065m-.657.975 1.609 3.037.01.024h.548l-.002-.014-.443-2.966a68 68 0 0 0-1.722-.082z" />
                                    </svg>
                                </a>

                                <?php if ($row['status'] == 'called'): ?>

                                    <a
                                        id="call-btn"
                                        class="btn btn-success btn-sm"
                                        href="#"
                                        onclick="doneQueue(event, <?= $row['id'] ?>)">
                                        Done
                                    </a>

                                <?php else: ?>

                                    <button
                                        id="call-btn-dis"
                                        class="btn btn-dark btn-sm"
                                        disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                        </svg>
                                    </button>

                                <?php endif; ?>

                                <a
                                    id="cancel-btn"
                                    class="btn btn-sm"
                                    href="#"
                                    onclick="openCancelModal(event, <?= $row['id'] ?>, '<?= str_pad($row['queue_number'], 3, '0', STR_PAD_LEFT); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-octagon" viewBox="0 0 16 16">
                                        <path d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1z" />
                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
                                    </svg>
                                </a>

                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancelConfirmModalLabel">Confirm Cancel</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to cancel queue <strong id="cancelQueueNumber"></strong>?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, keep</button>
                                <button type="button" class="btn btn-danger" onclick="confirmCancelQueue()">Yes, cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pagination-wrapper">

                    <?php if ($page > 1): ?>

                        <a
                            class="btn btn-outline-secondary"
                            href="?page=<?= $page - 1 ?>">

                            Previous

                        </a>

                    <?php endif; ?>


                    <?php

                    for (
                        $i = 1;
                        $i <= $totalPages;
                        $i++
                    ):

                    ?>

                        <a
                            class="btn <?= $i == $page
                                            ? 'btn-primary'
                                            : 'btn-outline-secondary'; ?>"
                            href="?page=<?= $i ?>&status=<?= $statusFilter ?>">

                            <?= $i ?>

                        </a>

                    <?php endfor; ?>


                    <?php if ($page < $totalPages): ?>

                        <a
                            class="btn btn-outline-secondary"
                            href="?page=<?= $i ?>&status=<?= $statusFilter ?>">

                            Next

                        </a>

                    <?php endif; ?>

                </div>
                
                <script>
                    let cancelQueueId = null;
                    let currentCalledQueueId = null;
                    let currentCalledQueueNumber = null;

                    function updateNowServing() {
                        const servingDisplay = document.getElementById('nowServingNumber');
                        if (currentCalledQueueNumber) {
                            servingDisplay.textContent = currentCalledQueueNumber;
                        } else {
                            servingDisplay.textContent = '---';
                        }
                    }

                    function updateActionButtonsState() {
                        const hasCalledQueue = currentCalledQueueId !== null;
                        document.getElementById("actionDoneBtn").disabled = !hasCalledQueue;
                        document.getElementById("actionNextBtn").disabled = !hasCalledQueue;
                        document.getElementById("actionRecallBtn").disabled = !hasCalledQueue;
                        document.getElementById("actionCancelBtn").disabled = !hasCalledQueue;
                        updateNowServing();
                    }

                    function actionCall() {
                        // Prefer the first visible waiting queue; fall back to first waiting or Now Serving
                        const waitingItems = Array.from(document.querySelectorAll('.queue-item[data-status="waiting"]'));

                        let waitingQueue = waitingItems.find(item => {
                            const style = window.getComputedStyle(item);
                            return style.display !== 'none' && style.visibility !== 'hidden' && item.offsetParent !== null;
                        });

                        if (!waitingQueue && waitingItems.length > 0) {
                            waitingQueue = waitingItems[0];
                        }

                        // If still not found, try to resolve using the Now Serving display (in case of filters/pagination)
                        if (!waitingQueue) {
                            const now = document.getElementById('nowServingNumber')?.textContent.trim();
                            if (now && now !== '---') {
                                const match = Array.from(document.querySelectorAll('.queue-item')).find(item => item.getAttribute('data-queue') === now);
                                if (match && match.getAttribute('data-status') === 'waiting') {
                                    waitingQueue = match;
                                } else if (match) {
                                    // try to find next waiting after nowServing number
                                    const qNum = parseInt(now, 10);
                                    waitingQueue = waitingItems.find(item => parseInt(item.getAttribute('data-queue'), 10) > qNum);
                                }
                            }
                        }

                        if (!waitingQueue) {
                            alert("No waiting queue available");
                            return;
                        }

                        const queueNumber = waitingQueue.querySelector('.queue-mini-number').textContent.trim();
                        const queueId = waitingQueue.getAttribute('data-id');

                        let speech = new SpeechSynthesisUtterance(queueNumber);
                        speech.rate = 0.9;

                        speech.onend = function() {
                            fetch("actions/call_queue.php?id=" + queueId)
                                .then(() => {
                                    currentCalledQueueId = queueId;
                                    currentCalledQueueNumber = queueNumber;
                                    updateActionButtonsState();
                                });
                        };

                        speechSynthesis.speak(speech);
                    }

                    function findNextWaitingQueue() {
                        const waitingItems = Array.from(document.querySelectorAll('.queue-item[data-status="waiting"]'));

                        return waitingItems.find(item => {
                            const style = window.getComputedStyle(item);
                            return style.display !== 'none' && style.visibility !== 'hidden' && item.offsetParent !== null;
                        }) || waitingItems[0] || null;
                    }

                    function actionDone() {
                        if (!currentCalledQueueId)
                            return;

                        fetch(
                                "actions/done_queue.php?id=" +
                                currentCalledQueueId
                            )
                            .then(() => {



                                currentCalledQueueId = null;
                                currentCalledQueueNumber = null;

                                updateActionButtonsState();
                                showToast("Queue completed.");

                                location.reload();
                            });
                    }

                    function actionNext()
                    {

                        const btn =
                        document.getElementById(
                            "actionNextBtn"
                        );

                        btn.disabled = true;

                        const nextQueue =
                            findNextWaitingQueue();

                        if (!nextQueue)
                        {
                            return;
                        }

                        const nextQueueId =
                            nextQueue.dataset.id;

                        const nextQueueNumber =
                            nextQueue.dataset.queue;

                        let speech =
                            new SpeechSynthesisUtterance(
                                nextQueueNumber
                            );

                        speech.rate = 0.9;

                        speech.onend = function()
                        {
                            fetch(
                                "actions/call_queue.php?id=" +
                                nextQueueId
                            )
                            .then(() =>
                            {
                                location.reload();
                            });
                        };

                        speechSynthesis.speak(speech);
}

                    function actionRecall() {
                        if (!currentCalledQueueNumber) return;

                        let speech = new SpeechSynthesisUtterance(currentCalledQueueNumber);
                        speech.rate = 0.9;
                        speechSynthesis.speak(speech);
                    }

                    function actionCancel() {
                        if (!currentCalledQueueId) return;

                        cancelQueueId = currentCalledQueueId;
                        document.getElementById("cancelQueueNumber").textContent = currentCalledQueueNumber;

                        const modalEl = document.getElementById("cancelConfirmModal");
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }

                    function callQueue(event, id, number) {
                        event.preventDefault();

                        let speech = new SpeechSynthesisUtterance(

                            number

                        );

                        speech.rate = 0.9;

                        speech.onend = function() {
                            // Make request in background without navigating (page stays in same position)
                            fetch("actions/call_queue.php?id=" + id)
                                .then(() => {
                                    currentCalledQueueId = id;
                                    currentCalledQueueNumber = number;
                                    updateActionButtonsState();
                                });
                        };

                        speechSynthesis.speak(speech);
                    }

                    function actionSkip() {
                        const nextQueue =
                            findNextWaitingQueue();

                        if (!nextQueue) {
                            alert("No waiting queue");
                            return;
                        }

                        const queueId =
                            nextQueue.dataset.id;

                        openCancelModal(
                            new Event('click'),
                            queueId,
                            nextQueue.dataset.queue
                        );
                    }

                    function doneQueue(event, id) {
                        event.preventDefault();
                        fetch("actions/done_queue.php?id=" + id);
                    }

                    function openCancelModal(event, id, number) {
                        event.preventDefault();
                        cancelQueueId = id;
                        document.getElementById("cancelQueueNumber").textContent = number;

                        const modalEl = document.getElementById("cancelConfirmModal");
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }

                    function confirmCancelQueue() {
                        if (!cancelQueueId) {
                            return;
                        }

                        fetch("actions/cancel_queue.php?id=" + cancelQueueId)
                            .then(() => {
                                const modalEl = document.getElementById("cancelConfirmModal");
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) {
                                    modal.hide();
                                }
                                currentCalledQueueId = null;
                                currentCalledQueueNumber = null;
                                cancelQueueId = null;
                                updateActionButtonsState();
                            });
                    }

                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        // If server has a current called queue, use it to initialize state
                        <?php if ($current): ?>
                            currentCalledQueueId = "<?= $current['id']; ?>";
                            currentCalledQueueNumber = "<?= str_pad($current['queue_number'], 3, '0', STR_PAD_LEFT); ?>";
                        <?php else: ?>
                            const calledQueue = document.querySelector('.queue-item[data-status="called"]');
                            if (calledQueue) {
                                currentCalledQueueId = calledQueue.getAttribute('data-id');
                                currentCalledQueueNumber = calledQueue.querySelector('.queue-mini-number').textContent.trim();
                            }
                        <?php endif; ?>
                        updateActionButtonsState();
                    });
                </script>

                <script>
                    document
                        .getElementById("queueSearch")
                        .addEventListener("keyup", function() {

                            let value =
                                this.value.toLowerCase();

                            document
                                .querySelectorAll(".queue-item")
                                .forEach(function(item) {

                                    let queue =
                                        item.dataset.queue.toLowerCase();

                                    item.style.display =
                                        queue.includes(value) ?
                                        "" :
                                        "none";

                                });

                        });
                </script>

                <script>
                    document
                        .querySelectorAll(".status-tab")
                        .forEach(tab => {

                            tab.addEventListener(
                                "click",
                                function() {

                                    document
                                        .querySelectorAll(".status-tab")
                                        .forEach(btn =>
                                            btn.classList.remove("active")
                                        );

                                    this.classList.add("active");

                                    let status =
                                        this.dataset.status;

                                    document
                                        .querySelectorAll(".queue-item")
                                        .forEach(card => {

                                            if (status === "all") {
                                                card.style.display = "";
                                                return;
                                            }

                                            card.style.display =
                                                card.dataset.status === status ?
                                                "" :
                                                "none";

                                        });

                                }
                            );

                        });
                </script>
</body>

</html>