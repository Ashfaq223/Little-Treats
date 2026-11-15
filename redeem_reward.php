<?php

require_once __DIR__ . "/includes/auth.php";


$user_id = (int) $_SESSION["user_id"];


// Redeeming changes the customer's points, so it must be a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: loyalty.php");
    exit();

}

csrf_verify();


// Check reward ID
if (!isset($_POST["reward_id"])) {

    header("Location: loyalty.php");
    exit();

}

$reward_id = (int) $_POST["reward_id"];


// Get reward
$reward_result = db_select(
    $conn,
    "SELECT * FROM loyalty_rewards WHERE reward_id = ?",
    "i",
    [$reward_id]
);


if ($reward_result->num_rows != 1) {

    echo "Reward not found.";
    exit();

}


$reward = $reward_result->fetch_assoc();

$required_points = (int) $reward["points_required"];


// Make sure the customer has a loyalty account (older accounts may not)
db_exec(
    $conn,
    "INSERT IGNORE INTO loyalty_points (user_id, points) VALUES (?, 0)",
    "i",
    [$user_id]
);


// Deduct points and record the redemption together.
// The deduction only succeeds if the customer still has enough points,
// so two quick requests can never spend the same points twice.
try {

    $conn->begin_transaction();

    $deducted = db_exec(
        $conn,
        "UPDATE loyalty_points
         SET points = points - ?
         WHERE user_id = ?
         AND points >= ?",
        "iii",
        [$required_points, $user_id, $required_points]
    );

    if ($deducted !== 1) {

        $conn->rollback();

        echo "You do not have enough loyalty points.";
        exit();

    }

    db_exec(
        $conn,
        "INSERT INTO loyalty_redemptions
         (user_id, reward_id, points_used, status)
         VALUES (?, ?, ?, 'Pending')",
        "iii",
        [$user_id, $reward_id, $required_points]
    );

    $conn->commit();

} catch (Throwable $ex) {

    $conn->rollback();

    error_log("Reward redemption failed: " . $ex->getMessage());

    echo "Unable to redeem this reward. Your points were not changed.";
    exit();

}


// Calculate remaining points
$remaining = db_select(
    $conn,
    "SELECT points FROM loyalty_points WHERE user_id = ?",
    "i",
    [$user_id]
)->fetch_assoc();

$remaining_points = (int) $remaining["points"];

$points = $remaining_points + $required_points;
?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Reward Redeemed - Little Treats Bakery
    </title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= REDEMPTION ================= -->

<main id="main-content" class="redemption-container">


    <div class="redemption-box">


        <!-- SUCCESS ICON -->

        <div class="redemption-success-icon" aria-hidden="true">

            ✓

        </div>


        <p class="section-label">
            LITTLE TREATS BAKERY
        </p>


        <h2>
            Reward Redeemed!
        </h2>


        <p class="redemption-message">

            Congratulations!

            <br>

            Your loyalty reward has been successfully redeemed.

        </p>



        <!-- REWARD -->

        <div class="reward-highlight">


            <div class="reward-icon" aria-hidden="true">
                🧁
            </div>


            <h3>

                <?php

                echo htmlspecialchars(
                    $reward["reward_name"]
                );

                ?>

            </h3>


            <p>

                <?php

                echo htmlspecialchars(
                    $reward["description"]
                );

                ?>

            </p>


        </div>



        <!-- REDEMPTION DETAILS -->

        <div class="redemption-details">


            <div class="redemption-detail">

                <span>
                    Points Used
                </span>

                <strong>

                    <?php echo $required_points; ?>

                    Points

                </strong>

            </div>



            <div class="redemption-detail">

                <span>
                    Remaining Points
                </span>

                <strong>

                    <?php echo $remaining_points; ?>

                    Points

                </strong>

            </div>



            <div class="redemption-detail">

                <span>
                    Redemption Status
                </span>

                <strong class="pending-badge">

                    Pending

                </strong>

            </div>


        </div>



        <!-- INFORMATION -->

        <div class="redemption-note">

            <strong>
                🎁 Your reward is being prepared
            </strong>

            <p>

                Your redemption has been sent to the
                Little Treats Bakery team.

                Please show your redemption details
                when collecting your reward.

            </p>

        </div>



        <!-- BUTTONS -->

        <div class="redemption-actions">


            <a
                href="loyalty.php"
                class="primary-redemption-button"
            >

                Back to Loyalty Rewards

            </a>


            <a
                href="my_orders.php"
                class="secondary-redemption-button"
            >

                My Orders

            </a>


        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>