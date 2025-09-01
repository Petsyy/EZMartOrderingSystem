<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - EZ Mart</title>
    
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/order.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="../../assets/images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px; height: auto;">
            <span class="logo-text">Mart</span>
        </div>
    </header>
    
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

<main class="container">
    <h1>Order Section</h1>
    <div class="table-container">
        <table class="order-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Order Details</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <!-- Orders will be dynamically inserted here -->
            </tbody>
        </table>
    </div>
</main>

    <!-- Order View Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Order Details</h2>
            <p id="orderDetails"></p>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="../../js/order.js"></script>
</html>