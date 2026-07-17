<?php
session_start();
include "../database/config.php";

header("Content-Type: application/json");

$today = date('Y-m-d');

$activeSlots = $_SESSION['active_slots'] ?? 1;

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$countResult = $conn->query("
SELECT
COUNT(*) total,

SUM(CASE WHEN status='waiting' THEN 1 ELSE 0 END) waiting,

SUM(CASE WHEN status='called' THEN 1 ELSE 0 END) called,

SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) done,

SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled

FROM queues

WHERE queue_date='$today'
")->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Current Queue
|--------------------------------------------------------------------------
*/

$current = $conn->query("
SELECT *

FROM queues

WHERE status='called'

AND queue_date='$today'

ORDER BY called_at DESC

LIMIT 1
")->fetch_assoc();

$currentQueueNumber = $current
    ? (int)$current['queue_number']
    : 0;

/*
|--------------------------------------------------------------------------
| Active Queues
|--------------------------------------------------------------------------
*/

$activeQueues = [];

$result = $conn->query("
SELECT

id,

queue_number,

called_at,

TIMESTAMPDIFF(SECOND,called_at,NOW()) waiting_seconds,

reception_name,

called_by

FROM queues

WHERE status='called'

AND queue_date='$today'

ORDER BY called_at ASC
");

while($row = $result->fetch_assoc()){
    $activeQueues[] = $row;
}

/*
|--------------------------------------------------------------------------
| Upcoming Queues
|--------------------------------------------------------------------------
*/

$upcomingQueues = [];

$sql = "

SELECT *

FROM queues

WHERE status='waiting'

AND queue_date='$today'
";

if($currentQueueNumber>0){

$sql .= " AND queue_number>{$currentQueueNumber}";
}

$sql .= "

ORDER BY

CASE

WHEN priority_order>0 THEN 0

ELSE 1

END,

priority_order ASC,

queue_number ASC

LIMIT 6
";

$result = $conn->query($sql);

while($row=$result->fetch_assoc()){

$upcomingQueues[]=$row;

}

/*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/

echo json_encode([

"success"=>true,

"activeSlots"=>$activeSlots,

"statistics"=>$countResult,

"current"=>$current,

"activeQueues"=>$activeQueues,

"upcomingQueues"=>$upcomingQueues

]);