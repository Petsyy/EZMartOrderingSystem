<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    // Fetch all users from the users_mobile table
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, contact, gender, birthdate, address FROM users_mobile ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$users) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No users found.']);
        exit;
    }

    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
}
