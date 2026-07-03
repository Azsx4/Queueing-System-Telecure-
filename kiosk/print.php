<?php

include '../database/config.php';

$id = intval($_GET['id']);

$row = $conn->query("
SELECT *
FROM queues
WHERE id='$id'
")->fetch_assoc();

$conn->query("
UPDATE queues
SET
printed=1,
status='waiting'
WHERE id='$id'
");

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html>
<head>

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

window.onload = function(){

    window.print();

    setTimeout(function(){

        window.location =
        "../index.php";

    },1000);

};

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