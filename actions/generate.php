<?php
include '../database/config.php';

$today = date('Y-m-d');

$row = $conn->query("
SELECT MAX(queue_number) AS max_no
FROM queues
WHERE queue_date = CURDATE()
")->fetch_assoc();

$next = ($row['max_no']) ? $row['max_no'] + 1 : 1;

$conn->query("
INSERT INTO queues
(
queue_number,
queue_date,
status
)
VALUES
(
'$next',
'$today',
'pending'
)
");

$id = $conn->insert_id;

header("Location: ../index.php?id=".$id);
exit;