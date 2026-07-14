<?php

/**
 * Dashboard Helper
 * Queue System Ver. 3.0
 */

if (!isset($conn)) {
    include __DIR__ . '/../database/config.php';
}

/**
 * Return today's date
 */
function dashboardDate()
{
    return date('Y-m-d');
}

/**
 * Format seconds to HH:MM:SS
 */
function formatDuration($seconds)
{
    if ($seconds == null || $seconds <= 0) {
        return "00:00";
    }

    $minutes = floor($seconds / 60);
    $remaining = $seconds % 60;

    return sprintf("%02d:%02d", $minutes, $remaining);
}

/**
 * Format percentage
 */
function formatPercent($value)
{
    return number_format($value, 1) . "%";
}

/**
 * Total Queue Today
 */
function getTotalQueues($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE queue_date='$today'
    ";

    return (int)$conn->query($sql)->fetch_assoc()['total'];
}

/**
 * Waiting Queue
 */
function getWaitingQueues($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE status='waiting'
        AND queue_date='$today'
    ";

    return (int)$conn->query($sql)->fetch_assoc()['total'];
}

/**
 * Active Queue
 */
function getActiveQueues($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE status='called'
        AND queue_date='$today'
    ";

    return (int)$conn->query($sql)->fetch_assoc()['total'];
}

/**
 * Completed Queue
 */
function getCompletedQueues($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE status='done'
        AND queue_date='$today'
    ";

    return (int)$conn->query($sql)->fetch_assoc()['total'];
}

/**
 * Missing Queue
 */
function getMissingQueues($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT COUNT(*) total
        FROM queues
        WHERE status='cancelled'
        AND queue_date='$today'
    ";

    return (int)$conn->query($sql)->fetch_assoc()['total'];
}

/**
 * Completion Rate
 */
function getCompletionRate($conn)
{
    $total = getTotalQueues($conn);

    if ($total == 0) {
        return 0;
    }

    $completed = getCompletedQueues($conn);

    return round(($completed / $total) * 100, 1);
}


/**
 * Average Waiting Time
 * (created_at -> called_at)
 */
function getAverageWaitingTime($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT
        AVG(
            TIMESTAMPDIFF(
                SECOND,
                issued_at,
                called_at
            )
        ) avg_wait
        FROM queues
        WHERE
        queue_date='$today'
        AND called_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    $seconds = (int)$result->fetch_assoc()['avg_wait'];

    return formatDuration($seconds);
}


/**
 * Average Service Time
 * (called_at -> done_at)
 */
function getAverageServiceTime($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT
        AVG(
            TIMESTAMPDIFF(
                SECOND,
                called_at,
                completed_at
            )
        ) avg_service
        FROM queues
        WHERE
        queue_date='$today'
        AND completed_at IS NOT NULL
    ";

    $result = $conn->query($sql);

    $seconds = (int)$result->fetch_assoc()['avg_service'];

    return formatDuration($seconds);
}

/**
 * Peak Hour
 */
function getPeakHour($conn)
{
    $today = dashboardDate();

    $sql = "
        SELECT
            HOUR(issued_at) hour_slot,
            COUNT(*) total
        FROM queues
        WHERE queue_date='$today'
        GROUP BY HOUR(issued_at)
        ORDER BY total DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);

    if($result->num_rows==0){

        return "-";

    }

    $row=$result->fetch_assoc();

    $hour=(int)$row['hour_slot'];

    return date(
        "g:00 A",
        strtotime($hour.":00")
    );
}

/**
 * Reception Utilization
 */
function getReceptionUtilization($conn)
{

    $today=dashboardDate();

    $sql="
        SELECT
        COUNT(*) completed
        FROM queues
        WHERE
        status='done'
        AND queue_date='$today'
    ";

    $completed=(int)$conn
        ->query($sql)
        ->fetch_assoc()['completed'];

    $capacity=100;

    return min(
        round(($completed/$capacity)*100),
        100
    );

}

/**
 * Dashboard Summary
 */
function getDashboardSummary($conn)
{
    return [

        "total" => getTotalQueues($conn),

        "waiting" => getWaitingQueues($conn),

        "active" => getActiveQueues($conn),

        "completed" => getCompletedQueues($conn),

        "missing" => getMissingQueues($conn),

        "completionRate" => getCompletionRate($conn),

        "averageWait"=>getAverageWaitingTime($conn),

        "averageService"=>getAverageServiceTime($conn),

        "peakHour"=>getPeakHour($conn),

        "utilization"=>getReceptionUtilization($conn)

    ];
}
