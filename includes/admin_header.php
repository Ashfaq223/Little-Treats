<?php

// Shared admin header. A page can set $admin_subtitle before including it.

$lt_admin_subtitle = $admin_subtitle ?? "Administration Panel";

$lt_admin_base = e(base_url());

?>
<a class="skip-link" href="#main-content">Skip to main content</a>

<header class="admin-header">

    <div class="admin-header-content">

        <div class="admin-brand">

            <img
                src="<?php echo $lt_admin_base; ?>/assets/images/logo.png"
                alt="Little Treats Bakery"
                class="site-logo"
            >

            <div>

                <h1>Little Treats Bakery</h1>

                <p><?php echo e($lt_admin_subtitle); ?></p>

            </div>

        </div>


        <div class="admin-header-actions">

            <a href="dashboard.php" class="logout-button">
                Dashboard
            </a>

            <a href="logout.php" class="logout-button">
                Logout
            </a>

        </div>

    </div>

</header>