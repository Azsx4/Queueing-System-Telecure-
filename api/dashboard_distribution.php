<?php

/**
 * Dashboard Distribution API
 * Queue System Ver. 3.0
 */

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    $sql = "
        SELECT
            status,
            COUNT(*) AS total
        FROM queues
        WHERE queue_date = '$today'
        GROUP BY status
    ";

    $result = $conn->query($sql);

    // Default values so every status always appears
    $distribution = [
        "waiting"   => 0,
        "called"    => 0,
        "done"      => 0,
        "cancelled" => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $distribution[$row['status']] = (int)$row['total'];
    }

    echo json_encode([
        "success" => true,
        "labels" => [
            "Waiting",
            "Serving",
            "Completed",
            "Missing"
        ],
        "values" => [
            $distribution['waiting'],
            $distribution['called'],
            $distribution['done'],
            $distribution['cancelled']
        ]
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}