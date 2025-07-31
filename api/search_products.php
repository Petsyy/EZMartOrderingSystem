<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$server_url = "http://192.168.100.15/EZMartOrderingSystem/";

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

// Search products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';

    if (empty($query)) {
        echo json_encode(["success" => false, "error" => "Search query is required"]);
        exit;
    }

    // Search products where name contains the query
    $stmt = $conn->prepare("SELECT id, name, price, stock, image, category FROM products WHERE name LIKE ?");
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Append full image URL
        $row['image'] = !empty($row['image']) ? $server_url . $row['image'] : null;
        $products[] = $row;
    }
    $stmt->close();
    $conn->close();

    echo json_encode(["success" => true, "products" => $products]);
    exit;
}

// Invalid request method
http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid request method"]);
