<?php

include 'config.php';

$conn->query("
TRUNCATE TABLE queues
");

$conn->query("
TRUNCATE TABLE queue_logs
");

header("Location:index.php");
exit;