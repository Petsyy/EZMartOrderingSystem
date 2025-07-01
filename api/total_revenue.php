<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT COALESCE(SUM(total_price), 0) AS total_revenue FROM orders WHERE status = 'Completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRevenue = $row['total_revenue'];

    echo json_encode([
        'success' => true,
        'totalRevenue' => number_format($totalRevenue, 2)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch revenue data',
        'message' => $e->getMessage()
    ]);
}
