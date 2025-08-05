<?php
require_once __DIR__ . '/../config/db.php';

header("Content-Type: application/json");

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

$response = ["success" => false, "admins" => [], "error" => ""];

try {
    $stmt = $conn->prepare("SELECT id, username, email, role, status FROM admins ORDER BY id DESC");
    $stmt->execute();

    $response["admins"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Error loading admin data: " . $e->getMessage();
}
echo json_encode($response);
