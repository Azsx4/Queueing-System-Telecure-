<?php
session_start();

include 'database/config.php';

// Optional: Restrict access if reception session is required
// if (!isset($_SESSION['reception_name'])) {
//     header("Location: reception.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Records & Reports Center</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Theme -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Module CSS -->
    <link rel="stylesheet" href="assets/css/history.css">

    <script src="assets/js/theme.js"></script>
</head>

<body>

<?php include 'components/sidebar.php'; ?>
<?php include 'components/header.php'; ?>

<div class="main-content">

    <!-- ===========================
         PAGE HEADER
    ============================ -->

    <div class="page-header mb-4">

        <div>

            <h2 class="mb-1">
                <i class="fas fa-folder-open text-primary"></i>
                Records & Reports Center
            </h2>

            <p class="text-muted mb-0">
                Complete Queue History, Search, Reports and Analytics
            </p>

        </div>

        <div>

            <button class="btn btn-primary dropdown-toggle"
                    data-bs-toggle="dropdown">

                <i class="fas fa-file-export"></i>

                Generate Report

            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a class="dropdown-item" href="#">
                        Daily Report
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Weekly Report
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Monthly Report
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Custom Report
                    </a>
                </li>

            </ul>

        </div>

    </div>



    <!-- ===========================
            SUMMARY CARDS
    ============================ -->

    <div class="row g-3 mb-4">

        <div class="col-lg-2 col-md-4">

            <div class="summary-card">

                <small>Total Queue</small>

                <h2 id="cardTotal">--</h2>

            </div>

        </div>

        <div class="col-lg-2 col-md-4">

            <div class="summary-card success">

                <small>Completed</small>

                <h2 id="cardCompleted">--</h2>

            </div>

        </div>

        <div class="col-lg-2 col-md-4">

            <div class="summary-card warning">

                <small>Missing</small>

                <h2 id="cardMissing">--</h2>

            </div>

        </div>

        <div class="col-lg-2 col-md-4">

            <div class="summary-card danger">

                <small>Cancelled</small>

                <h2 id="cardCancelled">--</h2>

            </div>

        </div>

        <div class="col-lg-2 col-md-4">

            <div class="summary-card info">

                <small>Avg Waiting</small>

                <h2 id="cardWaiting">--</h2>

            </div>

        </div>

        <div class="col-lg-2 col-md-4">

            <div class="summary-card secondary">

                <small>Avg Service</small>

                <h2 id="cardService">--</h2>

            </div>

        </div>

    </div>



    <!-- ===========================
              FILTER PANEL
    ============================ -->

    <div class="card filter-card mb-4">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-lg-3">

                    <label class="form-label">

                        Search

                    </label>

                    <input type="text"
                           class="form-control"
                           id="searchBox"
                           placeholder="Queue Number, Reception...">

                </div>

                <div class="col-lg-2">

                    <label class="form-label">

                        From

                    </label>

                    <input type="date"
                           id="dateFrom"
                           class="form-control">

                </div>

                <div class="col-lg-2">

                    <label class="form-label">

                        To

                    </label>

                    <input type="date"
                           id="dateTo"
                           class="form-control">

                </div>

                <div class="col-lg-2">

                    <label class="form-label">

                        Status

                    </label>

                    <select id="statusFilter"
                            class="form-select">

                        <option value="">All</option>
                        <option value="waiting">Waiting</option>
                        <option value="called">Called</option>
                        <option value="done">Completed</option>
                        <option value="cancelled">Missing</option>

                    </select>

                </div>

                <div class="col-lg-3">

                    <label class="form-label">

                        Reception

                    </label>

                    <select id="receptionFilter"
                            class="form-select">

                        <option value="">All Reception</option>

                    </select>

                </div>

                <div class="col-lg-2">

                    <label class="form-label">

                        Queue Type

                    </label>

                    <select id="queueType"
                            class="form-select">

                        <option value="">All</option>
                        <option>Regular</option>

                    </select>

                </div>

                <div class="col-lg-2">

                    <label class="form-label">

                        Sort

                    </label>

                    <select id="sortBy"
                            class="form-select">

                        <option value="desc">
                            Newest First
                        </option>

                        <option value="asc">
                            Oldest First
                        </option>

                    </select>

                </div>

                <div class="col-lg-8 d-flex align-items-end gap-2">
 
     <button id="applyFilter" class="btn btn-primary">
        <i class="fas fa-filter"></i>
        Apply
    </button> 

    <button id="resetFilter" class="btn btn-secondary">
        <i class="fas fa-undo"></i>
        Reset
    </button>
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

</div>

        <div class="d-flex gap-2">

            <button
                class="btn btn-outline-primary"
                onclick="printReport()">

                <i class="fas fa-print"></i>

                Print

            </button>

            <button
                class="btn btn-outline-success"
                onclick="exportExcel()">

                <i class="fas fa-file-excel"></i>

                Excel

            </button>

            <button
                class="btn btn-outline-danger"
                onclick="exportPDF()">

                <i class="fas fa-file-pdf"></i>

                PDF

            </button>

        </div>

    </div>


                </div>

            </div>

        </div>

    </div>



    <!-- ===========================
           HISTORY TABLE
    ============================ -->

    <div class="card table-card">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0 table-dark">

                <thead>

                <tr>

                    <th>Queue</th>

                    <th>Status</th>

                    <th>Reception</th>

                    <th>Issued</th>

                    <th>Called</th>

                    <th>Completed</th>

                    <th>Waiting</th>

                    <th>Service</th>

                    <th width="120">

                        Action

                    </th>

                </tr>

                </thead>

                <tbody id="historyTable">

                <tr>

                    <td colspan="9"
                        class="text-center p-5 text-muted">

                        Loading records...

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </div>



    <!-- ===========================
             PAGINATION
    ============================ -->

    <div class="pagination-wrapper mt-4">

        <div id="paginationInfo">

            Showing 0 of 0

        </div>

        <nav>

            <ul class="pagination mb-0"
                id="pagination">

            </ul>

        </nav>

    </div>

</div>



<!-- ===========================
        DETAILS MODAL
=========================== -->

<div class="modal fade"
     id="historyModal">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5>

                    Queue Details

                </h5>

                <button class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body"
                 id="historyDetails">

                Loading...

            </div>

        </div>

    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/history.js"></script>

</body>
</html>