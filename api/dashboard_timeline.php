<?php

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    $timeline = [];

    /*
     * Queue Issued
     */

    $sql = "
        SELECT
            queue_number,
            issued_at
        FROM queues
        WHERE
            queue_date='$today'
            AND issued_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){

        $timeline[] = [

            "time" => strtotime($row['issued_at']),

            "label" => date("h:i A", strtotime($row['issued_at'])),

            "icon" => "ticket",

            "color" => "primary",

            "text" => "Queue {$row['queue_number']} issued"

        ];

    }

    /*
     * Queue Called
     */

    $sql = "
        SELECT
            queue_number,
            called_at,
            reception_name
        FROM queues
        WHERE
            queue_date='$today'
            AND called_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){

        $timeline[] = [

            "time" => strtotime($row['called_at']),

            "label" => date("h:i A", strtotime($row['called_at'])),

            "icon" => "bullhorn",

            "color" => "info",

            "text" => "Queue {$row['queue_number']} called by {$row['reception_name']}"

        ];

    }

    /*
     * Queue Completed
     */

    $sql = "
        SELECT
            queue_number,
            completed_at,
            completed_by
        FROM queues
        WHERE
            queue_date='$today'
            AND completed_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){

        $timeline[] = [

            "time" => strtotime($row['completed_at']),

            "label" => date("h:i A", strtotime($row['completed_at'])),

            "icon" => "check",

            "color" => "success",

            "text" => "Queue {$row['queue_number']} completed"

        ];

    }

    /*
     * Queue Missing
     */

    $sql = "
        SELECT
            queue_number,
            completed_at,
            skipped_by
        FROM queues
        WHERE
            queue_date='$today'
            AND status='cancelled'
            AND completed_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){

        $timeline[] = [

            "time" => strtotime($row['completed_at']),

            "label" => date("h:i A", strtotime($row['completed_at'])),

            "icon" => "user-slash",

            "color" => "danger",

            "text" => "Queue {$row['queue_number']} marked missing"

        ];

    }

    usort($timeline,function($a,$b){

        return $b['time'] <=> $a['time'];

    });

    // Keep only the latest 10 events
    $timeline = array_slice($timeline, 0, 10);

    echo json_encode([

        "success"=>true,

        "events"=>$timeline

    ]);

}
catch(Exception $e){

    echo json_encode([

        "success"=>false,

        "message"=>$e->getMessage()

    ]);

}