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
                    <a href="../index" title="Home">
                        <img src="../../assets/icons/home-icon.png" alt="Home" id="sidebar-icon" style="width: 27px; height: 27px;">
                    </a>
                </li>
                <li>
                    <a href="../modules/products/product" title="Products">
                        <img src="../../assets/icons/product.png" alt="Products" id="sidebar-icon" style="width: 24px; height: 24px;">
                    </a>
                </li>
                <li>
                    <a href="../modules/orders/order" title="Orders">
                        <img src="../../assets/icons/order.png" alt="Orders" id="sidebar-icon" style="width: 27px; height: 27px;">
                    </a>
                </li>
                <li>
                    <a href="../modules/customers/customer" title="Customers">
                        <img src="../../assets/icons/customer.png" alt="Customer" id="sidebar-icon" style="width: 29px; height: 29px;">
                    </a>
                </li>
                <li>
                    <a href="../modules/admins/admin-accounts" title="User Accounts">
                        <img src="../../assets/icons/user-settings.png" alt="User-Settings" id="sidebar-icon" style="width: 30px; height: 30px;">
                    </a>
                </li>
                <li>
                    <a href="../login/login.php" title="Log out">
                        <img src="../../assets/icons/logout.png" alt="Log out" id="sidebar-icon" style="width: 26px; height: 26px;">
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