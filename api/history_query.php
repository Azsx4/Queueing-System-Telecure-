<?php

/**
 * ============================================================
 * HISTORY QUERY SERVICE
 * Queue Management System
 * ============================================================
 */


/**
 * ============================================================
 * Format Duration
 * ============================================================
 */
function formatDuration($seconds)
{
    if ($seconds === null || $seconds <= 0) {
        return "-";
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    if ($hours > 0) {
        return sprintf("%dh %02dm %02ds", $hours, $minutes, $seconds);
    }

    return sprintf("%dm %02ds", $minutes, $seconds);
}


/**
 * ============================================================
 * Format DateTime
 * ============================================================
 */
function formatDateTime($datetime)
{
    if (empty($datetime)) {
        return "-";
    }

    return date("M d, Y h:i A", strtotime($datetime));
}


/**
 * ============================================================
 * Translate Status
 * ============================================================
 */
function getStatusInfo($status)
{
    switch ($status) {

        case 'waiting':
            return [
                'display' => 'Waiting',
                'class' => 'waiting'
            ];

        case 'called':
            return [
                'display' => 'Serving',
                'class' => 'called'
            ];

        case 'done':
            return [
                'display' => 'Completed',
                'class' => 'completed'
            ];

        case 'cancelled':
            return [
                'display' => 'Missing',
                'class' => 'missing'
            ];

        case 'archived':
            return [
                'display' => 'Archived',
                'class' => 'archived'
            ];

        default:

            return [
                'display' => ucfirst($status),
                'class' => 'secondary'
            ];
    }
}


/**
 * ============================================================
 * Build WHERE Clause
 * ============================================================
 */
function buildHistoryWhere(array $request)
{

    $where = [];
    $params = [];
    $types = "";

    /*
    ---------------------------------------------------------
    SEARCH
    ---------------------------------------------------------
    */

    if (!empty($request['search'])) {

        $keyword = "%" . trim($request['search']) . "%";

        $where[] = "(queue_number LIKE ? OR reception_name LIKE ?)";

        $params[] = $keyword;
        $params[] = $keyword;

        $types .= "ss";
    }

    /*
    ---------------------------------------------------------
    STATUS
    ---------------------------------------------------------
    */

    if (!empty($request['status'])) {

        $where[] = "status=?";

        $params[] = trim($request['status']);

        $types .= "s";

    }

    /*
    ---------------------------------------------------------
    RECEPTION
    ---------------------------------------------------------
    */

    if (!empty($request['reception'])) {

        $where[] = "reception_name=?";

        $params[] = trim($request['reception']);

        $types .= "s";

    }

    /*
    ---------------------------------------------------------
    DATE FROM
    ---------------------------------------------------------
    */

    if (!empty($request['dateFrom'])) {

        $where[] = "queue_date>=?";

        $params[] = $request['dateFrom'];

        $types .= "s";

    }

    /*
    ---------------------------------------------------------
    DATE TO
    ---------------------------------------------------------
    */

    if (!empty($request['dateTo'])) {

        $where[] = "queue_date<=?";

        $params[] = $request['dateTo'];

        $types .= "s";

    }

    return [

        'sql' => count($where)
            ? "WHERE " . implode(" AND ", $where)
            : "",

        'params' => $params,

        'types' => $types

    ];

}


/**
 * ============================================================
 * Summary Cards
 * ============================================================
 */
function getHistorySummary(mysqli $conn, array $filter)
{

    $sql = "

        SELECT

            COUNT(*) total,

            SUM(status='done') completed,

            SUM(status='cancelled') missing,

            SUM(status='waiting') waiting,

            SUM(status='called') serving,

            SUM(status='archived') archived,

            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    issued_at,
                    called_at
                )
            ) avg_wait,

            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    called_at,
                    completed_at
                )
            ) avg_service

        FROM queues

        {$filter['sql']}

    ";

    $stmt = $conn->prepare($sql);

    if ($filter['types'] != "") {

        $stmt->bind_param(
            $filter['types'],
            ...$filter['params']
        );

    }

    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return [

        'total' => intval($row['total']),

        'completed' => intval($row['completed']),

        'missing' => intval($row['missing']),

        'waiting' => intval($row['waiting']),

        'serving' => intval($row['serving']),

        'archived' => intval($row['archived']),

        'cancelled' => intval($row['missing']),

        'average_wait' => formatDuration($row['avg_wait']),

        'average_service' => formatDuration($row['avg_service'])

    ];

}


/**
 * ============================================================
 * Reception Dropdown
 * ============================================================
 */
