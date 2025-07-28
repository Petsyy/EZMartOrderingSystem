<?php
require_once __DIR__ . '/../config/db.php';

header("Content-Type: application/json");

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'place') {
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $customer_name = $data['customer_name'] ?? '';
    $total_price = isset($data['total_price']) ? (float)$data['total_price'] : 0;
    $status = $data['status'] ?? 'Pending';
    $payment_method = $data['payment_method'] ?? '';
    $fcm_token = $data['fcm_token'] ?? null;
    $items = $data['items'] ?? [];

    if ($user_id <= 0 || empty($customer_name) || empty($payment_method) || empty($items)) {
        echo json_encode(["success" => false, "message" => "Invalid order data."]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Prepare order details (formatted text in a single line)
        $orderDetailsText = [];

        // Insert into orders table (Initially empty order_details)
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, order_details, total_price, status, payment_method, fcm_token, created_at) 
                                VALUES (:user_id, :customer_name, '', :total_price, :status, :payment_method, :fcm_token, NOW())");

        $stmt->execute([
            ':user_id' => $user_id,
            ':customer_name' => $customer_name,
            ':total_price' => $total_price,
            ':status' => $status,
            ':payment_method' => $payment_method,
            ':fcm_token' => $fcm_token
        ]);

        $order_id = $conn->lastInsertId();

        // Insert into order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, name) 
                                VALUES (:order_id, :product_id, :quantity, :price, :name)");

        foreach ($items as $item) {
            $product_id = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $price = isset($item['price']) ? (float)$item['price'] : 0;

            if ($product_id > 0 && $quantity > 0) {
                // Fetch product name from `products` table
                $productStmt = $conn->prepare("SELECT name FROM products WHERE id = :product_id");
                $productStmt->execute([':product_id' => $product_id]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                $product_name = $product ? $product['name'] : 'Unknown Product';

                // Insert into order_items table
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product_id,
                    ':quantity' => $quantity,
                    ':price' => $price,
                    ':name' => $product_name
                ]);

                // Format order details for `orders` table (Single-line format)
                $orderDetailsText[] = "{$product_name} (Qty: {$quantity}) - ₱{$price}";
            }
        }

        // Update `order_details` column with formatted text
        $orderDetailsFormatted = implode(" • ", $orderDetailsText);
        $updateStmt = $conn->prepare("UPDATE orders SET order_details = :order_details WHERE id = :order_id");
        $updateStmt->execute([
            ':order_details' => $orderDetailsFormatted,
            ':order_id' => $order_id
        ]);

        $conn->commit();

        echo json_encode(["success" => true, "message" => "Order placed successfully!", "order_id" => $order_id]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => "Transaction failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
