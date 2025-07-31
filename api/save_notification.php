<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'unread';
    $created_at = date('Y-m-d H:i:s');

    if ($user_id > 0 && !empty($message)) {
        try {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $message, $status, $created_at]);

            echo json_encode(["success" => true, "message" => "Notification saved"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User ID and message are required"]);
    }
}

$conn = null;
