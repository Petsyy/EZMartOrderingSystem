<?php
require_once "../config/db.php"; // Ensure the database connection is included

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the action is set
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "fetch":
        fetchOrders($conn);
        break;

    case "confirm":
        confirmOrder($conn);
        break;

    case "delete":
        deleteOrder($conn);
        break;

    case "getOrderStats":
        getOrderStats($conn);
        break;

    case "customerOrderStats":
        customerOrderStats($conn);
        break;

    case "getTotalOrders":
        getTotalOrders($conn);
        break;

    case "getTotalProducts":
        getTotalProducts($conn);
        break;

    case "place_order":
        placeOrder($conn);
        break;

    case "fetchSingleOrder":
        fetchSingleOrder($conn);
        break;

    case "updateOrder":
        updateOrder($conn);
        break;

    case "getSalesByCategory":
        getSalesByCategory($conn);
        break;

    case "getRecentOrders":
        getRecentOrders($conn);
        break;

    case "getTotalCustomers":
        getTotalCustomers($conn);
        break;

    case "fetchPreviousOrders":
        fetchPreviousOrders($conn);
        break;

    case "readyToPickup":
        readyToPickup($conn);
        break;

    default:
        echo json_encode(["success" => false, "error" => "Invalid action"]);
        break;
}

function sendPushNotification($fcmToken, $title, $body, $conn = null, $order_id = null)
{
    try {
        // Verify the token path
        $tokenPath = __DIR__ . '/../get_fcm_token.php';
        if (!file_exists($tokenPath)) {
            throw new Exception("FCM token handler not found at: $tokenPath");
        }

        require_once $tokenPath;
        $accessToken = getOAuthToken();

        if (strpos($accessToken, "Error") !== false) {
            throw new Exception($accessToken);
        }

        $fcmUrl = "https://fcm.googleapis.com/v1/projects/ezmart-f178a/messages:send";

        $payload = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "android" => [
                    "priority" => "high"
                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10"
                    ]
                ]
            ]
        ];

        $headers = [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fcmUrl,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_VERBOSE => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode($response, true);

        // Handle invalid token
        if ($httpCode === 404 && isset($responseData['error']['details'][0]['errorCode'])) {
            if ($responseData['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                if ($conn && $order_id) {
                    $conn->prepare("UPDATE orders SET fcm_token = NULL WHERE id = ?")->execute([$order_id]);
                }
                return [
                    "success" => false,
                    "error" => "UNREGISTERED",
                    "message" => "FCM token is no longer valid"
                ];
            }
        }

        if ($httpCode !== 200) {
            throw new Exception("FCM API Error: " . ($responseData['error']['message'] ?? 'Unknown error'));
        }

        return [
            "success" => true,
            "http_code" => $httpCode,
            "response" => $responseData
        ];
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
        return [
            "success" => false,
            "error" => $e->getMessage()
        ];
    }
}

