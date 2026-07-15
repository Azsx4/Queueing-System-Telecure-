<?php

header('Content-Type: application/json');

require '../database/config.php';
require 'history_query.php';

echo json_encode(
    getHistoryData($conn, $_GET)
);