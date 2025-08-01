<?php
require_once __DIR__ . '/../config/db.php';

$order_id = $_GET['order_id'] ?? '';

if (!is_numeric($order_id)) {
    die("Invalid order ID");
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found");
}
?>

<h2>Order Details</h2>
<p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
<p><strong>Order Details:</strong> <?php echo nl2br(htmlspecialchars($order['order_details'])); ?></p>
<p><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
<p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
<p><strong>Created At:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
<a href="customers.php">Back to Customers</a>