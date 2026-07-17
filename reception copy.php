<?php
session_start();

include 'database/config.php';

/* ============================================================
   DAILY SESSION RESET
============================================================ */

if (
    isset($_SESSION['reception_date']) &&
    $_SESSION['reception_date'] !== date('Y-m-d')
) {

    session_unset();
    session_destroy();

    session_start();

}

/* ============================================================
   LOAD RECEPTION FROM DATABASE
============================================================ */

if (isset($_SESSION['reception_id'])) {

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            active_slots,
            started_at
        FROM receptions
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $_SESSION['reception_id']
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows) {

            $reception = $result->fetch_assoc();

            $_SESSION['reception_name'] =
                $reception['name'];

            $_SESSION['active_slots'] =
                (int)$reception['active_slots'];

            $_SESSION['reception_start'] =
                $reception['started_at'];

            $_SESSION['reception_date'] =
                date(
                    "Y-m-d",
                    strtotime($reception['started_at'])
                );

        } else {

            unset($_SESSION['reception_id']);

        }

    }

}

/* ============================================================
   START RECEPTION
============================================================ */

if (isset($_POST['start_reception'])) {

    $name = trim($_POST['reception_name']);

    $activeSlots =
        max(
            1,
            (int)$_POST['active_slots']
        );

    $stmt = $conn->prepare("
        INSERT INTO receptions
        (
            name,
            active_slots,
            started_at
        )
        VALUES
        (
            ?,
            ?,
            NOW()
        )
    ");

    if ($stmt) {

        $stmt->bind_param(

            "si",

            $name,

            $activeSlots

        );

        $stmt->execute();

        $_SESSION['reception_id'] =
            $stmt->insert_id;

        $_SESSION['reception_name'] =
            $name;

        $_SESSION['active_slots'] =
            $activeSlots;

        $_SESSION['reception_start'] =
            date("Y-m-d H:i:s");

        $_SESSION['reception_date'] =
            date("Y-m-d");

    }

    header("Location: reception.php");

    exit;

}

/* ============================================================
   CHANGE RECEPTION
============================================================ */

if (isset($_POST['change_reception'])) {

    unset($_SESSION['reception_id']);
    unset($_SESSION['reception_name']);
    unset($_SESSION['active_slots']);
    unset($_SESSION['reception_start']);
    unset($_SESSION['reception_date']);

    header("Location: reception.php");

    exit;

}

/* ============================================================
   JAVASCRIPT CONFIGURATION
============================================================ */

$ReceptionConfig = [

    "receptionId" =>
        $_SESSION['reception_id'] ?? 0,

    "receptionName" =>
        $_SESSION['reception_name'] ?? "",

    "activeSlots" =>
        $_SESSION['active_slots'] ?? 1,

    "startedAt" =>
        $_SESSION['reception_start'] ?? ""

];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reception Panel</title>

    <!-- ==========================================================
         Bootstrap
    =========================================================== -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- ==========================================================
         Font Awesome
    =========================================================== -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          rel="stylesheet">

    <!-- ==========================================================
         Bootstrap Icons
    =========================================================== -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"
          rel="stylesheet">

    <!-- ==========================================================
         Animate.css
    =========================================================== -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- ==========================================================
         SweetAlert2
    =========================================================== -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ==========================================================
         Reception Stylesheet
    =========================================================== -->

    <link rel="stylesheet"
          href="assets/css/reception.css">
    <link rel="stylesheet"
          href="assets/css/style.css">
            <link rel="stylesheet"
          href="assets/style/theme.css">

    <!-- ==========================================================
         Custom Theme Variables
    =========================================================== -->

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
  background: var(--card);

  border-radius: 18px;

  padding: 20px;

  margin-bottom: 15px;

  border-left: 6px solid var(--primary);

}

.active-queue-card:hover {
  transform: translateY(-2px);

  box-shadow: 0 8px 18px rgba(207, 207, 207, 0.08);
}

