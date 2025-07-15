<?php
require_once __DIR__ . '../config/db.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

header("Content-Type: application/json");

$response = ["success" => false, "admin" => null, "error" => ""];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response["error"] = "Invalid admin ID";
    echo json_encode($response);
    exit();
}

$adminId = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT id, username, email, role, status, created_at FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $response["admin"] = $admin;
        $response["success"] = true;
    } else {
        $response["error"] = "Admin not found";
    }
} catch (Exception $e) {
    $response["error"] = "Error fetching admin data: " . $e->getMessage();
}

echo json_encode($response);
