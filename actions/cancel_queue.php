<?php

include '../database/config.php';

$id = (int)$_GET['id'];

$conn->query("
UPDATE queues
SET status='cancelled'
WHERE id=$id
");

header("Location: ../reception.php");