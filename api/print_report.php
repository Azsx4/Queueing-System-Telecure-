<?php

require '../database/config.php';
require 'history_query.php';

/*
|--------------------------------------------------------------------------
| Get All Records
|--------------------------------------------------------------------------
|
| Disable pagination for reports
|
*/

$_GET['limit'] = 999999;

$data = getHistoryData($conn, $_GET);

$summary = $data['summary'];

$records = $data['records'];

$dateToday = date("F d, Y h:i A");

?>
<!DOCTYPE html>
<html>

<head>

<meta charset="utf-8">

<title>Queue Report</title>

<style>

body{

    font-family:Arial,Helvetica,sans-serif;

    color:#222;

    margin:30px;

}

.report-header{

    text-align:center;

    margin-bottom:30px;

}

.report-header h2{

    margin:0;

}

.report-header h4{

    margin:5px 0;

}

.summary{

    display:flex;

    flex-wrap:wrap;

    gap:12px;

    margin-bottom:25px;

}

.card{

    border:1px solid #ccc;

    width:170px;

    padding:10px;

    border-radius:5px;

}

.card-title{

    font-size:13px;

    color:#777;

}

.card-value{

    font-size:22px;

    font-weight:bold;

    margin-top:8px;

}

table{

    width:100%;

    border-collapse:collapse;

}

th{

    background:#0d6efd;

    color:#fff;

    padding:8px;

    border:1px solid #ccc;

}

td{

    padding:8px;

    border:1px solid #ddd;

    text-align:center;

}

.footer{

    margin-top:40px;

    font-size:13px;

}

@media print{

    body{

        margin:15px;

    }

}

</style>

</head>

<body onload="window.print()">

<div class="report-header">

<h2>QUEUE MANAGEMENT SYSTEM</h2>

<h4>Records & Reports Center</h4>

<p>

Generated

<?= $dateToday ?>

</p>

</div>

<h3>Summary</h3>

<div class="summary">

<div class="card">

<div class="card-title">

Total Queue

</div>

<div class="card-value">

<?= $summary['total'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Completed

</div>

<div class="card-value">

<?= $summary['completed'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Missing

</div>

<div class="card-value">

<?= $summary['missing'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Waiting

</div>

<div class="card-value">

<?= $summary['waiting'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Serving

</div>

<div class="card-value">

<?= $summary['serving'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Average Wait

</div>

<div class="card-value">

<?= $summary['average_wait'] ?>

</div>

</div>

<div class="card">

<div class="card-title">

Average Service

</div>

<div class="card-value">

<?= $summary['average_service'] ?>

</div>

</div>

</div>

<h3>Queue Records</h3>

<table>

<thead>

<tr>

<th>#</th>

<th>Queue</th>

<th>Date</th>

<th>Status</th>

<th>Reception</th>

<th>Issued</th>

<th>Called</th>

<th>Completed</th>

<th>Waiting</th>

<th>Service</th>

</tr>

</thead>

<tbody>

<?php foreach($records as $index=>$row): ?>

<tr>

<td><?= $index+1 ?></td>

<td><?= str_pad($row['queue_number'],3,"0",STR_PAD_LEFT) ?></td>

<td><?= $row['queue_date'] ?></td>

<td><?= $row['status_display'] ?></td>

<td><?= $row['reception_name'] ?: "-" ?></td>

<td><?= $row['issued_display'] ?></td>

<td><?= $row['called_display'] ?></td>

<td><?= $row['completed_display'] ?></td>

<td><?= $row['waiting_time'] ?></td>

<td><?= $row['service_time'] ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="footer">

<br><br>

Prepared By:

<br><br><br>

_______________________________

<br>

Queue Management System

</div>

</body>

</html>