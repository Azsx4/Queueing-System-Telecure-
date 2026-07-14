<?php

session_start();
if (
    isset($_SESSION['reception_date']) &&
    $_SESSION['reception_date'] != date('Y-m-d')
) {
    session_unset();
    session_destroy();

    session_start();
}

include 'database/config.php';

// If a reception_id exists in session, load its persistent settings from DB
if (isset($_SESSION['reception_id'])) {
    $stmt = $conn->prepare("SELECT * FROM receptions WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['reception_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $receptionRow = $res->fetch_assoc();
        if ($receptionRow) {
            // populate session values from DB so code below can keep using session variables
            $_SESSION['reception_name'] = $receptionRow['name'];
            $_SESSION['active_slots'] = (int) $receptionRow['active_slots'];
            $_SESSION['reception_start'] = $receptionRow['started_at'];
            $_SESSION['reception_date'] = date('Y-m-d', strtotime($receptionRow['started_at']));
        } else {
            // invalid reception_id stored in session
            unset($_SESSION['reception_id']);
        }
    }
}

if (isset($_POST['change_reception'])) {
    // only clear the reception assignment for this user/session
    unset($_SESSION['reception_id']);
    unset($_SESSION['reception_name']);
    unset($_SESSION['active_slots']);
    unset($_SESSION['reception_start']);
    unset($_SESSION['reception_date']);

    header("Location: reception.php");
    exit;
}

if (isset($_POST['start_reception'])) {
    // Persist reception settings into `receptions` table and store the id in session
    $name = trim($_POST['reception_name']);
    $active_slots = intval($_POST['active_slots']);
    $started_at = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO receptions (name, active_slots, started_at) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sis', $name, $active_slots, $started_at);
        $stmt->execute();
        $newId = $stmt->insert_id;

        $_SESSION['reception_id'] = $newId;
        $_SESSION['reception_name'] = $name;
        $_SESSION['active_slots'] = $active_slots;
        $_SESSION['reception_date'] = date('Y-m-d');
        $_SESSION['reception_start'] = $started_at;
    }

    header("Location: reception.php");
    exit;
}

$today = date('Y-m-d');

$activeSlots = $_SESSION['active_slots'] ?? 1;

$countResult = $conn->query("
SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) AS waiting,
    SUM(CASE WHEN status = 'called' THEN 1 ELSE 0 END) AS called,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
FROM queues
WHERE queue_date = '$today'
")->fetch_assoc();

$activeCount = $countResult['called'];
$totalRows = $countResult['total'];
$waitingCount = $countResult['waiting'];
$doneCount = $countResult['done'];
$cancelledCount = $countResult['cancelled'];

/* Replace by $activeQueues */
$current = $conn->query("
SELECT *
FROM queues
WHERE status='called'
AND queue_date='$today'
ORDER BY called_at DESC
LIMIT 1
")->fetch_assoc();

$activeQueues = $conn->query(
"SELECT
    id,
    queue_number,
    called_at,
    TIMESTAMPDIFF(SECOND, called_at, NOW()) AS waiting_seconds,
    reception_name,
    called_by
FROM queues
WHERE status='called'
AND queue_date='$today'
ORDER BY called_at ASC
"
);

$activeCount = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE status='called'
AND queue_date='$today'
")->fetch_assoc()['total'];

$activeSlots =
$_SESSION['active_slots'] ?? 1;



$currentQueueNumber = $current ? (int) $current['queue_number'] : 0;

$nextRows = [];


if ($current) {
    $nextResult = $conn->query(
        "SELECT *
        FROM queues
        WHERE status='waiting'
        AND queue_date='$today'
        AND queue_number > {$currentQueueNumber}
ORDER BY

CASE
WHEN priority_order > 0 THEN 0
ELSE 1
END,

priority_order ASC,

queue_number ASC
        LIMIT 6"
    );
} else {
    $nextResult = $conn->query(
        "SELECT *
        FROM queues
        WHERE status='waiting'
        AND queue_date='$today'
ORDER BY

CASE
WHEN priority_order > 0 THEN 0
ELSE 1
END,

priority_order ASC,

queue_number ASC
        LIMIT 6"
    );
}

while ($row = $nextResult->fetch_assoc()) {
    $nextRows[] = $row;
}



?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="assets/css/reception.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <script src="assets/js/theme.js"></script>
              
    
</head>

<style>
/* ===========================
   Active Queue Cards
=========================== */

#activeQueueContainer {
  display: flex;

  flex-direction: column;

  gap: 15px;
}

