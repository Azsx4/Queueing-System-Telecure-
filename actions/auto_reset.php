<?php

include 'config.php';

$conn->query("
DELETE FROM queues
WHERE queue_date < CURDATE()
");