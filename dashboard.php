<?php
session_start();

include 'database/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Theme -->
    <link rel="stylesheet" href="assets/css/theme.css">

    <!-- Global Style -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <script src="assets/js/theme.js"></script>

    <style>
    /* ======================================================
   Dashboard
   Queue System Ver. 3.0
====================================================== */

:root {
  --dashboard-primary: var(--primary, #0ea5ff);
  --dashboard-success: var(--success, #16a34a);
  --dashboard-warning: var(--warning, #f59e0b);
  --dashboard-danger: var(--danger, #dc2626);
  --dashboard-info: #2563eb;

  --dashboard-card: var(--card, #161616);
  --dashboard-bg: var(--bg, #121212);
  --dashboard-border: var(--border, #262626);

  --dashboard-text: var(--text, #f5f5f5);
  --dashboard-text-muted: var(--muted, #9ca3af);
}

/* ======================================================
   Main Content
====================================================== */

.main-content {
  background: var(--dashboard-bg);

  min-height: 100vh;

  padding: 30px;

  color: var(--dashboard-text);
}

/* ======================================================
   Page Header
====================================================== */

.page-title {
  display: flex;

  justify-content: space-between;

  align-items: center;

  margin-bottom: 30px;
}

.page-title h2 {
  font-weight: 700;

  margin: 0;
}

.page-title small {
  color: var(--dashboard-text-muted);
}

.badge {
  padding: 10px 16px;

  border-radius: 25px;
}

/* ======================================================
   KPI Cards
====================================================== */

.dashboard-card {
  background: var(--dashboard-card);

  border-radius: 20px;

  padding: 25px;

  position: relative;

  overflow: hidden;

  transition: 0.25s;

  border: 1px solid var(--dashboard-border);

  height: 100%;
}

.dashboard-card::before {
  content: "";

  position: absolute;

  left: 0;

  top: 0;

  width: 100%;

  height: 4px;

  background: var(--dashboard-primary);
}

.dashboard-card:hover {
  transform: translateY(-4px);

  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
}

.dashboard-icon {
  width: 58px;

  height: 58px;

  border-radius: 15px;

  display: flex;

  justify-content: center;

  align-items: center;

  color: #fff;

  font-size: 24px;

  margin-bottom: 20px;
}

.dashboard-title {
  font-size: 15px;

  color: var(--dashboard-text-muted);
}

.dashboard-value {
  font-size: 42px;

  font-weight: 700;

  margin-top: 10px;

  margin-bottom: 10px;

  color: var(--dashboard-text);
}
/*======================================================
   Dashboard Panels
====================================================== */

.dashboard-panel {
  background: var(--dashboard-card);

  border-radius: 20px;

  border: 1px solid var(--dashboard-border);

  padding: 25px;

  height: 100%;
}

.panel-title {
  font-size: 18px;

  font-weight: 600;

  margin-bottom: 20px;
}

.panel-body {
  min-height: 350px;
}

.panel-body canvas{

    width:100% !important;

    height:320px !important;

}

.chart-placeholder {
  display: flex;

  align-items: center;

  justify-content: center;

  color: #6b7280;

  border: 2px dashed #2d3748;

  border-radius: 15px;

  font-size: 18px;
}

/* ======================================================
   Loading Animation
====================================================== */

.dashboard-loading {
  position: relative;

  overflow: hidden;
}

.dashboard-loading::after {
  content: "";

  position: absolute;

  top: 0;

  left: -150px;

  width: 150px;

  height: 100%;

  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.08),
    transparent
  );

  animation: loading 1.2s infinite;
}

@keyframes loading {
  100% {
    left: 100%;
  }
}

/* ======================================================
   Card Color Accent
====================================================== */

.bg-primary {
  background: var(--dashboard-primary) !important;
}

.bg-success {
  background: var(--dashboard-success) !important;
}

.bg-warning {
  background: var(--dashboard-warning) !important;
}

.bg-danger {
  background: var(--dashboard-danger) !important;
}

.bg-info {
  background: var(--dashboard-info) !important;
}

.bg-secondary {
  background: var(--muted, #6b7280) !important;
}

/* ======================================================
   Responsive
====================================================== */

@media (max-width: 992px) {
  .page-title {
    flex-direction: column;

    align-items: flex-start;

    gap: 15px;
  }
}

@media (max-width: 768px) {
  .dashboard-value {
    font-size: 34px;
  }

  .panel-body {
    min-height: 250px;
  }
}

/* ======================================================
   Live Queue Activity
====================================================== */

/* ==========================================
   Live Queue Activity
========================================== */

#timelineFeed{

    max-height: 500px;

    overflow-y: auto;

    overflow-x: hidden;

    padding-right: 8px;

}

/* Custom Scrollbar */

#timelineFeed::-webkit-scrollbar{

    width:8px;

}

#timelineFeed::-webkit-scrollbar-track{

    background:transparent;

}

#timelineFeed::-webkit-scrollbar-thumb{

    background:#4b5563;

    border-radius:10px;

}

#timelineFeed::-webkit-scrollbar-thumb:hover{

    background:#6b7280;

}

.timeline-item{

    display:flex;

    align-items:center;

    gap:15px;

    padding:14px 0;

    border-bottom:1px solid rgba(255,255,255,.08);

}

.timeline-item:last-child{

    border-bottom:none;

}

.timeline-time{

    width:90px;

    color:#9ca3af;

    font-size:13px;

    flex-shrink:0;

}

.timeline-icon{

    width:42px;

    height:42px;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    flex-shrink:0;

}

.timeline-text{

    flex:1;

    word-break:break-word;

}

/* ======================================================
   Reception Performance
====================================================== */


#receptionPerformanceTable{

    color:var(--dashboard-text);

}

#receptionPerformanceTable thead{

    background:#1f2937;

}

#receptionPerformanceTable th{

    border:none;

    color:#d1d5db;

    font-weight:600;

    font-size:14px;

}

