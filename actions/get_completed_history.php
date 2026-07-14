<?php

include "../database/config.php";

$status = $_GET['status'] ?? 'all';
$today = date('Y-m-d');

if ($status === 'all') {
    $stmt = $conn->prepare(
        "SELECT
        id,
        queue_number,
        issued_at,
        called_at,
        completed_at,
        reception_name,
        status
        FROM queues
        WHERE queue_date = ?
        ORDER BY queue_number DESC"
    );
    $stmt->bind_param('s', $today);
} else {
    $stmt = $conn->prepare(
        "SELECT
        id,
        queue_number,
        issued_at,
        called_at,
        completed_at,
        reception_name,
        status
        FROM queues
        WHERE status = ?
        AND queue_date = ?
        ORDER BY completed_at DESC"
    );
    $stmt->bind_param('ss', $status, $today);
}

$stmt->execute();

$result=$stmt->get_result();

echo "<table class='table table-hover'>";

echo "<thead>
<tr>
<th>Queue</th>
<th>Waiting Time</th>
<th>Issued</th>
<th>Called</th>
<th>Status</th>
<th>Reception</th>
</tr>
</thead>";

while($row=$result->fetch_assoc()){

echo "<tr>";

echo "<td>".str_pad($row['queue_number'],3,"0",STR_PAD_LEFT)."</td>";
    $waitingTime = null;
    if (!empty($row['called_at'])) {
        $waitingTime = strtotime($row['called_at']) - strtotime($row['issued_at']);
    }
    $waitingDisplay = $waitingTime !== null
        ? sprintf("%02d:%02d", floor($waitingTime / 60), $waitingTime % 60)
        : '---';
    echo "<td>" . $waitingDisplay . "</td>";
    echo "<td>" . date('h:i A', strtotime($row['issued_at'])) . "</td>";
    echo "<td>" . (!empty($row['called_at']) ? date('h:i A', strtotime($row['called_at'])) : '---') . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>" . $row['reception_name'] . "</td>";

echo "</tr>";

}

echo "</table>";