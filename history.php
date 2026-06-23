<?php

include 'database/config.php';



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
?>

<!DOCTYPE html>
<html>

<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/theme.css">
<link href="assets/css/styles.css" rel="stylesheet">
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
</body>

</html>