#receptionPerformanceTable td{

    border-color:#2d3748;

    vertical-align:middle;

}

#receptionPerformanceTable tbody tr{

    transition:.2s;

}

#receptionPerformanceTable tbody tr:hover{

    background:rgba(255,255,255,.03);

}

.performance-progress{

    height:22px;

    background:#2d3748;

    border-radius:20px;

}

.performance-progress .progress-bar{

    font-size:12px;

    font-weight:600;

}

/* ======================================================
   Queue Insights
====================================================== */

.insight-card{

    background:#1a1a1a;

    border:1px solid #2d3748;

    border-radius:18px;

    padding:22px;

    height:100%;

    transition:.25s;

}

.insight-card:hover{

    transform:translateY(-3px);

    box-shadow:0 12px 25px rgba(0,0,0,.25);

}

.insight-icon{

    width:54px;

    height:54px;

    border-radius:15px;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    font-size:22px;

    margin-bottom:18px;

}

.insight-title{

    font-size:14px;

    color:#9ca3af;

}

.insight-value{

    font-size:30px;

    font-weight:700;

    margin:8px 0;

}

.insight-subtitle{

    color:#9ca3af;

    font-size:13px;

}

    </style>

</head>


<body>

<?php include 'components/sidebar.php'; ?>
<?php include 'components/header.php'; ?>

