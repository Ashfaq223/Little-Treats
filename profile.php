<?php

require_once __DIR__ . "/includes/auth.php";


$user_id = (int) $_SESSION["user_id"];

/* Get customer information */
$result = db_select(
    $conn,
    "SELECT name, email, phone, address
     FROM users
     WHERE user_id = ?",
    "i",
    [$user_id]
);

$user = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

    <title>My Profile - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= PROFILE HERO ================= -->

<section class="profile-hero">

    <p class="section-label">
        LITTLE TREATS BAKERY
    </p>

    <h2>
        My Profile
    </h2>

    <p>
        Manage your account information and view your details.
    </p>

</section>



<!-- ================= PROFILE ================= -->

<main id="main-content" class="profile-container">


    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-card-header">

            <div class="profile-avatar">

                <?php

                echo e(strtoupper(
                    substr($user["name"], 0, 1)
                ));

                ?>

            </div>


            <div>

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $user["name"]
                    );

                    ?>

                </h2>

                <p>
                    Customer Account
                </p>

            </div>

        </div>



        <!-- ACCOUNT INFORMATION -->

        <div class="profile-section">

            <h3>
                Account Information
            </h3>


            <div class="profile-info-grid">


                <div class="profile-info">

                    <span>
                        User ID
                    </span>

                    <strong>
                        #<?php echo $user_id; ?>
                    </strong>

                </div>


                <div class="profile-info">

                    <span>
                        Full Name
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user["name"]
                        );
                        ?>
                    </strong>

                </div>


                <div class="profile-info">

                    <span>
                        Email Address
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user["email"]
                        );
                        ?>
                    </strong>

                </div>


                <div class="profile-info">

                    <span>
                        Phone Number
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user["phone"]
                        );
                        ?>
                    </strong>

                </div>


                <div class="profile-info profile-address">

                    <span>
                        Delivery Address
                    </span>

                    <strong>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $user["address"]
                            )
                        );
                        ?>
                    </strong>

                </div>


            </div>

        </div>



        <!-- QUICK LINKS -->

        <div class="profile-section">

            <h3>
                My Little Treats
            </h3>


            <div class="profile-actions">


                <a
                    href="edit_profile.php"
                    class="profile-action"
                >

                    <span class="action-icon" aria-hidden="true">
                        ✏️
                    </span>

                    <div>

                        <strong>
                            Edit Profile
                        </strong>

                        <small>
                            Update your details or change your password
                        </small>

                    </div>

                </a>



                <a
                    href="my_orders.php"
                    class="profile-action"
                >

                    <span class="action-icon" aria-hidden="true">
                        📦
                    </span>

                    <div>

                        <strong>
                            My Orders
                        </strong>

                        <small>
                            View your previous orders
                        </small>

                    </div>

                </a>



                <a
                    href="loyalty.php"
                    class="profile-action"
                >

                    <span class="action-icon" aria-hidden="true">
                        ⭐
                    </span>

                    <div>

                        <strong>
                            Loyalty Points
                        </strong>

                        <small>
                            View your rewards and points
                        </small>

                    </div>

                </a>



                <a
                    href="cart.php"
                    class="profile-action"
                >

                    <span class="action-icon" aria-hidden="true">
                        🛒
                    </span>

                    <div>

                        <strong>
                            Shopping Cart
                        </strong>

                        <small>
                            View items in your cart
                        </small>

                    </div>

                </a>


            </div>

        </div>



        <!-- LOGOUT -->

        <div class="profile-footer">

            <a
                href="logout.php"
                class="logout-button"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                Logout
            </a>

        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>