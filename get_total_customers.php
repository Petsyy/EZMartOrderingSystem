<?php

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

try {
    
    $checkColumnQuery = "SHOW COLUMNS FROM orders LIKE 'customer_id'";
    $checkStmt = $conn->prepare($checkColumnQuery);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        $sql = "SELECT COUNT(DISTINCT customer_id) AS total FROM orders";
    } else {
        $sql = "SELECT COUNT(DISTINCT customer_name) AS total FROM orders";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCustomers = $result ? (int) $result['total'] : 0;

    error_log("Total unique customers counted: " . $totalCustomers);

    echo json_encode(["total" => $totalCustomers]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
