<?php

include 'config.php';

$id = $_POST['id'];
$queue = intval($_POST['queue_number']);

$conn->query("
UPDATE queues
SET queue_number = '$queue'
WHERE id = '$id'
");

header("Location:index.php");
exit;