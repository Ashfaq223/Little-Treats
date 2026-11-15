<?php

/*
 * Shared customer header: skip link, brand, navigation (with cart count
 * and mobile menu button) and the one-time toast message.
 */

$lt_page = basename($_SERVER["SCRIPT_NAME"] ?? "");

$lt_sections = [
    "home"     => ["index.php"],
    "products" => ["products.php", "product_details.php"],
    "profile"  => ["profile.php", "edit_profile.php"],
    "orders"   => ["my_orders.php", "order_details.php", "order_confirmation.php"],
    "loyalty"  => ["loyalty.php", "redeem_reward.php"],
    "cart"     => ["cart.php", "checkout.php"],
    "contact"  => ["contact.php"],
    "login"    => ["login.php", "forgot_password.php", "reset_password.php", "register.php"]
];

$lt_active = "";

foreach ($lt_sections as $lt_key => $lt_pages) {

    if (in_array($lt_page, $lt_pages, true)) {
        $lt_active = $lt_key;
    }

}

function lt_nav_class(string $key, string $active, string $extra = ""): string
{
    $classes = trim($extra . ($key === $active ? " active" : ""));

    return $classes === "" ? "" : ' class="' . $classes . '"';
}

$lt_cart_count = cart_count();

$lt_flash = flash_pull();

?>
<a class="skip-link" href="#main-content">Skip to main content</a>

<?php if ($lt_flash) { ?>

    <div class="toast-region" role="status" aria-live="polite">

        <div class="toast toast-<?php echo e($lt_flash["type"]); ?>">

            <span><?php echo e($lt_flash["text"]); ?></span>

            <?php if (!empty($lt_flash["link"])) { ?>
                <a href="<?php echo e($lt_flash["link"]); ?>"><?php echo e($lt_flash["link_text"]); ?></a>
            <?php } ?>

            <button type="button" class="toast-close" aria-label="Dismiss message">&times;</button>

        </div>

    </div>

<?php } ?>

<!-- ================= HEADER ================= -->

<header class="site-header">

    <div class="header-container">

        <!-- LOGO / BRAND -->

        <a href="index.php" class="brand">

            <img
                src="assets/images/logo.png"
                alt="Little Treats Bakery Logo"
                class="site-logo"
            >

            <div class="brand-text">

                <h1>Little Treats Bakery</h1>

                <span>Freshly baked with love</span>

            </div>

        </a>


        <button
            type="button"
            class="nav-toggle"
            aria-controls="main-nav"
            aria-expanded="false"
            aria-label="Open menu"
        >
            <span></span><span></span><span></span>
        </button>


        <!-- NAVIGATION -->

        <nav class="main-nav" id="main-nav" aria-label="Main navigation">

            <a href="index.php"<?php echo lt_nav_class("home", $lt_active); ?>>
                Home
            </a>

            <a href="products.php"<?php echo lt_nav_class("products", $lt_active); ?>>
                Products
            </a>

            <?php if (isset($_SESSION["user_id"])) { ?>

                <a href="profile.php"<?php echo lt_nav_class("profile", $lt_active); ?>>
                    My Profile
                </a>

                <a href="my_orders.php"<?php echo lt_nav_class("orders", $lt_active); ?>>
                    My Orders
                </a>

                <a href="loyalty.php"<?php echo lt_nav_class("loyalty", $lt_active); ?>>
                    Loyalty Points
                </a>

            <?php } else { ?>

                <a href="login.php"<?php echo lt_nav_class("login", $lt_active, "nav-login"); ?>>
                    Login
                </a>

            <?php } ?>

            <a href="cart.php"<?php echo lt_nav_class("cart", $lt_active); ?>>
                Cart
                <?php if ($lt_cart_count > 0) { ?>
                    <span class="cart-badge" aria-label="<?php echo $lt_cart_count; ?> items in cart">
                        <?php echo $lt_cart_count; ?>
                    </span>
                <?php } ?>
            </a>

            <a href="contact.php"<?php echo lt_nav_class("contact", $lt_active); ?>>
                Contact
            </a>

        </nav>

    </div>

</header>