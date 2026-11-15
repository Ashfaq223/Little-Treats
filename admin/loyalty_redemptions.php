<?php

require_once __DIR__ . "/admin_auth.php";


// Get all loyalty redemptions
$sql = "SELECT 
            lr.redemption_id,
            lr.user_id,
            u.name AS customer_name,
            r.reward_name,
            lr.points_used,
            lr.redemption_date,
            lr.status
        FROM loyalty_redemptions lr
        JOIN users u
            ON lr.user_id = u.user_id
        JOIN loyalty_rewards r
            ON lr.reward_id = r.reward_id
        ORDER BY lr.redemption_date DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Loyalty Redemptions - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- ================= MAIN ================= -->

<main id="main-content" class="admin-container">


    <div class="admin-welcome">

        <h2>🎁 Loyalty Reward Redemptions</h2>

        <p>
            View customer loyalty rewards that have been redeemed.
        </p>

    </div>



    <!-- ================= REDEMPTIONS TABLE ================= -->

    <div class="admin-card">

        <h3>Reward Redemption History</h3>


        <div class="table-container">

            <table>

                <tr>

                    <th>Redemption ID</th>

                    <th>Customer</th>

                    <th>Reward</th>

                    <th>Points Used</th>

                    <th>Date</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>


                <?php

                if (mysqli_num_rows($result) > 0) {

                    while ($row = mysqli_fetch_assoc($result)) {

                ?>

                <tr>

                    <td>

                        <strong>
                            #<?php echo $row["redemption_id"]; ?>
                        </strong>

                    </td>


                    <td>

                        <?php echo htmlspecialchars($row["customer_name"]); ?>

                    </td>


                    <td>

                        <strong>

                            <?php echo htmlspecialchars($row["reward_name"]); ?>

                        </strong>

                    </td>


                    <td>

                        <?php echo $row["points_used"]; ?> points

                    </td>


                    <td>

                        <?php echo $row["redemption_date"]; ?>

                    </td>


                    <td>

    <span class="status-badge">

        <?php echo htmlspecialchars($row["status"]); ?>

    </span>

</td>


<td>

    <a
        href="update_redemption_status.php?id=<?php echo $row["redemption_id"]; ?>"
        class="table-button edit-button"
    >
        Update Status
    </a>

</td>

                </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="6">

                            No loyalty rewards have been redeemed yet.

                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>

    </div>


    <br>


    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>


</main>



<!-- ================= FOOTER ================= -->

<footer class="admin-footer">

    <p>
        © 2026 Little Treats Bakery | Administration Panel
    </p>

</footer>


</body>

</html>