.active-queue-card {
  background: #181818;

  border-radius: 18px;

  padding: 20px;

  margin-bottom: 15px;

  border-left: 6px solid #0ea5ff;

}

.active-queue-card:hover {
  transform: translateY(-2px);

  box-shadow: 0 8px 18px rgba(207, 207, 207, 0.08);
}

.queue-card-header {
  border-bottom: 1px solid #ececec;

  padding-bottom: 12px;

  margin-bottom: 15px;

  display: flex;

  justify-content: space-between;

  align-items: center;
}

.queue-title {
  color: #fff;

  display: inline-flex;

  align-items: center;

  gap: 0.5rem;
}

.queue-card-body {
  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 1.5rem;

  flex-wrap: wrap;
  
}

.queue-number-col {
  flex: 0 0 auto;

  min-width: 120px;

  display: flex;

  align-items: center;

  justify-content: center;
}

.queue-no {
  color: #0ea5ff;

  font-size: 4rem;

  font-weight: 800;

  line-height: 1;

  margin: 0;
}

.queue-info {
  flex: 1 1 240px;

  display: flex;

  flex-direction: column;

  gap: 0.65rem;

  color: #fff;
}

.queue-info div {
  display: flex;

  align-items: center;

  gap: 0.5rem;

  font-size: 0.95rem;
}

.queue-info i {
  color: #0ea5ff;
}

.queue-actions {
  flex: 0 0 auto;

  display: flex;

  align-items: center;

  gap: 0.5rem;

  justify-content: flex-end;

  color: #fff;
}

