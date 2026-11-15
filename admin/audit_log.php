<?php

require_once __DIR__ . "/admin_auth.php";

// The audit log is for the owner only
admin_require_owner();

$page = current_page();

$total = (int) db_select($conn, "SELECT COUNT(*) AS total FROM audit_log")->fetch_assoc()["total"];

$entries = db_select(
    $conn,
    "SELECT * FROM audit_log ORDER BY audit_id DESC LIMIT ? OFFSET ?",
    "ii",
    [PAGE_SIZE * 2, ($page - 1) * PAGE_SIZE * 2]
);

?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Audit Log - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">
    <div class="admin-welcome">

        <h2>🧾 Audit Log</h2>

        <p>A record of what each admin changed, and when.</p>

    </div>

    <div class="admin-card">

        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Item</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($entries->num_rows === 0) { ?>
                        <tr><td colspan="6">Nothing has been recorded yet.</td></tr>
                    <?php } ?>

                    <?php while ($entry = $entries->fetch_assoc()) { ?>

                        <tr>
                            <td><?php echo e(date("d M Y, h:i A", strtotime($entry["created_at"]))); ?></td>
                            <td><?php echo e($entry["admin_name"]); ?></td>
                            <td><?php echo e($entry["action"]); ?></td>
                            <td>
                                <?php echo e($entry["entity"]); ?>
                                <?php echo $entry["entity_id"] ? "#" . (int) $entry["entity_id"] : ""; ?>
                            </td>
                            <td><?php echo e($entry["details"]); ?></td>
                            <td><?php echo e($entry["ip_address"]); ?></td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

        <?php echo pager_html($page, $total, PAGE_SIZE * 2); ?>

    </div>

    <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</main>

</body>

</html>