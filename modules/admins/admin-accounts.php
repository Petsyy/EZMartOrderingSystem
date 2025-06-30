<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts - EZ Mart</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/user.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <header class="header">
        <div class="logo-container">
            <img src="../../assets/images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px; height: auto;">
            <span class="logo-text">Mart</span>
        </div>
    </header>

    <main class="container">
        <div class="user-accounts-header">
            <h1 id="admin">Admin Section</h1>
        </div>

        <div class="user-accounts-container">

            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Users</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
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


    <script src="../../js/admin.js"></script>
</body>

</html>