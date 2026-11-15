<?php

require_once __DIR__ . "/admin_auth.php";

$message_id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

$row = db_select(
    $conn,
    "SELECT * FROM contact_messages WHERE message_id = ?",
    "i",
    [$message_id]
)->fetch_assoc();

if (!$row) {

    http_response_code(404);

    exit("Message not found.");

}

$notice = "";
$notice_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    $reply = trim($_POST["reply"] ?? "");

    if ($reply === "") {

        $notice = "Please write a reply.";
        $notice_type = "error";

    } elseif (mb_strlen($reply) > 3000) {

        $notice = "The reply is too long.";
        $notice_type = "error";

    } else {

        lt_send_mail(
            $row["email"],
            "Re: " . $row["subject"],
            "Hello " . $row["name"] . ",\n\n" . $reply . "\n\n" .
            "-- " . SITE_NAME . "\n\n" .
            "Your original message:\n> " . str_replace("\n", "\n> ", $row["message"])
        );

        db_exec(
            $conn,
            "UPDATE contact_messages
             SET reply_message = ?, replied_at = NOW(), status = 'Read'
             WHERE message_id = ?",
            "si",
            [$reply, $message_id]
        );

        audit_log($conn, "reply", "contact_message", $message_id, "Replied to " . $row["email"]);

        $row["reply_message"] = $reply;
        $row["replied_at"] = date("Y-m-d H:i:s");

        $notice = "Your reply has been recorded and emailed to the customer.";
        $notice_type = "success";

    }

}

?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reply to Message - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">
    <div class="admin-welcome">

        <h2>✉️ Reply to Message</h2>

        <p>Your reply is emailed to the customer and saved with the message.</p>

    </div>

    <div class="admin-card">

        <?php if ($notice !== "") { ?>
            <div class="message <?php echo e($notice_type); ?>" role="status">
                <?php echo e($notice); ?>
            </div>
        <?php } ?>

        <h3><?php echo e($row["subject"]); ?></h3>

        <p>
            From <strong><?php echo e($row["name"]); ?></strong>
            (<?php echo e($row["email"]); ?>)
            on <?php echo e(date("d M Y, h:i A", strtotime($row["created_at"]))); ?>
        </p>

        <p><?php echo nl2br(e($row["message"])); ?></p>

        <?php if ($row["replied_at"]) { ?>

            <hr>

            <p>
                <strong>Last reply</strong>
                (<?php echo e(date("d M Y, h:i A", strtotime($row["replied_at"]))); ?>):
            </p>

            <p><?php echo nl2br(e($row["reply_message"])); ?></p>

        <?php } ?>

        <hr>

        <form method="POST">

            <?php echo csrf_field(); ?>

            <input type="hidden" name="id" value="<?php echo (int) $message_id; ?>">

            <div class="form-group">
                <label for="reply">Your reply</label>
                <textarea id="reply" name="reply" rows="7" maxlength="3000" required></textarea>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-button">Send Reply</button>
                <a href="contact_messages.php" class="back-link">Back to Messages</a>
            </div>

        </form>

    </div>
</main>

</body>

</html>