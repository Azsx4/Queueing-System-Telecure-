<?php

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    /*
    |--------------------------------------------------------------------------
    | Best Performing Reception
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            reception_name,
            COUNT(*) completed
        FROM queues
        WHERE
            queue_date='$today'
            AND status='done'
        GROUP BY reception_name
        ORDER BY completed DESC
        LIMIT 1
    ";

    $bestReception = $conn->query($sql)->fetch_assoc();

    /*
    |--------------------------------------------------------------------------
    | Peak Hour
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            HOUR(issued_at) hr,
            COUNT(*) total
        FROM queues
        WHERE queue_date='$today'
        GROUP BY HOUR(issued_at)
        ORDER BY total DESC
        LIMIT 1
    ";

    $peak = $conn->query($sql)->fetch_assoc();

    /*
    |--------------------------------------------------------------------------
    | Longest Waiting Queue
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            queue_number,
            TIMESTAMPDIFF(
                MINUTE,
                issued_at,
                NOW()
            ) waiting
        FROM queues
        WHERE
            status='waiting'
            AND queue_date='$today'
        ORDER BY waiting DESC
        LIMIT 1
    ";

    $waiting = $conn->query($sql)->fetch_assoc();

    /*
    |--------------------------------------------------------------------------
    | Active Reception
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            COUNT(DISTINCT reception_name) total
        FROM queues
        WHERE
            queue_date='$today'
            AND reception_name IS NOT NULL
            AND reception_name<>''
    ";

    $activeReception =
        $conn->query($sql)
        ->fetch_assoc()['total'];

    /*
    |--------------------------------------------------------------------------
    | Returned
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            COUNT(*) total
        FROM queues
        WHERE
            queue_date='$today'
            AND is_returned=1
    ";

    $returned =
        $conn->query($sql)
        ->fetch_assoc()['total'];

    /*
    |--------------------------------------------------------------------------
    | Completion
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            COUNT(*) total,
            SUM(status='done') completed
        FROM queues
        WHERE queue_date='$today'
    ";

    $summary =
        $conn->query($sql)
        ->fetch_assoc();

    $completion = 0;

    if($summary['total']>0){

        $completion =
        round(
            ($summary['completed']/$summary['total'])*100,
            1
        );

    }

    echo json_encode([

        "success"=>true,

        "bestReception"=>$bestReception,

        "peakHour"=>$peak,

        "waiting"=>$waiting,

        "activeReception"=>$activeReception,

        "returned"=>$returned,

        "completion"=>$completion

    ]);

}
catch(Exception $e){

    echo json_encode([

        "success"=>false,

        "message"=>$e->getMessage()

    ]);

}