.action-icon {
  width: 44px;

  height: 44px;

  display: inline-flex;

  align-items: center;

  justify-content: center;

  border-radius: 50%;

  border: none;

  color: #fff;

  cursor: pointer;

  position: relative;

  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.action-icon:hover {
  transform: translateY(-2px);

  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.action-icon.recall {
  background: #0d6efd;
}

.action-icon.done {
  background: #3e4140;
}

.action-icon.done:hover {
  background: #198754;
}

.action-icon.missing {
  background: #cf2717;

  color: #000;
}

.queue-badge {
  background: #202d27;
  color: #198754;
  border:1px solid #198754
}

.action-icon .action-label {
  position: absolute;

  bottom: -2.25rem;

  left: 50%;

  transform: translateX(-50%);

  background: rgba(0, 0, 0, 0.85);

  color: #fff;

  padding: 4px 8px;

  border-radius: 999px;

  font-size: 0.75rem;

  white-space: nowrap;

  opacity: 0;

  visibility: hidden;

  transition: opacity 0.16s ease;
}

.action-icon:hover .action-label {
  opacity: 1;

  visibility: visible;
}
</style>
<body>

    <?php
    if (
        !isset($_SESSION['reception_name'])
        ||
        empty($_SESSION['reception_name'])
    ): ?>

        <div class="container py-5">

            <div
                class="card mx-auto shadow"
                style="max-width:500px;">

                <div class="card-body">

                    <h3 class="mb-4">
                        Start Reception
                    </h3>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Reception Name
                            </label>

                            <input
                                type="text"
                                name="reception_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Active Queue Slots
                            </label>

                            <select
                                name="active_slots"
                                class="form-select">

                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>

                            </select>

                        </div>

                        <button
                            type="submit"
                            name="start_reception"
                            class="btn btn-primary w-100">

                            Start Reception

                        </button>

                    </form>

                </div>

            </div>

        </div>

    <?php 
        exit;
    endif;
    ?>

    <?php include 'components/sidebar.php'; ?>
    <?php include 'components/header.php'; ?>
    <div class="main-content">
        <div id="reception-header" class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3 header-top-row">
                <div>
                    <strong>
                        Reception:
                        <span id="reception-name">
                            <?= htmlspecialchars($_SESSION['reception_name']); ?>
                        </span>
                    </strong>
                    <small id="reception-status" class="text-success">&nbsp;(Active)</small>
                    <br>
                    <small>Started: <?= isset($_SESSION['reception_start']) ? htmlspecialchars($_SESSION['reception_start']) : 'N/A' ?></small>
                </div>


            </div>
                <div id="active-queue-counter">
                    Active Queue:
                    <span id="active-count"><?= $activeCount ?>/<?= $activeSlots ?></span>
                </div>

    
            <div class="d-flex flex-wrap align-items-center gap-3 header-controls-row">
                                <form id="changeReceptionForm" method="POST" class="change-reception-form">
                                    <input type="hidden" name="change_reception" value="1">
                                    <button
                                        type="button"
                                        id="changeReceptionBtn"
                                        class="change-reception-btn"
                                        >
                                        <i class="fas fa-exchange-alt"></i>
                                        <span class="action-label">Change Reception</span>
                                    </button>
                                </form>
                <div id="voice-settings">
                    <select id="voiceSelect" class="voice-select">
                        <option value="">Voice Settings</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Hero Now Serving -->
            <!--<div
class="now-serving-card"
onclick="announceCurrentQueue()">  <?= $currentQueueNumber ?> -->

            <div
                class="hero-serving col-md-6">
      <div class="card-header">
        <div class="header-left">
            <!--Card Header -->
            <div>
                <div class="title text-start inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16" style="color:#0ea5ff;">
                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                </svg>
                &nbsp; ACTIVE QUEUES
                <span style="font-size: 12px; color:gray;">
                    &nbsp;&nbsp;Click a queue card to recall (voice)
                </span></div>
            </div>
        </div>
    </div>

    <hr>
                <div class="hero-label"
                style="position:relative; cursor:pointer;"
                onclick="announceCurrentQueue()">
                    
                </div>
<div id="activeQueueContainer">

<?php if($activeQueues->num_rows > 0): ?>

    <?php while($queue = $activeQueues->fetch_assoc()): ?>

        <?php
        $minutes = floor($queue['waiting_seconds'] / 60);
        $seconds = $queue['waiting_seconds'] % 60;

        $callerName = $queue['reception_name'] ?? 'Unknown';
        $isCaller = isset($_SESSION['reception_id']) && isset($queue['called_by']) && (int)$queue['called_by'] === (int)$_SESSION['reception_id'];

        ?>

        <div class="active-queue-card"
             data-id="<?= $queue['id'] ?>"
             data-queue="<?= str_pad($queue['queue_number'],3,'0',STR_PAD_LEFT) ?>"
             onclick="recallQueue(
                <?= $queue['id'] ?>,
                '<?= str_pad($queue['queue_number'],3,'0',STR_PAD_LEFT) ?>'
             )">

            <div class="queue-card-header">
                <div class="queue-title">
                    <i class="fas fa-user"></i>

                    <strong>Reception :</strong>

                    <?= htmlspecialchars($callerName) ?>
                </div>

                <span class="queue-badge">
                    ACTIVE
                </span>
            </div>

            <div class="queue-card-body">
                <div class="queue-number-col">
                    <span class="queue-no">
                        <?= str_pad($queue['queue_number'],3,'0',STR_PAD_LEFT) ?>
                    </span>
                </div>

                <div class="queue-info">
                    <div>
                        <i class="fas fa-clock"></i>
                        <strong>Called :</strong>
                        <?= date(
                            "h:i:s A",
                            strtotime($queue['called_at'])
                        ) ?>
                    </div>

                    <div>
                        <i class="fas fa-user"></i>
                        <strong>Called by :</strong>
                        <?= htmlspecialchars($callerName) ?>
                    </div>

                    <div>
                        <i class="fas fa-stopwatch"></i>
                        <strong>Waiting :</strong>
                        <span class="waiting-time">
                            <?= sprintf("%02d:%02d",$minutes,$seconds) ?>
                        </span>
                    </div>
                </div>

                <div class="queue-actions">
                    <?php if ($isCaller): ?>
                        <button class="action-icon done" type="button" onclick="event.stopPropagation(); doneQueue(<?= $queue['id'] ?>)">
                            <i class="fas fa-check-circle"></i>
                            <span class="action-label">Done</span>
                        </button>

                        <button class="action-icon missing queue-action action-missing" type="button" onclick="event.stopPropagation(); showMissingModal(<?= $queue['id'] ?>,
                        '<?= str_pad($queue['queue_number'],3,'0',STR_PAD_LEFT) ?>'
            )">
                            <i class="fas fa-user-slash"></i>
                            <span class="action-label">Missing</span>
                        </button>
                    <?php else: ?>
                        <button class="action-icon done" type="button" disabled title="Called by <?= htmlspecialchars($callerName) ?>">
                            <i class="fas fa-check-circle"></i>
                            <span class="action-label">Done</span>
                        </button>

                        <button class="action-icon missing queue-action action-missing" type="button" disabled title="Called by <?= htmlspecialchars($callerName) ?>">
                            <i class="fas fa-user-slash"></i>
                            <span class="action-label">Missing</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

