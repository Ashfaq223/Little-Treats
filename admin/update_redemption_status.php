<?php

require_once __DIR__ . "/admin_auth.php";


/* ================================
   CHECK REDEMPTION ID
================================ */

if (!isset($_GET["id"])) {

    header("Location: loyalty_redemptions.php");
    exit();

}

$redemption_id = (int) $_GET["id"];


/* ================================
   GET REDEMPTION
================================ */

$result = db_select(
    $conn,
    "SELECT
         lr.*,
         u.name AS customer_name,
         r.reward_name
     FROM loyalty_redemptions lr

     JOIN users u
         ON lr.user_id = u.user_id

     JOIN loyalty_rewards r
         ON lr.reward_id = r.reward_id

     WHERE lr.redemption_id = ?",
    "i",
    [$redemption_id]
);


if ($result->num_rows != 1) {

    echo "Redemption not found.";
    exit();

}


$redemption = $result->fetch_assoc();


/* ================================
   UPDATE STATUS
================================ */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $status = $_POST["status"] ?? "";


    $allowed_statuses = REDEMPTION_STATUSES;


    if (in_array($status, $allowed_statuses, true)) {

        // Points were refunded when this redemption was cancelled,
        // so it cannot be reopened (that would be a free reward).
        if (
            $redemption["status"] === "Cancelled" &&
            $status !== "Cancelled"
        ) {

            echo "A cancelled redemption cannot be reopened.";
            echo '<br><a href="loyalty_redemptions.php">Back</a>';
            exit();

        }


        try {

            $conn->begin_transaction();

            db_exec(
                $conn,
                "UPDATE loyalty_redemptions
                 SET status = ?
                 WHERE redemption_id = ?",
                "si",
                [$status, $redemption_id]
            );


            // Refund the points once when a redemption is cancelled
            if ($status === "Cancelled") {

                $refund_flag = db_exec(
                    $conn,
                    "UPDATE loyalty_redemptions
                     SET points_refunded = 1
                     WHERE redemption_id = ?
                     AND points_refunded = 0",
                    "i",
                    [$redemption_id]
                );

                if ($refund_flag === 1) {

                    db_exec(
                        $conn,
                        "INSERT INTO loyalty_points (user_id, points)
                         VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE points = points + VALUES(points)",
                        "ii",
                        [
                            (int) $redemption["user_id"],
                            (int) $redemption["points_used"]
                        ]
                    );

                }

            }

            $conn->commit();

            audit_log(
                $conn,
                $status === "Cancelled" ? "cancel_and_refund" : "update_status",
                "redemption",
                $redemption_id,
                $redemption["status"] . " -> " . $status .
                ($status === "Cancelled" ? "; points refunded: " . (int) $redemption["points_used"] : "")
            );

            header("Location: loyalty_redemptions.php");
            exit();

        } catch (Throwable $ex) {

            $conn->rollback();

            error_log("Redemption update failed: " . $ex->getMessage());

            echo "Unable to update redemption status.";

        }

    }

}
?>

<!DOCTYPE html>

<html>

<head>

    <title>Update Loyalty Redemption</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>


<!-- ================= HEADER ================= -->

<?php $admin_subtitle = "Loyalty Reward Management"; include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- ================= MAIN ================= -->

<main id="main-content" class="admin-container">


    <div class="admin-card">

        <h2>🎁 Update Loyalty Redemption</h2>


        <p>

            <strong>Redemption ID:</strong>

            #<?php echo $redemption["redemption_id"]; ?>

        </p>


        <p>

            <strong>Customer:</strong>

            <?php

            echo htmlspecialchars(
                $redemption["customer_name"]
            );

            ?>

        </p>


        <p>

            <strong>Reward:</strong>

            <?php

            echo htmlspecialchars(
                $redemption["reward_name"]
            );

            ?>

        </p>


        <p>

            <strong>Points Used:</strong>

            <?php echo $redemption["points_used"]; ?>

        </p>


        <p>

            <strong>Current Status:</strong>

            <?php echo htmlspecialchars($redemption["status"]); ?>

        </p>


        <hr>


        <form method="POST">

        <?php echo csrf_field(); ?>

            <label>

                <strong>Change Status</strong>

            </label>

            <br><br>


            <select name="status" required>

                <option value="Pending"
                    <?php
                    if ($redemption["status"] == "Pending")
                        echo "selected";
                    ?>
                >
                    Pending
                </option>


                <option value="Approved"
                    <?php
                    if ($redemption["status"] == "Approved")
                        echo "selected";
                    ?>
                >
                    Approved
                </option>


                <option value="Collected"
                    <?php
                    if ($redemption["status"] == "Collected")
                        echo "selected";
                    ?>
                >
                    Collected
                </option>


                <option value="Cancelled"
                    <?php
                    if ($redemption["status"] == "Cancelled")
                        echo "selected";
                    ?>
                >
                    Cancelled
                </option>

            </select>


            <br><br>


            <button type="submit">
                Update Redemption Status
            </button>

        </form>


        <br>


        <a href="loyalty_redemptions.php" class="back-link">
            ← Back to Loyalty Redemptions
        </a>

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