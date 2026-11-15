<?php

require_once __DIR__ . "/includes/auth.php";


$user_id = (int) $_SESSION["user_id"];


/* ==============================
   GET CUSTOMER'S POINTS
============================== */

$result = db_select(
    $conn,
    "SELECT points
     FROM loyalty_points
     WHERE user_id = ?",
    "i",
    [$user_id]
);


if ($result->num_rows == 1) {

    $loyalty = $result->fetch_assoc();

    $points = $loyalty["points"];

} else {

    $points = 0;

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Loyalty Rewards - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= HERO ================= -->

<section class="loyalty-hero">

    <p class="section-label">
        LITTLE TREATS BAKERY
    </p>

    <h2>
        Loyalty Rewards
    </h2>

    <p>
        Every order brings you closer to something delicious.
    </p>

</section>



<!-- ================= LOYALTY CONTENT ================= -->

<main id="main-content" class="loyalty-container">


    <!-- POINTS CARD -->

    <div class="points-card">


        <div class="points-icon" aria-hidden="true">
            ⭐
        </div>


        <div>

            <span class="points-label">
                YOUR CURRENT LOYALTY POINTS
            </span>


            <div class="points-number">

                <?php echo $points; ?>

            </div>


            <p>
                Loyalty Points
            </p>

        </div>


    </div>



    <!-- HOW TO EARN -->

    <div class="earn-card">


        <div class="earn-icon" aria-hidden="true">
            🎁
        </div>


        <div>

            <h3>
                How You Earn Points
            </h3>

            <p>
                You earn <strong>1 loyalty point</strong>
                for every <strong>Rs. <?php echo (int) LOYALTY_RS_PER_POINT; ?></strong> spent.
            </p>

        </div>


    </div>



    <!-- REWARDS -->

    <div class="rewards-heading">

        <h2>
            Available Rewards
        </h2>

        <p>
            Use your loyalty points to enjoy special treats.
        </p>

    </div>



    <div class="rewards-grid">


        <?php

        $rewards_sql = "SELECT *
                        FROM loyalty_rewards
                        ORDER BY points_required ASC";

        $rewards_result = mysqli_query(
            $conn,
            $rewards_sql
        );


        if (mysqli_num_rows($rewards_result) > 0) {


            while ($reward = mysqli_fetch_assoc($rewards_result)) {

        ?>


        <!-- REWARD CARD -->

        <div class="reward-card">


            <div class="reward-image" aria-hidden="true">
                🍫
            </div>


            <div class="reward-content">


                <h3>

                    <?php

                    echo htmlspecialchars(
                        $reward["reward_name"]
                    );

                    ?>

                </h3>


                <p class="reward-description">

                    <?php

                    echo htmlspecialchars(
                        $reward["description"]
                    );

                    ?>

                </p>


                <div class="reward-points">

                    <span>
                        Required Points
                    </span>

                    <strong>

                        <?php

                        echo $reward["points_required"];

                        ?>

                        ⭐

                    </strong>

                </div>



                <?php

                if (
                    $points >=
                    $reward["points_required"]
                ) {

                ?>


                    <form
                        method="POST"
                        action="redeem_reward.php"
                        data-confirm="Are you sure you want to redeem this reward?"
                    >

                        <?php echo csrf_field(); ?>

                        <input
                            type="hidden"
                            name="reward_id"
                            value="<?php echo (int) $reward["reward_id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="redeem-button"
                        >
                            Redeem Reward
                        </button>

                    </form>


                <?php

                } else {

                ?>


                    <div class="not-enough">

                        🔒

                        Not enough points

                    </div>


                <?php

                }


                ?>


            </div>


        </div>


        <?php

            }


        } else {

        ?>


            <div class="no-rewards">

                <div>
                    🎁
                </div>

                <h3>
                    No Rewards Available
                </h3>

                <p>
                    Check back later for exciting loyalty rewards.
                </p>

            </div>


        <?php

        }

        ?>


    </div>


</main>



<!-- ================= CTA ================= -->

<section class="loyalty-cta">

    <h2>
        Keep Earning, Keep Enjoying!
    </h2>

    <p>
        Every Rs. 1000 you spend brings you one step closer to a delicious reward.
    </p>

    <a
        href="products.php"
        class="primary-button"
    >
        Continue Shopping
    </a>

</section>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>