<?php

require_once "../config/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM customers";
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($customers);
    exit;
}

// Add a new customer (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name = $_POST['name'] ?? '';
    $product = $_POST['product'] ?? '';
    $total_price = $_POST['total_price'] ?? '';

    if (empty($name) || empty($product) || !is_numeric($total_price)) {
        echo json_encode(["error" => "Invalid input data"]);
        exit;
    }

    $sql = "INSERT INTO customers (name, product, total_price) VALUES (:name, :product, :total_price)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':product', $product);
    $stmt->bindParam(':total_price', $total_price);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Customer added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding customer"]);
    }
    exit;
}

// Update a customer (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $id = $_POST['id'] ?? '';
    $product = $_POST['product'] ?? '';
    $total_price = $_POST['total_price'] ?? '';

    if (!is_numeric($id) || empty($product) || !is_numeric($total_price)) {
        echo json_encode(["error" => "Invalid input data"]);
        exit;
    }

    $sql = "UPDATE customers SET product=:product, total_price=:total_price WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product', $product);
    $stmt->bindParam(':total_price', $total_price);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Customer updated successfully"]);
    } else {
        echo json_encode(["error" => "Error updating customer"]);
    }
    exit;
}

// Delete a customer (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? '';

    if (!is_numeric($id)) {
        echo json_encode(["error" => "Invalid customer ID"]);
        exit;
    }

    $sql = "DELETE FROM customers WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Customer deleted successfully"]);
    } else {
        echo json_encode(["error" => "Error deleting customer"]);
    }
    exit;
}

$conn = null;
