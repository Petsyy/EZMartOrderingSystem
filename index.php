<?php

declare(strict_types=1);
session_start();

header("Cache-Control: no-cache, must-revalidate");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
    header("Location: /EZMartOrderingSystem/auth/admin_login.php");
    exit();
}

$user_id = (int)$_SESSION['admin_id'];
$username = htmlspecialchars(trim($_SESSION['admin_username']), ENT_QUOTES, 'UTF-8');
$user_role = !empty($_SESSION['admin_role'])
    ? htmlspecialchars(ucfirst(strtolower(trim($_SESSION['admin_role']))), ENT_QUOTES, 'UTF-8')
    : 'Administrator';

$display_name = current(explode(' ', $username));

$page = $_GET['page'] ?? 'dashboard';

$validPages = [
    'dashboard' => 'index.php',
    'products' => 'modules/products/product.php',
    'orders' => 'modules/orders/order.php',
    'customer' => 'modules/customers/customer.php'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZ Mart</title>

    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/sidebar.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <img src="./assets/images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px; height: auto;">
            <span class="logo-text">Mart</span>
        </div>

        <!-- User Profile Section -->
        <div class="user-container" id="userContainer">
            <div class="user-profile-info">
                <div class="user-greeting">Hello, <span class="user-display-name"><?php echo $display_name; ?></span></div>
                <div class="user-role-badge"><?php echo $user_role; ?></div>
            </div>
        </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 id="dashBoard">Dashboard</h1>

        <!-- Counter Container -->
        <div class="counter-container">

            <div class="counter-box">
                <label>Total Revenue:</label>
                <img src="./assets/icons/revenue-icon.png" alt="Revenue Icon">
                <p id="totalRevenue">Loading...</p>
            </div>

            <div class="counter-box">
                <label>Total Products:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/product-icon-counter.png" alt="Product Icon">
                    <span id="totalProductCount">0</span>
                </div>
            </div>

            <!-- Order Counter -->
            <div class="counter-box">
                <label>Total Orders:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/order-icon.png" alt="Order Icon">
                    <span id="totalOrderCount">0</span>
                </div>
            </div>

            <!-- Total Customer Counter -->
            <div class="counter-box">
                <label>Total Customers:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/customer-icon-counter.png" alt="Customer Icon">
                    <span id="totalCustomerCount">0</span>
                </div>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
            <!-- Recent Orders Section -->
            <div class="dashboard-section recent-orders-section">
                <h2>Recent Orders</h2>
                <div class="recent-orders-container">
                    <table class="recent-orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-orders-body">
                            <!-- Orders will be loaded here via JavaScript -->
                            <tr>
                                <td colspan="5" style="text-align: center;">Loading orders...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charts Container -->
            <div class="charts-container">
                <!-- Sales Chart -->
                <div class="chart-wrapper">
                    <canvas id="myChart" class="chart"></canvas>
                </div>

                <!-- Customer Chart -->
                <div class="chart-wrapper">
                    <canvas id="customerChart" class="chart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <ul>
            <li>
                <a href="/EZMartOrderingSystem/index.php" title="Home">
                    <img src="/EZMartOrderingSystem/assets/icons/home-icon.png" alt="Home" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="/EZMartOrderingSystem/modules/products/product.php" title="Products">
                    <img src="/EZMartOrderingSystem/assets/icons/product.png" alt="Products" style="width: 24px; height: 24px;">
                </a>
            </li>
            <li>
                <a href="/EZMartOrderingSystem/modules/orders/order.php" title="Orders">
                    <img src="/EZMartOrderingSystem/assets/icons/order.png" alt="Orders" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="/EZMartOrderingSystem/modules/customers/customer.php" title="Customers">
                    <img src="/EZMartOrderingSystem/assets/icons/customer.png" alt="Customer" style="width: 29px; height: 29px;">
                </a>
            </li>
            <li>
                <a href="/EZMartOrderingSystem/modules/admins/admin-accounts.php" title="User Accounts">
                    <img src="/EZMartOrderingSystem/assets/icons/user-settings.png" alt="User-Settings" style="width: 30px; height: 30px;">
                </a>
            </li>
            <li>
                <a href="/EZMartOrderingSystem/login/login.php" title="Log out">
                    <img src="/EZMartOrderingSystem/assets/icons/logout.png" alt="Log out" style="width: 26px; height: 26px;">
                </a>
            </li>
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/chart.js"></script>
    <script src="./js/get_total_admin.js"></script>
    <script src="./js/get_total_customers.js"></script>
    <script src="./js/recent_order.js"></script>
    <script src="./js/index.js"></script>
    <script src="./js/user.js"></script>
    <script src="./js/notification.js"></script>
    <script src="./js/counter.js"></script>
    <script src="./js/total_revenue.js"></script>

</body>

</html>