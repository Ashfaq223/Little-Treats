<?php

require_once __DIR__ . "/admin_auth.php";


// Changing a status must be a POST request with a valid token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: contact_messages.php");
    exit();

}

csrf_verify();


$message_id = (int) ($_POST["id"] ?? 0);

$status = $_POST["status"] ?? "";


// Only allow these statuses
$allowed_statuses = [
    "Read",
    "Unread"
];


if ($message_id <= 0 || !in_array($status, $allowed_statuses, true)) {

    header("Location: contact_messages.php");
    exit();

}


// Update message status
db_exec(
    $conn,
    "UPDATE contact_messages
     SET status = ?
     WHERE message_id = ?",
    "si",
    [$status, $message_id]
);


header("Location: contact_messages.php");
exit();

?>
