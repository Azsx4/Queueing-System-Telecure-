<?php

include 'config.php';

$id = intval($_GET['id']);

$row = $conn->query("
SELECT *
FROM queues
WHERE id='$id'
")->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

<title>Reprint Ticket</title>

<style>

@page{
size:58mm auto;
margin:0;
}

body{
font-family:Arial;
text-align:center;
}

.number{
font-size:70px;
font-weight:bold;
}

</style>

<script>

window.onload=function(){
window.print();
}

</script>

</head>

<body>

<h3>
Patient Registration
</h3>

<p>
Queue Number
</p>

<div class="number">

<?= str_pad(
$row['queue_number'],
3,
'0',
STR_PAD_LEFT
); ?>

</div>

<p>

<?= date(
"M d, Y h:i A",
strtotime($row['issued_at'])
); ?>

</p>

</body>

</html>