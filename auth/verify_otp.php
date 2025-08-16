<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email']) || !isset($data['otp'])) {
    echo json_encode(["success" => false, "error" => "Email and OTP are required"]);
    exit;
}

$email = $data['email'];
$otp = $data['otp'];
$conn = new mysqli($servername, $username, $password, $dbname);

// Verify OTP exists and isn't expired
$query = $conn->prepare("SELECT id FROM users_mobile 
                        WHERE email = ? 
                        AND reset_token = ? 
                        AND reset_expiry > NOW()");
$query->bind_param("ss", $email, $otp);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 1) {
    echo json_encode(["success" => true, "message" => "OTP verified"]);
} else {
    echo json_encode(["success" => false, "error" => "Invalid or expired OTP"]);
}
