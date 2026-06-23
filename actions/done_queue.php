<?php

include '../database/config.php';

$id = (int)$_GET['id'];

$conn->query("
UPDATE queues
SET status='done',
    completed_at=NOW()
WHERE id=$id
");

header("Location: ../reception.php");