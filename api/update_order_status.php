<?php
require_once "../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order ID from the request
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    // Validate input
    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    try {
        // Force the status to "Completed"
        $newStatus = "Completed";

        // Prepare the query using PDO
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");

        // Bind parameters
        $stmt->bindParam(":status", $newStatus, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order marked as Completed"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update order"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