<div class="main-content">

    <!-- =======================
            PAGE HEADER
    ======================== -->

    <div class="page-title">

        <div>

            <h2>Dashboard</h2>

            <small class="text-secondary">

                Queue Monitoring & Performance Analytics

            </small>

        </div>

        <div>

            <span class="badge bg-success">

                <i class="fas fa-circle"></i>

                Live

            </span>

        

            <div
                id="dashboardTimestamp"
                class="small text-secondary mt-2">

                Loading...

            </div>

        </div>

    </div>

    <!-- =======================
            KPI CARDS
    ======================== -->

    <div class="row g-4 mb-4">

        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-primary">

                    <i class="fas fa-ticket-alt"></i>

                </div>

                <div class="dashboard-title">

                    Total Queues

                </div>

                <div class="dashboard-value" id="totalQueues">

                    --

                </div>

                <div class="dashboard-footer">

                    Today's Queue

                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-warning">

                    <i class="fas fa-user-clock"></i>

                </div>

                <div class="dashboard-title">

                    Waiting

                </div>

                <div class="dashboard-value" id="waitingQueues">

                    --

                </div>

                <div class="dashboard-footer">

                    Waiting Patients

                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-info">

                    <i class="fas fa-bullhorn"></i>

                </div>

                <div class="dashboard-title">

                    Active

                </div>

                <div class="dashboard-value" id="activeQueues">

                    --

                </div>

                <div class="dashboard-footer">

                    Currently Serving

                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-success">

                    <i class="fas fa-check-circle"></i>

                </div>

                <div class="dashboard-title">

                    Completed

                </div>

                <div class="dashboard-value" id="completedQueues">

                    --

                </div>

                <div class="dashboard-footer">

                    Completed Today

                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-danger">

                    <i class="fas fa-user-slash"></i>

                </div>

                <div class="dashboard-title">

                    Missing

                </div>

                <div class="dashboard-value" id="missingQueues">

                    --

                </div>

                <div class="dashboard-footer">

                    Missed Queues

                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-4 col-md-6">

            <div class="dashboard-card">

                <div class="dashboard-icon bg-secondary">

                    <i class="fas fa-chart-line"></i>

                </div>

                <div class="dashboard-title">

                    Completion

                </div>

                <div class="dashboard-value" id="completionRate">

                    --%

                </div>

                <div class="dashboard-footer">

                    Completion Rate

                </div>

            </div>

        </div>

    </div>

    <div class="row g-4 mb-4">

    <div class="col-lg-3">

        <div class="dashboard-card">

            <div class="dashboard-title">

                Average Waiting

            </div>

            <div
            class="dashboard-value"
            id="averageWait">

                --

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="dashboard-card">

            <div class="dashboard-title">

                Average Service

            </div>

            <div
            class="dashboard-value"
            id="averageService">

                --

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="dashboard-card">

            <div class="dashboard-title">

                Peak Hour

            </div>

            <div
            class="dashboard-value"
            id="peakHour">

                --

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="dashboard-card">

            <div class="dashboard-title">

                Reception Utilization

            </div>

            <div
            class="dashboard-value"
            id="utilization">

                --

            </div>

        </div>

    </div>

</div>

    <!-- =======================
            CHART PLACEHOLDERS
    ======================== -->

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="dashboard-panel">

                <div class="panel-title">

                    Queue Activity

                </div>

                <div class="panel-body">

                    <canvas
                        id="activityChart">
                    </canvas>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="dashboard-panel">

                <div class="panel-title">

                    Queue Distribution

                </div>

                    <div class="panel-body">

                        <canvas id="distributionChart"></canvas>

                    </div>

            </div>

        </div>

    </div>

    <div class="row mt-4">

    <div class="col-lg-12">

        <div class="dashboard-panel">

            <div class="panel-title">

                Live Queue Activity

            </div>

            <div id="timelineFeed">

                Loading...

            </div>

        </div>

    </div>

</div>

<!-- =======================
        RECEPTION PERFORMANCE
======================= -->

<div class="row mt-4">

    <div class="col-12">

        <div class="dashboard-panel">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div class="panel-title mb-0">

                    <i class="fas fa-user-tie me-2"></i>

                    Reception Performance

                </div>

                <small class="text-secondary">

                    Today's Performance

                </small>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0" id="receptionPerformanceTable">

                    <thead>

                        <tr>

                            <th>Receptionist</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Missing</th>
                            <th class="text-center">Avg. Wait</th>
                            <th class="text-center">Avg. Service</th>
                            <th class="text-center">Returns</th>
                            <th style="width:220px;">Utilization</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td colspan="7" class="text-center text-secondary py-4">

                                Loading Reception Performance...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- =======================
        QUEUE INSIGHTS
======================= -->

<div class="row mt-4">

    <div class="col-12">

        <div class="dashboard-panel">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="panel-title mb-0">

                    <i class="fas fa-lightbulb me-2"></i>

                    Queue Insights

                </div>

                <small class="text-secondary">

                    Today's Operational Highlights

                </small>

            </div>

            <div class="row g-3" id="queueInsights">

                <!-- Loaded by dashboard.js -->

            </div>

        </div>

    </div>

</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="assets/js/dashboard.js"></script>

</body>
</html>