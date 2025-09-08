<?php
require_once __DIR__ . '/../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

$user_id = (int)$_GET['user_id'];

try {
    // SQL query to fetch orders and their items
    $sql = "SELECT o.id, o.total_price, o.payment_method, o.status,
                   oi.product_id, oi.name AS product_name, oi.price, oi.quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id
            ORDER BY o.id DESC";  // Fetch all orders for the user regardless of status

    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all order data
    $ordersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$ordersData) {
        echo json_encode(["success" => false, "message" => "No orders found."]);
        exit;
    }

    // Initialize orders array to organize order data
    $orders = [];
    foreach ($ordersData as $row) {
        $orderId = $row['id'];

        // If this is the first time seeing this order, initialize it
        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                "id" => $orderId,
                "total_price" => $row['total_price'],
                "payment_method" => $row['payment_method'],
                "status" => $row['status'],
                "items" => []  // Store order items here
            ];
        }

        // If the row has product data, add it to the order's items
        if (!empty($row['product_id'])) {
            $orders[$orderId]["items"][] = [
                "product_id" => $row['product_id'],
                "name" => $row['product_name'],
                "price" => $row['price'],
                "quantity" => $row['quantity']
            ];
        }
    }

    // Return the orders data as JSON
    echo json_encode(["success" => true, "orders" => array_values($orders)]);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