<div class="empty-active">

    <i class="fas fa-users fa-5x text-secondary mb-3"></i>

    <h5>No active queues</h5>

    <small class="text-secondary">

        Click "Next Queue" to call the next patient.

    </small>

</div>

<?php endif; ?>

</div>





                <!-- </div>-->
            </div>

                <!-- Quick Actions -->
            <div
                class="col-md-6">
                <div
                class="hero-serving col-md-12">

                <div class="hero-label text-start">
                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi 
                 zbi-calendar2-week" viewBox="0 0 16 16" style="color:#0ea5ff;">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5zM11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                </svg>&nbsp; UPCOMING QUEUE
                </div><br>


                <!-- Next Numbers -->
                <div class="row mb-4">
                  <?php  for ($i = 0; $i < 6; $i++): ?>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card">
<?php if (
    isset($nextRows[$i]) &&
    !empty($nextRows[$i]['priority_order']) &&
    $nextRows[$i]['priority_order'] > 0
): ?>

<div class="priority-ribbon">
    RETURNED
</div>

<?php endif; ?>
                                <small>UPCOMING</small>
                                <div class="stats-number-queue" style="color:#0ea5ff; font-size: 35px; font-weight: bold;">
                                    <?= isset($nextRows[$i])
                                        ? str_pad($nextRows[$i]['queue_number'], 3, '0', STR_PAD_LEFT)
                                        : '---'; ?>
                                </div>
                            </div>
                        </div> 

                    <?php endfor;  ?>

                  </DIV> 
                </div><br>
                <div class="action-panel col-lg-12">

                    <!--<div class="d-flex justify-content-between align-items-center"> -->


   
                        <!--
                            <div class="d-flex gap-2 mb-2">

                                <button
                                    id="actionDoneBtn"
                                    class="btn btn-success flex-fill"
                                    disabled
                                    onclick="actionDone()">
                                    Done
                                </button>

                                <button
                                    id="actionCancelBtn"
                                    class="btn btn-warning flex-fill"
                                    disabled
                                    onclick="actionCancel()">
                                    Skip
                                </button>

                            </div> -->

                            <button
                                id="actionNextBtn"
                                class="btn btn-primary w-100 p-3"
                                onclick="actionNext()"
                                <?= ($activeCount >= $activeSlots) ? 'disabled' : ''; ?>>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-volume-up-fill" viewBox="0 0 16 16">
                                    <path d="M11.536 14.01A8.47 8.47 0 0 0 14.026 8a8.47 8.47 0 0 0-2.49-6.01l-.708.707A7.48 7.48 0 0 1 13.025 8c0 2.071-.84 3.946-2.197 5.303z"/>
                                    <path d="M10.121 12.596A6.48 6.48 0 0 0 12.025 8a6.48 6.48 0 0 0-1.904-4.596l-.707.707A5.48 5.48 0 0 1 11.025 8a5.48 5.48 0 0 1-1.61 3.89z"/>
                                    <path d="M8.707 11.182A4.5 4.5 0 0 0 10.025 8a4.5 4.5 0 0 0-1.318-3.182L8 5.525A3.5 3.5 0 0 1 9.025 8 3.5 3.5 0 0 1 8 10.475zM6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06"/>
                                </svg>
                                Next Queue
                            </button>

                            <?php if($activeCount >= $activeSlots): ?>
                            <small class="text-warning">

                                Maximum active queues reached
                                (<?= $activeSlots ?>)
                            </small>
                           

    