.queue-card-header {
  border-bottom: 1px solid var(--border);

  padding-bottom: 12px;

  margin-bottom: 15px;

  display: flex;

  justify-content: space-between;

  align-items: center;
}

.queue-title {
  color: var(--text-strong);

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

  color: var(--text);
}

.queue-info div {
  display: flex;

  align-items: center;

  gap: 0.5rem;

  font-size: 0.95rem;
}

.queue-info i {
  color: var(--primary);
}

.queue-actions {
  flex: 0 0 auto;

  display: flex;

  align-items: center;

  gap: 0.5rem;

  justify-content: flex-end;

  color: var(--text);
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
  background: rgba(25, 135, 84, 0.16);
  color: #198754;
  border: 1px solid #198754;
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

</head>


<script>

window.ReceptionConfig = <?= json_encode($ReceptionConfig) ?>;

</script>


<body>

   <?php if (empty($_SESSION['reception_name'])): ?>

<div class="container py-5">

    <div class="card mx-auto shadow" style="max-width:500px;">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                <i class="fas fa-user-tie me-2"></i>
                Start Reception
            </h4>

        </div>

        <div class="card-body">

            <form id="startReceptionForm" method="POST">

                <div class="mb-3">

                    <label class="form-label">

                        Reception Name

                    </label>

                    <input
                        type="text"
                        id="receptionName"
                        name="reception_name"
                        class="form-control"
                        placeholder="Enter reception name"
                        autocomplete="off"
                        required>

                </div>

                <div class="mb-4">

                    <label class="form-label">

                        Active Queue Slots

                    </label>

                    <select
                        id="activeSlots"
                        name="active_slots"
                        class="form-select">

                        <option value="1">1 Active Queue</option>
                        <option value="2" selected>2 Active Queues</option>
                        <option value="3">3 Active Queues</option>
                        <option value="4">4 Active Queues</option>

                    </select>

                </div>

                <button
                    type="submit"
                    name="start_reception"
                    id="startReceptionBtn"
                    class="btn btn-primary w-100">

                    <i class="fas fa-play-circle me-2"></i>

                    Start Reception

                </button>

            </form>

        </div>

    </div>

</div>

<?php exit; ?>

<?php endif; ?>

    <?php include 'components/sidebar.php'; ?>
<?php include 'components/header.php'; ?>

<div class="main-content">

    <!-- ==========================================
         Reception Header
    =========================================== -->

    <div id="reception-header"
         class="d-flex flex-wrap align-items-center justify-content-between mb-4">

        <!-- ===============================
             Reception Information
        ================================ -->

        <div class="d-flex align-items-center gap-3 header-top-row">

            <div>

                <strong>

                    Reception :

                    <span id="reception-name">

                        <?= htmlspecialchars($_SESSION['reception_name']); ?>

                    </span>

                </strong>

                <small
                    id="reception-status"
                    class="text-success">

                    &nbsp;(Active)

                </small>

                <br>

                <small>

                    Started :

                    <span id="reception-start">

                        <?= htmlspecialchars($_SESSION['reception_start'] ?? 'N/A'); ?>

                    </span>

                </small>

            </div>

        </div>

        <!-- ===============================
             Active Queue Counter
        ================================ -->

        <div id="active-queue-counter">

            Active Queue :

            <span id="active-count">

                0/<?= $_SESSION['active_slots'] ?? 1 ?>

            </span>

        </div>

        <!-- ===============================
             Header Controls
        ================================ -->

        <div class="d-flex flex-wrap align-items-center gap-3 header-controls-row">

            <!-- Change Reception -->

            <form
                id="changeReceptionForm"
                method="POST"
                class="change-reception-form">

                <input
                    type="hidden"
                    name="change_reception"
                    value="1">

                <button
                    type="button"
                    id="changeReceptionBtn"
                    class="change-reception-btn">

                    <i class="fas fa-exchange-alt"></i>

                    <span class="action-label">

                        Change Reception

                    </span>

                </button>

            </form>

            <!-- Voice Settings -->

            <div id="voice-settings">

                <select
                    id="voiceSelect"
                    class="voice-select">

                    <option value="">

                        Voice Settings

                    </option>

                </select>

            </div>

        </div>

    </div>


<div class="hero-serving col-md-6">

    <!-- Header -->

    <div class="card-header">

        <div class="header-left">

            <div>

                <div class="title text-start inline-block">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="25"
                         height="25"
                         fill="currentColor"
                         class="bi bi-people-fill"
                         viewBox="0 0 16 16"
                         style="color:var(--primary);">

                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>

                    </svg>

                    &nbsp; ACTIVE QUEUES

                    <span
                        style="font-size:12px;color:var(--muted);">

                        &nbsp;&nbsp;Click a queue card to recall (voice)

                    </span>

                </div>

            </div>

        </div>

    </div>

    <hr>

    <!-- Now Serving Click -->

    <div
        class="hero-label"
        style="cursor:pointer;"
        onclick="announceCurrentQueue()">

    </div>

    <!-- AJAX Container -->

    <div id="activeQueueContainer">

        <div class="empty-active">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="75"
                 height="75"
                 fill="currentColor"
                 class="bi bi-people-fill"
                 viewBox="0 0 16 16">

                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>

            </svg>

            <h5>No active queues</h5>

            <small class="text-secondary">

                Click "Next Queue" to call the next patient.

            </small>

        </div>

    </div>

</div>




<!-- ==========================================================
     UPCOMING QUEUES
=========================================================== -->

<div class="col-md-6">

    <div class="hero-serving col-md-12">

        <!-- Header -->

        <div class="hero-label text-start">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="20"
                 height="20"
                 fill="currentColor"
                 class="bi bi-calendar2-week"
                 viewBox="0 0 16 16"
                 style="color:var(--primary);">

                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>

                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>

                <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>

            </svg>

            &nbsp; UPCOMING QUEUES

        </div>

        <br>

        <!-- AJAX Upcoming Queue Container -->

        <div
            id="upcomingQueueContainer"
            class="row mb-4">

            <!-- Filled by reception.js -->

        </div>

    </div>

    <br>

    <!-- ==========================================
         ACTION PANEL
    =========================================== -->

    <div class="action-panel col-lg-12">

        <button

            id="actionNextBtn"

            class="btn btn-primary w-100 p-3"

            onclick="actionNext()">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="16"
                 height="16"
                 fill="currentColor"
                 class="bi bi-volume-up-fill"
                 viewBox="0 0 16 16">

                <path d="M11.536 14.01A8.47 8.47 0 0 0 14.026 8a8.47 8.47 0 0 0-2.49-6.01l-.708.707A7.48 7.48 0 0 1 13.025 8c0 2.071-.84 3.946-2.197 5.303z"/>

                <path d="M10.121 12.596A6.48 6.48 0 0 0 12.025 8a6.48 6.48 0 0 0-1.904-4.596l-.707.707A5.48 5.48 0 0 1 11.025 8a5.48 5.48 0 0 1-1.61 3.89z"/>

                <path d="M8.707 11.182A4.5 4.5 0 0 0 10.025 8a4.5 4.5 0 0 0-1.318-3.182L8 5.525A3.5 3.5 0 0 1 9.025 8 3.5 3.5 0 0 1 8 10.475zM6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06"/>

            </svg>

            &nbsp;

            Next Queue

        </button>

        <small
            id="activeQueueWarning"
            class="text-warning d-none mt-2 d-block">

        </small>

    </div>

</div>
    


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
<!-- ==========================================
     STATISTICS
========================================== -->

<div class="row mb-1">

    <!-- Waiting -->

    <div class="col-md-3">

        <div class="stats-card">

            <small>WAITING</small>

            <div
                id="waitingCount"
                class="stats-number"
                style="color:var(--primary)">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="20"
                     height="20"
                     fill="currentColor"
                     class="bi bi-people"
                     viewBox="0 0 16 16"
                     style="color:var(--primary);">

                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>

                </svg>

                &nbsp;<span>0</span>

            </div>

        </div>

    </div>

    <!-- Completed -->

    <div class="col-md-3">

        <div
            class="stats-card clickable"
            onclick="showQueueCompletedHistory('done')">

            <small>COMPLETED</small>

            <div
                id="doneCount"
                class="stats-number"
                style="color:green">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="20"
                     height="20"
                     fill="currentColor"
                     class="bi bi-check-circle"
                     viewBox="0 0 16 16">

                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14"/>

                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>

                </svg>

                &nbsp;<span>0</span>

            </div>

        </div>

    </div>

    <!-- Missing -->

    <div class="col-md-3">

        <div
            class="stats-card clickable"
            onclick="showQueueMissingHistory('cancelled')">

            <small>MISSING / SKIP</small>

            <div
                id="cancelledCount"
                class="stats-number"
                style="color:red">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="20"
                     height="20"
                     fill="currentColor"
                     class="bi bi-x-circle"
                     viewBox="0 0 16 16">

                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14"/>

                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>

                </svg>

                &nbsp;<span>0</span>

            </div>

        </div>

    </div>

    <!-- Total -->

    <div class="col-md-3">

        <div class="stats-card">

            <small>TOTAL</small>

            <div
                id="totalCount"
                class="stats-number">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="20"
                     height="20"
                     fill="currentColor"
                     class="bi bi-ticket-detailed"
                     viewBox="0 0 16 16">

                    <path d="M4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5"/>

                    <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6"/>

                </svg>

                &nbsp;<span>0</span>

            </div>

        </div>

    </div>

</div>



                </div>
                <!-- ==========================================================
     JAVASCRIPT
=========================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/reception.js"></script>


<!-- ==========================================================
     QUEUE HISTORY MODAL
=========================================================== -->

<div
    class="modal fade"
    id="queueHistoryModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="queueHistoryTitle">

                    Queue History

                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <div
                class="modal-body"
                id="queueHistoryContent">

            </div>

        </div>

    </div>

</div>


<!-- ==========================================================
     RETURN PATIENT MODAL
=========================================================== -->

<div
    class="modal fade"
    id="returnQueueModal"
    tabindex="-1"
    aria-hidden="true">

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

                    Has patient

                    <strong>

                        Queue
                        <span id="returnQueueNumber"></span>

                    </strong>

                    returned?

                </p>

                <p class="text-muted mb-0">

                    Move this patient to the

                    <strong>

                        Priority Queue

                    </strong>

                    ?

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


<!-- ==========================================================
     MISSING PATIENT MODAL
=========================================================== -->

<div
    class="modal fade"
    id="missingModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    Mark Queue as Missing

                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <div class="modal-body">

                <p>

                    Are you sure you want to mark

                    <strong id="missingQueueNumber"></strong>

                    as

                    <strong>

                        Missing

                    </strong>

                    ?

                </p>

                <small class="text-muted">

                    The patient can still be recalled later.

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

</div>


<!-- ==========================================================
     TOAST NOTIFICATION
=========================================================== -->

<div class="toast-container position-fixed bottom-0 end-0 p-3">

    <div
        id="actionToast"
        class="toast align-items-center text-bg-success border-0">

        <div class="d-flex">

            <div
                id="actionToastMessage"
                class="toast-body">

                Success

            </div>

            <button
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">

            </button>

        </div>

    </div>

</div>


<!-- ==========================================================
     RECEPTION CONFIGURATION
=========================================================== -->

<script>

window.ReceptionConfig = {

    receptionId:
        <?= (int)($_SESSION['reception_id'] ?? 0) ?>,

    receptionName:
        <?= json_encode($_SESSION['reception_name'] ?? '') ?>,

    activeSlots:
        <?= (int)($_SESSION['active_slots'] ?? 1) ?>

};

document.addEventListener("DOMContentLoaded", () => {

    Reception.init();

});

</script>

<!-- Reception JavaScript -->
<script src="assets/js/reception.js"></script>

</body>
</html>