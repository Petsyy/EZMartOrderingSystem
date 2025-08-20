<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email'])) {
    echo json_encode(["success" => false, "error" => "Email is required"]);
    exit;
}

$email = $data['email'];
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if email exists
$query = $conn->prepare("SELECT id FROM users_mobile WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Email not found"]);
    exit;
}

// Generate 6-digit OTP
$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Save OTP in database
$stmt = $conn->prepare("UPDATE users_mobile SET reset_token=?, reset_expiry=? WHERE email=?");
$stmt->bind_param("sss", $otp, $expiry, $email);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Database error"]);
    exit;
}

// Send OTP email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ezmarket1604@gmail.com';
    $mail->Password = 'lviw pfoy yqup zbso';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('ezmarket1604@gmail.com', 'EZ Mart');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Your EZ Mart Password Reset OTP";

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2563eb;'>EZ Mart Password Reset</h2>
            <p>Your One-Time Password (OTP) is:</p>
            
            <div style='background: #f3f4f6; padding: 15px; text-align: center; 
                        margin: 20px 0; border-radius: 5px;'>
                <h1 style='margin: 0; color: #2563eb; letter-spacing: 3px;'>$otp</h1>
            </div>
            
            <p>This OTP is valid for 15 minutes. If you didn't request this, please ignore this email.</p>
        </div>
    ";

    $mail->AltBody = "EZ Mart Password Reset\n\nYour OTP is: $otp\n\nValid for 15 minutes.";

    $mail->send();
    echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
} catch (Exception $e) {
    error_log("Email sending failed: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Failed to send OTP"]);
}
