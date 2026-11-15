<?php

require_once __DIR__ . "/admin_auth.php";

$search = trim($_GET["q"] ?? "");
$page = current_page();

$where = "1 = 1";
$types = "";
$params = [];

if ($search !== "") {

    $like = "%" . addcslashes(mb_substr($search, 0, 100), "%_\\") . "%";

    $where = "(users.name LIKE ? OR users.email LIKE ? OR users.phone LIKE ?)";
    $types = "sss";
    $params = [$like, $like, $like];

}

$total = (int) db_select(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE " . $where,
    $types,
    $params
)->fetch_assoc()["total"];

$customers = db_select(
    $conn,
    "SELECT users.user_id, users.name, users.email, users.phone,
            COALESCE(lp.points, 0) AS points,
            COUNT(DISTINCT orders.order_id) AS order_count,
            COALESCE(SUM(CASE WHEN orders.order_status <> 'Cancelled' THEN orders.total_amount END), 0) AS total_spent
     FROM users
     LEFT JOIN loyalty_points lp ON lp.user_id = users.user_id
     LEFT JOIN orders ON orders.user_id = users.user_id
     WHERE " . $where . "
     GROUP BY users.user_id, users.name, users.email, users.phone, lp.points
     ORDER BY users.user_id DESC
     LIMIT ? OFFSET ?",
    $types . "ii",
    array_merge($params, [PAGE_SIZE, ($page - 1) * PAGE_SIZE])
);

?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customers - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">
    <div class="admin-welcome">

        <h2>👥 Customers</h2>

        <p>Everyone who has registered, with their orders and loyalty points.</p>

    </div>

    <div class="admin-card">

        <form method="GET" class="admin-filters" role="search">

            <label class="visually-hidden" for="f-q">Search</label>
            <input type="search" id="f-q" name="q" value="<?php echo e($search); ?>" placeholder="Search name, email or phone">

            <button type="submit">Search</button>

            <?php if ($search !== "") { ?>
                <a href="customers.php">Clear</a>
            <?php } ?>

        </form>

        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total spent</th>
                        <th>Points</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($customers->num_rows === 0) { ?>
                        <tr><td colspan="7">No customers found.</td></tr>
                    <?php } ?>

                    <?php while ($c = $customers->fetch_assoc()) { ?>

                        <tr>
                            <td>#<?php echo (int) $c["user_id"]; ?></td>
                            <td><strong><?php echo e($c["name"]); ?></strong></td>
                            <td><?php echo e($c["email"]); ?></td>
                            <td><?php echo e($c["phone"]); ?></td>
                            <td><?php echo (int) $c["order_count"]; ?></td>
                            <td>Rs. <?php echo number_format((float) $c["total_spent"], 2); ?></td>
                            <td><?php echo (int) $c["points"]; ?></td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

        <?php echo pager_html($page, $total); ?>

    </div>

    <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</main>

</body>

</html>