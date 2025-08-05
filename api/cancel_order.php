<?php
require_once __DIR__ . '/../config/db.php';

function cancelOrder($conn, $orderId)
{
    // Prepare the SQL query to update the order status to 'Cancelled'
    $query = "UPDATE orders SET status = 'Cancelled' WHERE id = :orderId";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Order has been successfully cancelled."]);
        } else {
            echo json_encode(["success" => false, "message" => "Order not found or already cancelled."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

if (isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    cancelOrder($conn, $orderId);
} else {
    echo json_encode(["success" => false, "message" => "Order ID is required."]);
}
