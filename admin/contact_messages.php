<?php

require_once __DIR__ . "/admin_auth.php";


/* ---------- Search, status filter and paging ---------- */

$search = trim($_GET["q"] ?? "");
$status_filter = $_GET["status"] ?? "";
$page = current_page();

$where = ["1 = 1"];
$types = "";
$params = [];

if ($search !== "") {

    $like = "%" . addcslashes(mb_substr($search, 0, 100), "%_\\") . "%";

    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $types .= "ssss";
    array_push($params, $like, $like, $like, $like);

}

if (in_array($status_filter, ["Read", "Unread"], true)) {

    $where[] = "status = ?";
    $types .= "s";
    $params[] = $status_filter;

} else {

    $status_filter = "";

}

$total_messages = (int) db_select(
    $conn,
    "SELECT COUNT(*) AS total FROM contact_messages WHERE " . implode(" AND ", $where),
    $types,
    $params
)->fetch_assoc()["total"];

$result = db_select(
    $conn,
    "SELECT * FROM contact_messages
     WHERE " . implode(" AND ", $where) . "
     ORDER BY created_at DESC
     LIMIT ? OFFSET ?",
    $types . "ii",
    array_merge($params, [PAGE_SIZE, ($page - 1) * PAGE_SIZE])
);

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Messages - Admin</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>


<main id="main-content">

    <div class="admin-container">


        <div class="admin-page-header">

            <div>

                <h2>Customer Messages</h2>

                <p>
                    View messages sent by your customers.
                </p>

            </div>

            <a href="dashboard.php" class="admin-back-button">
                ← Back to Dashboard
            </a>

        </div>



        <form method="GET" class="admin-filters" role="search">

            <label class="visually-hidden" for="f-q">Search</label>
            <input type="search" id="f-q" name="q" value="<?php echo e($search); ?>" placeholder="Search name, email, subject or message">

            <label class="visually-hidden" for="f-status">Status</label>
            <select id="f-status" name="status">
                <option value="">All messages</option>
                <option value="Unread" <?php echo $status_filter === "Unread" ? "selected" : ""; ?>>Unread</option>
                <option value="Read" <?php echo $status_filter === "Read" ? "selected" : ""; ?>>Read</option>
            </select>

            <button type="submit">Filter</button>

            <?php if ($search !== "" || $status_filter !== "") { ?>
                <a href="contact_messages.php">Clear</a>
            <?php } ?>

        </form>


        <?php

        if (mysqli_num_rows($result) > 0) {

        ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Customer</th>

                        <th>Email</th>

                        <th>Subject</th>

                        <th>Message</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                while ($row = mysqli_fetch_assoc($result)) {

                ?>

                    <tr>

                        <td>

                            #<?php echo $row["message_id"]; ?>

                        </td>


                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $row["name"]
                                );

                                ?>

                            </strong>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["email"]
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["subject"]
                            );

                            ?>

                        </td>


                        <td class="message-cell">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $row["message"]
                                )
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            if ($row["status"] == "Unread") {

                                echo '<span class="status-unread">
                                        Unread
                                      </span>';

                            } else {

                                echo '<span class="status-read">
                                        Read
                                      </span>';

                            }

                            ?>

                        </td>


                        <td>

                            <?php

                            echo date(
                                "d M Y, h:i A",
                                strtotime($row["created_at"])
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            if ($row["status"] == "Unread") {

                            ?>

                                <form
                                    method="POST"
                                    action="update_contact_status.php"
                                    class="inline-form"
                                >
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $row["message_id"]; ?>">
                                    <input type="hidden" name="status" value="Read">
                                    <button type="submit" class="action-button">
                                        Mark Read
                                    </button>
                                </form>

                                <a
                                    href="reply_message.php?id=<?php echo (int) $row["message_id"]; ?>"
                                    class="action-button"
                                >
                                    <?php echo $row["replied_at"] ? "Replied" : "Reply"; ?>
                                </a>

                            <?php

                            } else {

                            ?>

                                <form
                                    method="POST"
                                    action="update_contact_status.php"
                                    class="inline-form"
                                >
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $row["message_id"]; ?>">
                                    <input type="hidden" name="status" value="Unread">
                                    <button type="submit" class="action-button">
                                        Mark Unread
                                    </button>
                                </form>

                                <a
                                    href="reply_message.php?id=<?php echo (int) $row["message_id"]; ?>"
                                    class="action-button"
                                >
                                    <?php echo $row["replied_at"] ? "Replied" : "Reply"; ?>
                                </a>

                            <?php

                            }

                            ?>

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

        <?php echo pager_html($page, $total_messages); ?>

        <?php

        } else {

        ?>

            <div class="empty-message">

                <h3>No Contact Messages</h3>

                <p>
                    There are currently no messages from customers.
                </p>

            </div>

        <?php

        }

        ?>

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