<?php

include "../database/config.php";

$today = date("Y-m-d");

$q = $conn->query("
SELECT *
FROM queues
WHERE status='waiting'
AND queue_date='$today'
ORDER BY queue_number ASC
LIMIT 1
");

if($q->num_rows==0)
{
    echo json_encode([
        "success"=>false
    ]);
    exit;
}

$row = $q->fetch_assoc();

$conn->query("
UPDATE queues
SET
status='called',
called_at=NOW()
WHERE id=".$row['id']);

echo json_encode([
    "success"=>true,
    "id"=>$row['id'],
    "number"=>str_pad($row['queue_number'],3,"0",STR_PAD_LEFT)
]);