function getReceptionList(mysqli $conn)
{

    $list = [];

    $query = $conn->query("

        SELECT DISTINCT reception_name

        FROM queues

        WHERE reception_name IS NOT NULL

        AND reception_name<>''

        ORDER BY reception_name

    ");

    while ($row = $query->fetch_assoc()) {

        $list[] = $row['reception_name'];

    }

    return $list;

}
/**
 * ============================================================
 * Queue Records
 * ============================================================
 */
function getHistoryRecords(mysqli $conn, array $filter, array $request)
{
    $page = max(1, intval($request['page'] ?? 1));
    $limit = max(1, intval($request['limit'] ?? 20));
    $offset = ($page - 1) * $limit;

    $sort = strtolower($request['sort'] ?? 'desc') === 'asc'
        ? 'ASC'
        : 'DESC';

    /*
    ------------------------------------------------------------
    Count Records
    ------------------------------------------------------------
    */

    $countSql = "
        SELECT COUNT(*) total
        FROM queues
        {$filter['sql']}
    ";

    $stmt = $conn->prepare($countSql);

    if ($filter['types'] !== "") {
        $stmt->bind_param(
            $filter['types'],
            ...$filter['params']
        );
    }

    $stmt->execute();

    $total = intval(
        $stmt->get_result()->fetch_assoc()['total']
    );

    /*
    ------------------------------------------------------------
    Fetch Records
    ------------------------------------------------------------
    */

    $sql = "
        SELECT *
        FROM queues

        {$filter['sql']}

        ORDER BY
            queue_date {$sort},
            queue_number {$sort}

        LIMIT ?
        OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $types = $filter['types'] . "ii";

    $params = $filter['params'];

    $params[] = $limit;
    $params[] = $offset;

    $stmt->bind_param($types, ...$params);

    $stmt->execute();

    $result = $stmt->get_result();

    $records = [];

    while ($row = $result->fetch_assoc()) {

        /*
        --------------------------------------------------------
        Waiting Time
        --------------------------------------------------------
        */

        $waitingSeconds = null;

        if (
            !empty($row['issued_at']) &&
            !empty($row['called_at'])
        ) {

            $waitingSeconds =
                strtotime($row['called_at'])
                -
                strtotime($row['issued_at']);

        }

        /*
        --------------------------------------------------------
        Service Time
        --------------------------------------------------------
        */

        $serviceSeconds = null;

        if (
            !empty($row['called_at']) &&
            !empty($row['completed_at'])
        ) {

            $serviceSeconds =
                strtotime($row['completed_at'])
                -
                strtotime($row['called_at']);

        }

        /*
        --------------------------------------------------------
        Status
        --------------------------------------------------------
        */

        $status = getStatusInfo($row['status']);

        $row['status_display'] = $status['display'];
        $row['status_class'] = $status['class'];

        /*
        --------------------------------------------------------
        Waiting
        --------------------------------------------------------
        */

        $row['waiting_seconds'] = $waitingSeconds;
        $row['waiting_time'] = formatDuration($waitingSeconds);

        /*
        --------------------------------------------------------
        Service
        --------------------------------------------------------
        */

        $row['service_seconds'] = $serviceSeconds;
        $row['service_time'] = formatDuration($serviceSeconds);

        /*
        --------------------------------------------------------
        Timeline
        --------------------------------------------------------
        */

        $timeline = [];

        $timeline[] = [

            'title' => 'Queue Issued',

            'time' => $row['issued_at'],

            'icon' => 'ticket'

        ];

        if (!empty($row['called_at'])) {

            $timeline[] = [

                'title' => 'Called',

                'time' => $row['called_at'],

                'icon' => 'bullhorn'

            ];

        }

        if (!empty($row['returned_at'])) {

            $timeline[] = [

                'title' => 'Returned',

                'time' => $row['returned_at'],

                'icon' => 'rotate-left'

            ];

        }

        if (!empty($row['completed_at'])) {

            $timeline[] = [

                'title' => 'Completed',

                'time' => $row['completed_at'],

                'icon' => 'check'

            ];

        }

        if (
            $row['status'] == 'cancelled' &&
            !empty($row['completed_at'])
        ) {

            $timeline[] = [

                'title' => 'Marked Missing',

                'time' => $row['completed_at'],

                'icon' => 'triangle-exclamation'

            ];

        }

        $row['timeline'] = $timeline;

        /*
        --------------------------------------------------------
        Display Date
        --------------------------------------------------------
        */

        $row['issued_display'] =
            formatDateTime($row['issued_at']);

        $row['called_display'] =
            formatDateTime($row['called_at']);

        $row['completed_display'] =
            formatDateTime($row['completed_at']);

        $row['returned_display'] =
            formatDateTime($row['returned_at']);

        $records[] = $row;

    }

    return [

        'records' => $records,

        'pagination' => [

            'page' => $page,

            'pages' => max(
                1,
                ceil($total / $limit)
            ),

            'total' => $total,

            'from' => $total
                ? $offset + 1
                : 0,

            'to' => min(
                $offset + $limit,
                $total
            )

        ]

    ];

}

/**
 * ============================================================
 * Main Service
 * ============================================================
 */
function getHistoryData(mysqli $conn, array $request)
{
    $filter = buildHistoryWhere($request);

    $records = getHistoryRecords(
        $conn,
        $filter,
        $request
    );

    return [

        'summary' => getHistorySummary(
            $conn,
            $filter
        ),

        'records' => $records['records'],

        'pagination' => $records['pagination'],

        'receptions' => getReceptionList($conn)

    ];

}