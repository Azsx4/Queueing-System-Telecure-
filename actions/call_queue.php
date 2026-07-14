<?php

session_start();

include '../database/config.php';

$id = (int)$_GET['id'];

$reception = $_SESSION['reception_name'] ?? 'Unknown';
$reception_id = isset($_SESSION['reception_id']) ? (int)$_SESSION['reception_id'] : null;

if ($reception_id) {
    $stmt = $conn->prepare("UPDATE queues SET status='called', called_at=NOW(), reception_name=?, called_by=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('sii', $reception, $reception_id, $id);
        $stmt->execute();
    }
} else {
    // fallback: mark called but without caller id
    $stmt = $conn->prepare("UPDATE queues SET status='called', called_at=NOW(), reception_name=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('si', $reception, $id);
        $stmt->execute();
    }
}

header("Location: ../reception.php");
