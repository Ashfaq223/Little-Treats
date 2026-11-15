<!-- ================= FOOTER ================= -->

<footer class="site-footer">

    <div class="footer-content">

        <div>

            <h3>
                Little Treats Bakery
            </h3>

            <p>
                Freshly baked happiness for every occasion.
            </p>

        </div>


        <div>

            <h4>
                Quick Links
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="contact.php">
                Contact
            </a>

            <a href="privacy.php">
                Privacy Policy
            </a>

            <a href="terms.php">
                Terms &amp; Conditions
            </a>

        </div>


        <div>

            <h4>
                Customer
            </h4>

            <?php if (isset($_SESSION["user_id"])) { ?>

                <a href="profile.php">
                    My Profile
                </a>

                <a href="my_orders.php">
                    My Orders
                </a>

                <a href="loyalty.php">
                    Loyalty Points
                </a>

                <a href="logout.php">
                    Logout
                </a>

            <?php } else { ?>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Register
                </a>

            <?php } ?>

        </div>

    </div>


    <div class="footer-bottom">

        <p>
            &copy; <?php echo date("Y"); ?> Little Treats Bakery. All rights reserved.
        </p>

    </div>

</footer>