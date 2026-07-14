<?php

session_start();
include "../database/config.php";

$id = (int)($_GET['id'] ?? 0);
$reception_id = $_SESSION['reception_id'] ?? 0;

if ($id <= 0 || $reception_id <= 0) {
    http_response_code(400);
    exit("Invalid request");
}

/*
|--------------------------------------------------------------------------
| Verify queue exists and belongs to this receptionist
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        queue_number,
        called_by
    FROM queues
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$queue = $result->fetch_assoc();

if (!$queue) {
    exit("Queue not found");
}

/*
|--------------------------------------------------------------------------
| Ownership validation
|--------------------------------------------------------------------------
*/

if (
    !is_null($queue['called_by']) &&
    (int)$queue['called_by'] !== (int)$reception_id
) {
    exit("Not allowed");
}

/*
|--------------------------------------------------------------------------
| Get next priority order
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COALESCE(MAX(priority_order),0)+1 AS next_priority
    FROM queues
    WHERE queue_date = CURDATE()
");

$stmt->execute();

$nextPriority = (int)$stmt
    ->get_result()
    ->fetch_assoc()['next_priority'];

/*
|--------------------------------------------------------------------------
| Return queue
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
UPDATE queues
SET

    status='waiting',

    is_returned=1,

    priority_order=?,

    returned_at=NOW(),

    return_count=return_count+1,

    called_at=NULL,

    completed_at=NULL,

    called_by=NULL,

    reception_name=NULL

WHERE id=?
");

$stmt->bind_param("ii", $nextPriority, $id);

$stmt->execute();

if ($stmt->affected_rows > 0) {

    echo "success";

} else {

    echo "No rows updated";

}