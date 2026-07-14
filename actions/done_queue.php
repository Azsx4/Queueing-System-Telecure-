<?php

session_start();
include '../database/config.php';

$id = (int)($_GET['id'] ?? 0);

$reception_id = $_SESSION['reception_id'] ?? null;

if (!$reception_id || !$id) {
    http_response_code(400);
    exit("Invalid request");
}

// Verify this reception owns the queue
$stmt = $conn->prepare("
    SELECT called_by
    FROM queues
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    exit("Queue not found");
}

if ((int)$row['called_by'] !== (int)$reception_id) {
    exit("Not allowed");
}

// Mark queue as done
$stmt = $conn->prepare("
    UPDATE queues
    SET
        status='done',
        completed_at=NOW(),
        completed_by=?
    WHERE id=?
");

$stmt->bind_param("ii", $reception_id, $id);
$stmt->execute();

echo "success";