<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Read JSON input
$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Validate input fields
$email = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

// Check if email format is valid
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

try {

    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users_mobile WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // Verify the password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        exit;
    }

    // Store user data in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];

    // Return success response without exposing password
    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'user' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
}
