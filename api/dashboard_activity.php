<?php

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    $sql = "

        SELECT

            HOUR(issued_at) AS hour,

            COUNT(*) AS total

        FROM queues

        WHERE queue_date='$today'

        GROUP BY HOUR(issued_at)

        ORDER BY HOUR(issued_at)

    ";

    $result = $conn->query($sql);

    $labels = [];

    $values = [];

    while($row = $result->fetch_assoc()){

        $hour = (int)$row['hour'];

        $labels[] = date(
            "g A",
            strtotime($hour . ":00")
        );

        $values[] = (int)$row['total'];

    }

    echo json_encode([

        "success"=>true,

        "labels"=>$labels,

        "values"=>$values

    ]);

}
catch(Exception $e){

    http_response_code(500);

    echo json_encode([

        "success"=>false,

        "message"=>$e->getMessage()

    ]);

}