<?php endif; ?>
                       
                    <!-- </div> -->


                </div>

                <div
                    class="modal fade"
                    id="cancelConfirmModal">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5>
                                    Cancel Queue
                                </h5>

                            </div>

                            <div class="modal-body">

                                Are you sure you want to cancel queue
                                <strong id="cancelQueueNumber"></strong>?

                            </div>

                            <div class="modal-footer">

                                <button
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">

                                    Cancel

                                </button>

                                <button
                                    class="btn btn-warning"
                                    onclick="confirmCancelQueue()">

                                    Confirm

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal fade" id="changeReceptionModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Change Reception</h5>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to change reception? This will reset the current reception session.</p>
                                <p class="mb-0"><strong>Reception:</strong> <?= htmlspecialchars($_SESSION['reception_name'] ?? 'N/A') ?></p>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-danger" id="confirmChangeReceptionBtn" type="button">Confirm Change</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Statistics -->

                <div class="row mb-1">

                    <div class="col-md-3">

                        <div class="stats-card">
                            
                            <small>WAITING</small>

                            <div class="stats-number" style="color:#0ea5ff">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" style="color:#0ea5ff" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                </svg>&nbsp;<?= $waitingCount ?>
                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="stats-card clickable"
     onclick="showQueueCompletedHistory('done')">

                            <small>COMPLETED</small>

                            <div class="stats-number" style="color:green">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>&nbsp;<?= $doneCount ?> 

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3" >

                        <div class="stats-card clickable"
     onclick="showQueueMissingHistory('cancelled')">

                            <small>MISSING/SKIP</small>

                            <div class="stats-number" style="color:red">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                </svg>&nbsp;<?= $cancelledCount ?>
                                

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="stats-card" style="color:yellow">

                            <small>TOTAL</small>

                            <div class="stats-number">

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-ticket-detailed" viewBox="0 0 16 16">
                                <path d="M4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5M5 7a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2z"/>
                                <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6zM1.5 4a.5.5 0 0 0-.5.5v1.05a2.5 2.5 0 0 1 0 4.9v1.05a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-1.05a2.5 2.5 0 0 1 0-4.9V4.5a.5.5 0 0 0-.5-.5z"/>
                                </svg>&nbsp;<?= $totalRows ?>

                            </div>

                        </div>

                    </div>



                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
                <script src="assets/js/reception.js"></script>

                

                
               <div class="modal fade"
     id="queueHistoryModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 id="queueHistoryTitle"></h5>

                <button class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div id="queueHistoryContent">

                </div>

            </div>

        </div>

    </div>

</div>
<div class="modal fade" id="returnQueueModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Patient Returned
                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p>
                    Has <strong>Patient with Queue 
                    <span id="returnQueueNumber"></span></strong>
                    has returned.
                </p>

                <p class="text-muted mb-0">
                    Move this patient to the
                    <strong>Priority Queue</strong>?
                </p>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    No
                </button>

                <button
                    class="btn btn-success"
                    id="confirmReturnQueueBtn">
                    Return Patient
                </button>

            </div>

        </div>

    </div>

</div>

                <div
                    class="toast-container position-fixed bottom-0 end-0 p-3">

                    <div
                        id="actionToast"
                        class="toast align-items-center text-bg-success border-0">

                        <div class="d-flex">

                            <div
                                class="toast-body"
                                id="actionToastMessage">

                                Success

                            </div>

                            <button
                                type="button"
                                class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast">
                            </button>

                        </div>

                    </div>

                </div>


</div>


                <div class="modal fade" id="missingModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    Mark Queue as Missing

                </h5>

            </div>

            <div class="modal-body">

                <p>

                    Are you sure you want to mark
                    <strong id="missingQueueNumber"></strong>
                    as <strong>Missing</strong>?

                </p>

                <small class="text-muted">

                    The patient can still be recalled later if they return.

                </small>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cancel

                </button>

                <button
                    class="btn btn-danger"
                    id="confirmMissingBtn">

                    Confirm Missing

                </button>

            </div>

        </div>

    </div>




</body>

</html>