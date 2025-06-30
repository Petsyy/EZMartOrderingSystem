<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - EZ Mart</title>
    <link rel="stylesheet" href="../../assets/css/customer.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../js/customer.js"></script>
</head>

<body>
    <header class="header">
        <img src="../../assets/images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px;">
        <span class="logo-text">Mart</span>
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


    <!-- Customers Section -->
    <main>
        <h1>Customers</h1>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Total Orders</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="customerList">
                <!-- Customer data will be inserted here dynamically -->
            </tbody>
        </table>

        <!-- Previous Orders Modal -->
        <div id="previousOrdersModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2>Previous Orders</h2>
                <div id="previousOrdersContainer"></div>
            </div>
        </div>
    </main>
</body>

<style>
    /* Fixed Modal Styling - Centered */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
        overflow: hidden;
    }

    .modal-content {
        background-color: #fff;
        margin: 2% auto;
        padding: 25px;
        width: 90%;
        /* Slightly wider for better content fit */
        max-width: 1000px;
        /* Increased max-width */
        border-radius: 12px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        animation: slideDown 0.3s ease;
        left: 0;
        right: 0;
    }

    .modal-header {
        padding-right: 40px;
        /* Make space for close button */
        position: relative;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }

    .modal-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.4rem;
        padding-right: 10px;
        /* Prevent text overlap */
    }

    .close {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 28px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
        transition: color 0.2s;
        z-index: 1;
        /* Ensure it stays above other content */
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .close:hover {
        color: #333;
        background-color: #f5f5f5;
        border-radius: 50%;
    }

    /* Table container adjustments */
    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 auto;
        /* Center table */
    }

    /* Column width adjustments */
    .orders-table th:nth-child(1),
    .orders-table td:nth-child(1) {
        width: 10%;
    }

    .orders-table th:nth-child(2),
    .orders-table td:nth-child(2) {
        width: 20%;
    }

    .orders-table th:nth-child(3),
    .orders-table td:nth-child(3) {
        width: 15%;
    }

    .orders-table th:nth-child(4),
    .orders-table td:nth-child(4) {
        width: 15%;
    }

    .orders-table th:nth-child(5),
    .orders-table td:nth-child(5) {
        width: 40%;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 15px;
            margin: 2% auto;
        }

        .orders-table {
            font-size: 0.8rem;
        }

        .orders-table th,
        .orders-table td {
            padding: 8px 10px;
        }
    }
</style>

<script src="../../js/customer.js"></script>

</html>