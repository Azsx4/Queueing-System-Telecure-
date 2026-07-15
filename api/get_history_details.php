<?php

include '../database/config.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    exit("<div class='text-danger'>Invalid queue.</div>");
}

$stmt = $conn->prepare("
SELECT *
FROM queues
WHERE id=?
LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    exit("<div class='text-danger'>Queue not found.</div>");
}

/*
|--------------------------------------------------------------------------
| Waiting Time
|--------------------------------------------------------------------------
*/

$waiting = "-";

if (
    !empty($row['issued_at']) &&
    !empty($row['called_at'])
) {

    $waiting = gmdate(
        "i\\m s\\s",
        strtotime($row['called_at']) -
        strtotime($row['issued_at'])
    );

}

/*
|--------------------------------------------------------------------------
| Service Time
|--------------------------------------------------------------------------
*/

$service = "-";

if (
    !empty($row['called_at']) &&
    !empty($row['completed_at'])
) {

    $service = gmdate(
        "i\\m s\\s",
        strtotime($row['completed_at']) -
        strtotime($row['called_at'])
    );

}

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

$status = match($row['status']){

    'done' => 'Completed',

    'cancelled' => 'Missing',

    'called' => 'Serving',

    'waiting' => 'Waiting',

    'archived' => 'Archived',

    default => ucfirst($row['status'])

};

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-6">

            <h6 class="border-bottom pb-2">

                Queue Information

            </h6>

            <table class="table table-borderless">

                <tr>
                    <th width="180">Queue Number</th>
                    <td><?= str_pad($row['queue_number'],3,"0",STR_PAD_LEFT) ?></td>
                </tr>

                <tr>
                    <th>Queue Date</th>
                    <td><?= $row['queue_date'] ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td><?= $status ?></td>
                </tr>

                <tr>
                    <th>Reception</th>
                    <td><?= $row['reception_name'] ?: '-' ?></td>
                </tr>

            </table>

        </div>

        <div class="col-md-6">

            <h6 class="border-bottom pb-2">

                Performance

            </h6>

            <table class="table table-borderless">

                <tr>

                    <th width="180">

                        Waiting Time

                    </th>

                    <td>

                        <?= $waiting ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Service Time

                    </th>

                    <td>

                        <?= $service ?>

                    </td>

                </tr>

            </table>

        </div>

    </div>

    <hr>

    <h6 class="mb-3">

        Queue Timeline

    </h6>

    <table class="table">

        <tr>

            <th width="180">

                Queue Issued

            </th>

            <td>

                <?= $row['issued_at'] ?: '-' ?>

            </td>

        </tr>

        <tr>

            <th>

                Called

            </th>

            <td>

                <?= $row['called_at'] ?: '-' ?>

            </td>

        </tr>

        <tr>

            <th>

                Completed

            </th>

            <td>

                <?= $row['completed_at'] ?: '-' ?>

            </td>

        </tr>

    </table>

    <hr>

    <h6>

        Action History

    </h6>

    <ul class="timeline-list">

        <li>

            Queue Generated

            <small>

                <?= $row['issued_at'] ?: '-' ?>

            </small>

        </li>

        <?php if(!empty($row['called_at'])): ?>

        <li>

            Called by

            <strong>

                <?= $row['reception_name'] ?: '-' ?>

            </strong>

            <small>

                <?= $row['called_at'] ?>

            </small>

        </li>

        <?php endif; ?>

        <?php if(!empty($row['completed_at'])): ?>

        <li>

            Queue Completed

            <small>

                <?= $row['completed_at'] ?>

            </small>

        </li>

        <?php endif; ?>

    </ul>

</div>