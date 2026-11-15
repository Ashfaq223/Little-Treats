<?php

include("admin_auth.php");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin Dashboard - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- ================= MAIN CONTENT ================= -->

<main id="main-content" class="admin-container">


    <div class="admin-welcome">

        <h2>Welcome, Admin! 👋</h2>

        <p>
            Manage your bakery products, categories, orders and
            customer loyalty rewards from one place.
        </p>

    </div>



    <!-- ================= DASHBOARD CARDS ================= -->

    <div class="admin-grid">


        <!-- PRODUCTS -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                🧁
            </div>

            <h3>Product Management</h3>

            <p>
                Add, edit, delete and manage bakery products.
            </p>

            <a href="products.php" class="admin-button">
                Manage Products
            </a>

        </div>



        <!-- CATEGORIES -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                📂
            </div>

            <h3>Category Management</h3>

            <p>
                Add, edit and delete product categories.
            </p>

            <a href="categories.php" class="admin-button">
                Manage Categories
            </a>

        </div>



        <!-- ORDERS -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                📦
            </div>

            <h3>Orders</h3>

            <p>
                View customer orders and update their status.
            </p>

            <a href="orders.php" class="admin-button">
                Manage Orders
            </a>

        </div>



        <!-- LOYALTY -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                🎁
            </div>

            <h3>Loyalty Rewards</h3>

            <p>
                View customer reward redemptions and manage loyalty rewards.
            </p>

            <a href="loyalty_redemptions.php" class="admin-button">
                View Redemptions
            </a>

        </div>

    
            <!-- contact messages -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                👥
            </div>

            <h3>Contact Messages</h3>

            <p>
                View customer Messages.
            </p>

           <a href="contact_messages.php" class="admin-button">
                Contact Messages
            </a>

        </div>

         <!-- Analytics --> 

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">📊</div>

            <h3>Analytics &amp; Reports</h3>
            
            <p>View sales performance, order statistics, monthly sales trends, and business insights.</p>
            
            <a href="analytics.php" class="admin-button">View Analytics</a>
        
        </div>



        <!-- CUSTOMERS -->

        <div class="admin-card">

            <div class="admin-card-icon" aria-hidden="true">
                👥
            </div>

            <h3>Customers</h3>

            <p>
                Customer management and account information.
            </p>

            <a href="customers.php" class="admin-button">
                View Customers
            </a>

        </div>


        <?php if (admin_is_owner()) { ?>

            <!-- AUDIT LOG -->

            <div class="admin-card">

                <div class="admin-card-icon" aria-hidden="true">
                    🧾
                </div>

                <h3>Audit Log</h3>

                <p>
                    See what each admin changed and when.
                </p>

                <a href="audit_log.php" class="admin-button">
                    View Audit Log
                </a>

            </div>


            <!-- ADMIN ACCOUNTS -->

            <div class="admin-card">

                <div class="admin-card-icon" aria-hidden="true">
                    🔑
                </div>

                <h3>Admin Accounts</h3>

                <p>
                    Add staff and owners.
                </p>

                <a href="staff.php" class="admin-button">
                    Manage Admins
                </a>

            </div>

        <?php } ?>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<footer class="admin-footer">

    <p>
        © 2026 Little Treats Bakery | Administration Panel
    </p>

</footer>


</body>

</html>