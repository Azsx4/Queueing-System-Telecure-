<?php

include 'database/config.php';

$today = date('Y-m-d');

$total = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
")->fetch_assoc()['total'];

$waiting = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
AND status='waiting'
")->fetch_assoc()['total'];

$called = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
AND status='called'
")->fetch_assoc()['total'];

$done = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
AND status='done'
")->fetch_assoc()['total'];

$done = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
AND status='done'
")->fetch_assoc()['total'];

$cancelled = $conn->query("
SELECT COUNT(*) total
FROM queues
WHERE queue_date='$today'
AND status='cancelled'
")->fetch_assoc()['total'];

$recent = $conn->query("
SELECT *
FROM queues
ORDER BY issued_at DESC
LIMIT 20
");
?>

<!DOCTYPE html>
<html>

<head>

<?php include 'page_header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<?php include 'components/sidebar.php'; ?>

<div class="main-content">

<div class="container mt-4">

<h2>Queue Dashboard</h2>

<div class="row">

<div class="col-md-3">

<div class="card text-center">

<div class="card-body" style="color:white; background-color:#0d6efd  ;">

<h5>Total Issued</h5>

<h1><?= $total ?></h1>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-center">

<div class="card-body" style="color:white; background-color:#ffc107  ;">

<h5>Waiting</h5>

<h1><?= $waiting ?></h1>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-center">

<div class="card-body" style="color:white; background-color:#0dcaf0  ;">
<h5>Called</h5>

<h1><?= $called ?></h1>
</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-center">

<div class="card-body" style="color:white; background-color:#198754 ;">

<h5>Done</h5>

<h1><?= $done ?></h1>

</div>

</div>

</div>

<div style="padding-bottom: 1rem;"></div>
<!--
<div class="col-md-3">

<div class="card text-center">

<div class="card-body">

<h5>Cancelled</h5>

<h1><?= $cancelled ?></h1>

</div>

</div>

</div> -->


<table class="table table-bordered table-striped">
<thead>
<tr>

<th>Queue</th>
<th>Status</th>
<th>Issued</th>
</tr>
</thead>
</tbody>
<?php while($row = $recent->fetch_assoc()): ?>

<tr>

<td>
<?= str_pad($row['queue_number'],3,'0',STR_PAD_LEFT) ?>
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
<?= $row['issued_at'] ?>
</td>

</tr>

<?php endwhile; ?>
</tbody>
</table>

</div>
</div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>