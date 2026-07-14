<?php

include "../database/config.php";

$status = $_GET['status'];
$today = date('Y-m-d');

$stmt = $conn->prepare("
SELECT
id,
queue_number,
issued_at,
called_at,
completed_at,
reception_name
FROM queues
WHERE status = ?
AND queue_date = ?
ORDER BY completed_at DESC
");

$stmt->bind_param("ss", $status, $today);

$stmt->execute();

$result=$stmt->get_result();

echo "<table class='table table-hover'>";

echo "<thead>
<tr>
<th>Queue</th>
<th>Waiting Time</th>
<th>Issued</th>
<th>Called</th>
<th>Action Triggered At</th>
<th>Reception</th>
</tr>
</thead>";

while($row = $result->fetch_assoc()){

    $waitingTime = strtotime($row['called_at']) - strtotime($row['issued_at']);
    $minutes = floor($waitingTime / 60);
    $seconds = $waitingTime % 60;

    echo "<tr class='missing-row'
            data-id='".$row['id']."'
            data-queue='".str_pad($row['queue_number'],3,"0",STR_PAD_LEFT)."'
            onclick='confirmReturnQueue(this)'>";

    echo "<td>".str_pad($row['queue_number'],3,"0",STR_PAD_LEFT)."</td>";

    echo "<td>".sprintf("%02d:%02d",$minutes,$seconds)."</td>";

    echo "<td>".date('h:i A',strtotime($row['issued_at']))."</td>";

    echo "<td>".date('h:i A',strtotime($row['called_at']))."</td>";

    echo "<td>".date('h:i A',strtotime($row['completed_at']))."</td>";

    echo "<td>".$row['reception_name']."</td>";



    echo "</tr>";
}


echo "</table>";