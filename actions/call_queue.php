<?php

session_start();

include '../database/config.php';

$id = (int)$_GET['id'];

$reception =
$_SESSION['reception_name'] ?? 'Unknown';

$stmt = $conn->prepare("
UPDATE queues
SET
    status='called',
    called_at=NOW(),
    reception_name=?
WHERE id=?
");

$stmt->bind_param(
    "si",
    $reception,
    $id
);

$stmt->execute();

header("Location: ../reception.php");
