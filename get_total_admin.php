<?php
// Include database connection
require_once __DIR__ . '../config/db.php';

try {
    // Fix table name from 'users' to 'admins'
    $sql = "SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(["total" => $result['total']]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
