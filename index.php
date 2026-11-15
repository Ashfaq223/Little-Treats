
<?php

require_once __DIR__ . "/includes/bootstrap.php";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= HERO SECTION ================= -->

<section class="hero-section" id="main-content">

    <div class="hero-content">

        <p class="hero-small-title">
            WELCOME TO LITTLE TREATS
        </p>

        <h2>
            Freshly Baked
            <br>
            Happiness
        </h2>

        <p class="hero-description">

            Delicious cakes, brownies and sweet treats
            made with care and delivered straight to you.

        </p>

        <div class="hero-buttons">

            <a href="products.php" class="primary-button">
                Explore Our Treats
            </a>

            <?php if (!isset($_SESSION["user_id"])) { ?>

                <a href="register.php" class="secondary-button">
                    Create Account
                </a>

            <?php } ?>

        </div>

    </div>

</section>



<!-- ================= FEATURE SECTION ================= -->

<section class="features-section">

    <div class="section-heading">

        <p class="section-label">
            WHY CHOOSE US
        </p>

        <h2>
            Made for Your Sweet Moments
        </h2>

        <p>
            Everything we make is prepared with care
            to make your special moments even sweeter.
        </p>

    </div>


    <div class="features-grid">

        <div class="feature-card">

            <div class="feature-icon" aria-hidden="true">
                🧁
            </div>

            <h3>
                Freshly Baked
            </h3>

            <p>
                Our bakery treats are prepared fresh
                with quality ingredients.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon" aria-hidden="true">
                ❤️
            </div>

            <h3>
                Made With Love
            </h3>

            <p>
                Every treat is prepared with care
                and attention to detail.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon" aria-hidden="true">
                🚚
            </div>

            <h3>
                Easy Ordering
            </h3>

            <p>
                Browse our products, add them to your
                cart and place your order easily.
            </p>

        </div>

    </div>

</section>



<!-- ================= LOYALTY SECTION ================= -->

<section class="loyalty-banner">

    <div>

        <p class="section-label">
            LITTLE TREATS LOYALTY
        </p>

        <h2>
            Every Sweet Purchase
            <br>
            Comes With Rewards
        </h2>

        <p>
            Earn 1 loyalty point for every Rs. 1000 spent
            and enjoy special rewards.
        </p>

        <?php if (isset($_SESSION["user_id"])) { ?>

            <a href="loyalty.php" class="primary-button">
                View My Loyalty Points
            </a>

        <?php } else { ?>

            <a href="register.php" class="primary-button">
                Join Little Treats
            </a>

        <?php } ?>

    </div>

</section>



<!-- ================= CALL TO ACTION ================= -->

<section class="cta-section">

    <p class="section-label">
        READY FOR A TREAT?
    </p>

    <h2>
        Find Your New Favourite Dessert
    </h2>

    <p>
        Explore our delicious collection of bakery treats.
    </p>

    <a href="products.php" class="primary-button">
        Shop Now
    </a>

</section>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>
</html>
