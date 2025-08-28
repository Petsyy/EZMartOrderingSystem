<?php
require_once __DIR__ . '/../config/db.php';

header(header: 'Content-Type: application/json');
header(header: 'Access-Control-Allow-Origin: *');
header(header: 'Access-Control-Allow-Methods: POST');
header(header: 'Access-Control-Allow-Headers: Content-Type, Authorization');

$inputJSON = file_get_contents(filename: "php://input");
$input = json_decode(json: $inputJSON, associative: true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(response_code: 400);
    echo json_encode(value: ['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$firstName = trim(string: $input['first_name'] ?? '');
$lastName = trim(string: $input['last_name'] ?? '');
$email = trim(string: $input['email'] ?? '');
$password = trim(string: $input['password'] ?? '');
$confirmPassword = trim(string: $input['confirm_password'] ?? '');
$gender = trim(string: $input['gender'] ?? '');
$birthdate = trim(string: $input['birthdate'] ?? '');
$contact = trim(string: $input['contact'] ?? '');
$address = trim(string: $input['address'] ?? '');

// Validate input fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword) || empty($contact)) {
    http_response_code(response_code: 400);
    echo json_encode(value: ['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if ($password !== $confirmPassword) {
    http_response_code(response_code: 400);
    echo json_encode(value: ['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

try {
    $stmt = $conn->prepare(query: "SELECT id FROM users_mobile WHERE email = ?");
    $stmt->execute(params: [$email]);

    if ($stmt->rowCount() > 0) {
        http_response_code(response_code: 409);
        echo json_encode(value: ['success' => false, 'message' => 'Email already exists.']);
        exit;
    }

    $hashedPassword = password_hash(password: $password, algo: PASSWORD_BCRYPT);

    $stmt = $conn->prepare(query: "
        INSERT INTO users_mobile (first_name, last_name, email, password, contact, gender, birthdate, address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute(params: [$firstName, $lastName, $email, $hashedPassword, $contact, $gender, $birthdate, $address]);

    http_response_code(response_code: 201);
    echo json_encode(value: ['success' => true, 'message' => 'Registration successful!']);
} catch (PDOException $e) {
    error_log(message: "Database error: " . $e->getMessage());
    http_response_code(response_code: 500);
    echo json_encode(value: ['success' => false, 'message' => 'Registration failed. Please try again.']);
}
