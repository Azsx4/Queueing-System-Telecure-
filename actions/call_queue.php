<?php

include '../database/config.php';

$id = (int)$_GET['id'];

$conn->query("
UPDATE queues
SET status='called',
    called_at=NOW()
WHERE id=$id
");

header("Location: ../reception.php");
