<?php
require_once __DIR__ . '/../config/db_config.php';
header("Content-Type: application/json");

$server_url = "$server_url";
$paymongoApiUrl = "https://api.paymongo.com/v1/checkout_sessions";
$paymongoApiKey = "sk_test_EbvCZMXi6ARpJuYgK4Z8ZgNh";

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

error_log("🔹 Raw Payload: " . $payload);
if (!$data) {
    error_log(" JSON decoding failed: " . json_last_error_msg());
    echo json_encode(['success' => false, 'message' => 'Invalid JSON format']);
    exit;
}

$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$price = isset($data['price']) ? floatval($data['price']) : 0;
$bookingId = filter_var($data['bookingId'] ?? '', FILTER_SANITIZE_STRING);
$propertyName = filter_var($data['propertyName'] ?? '', FILTER_SANITIZE_STRING);
$propertyAddress = filter_var($data['propertyAddress'] ?? '', FILTER_SANITIZE_STRING);
$paymentType = filter_var($data['paymentType'] ?? '', FILTER_SANITIZE_STRING);

error_log("📌 Extracted Data - Email: $email, Price: $price, Booking ID: $bookingId, Property Name: $propertyName, Address: $propertyAddress, Payment Type: $paymentType");

if (empty($email) || empty($bookingId) || empty($paymentType) || $price <= 0) {
    error_log("❌ Validation failed: Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("❌ Invalid email format: $email");
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if ($paymentType === "partial") {
    $amountToPay = $price * 0.30;
    $status = "Partially Paid";
} else {
    $amountToPay = $price;
    $status = "Fully Paid";
}

$amountToPayCents = intval($amountToPay * 100);

$requestData = [
    "data" => [
        "attributes" => [
            "line_items" => [
                [
                    "currency" => "PHP",
                    "amount" => $amountToPayCents,
                    "name" => "HouseSmart Property",
                    "quantity" => 1
                ]
            ],
            "payment_method_types" => ["gcash", "card"],
            "send_email_receipt" => true,
            "show_description" => true,
            "description" => "$propertyName in $propertyAddress",
            "success_url" => $server_url . "api/payment_success.php?bookingId=$bookingId&paymentType=$paymentType",
            "cancel_url" => $server_url . "api/payment_failed.php",
            "metadata" => [
                "email" => $email,
                "bookingId" => $bookingId,
                "propertyName" => $propertyName,
                "propertyAddress" => $propertyAddress,
                "paymentType" => $paymentType
            ]
        ]
    ]
];

error_log("🔹 Sending request to PayMongo: " . json_encode($requestData, JSON_PRETTY_PRINT));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paymongoApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($paymongoApiKey . ':'),
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $curlError = curl_error($ch);
    error_log("❌ cURL Error: " . $curlError);
    echo json_encode(['success' => false, 'message' => 'Error connecting to PayMongo API: ' . $curlError]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$responseData = json_decode($response, true);
error_log("🔹 PayMongo API Response: " . print_r($responseData, true));

if (isset($responseData['data']['attributes']['checkout_url'])) {
    $checkoutUrl = $responseData['data']['attributes']['checkout_url'];

    // Update booking status in MySQL
    if ($conn->connect_error) {
        error_log("❌ Database connection failed: " . $conn->connect_error);
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET payment_status=? WHERE id=?");
        $stmt->bind_param("si", $status, $bookingId);
        if ($stmt->execute()) {
            error_log("✅ Booking status updated to $status for Booking ID: $bookingId");
        } else {
            error_log("❌ Failed to update booking status: " . $stmt->error);
        }
        $stmt->close();
        $conn->close();
    }

    echo json_encode([
        'success' => true,
        'checkoutUrl' => $checkoutUrl,
        'bookingId' => $bookingId,
        'propertyName' => $propertyName,
        'propertyAddress' => $propertyAddress,
        'paymentType' => $paymentType
    ]);
} else {
    $errorMessage = $responseData['errors'][0]['detail'] ?? 'Unknown error';
    error_log("❌ PayMongo API Error: " . $errorMessage);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to create checkout session. Error: ' . $errorMessage
    ]);
}
