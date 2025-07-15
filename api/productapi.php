<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// Handle OPTIONS preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Fetch Products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT id, name, price, stock, image, category FROM products");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database query failed"]);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();
    $conn->close();

    echo json_encode(["success" => true, "products" => $products]);
    exit;
}

// Handle DELETE (Fixing the issue)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $data['id'] ?? $_GET['id'] ?? null;

    if (!$productId) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing product ID"]);
        exit;
    }

    // Check if product exists before deleting
    $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->bind_param("i", $productId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Product not found"]);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();

    // Proceed with deletion
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Product deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Failed to delete product"]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Handle POST actions (Add, Update, Reduce Stock)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';

    // Add Product
    if ($action === 'add') {
        if (isset($data['name'], $data['price'], $data['stock'], $data['image'], $data['category'])) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiss", $data['name'], $data['price'], $data['stock'], $data['image'], $data['category']);
            echo json_encode(["success" => $stmt->execute()]);
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Missing required fields"]);
        }
        exit;
    }

    // Update Product
    if ($action === 'update') {
        if (isset($data['name'], $data['id'], $data['price'], $data['stock'], $data['category'])) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
            $stmt->bind_param("sdssi", $data['name'], $data['price'], $data['stock'], $data['category'], $data['id']);
            echo json_encode(["success" => $stmt->execute()]);
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Missing required fields for update"]);
        }
        exit;
    }

    // Reduce Stock for Multiple Products
    if ($action === 'reduce_stock') {
        if (isset($data['products']) && is_array($data['products'])) {
            $success = true;
            foreach ($data['products'] as $product) {
                $product_id = $product['product_id'];
                $quantity = $product['quantity'];

                $checkStock = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $checkStock->bind_param("i", $product_id);
                $checkStock->execute();
                $result = $checkStock->get_result();
                $row = $result->fetch_assoc();
                $checkStock->close();

                if ($row && $row['stock'] >= $quantity) {
                    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->bind_param("ii", $quantity, $product_id);
                    if (!$stmt->execute()) {
                        $success = false;
                    }
                    $stmt->close();
                } else {
                    $success = false;
                }
            }

            echo json_encode(["success" => $success, "message" => $success ? "Stock updated" : "Insufficient stock"]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid or missing product data"]);
        }
        exit;
    }

    echo json_encode(["success" => false, "error" => "Invalid action"]);
    exit;
}

// Invalid request method
http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid request"]);
