<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../database/config.php';
require 'history_query.php';

/*
|--------------------------------------------------------------------------
| Export All Records
|--------------------------------------------------------------------------
*/
$_GET['limit'] = 999999;

$data = getHistoryData($conn, $_GET);

$summary = $data['summary'];
$records = $data['records'];

$filename = "Queue_Report_" . date("Ymd_His") . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>

<html>

<head>

<meta charset="UTF-8">

<style>

table{

    border-collapse:collapse;

    width:100%;

}

th{

    background:#d9d9d9;

    font-weight:bold;

}

th,td{

    border:1px solid #000;

    padding:6px;

}

.title{

    font-size:18px;

    font-weight:bold;

}

.subtitle{

    font-size:13px;

}

.summary td{

    font-weight:bold;

}

</style>

</head>

<body>

<div class="title">

Queue Management System

</div>

<div class="subtitle">

Records & Reports Center

</div>

<br>

Generated:

<?= date("F d, Y h:i A") ?>

<br><br>

<table class="summary">

<tr>

<td>Total Queue</td>

<td><?= $summary['total'] ?></td>

<td>Completed</td>

<td><?= $summary['completed'] ?></td>

</tr>

<tr>

<td>Missing</td>

<td><?= $summary['missing'] ?></td>

<td>Waiting</td>

<td><?= $summary['waiting'] ?></td>

</tr>

<tr>

<td>Serving</td>

<td><?= $summary['serving'] ?></td>

<td>Archived</td>

<td><?= $summary['archived'] ?></td>

</tr>

<tr>

<td>Average Wait</td>

<td><?= $summary['average_wait'] ?></td>

<td>Average Service</td>

<td><?= $summary['average_service'] ?></td>

</tr>

</table>

<br>

<table>

<thead>

<tr>

<th>#</th>

<th>Queue No.</th>

<th>Date</th>

<th>Status</th>

<th>Reception</th>

<th>Issued</th>

<th>Called</th>

<th>Completed</th>

<th>Waiting Time</th>

<th>Service Time</th>

<th>Return Count</th>

</tr>

</thead>

<tbody>

<?php foreach($records as $i=>$row): ?>

<tr>

<td><?= $i+1 ?></td>

<td><?= $row['queue_number'] ?></td>

<td><?= $row['queue_date'] ?></td>

<td><?= $row['status_display'] ?></td>

<td><?= $row['reception_name'] ?: "-" ?></td>

<td><?= $row['issued_display'] ?></td>

<td><?= $row['called_display'] ?></td>

<td><?= $row['completed_display'] ?></td>

<td><?= $row['waiting_time'] ?></td>

<td><?= $row['service_time'] ?></td>

<td><?= $row['return_count'] ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</body>

</html>