// Fetch all orders
function fetchOrders($conn)
{
    try {
        // Clear any accidental output
        if (ob_get_length()) ob_clean();

        $stmt = $conn->prepare("SELECT o.id AS order_id, o.customer_name, o.order_details, 
                               o.total_price AS order_total_price, o.status, 
                               o.created_at AS order_created_at 
                               FROM orders o 
                               WHERE o.status IN ('Pending', 'Paid', 'Ready to Pick Up') 
                               ORDER BY 
                                 CASE o.status 
                                   WHEN 'Pending' THEN 1 
                                   WHEN 'Paid' THEN 2 
                                   WHEN 'Ready to Pick Up' THEN 3 
                                   ELSE 4 
                                 END,
                               o.created_at DESC");

        if (!$stmt->execute()) {
            throw new Exception("Database query failed");
        }

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Clear buffer before output
        ob_clean();
        echo json_encode($results ?: ["error" => "No orders found"]);
    } catch (Exception $e) {
        // Log the error instead of displaying it
        error_log("fetchOrders error: " . $e->getMessage());

        // Return clean JSON error
        ob_clean();
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch orders"]);
    }
}

// Confirm an order
function confirmOrder($conn)
{
    header('Content-Type: application/json');

    try {
        $id = $_POST['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            throw new Exception("Invalid order ID");
        }

        // Get order details with FCM token
        $stmt = $conn->prepare("SELECT customer_name, total_price, fcm_token FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Order not found");
        }

        // Update status to "To Pay"
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'To Pay' WHERE id = ?");
        $updateStmt->execute([$id]);

        $notificationResult = null;
        $tokenInvalid = false;

        if (!empty($order['fcm_token'])) {
            // Send confirmation notification
            $notificationResult = sendPushNotification(
                $order['fcm_token'],
                "Order Confirmed",
                "Your order " . $order['customer_name'] . " has been confirmed. Total: ₱" . number_format($order['total_price'], 2),
                $conn,
                $id
            );

            // Handle invalid token
            if (isset($notificationResult['error']) && $notificationResult['error'] === 'UNREGISTERED') {
                $tokenInvalid = true;
                $conn->prepare("UPDATE orders SET fcm_token = NULL WHERE id = ?")->execute([$id]);
            }
        }

        echo json_encode([
            'success' => true,
            'status_updated' => true,
            'notification_sent' => ($notificationResult['success'] ?? false),
            'token_invalid' => $tokenInvalid
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


// Delete an order
function deleteOrder($conn)
{
    $id = $_POST['id'] ?? '';
    if (!is_numeric($id)) {
        echo json_encode(["success" => false, "error" => "Invalid or missing order ID"]);
        return;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        echo $stmt->execute([$id]) ? json_encode(["success" => true]) : json_encode(["success" => false, "error" => "Failed to delete order"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Get order statistics (status-wise)
function getOrderStats($conn)
{
    try {
        $stmt = $conn->prepare("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
// Fetch all products
function fetchProducts($conn)
{
    try {
        // Prepare the query to fetch product details
        $stmt = $conn->prepare("SELECT id, product_name, product_description, price, stock FROM products ORDER BY product_name ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If there are products, return them as JSON
        echo count($results) > 0 ? json_encode($results) : json_encode(["success" => false, "error" => "No products found"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}


// Get customer-wise order statistics
function customerOrderStats($conn)
{
    try {
        $stmt = $conn->prepare("SELECT customer_name, COUNT(*) AS total_orders FROM orders GROUP BY customer_name ORDER BY total_orders DESC");
        $stmt->execute();

        $data = ["labels" => [], "values" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data['labels'][] = $row['customer_name'];
            $data['values'][] = $row['total_orders'];
        }

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Get total orders count
function getTotalOrders($conn)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Get total products count
function getTotalProducts($conn)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_products FROM products");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Place a new order
function placeOrder($conn)
{
    $customer_name = $_POST['customer_name'] ?? '';
    $order_details = $_POST['order_details'] ?? '';
    $total_price = $_POST['total_price'] ?? 0;

    if (empty($customer_name) || empty($order_details) || !is_numeric($total_price)) {
        echo json_encode(["success" => false, "error" => "Invalid input data"]);
        return;
    }

    try {
        // Start transaction
        $conn->beginTransaction();

        // Insert the order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, order_details, total_price, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$customer_name, $order_details, $total_price]);
        $order_id = $conn->lastInsertId();

        // Insert in notifications table for the new order
        $message = "New order #$order_id received from $customer_name (Total: $" . number_format($total_price, 2) . ")";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (message, status, created_at) VALUES (?, 'unread', NOW())");
        $notif_stmt->execute([$message]);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            "success" => true,
            "order_id" => $order_id,
            "message" => "Order placed successfully"
        ]);
    } catch (PDOException $e) {
        // Rollback on error
        $conn->rollBack();
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage(),
            "details" => "Failed to place order and create notification"
        ]);
    }
}

// Fetch a single order's details
function fetchSingleOrder($conn)
{
    $id = $_GET['id'] ?? '';
    if (!is_numeric($id)) {
        echo json_encode(["success" => false, "error" => "Invalid or missing order ID"]);
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT o.id AS order_id, o.customer_name, o.order_details, o.total_price AS order_total_price, o.status, o.created_at AS order_created_at FROM orders o WHERE o.id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the order exists
        if ($order) {
            $order['status'] = $order['status'] ?? 'Unknown';  // Default to 'Unknown' if status is missing
            echo json_encode($order);
        } else {
            echo json_encode(["success" => false, "error" => "Order not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Update an existing order
function updateOrder($conn)
{
    $id = $_POST['id'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $order_details = $_POST['order_details'] ?? '';
    $total_price = $_POST['total_price'] ?? 0;
    $status = $_POST['status'] ?? 'Pending';

    if (!is_numeric($id) || empty($customer_name) || empty($order_details) || !is_numeric($total_price)) {
        echo json_encode(["success" => false, "error" => "Invalid input data"]);
        return;
    }

    try {
        $stmt = $conn->prepare("UPDATE orders SET customer_name = ?, order_details = ?, total_price = ?, status = ? WHERE id = ?");
        echo $stmt->execute([$customer_name, $order_details, $total_price, $status, $id]) ? json_encode(["success" => true]) : json_encode(["success" => false, "error" => "Failed to update order"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// Add this new function to your orderapi.php
function getSalesByCategory($conn)
{
    try {
        $query = "SELECT p.category, SUM(oi.quantity) as total_sales 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  GROUP BY p.category";

        $stmt = $conn->prepare($query);
        $stmt->execute();

        $categories = [];
        $sales = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row['category'];
            $sales[] = (int)$row['total_sales'];
        }

        echo json_encode([
            'success' => true,
            'categories' => $categories,
            'sales' => $sales
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch sales by category: ' . $e->getMessage()
        ]);
    }
}

function getRecentOrders($conn)
{
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $limit = max(1, min($limit, 20)); // Ensure limit is between 1-20

        $stmt = $conn->prepare("SELECT id, customer_name, total_price, status, created_at 
                              FROM orders 
                              ORDER BY created_at DESC 
                              LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'orders' => $orders
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch recent orders: ' . $e->getMessage()
        ]);
    }
}

// Get total customers count
function getTotalCustomers($conn)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_customers FROM customers");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "total_customers" => $row['total_customers']]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

function readyToPickup($conn)
{
    header('Content-Type: application/json');

    try {
        $id = $_POST['id'] ?? null;

        // Validate input
        if (!$id || !is_numeric($id)) {
            throw new Exception("Invalid order ID");
        }

        // Get order with token
        $stmt = $conn->prepare("SELECT fcm_token FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Order not found");
        }

        // Update status
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'Ready to Pick Up' WHERE id = ?");
        $updateStmt->execute([$id]);

        $notificationResult = null;
        $tokenInvalid = false;

        if (!empty($order['fcm_token'])) {
            $notificationResult = sendPushNotification(
                $order['fcm_token'],
                "Order Ready",
                "Your order is ready for pickup!",
                $conn,
                $id
            );

            // Check if token is invalid
            if (isset($notificationResult['error']) && $notificationResult['error'] === 'UNREGISTERED') {
                $tokenInvalid = true;
                // Remove invalid token
                $conn->prepare("UPDATE orders SET fcm_token = NULL WHERE id = ?")->execute([$id]);
            }
        }

        echo json_encode([
            'success' => true,
            'status_updated' => true,
            'notification_sent' => ($notificationResult['success'] ?? false),
            'token_invalid' => $tokenInvalid
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

// Fetch previous orders for a customer
function fetchPreviousOrders($conn)
{
    $customer_name = $_GET['customer_name'] ?? null;

    if (!$customer_name) {
        echo json_encode(["success" => false, "error" => "Customer name is required"]);
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE customer_name = ? ORDER BY created_at DESC");
        $stmt->execute([urldecode($customer_name)]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "orders" => $orders,
            "customer_name" => $customer_name
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
    }
}
