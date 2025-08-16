<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$response = ["success" => false, "message" => ""];

try {
    // Get and validate JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        throw new Exception("Invalid request data");
    }

    // Validate required fields
    $required = ['email', 'otp', 'new_password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $email = trim($data['email']);
    $inputOtp = trim($data['otp']);
    $newPassword = $data['new_password'];

    // Fetch stored OTP data
    $stmt = $conn->prepare("SELECT reset_token, reset_expiry FROM users_mobile WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Account not found");
    }

    // Verify OTP exists
    if (empty($user['reset_token'])) {
        throw new Exception("No active OTP request found");
    }

    // Critical Fix: Proper OTP comparison
    if ($user['reset_token'] !== $inputOtp) {
        throw new Exception("Invalid OTP code");
    }

    // Check OTP expiration
    if (strtotime($user['reset_expiry']) < time()) {
        throw new Exception("OTP has expired");
    }

    // Validate password strength
    if (strlen($newPassword) < 8) {
        throw new Exception("Password must be at least 8 characters");
    }

    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password and clear OTP
    $updateStmt = $conn->prepare("UPDATE users_mobile SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
    $updateSuccess = $updateStmt->execute([$passwordHash, $email]);

    if (!$updateSuccess) {
        throw new Exception("Failed to update password");
    }

    $response['success'] = true;
    $response['message'] = "Password reset successfully";
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    // Log the error securely
    error_log("Password reset error: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
