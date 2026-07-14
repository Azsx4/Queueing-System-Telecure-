<?php

header('Content-Type: application/json');

require_once '../database/config.php';

try {

    $today = date('Y-m-d');

    /*
    |----------------------------------------
    | Waiting Queues
    |----------------------------------------
    */

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE
            queue_date='$today'
            AND status='waiting'
    ";

    $waiting = (int)$conn
        ->query($sql)
        ->fetch_assoc()['total'];

    /*
    |----------------------------------------
    | Average Service Time (seconds)
    |----------------------------------------
    */

    $sql = "
        SELECT
            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    called_at,
                    completed_at
                )
            ) avg_time
        FROM queues
        WHERE
            queue_date='$today'
            AND completed_at IS NOT NULL
            AND called_at IS NOT NULL
    ";

    $average = (int)$conn
        ->query($sql)
        ->fetch_assoc()['avg_time'];

    /*
    |----------------------------------------
    | Active Capacity
    |----------------------------------------
    | NOTE:
    | Replace this query with your
    | reception session table once available.
    */

    /*
    |--------------------------------------------------------------------------
    | Total Active Slots
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            COALESCE(SUM(active_slots),0) AS capacity
        FROM receptions
        WHERE started_at IS NOT NULL
    ";

    $capacity = (int)$conn
        ->query($sql)
        ->fetch_assoc()['capacity'];

    if ($capacity <= 0) {

        $capacity = 1;

    }

    /*
    |----------------------------------------
    | Estimated Seconds Remaining
    |----------------------------------------
    */

    $estimateSeconds = ($waiting * $average) / $capacity;

    $completionTime = date(
        "h:i A",
        time() + $estimateSeconds
    );

    $hours = floor($estimateSeconds / 3600);
    $minutes = floor(($estimateSeconds % 3600) / 60);

    $duration = "";

    if ($hours > 0) {

        $duration .= $hours . " hour";

        if ($hours > 1) {
            $duration .= "s";
        }

        $duration .= " ";

    }

    $duration .= $minutes . " minutes";

    echo json_encode([

        "success" => true,

        "waiting" => $waiting,

        "averageService" => $average,

        "capacity" => $capacity,

        "estimatedTime" => $completionTime,

        "remainingText" => trim($duration)

    ]);

} catch (Exception $e) {

    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}