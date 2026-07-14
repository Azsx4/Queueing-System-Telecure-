<?php

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    $sql = "
        SELECT

            reception_name,

            COUNT(*) AS total,

            SUM(status='done') AS completed,

            SUM(status='cancelled') AS missing,

            SUM(is_returned=1) AS returned,

            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    issued_at,
                    called_at
                )
            ) AS avg_wait,

            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    called_at,
                    completed_at
                )
            ) AS avg_service

        FROM queues

        WHERE queue_date='$today'

        GROUP BY reception_name

        ORDER BY completed DESC
    ";

    $result = $conn->query($sql);

    $rows = [];

    while ($row = $result->fetch_assoc()) {

        $completed = (int)$row['completed'];
        $total = max((int)$row['total'], 1);

        $rows[] = [

            "reception" => $row['reception_name'] ?: "Unassigned",

            "completed" => $completed,

            "missing" => (int)$row['missing'],

            "averageWait" => gmdate(
                "i\\m s\\s",
                (int)$row['avg_wait']
            ),

            "averageService" => gmdate(
                "i\\m s\\s",
                (int)$row['avg_service']
            ),

            "returned" => (int)$row['returned'],

            "utilization" => round(
                ($completed / $total) * 100
            )

        ];

    }

    echo json_encode([
        "success" => true,
        "rows" => $rows
    ]);

}
catch(Exception $e){

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}