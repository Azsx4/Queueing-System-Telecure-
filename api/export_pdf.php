<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require '../database/config.php';
require 'history_query.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

$_GET['limit']=999999;

$data=getHistoryData($conn,$_GET);

$summary=$data['summary'];
$records=$data['records'];

ob_start();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="utf-8">

<style>

body{

    font-family:DejaVu Sans;

    font-size:12px;

}

h2{

    text-align:center;

}

table{

    width:100%;

    border-collapse:collapse;

}

th{

    background:#dddddd;

}

th,td{

    border:1px solid #999;

    padding:5px;

}

.summary{

    margin-bottom:20px;

}

</style>

</head>

<body>

<h2>

Queue Management System

</h2>

<h3 style="text-align:center">

Records & Reports Center

</h3>

<p>

Generated:

<?= date("F d, Y h:i A") ?>

</p>

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

<td>Average Wait</td>

<td><?= $summary['average_wait'] ?></td>

</tr>

<tr>

<td>Average Service</td>

<td><?= $summary['average_service'] ?></td>

<td>Archived</td>

<td><?= $summary['archived'] ?></td>

</tr>

</table>

<table>

<thead>

<tr>

<th>#</th>

<th>Queue</th>

<th>Date</th>

<th>Status</th>

<th>Reception</th>

<th>Waiting</th>

<th>Service</th>

</tr>

</thead>

<tbody>

<?php foreach($records as $i=>$row): ?>

<tr>

<td><?= $i+1 ?></td>

<td><?= $row['queue_number'] ?></td>

<td><?= $row['queue_date'] ?></td>

<td><?= $row['status_display'] ?></td>

<td><?= $row['reception_name'] ?></td>

<td><?= $row['waiting_time'] ?></td>

<td><?= $row['service_time'] ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</body>

</html>

<?php

$html=ob_get_clean();

$pdf=new Dompdf();

$pdf->loadHtml($html);

$pdf->setPaper('A4','landscape');

$pdf->render();

$pdf->stream(

    "Queue_Report_".date("Ymd_His").".pdf",

    ["Attachment"=>true]

);

exit;