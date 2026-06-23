<?php

include 'config.php';

$ADMIN_PASSWORD = "ITADMIN2026";

$id = (int)($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

if($password !== $ADMIN_PASSWORD)
{
    die("Invalid Admin Password.");
}

$conn->query("
UPDATE queues
SET status='archived'
WHERE id=$id
");

header("Location: history.php");
exit;