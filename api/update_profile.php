<?php
require_once "../config/db.php"; // Ensure db.php uses PDO

header("Content-Type: application/json");
$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';

    if (empty($email)) {
        $response["success"] = false;
        $response["message"] = "Email is required.";
        echo json_encode($response);
        exit();
    }

    $sql = "UPDATE users_mobile SET first_name=?, last_name=?, birthdate=?, contact=?, address=?, gender=? WHERE email=?";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$firstName, $lastName, $birthdate, $contact, $address, $gender, $email]);

        if ($stmt->rowCount() > 0) {
            $response["success"] = true;
            $response["message"] = "Profile updated successfully.";
        } else {
            $response["success"] = false;
            $response["message"] = "No changes were made.";
        }
    } catch (PDOException $e) {
        $response["success"] = false;
        $response["message"] = "Database error: " . $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
