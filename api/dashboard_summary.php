<?php

/**
 * Dashboard Summary API
 * Queue System Ver. 3.0
 */

header('Content-Type: application/json');

require_once '../database/config.php';
require_once '../includes/dashboard_helper.php';

try {

    // Get Dashboard KPI Summary
    $summary = getDashboardSummary($conn);

    echo json_encode([
        "success" => true,
        "timestamp" => date("Y-m-d H:i:s"),
        "data" => $summary
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load dashboard summary.",
        "error" => $e->getMessage()
    ]);

}