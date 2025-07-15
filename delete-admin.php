<?php
require_once __DIR__ . '../config/db.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

header("Content-Type: application/json");

$response = ["success" => false, "error" => ""];

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response["error"] = "Invalid admin ID";
    echo json_encode($response);
    exit();
}

$adminId = $_POST['id'];

// Prevent deleting the current admin
if ($adminId == $_SESSION['admin_id']) {
    $response["error"] = "You cannot delete your own account while logged in";
    echo json_encode($response);
    exit();
}

try {
    // First, check if the admin exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);

    if (!$stmt->fetch()) {
        $response["error"] = "Admin not found";
        echo json_encode($response);
        exit();
    }

    // Delete the admin
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);

    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Error deleting admin: " . $e->getMessage();
}

echo json_encode($response);
