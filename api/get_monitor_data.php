<?php

include '../database/config.php';

date_default_timezone_set('Asia/Manila');

$today = date('Y-m-d');

$response = [
    'active' => [],
    'upcoming' => []
];

/*
|--------------------------------------------------------------------------
| ACTIVE QUEUES
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    q.queue_number,
    r.name AS reception_name
FROM queues q
LEFT JOIN receptions r
    ON q.called_by = r.id
WHERE q.status='called'
AND q.queue_date=?
ORDER BY q.called_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$today);
$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){

    $response['active'][] = [

        'queue_number' =>
            str_pad($row['queue_number'],3,'0',STR_PAD_LEFT),

        'reception' =>
            $row['reception_name'] ?: 'Reception'

    ];

}

/*
|--------------------------------------------------------------------------
| UPCOMING
|--------------------------------------------------------------------------
*/

$sql = "
SELECT queue_number
FROM queues
WHERE status='waiting'
AND queue_date=?
ORDER BY queue_number ASC
LIMIT 6
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s",$today);

$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){

    $response['upcoming'][] =
        str_pad($row['queue_number'],3,'0',STR_PAD_LEFT);

}

header('Content-Type: application/json');

echo json_encode($response);