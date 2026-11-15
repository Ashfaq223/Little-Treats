<?php

require_once __DIR__ . "/includes/bootstrap.php";

$message = "";
$sent = false;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $email = strtolower(trim($_POST["email"] ?? ""));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        // Limit how many reset emails one address / IP can request
        if (!login_is_locked($conn, "reset", $email)) {

            login_record_failure($conn, "reset", $email);

            $user = db_select(
                $conn,
                "SELECT user_id, name FROM users WHERE email = ?",
                "s",
                [$email]
            )->fetch_assoc();

            if ($user) {

                // Only a hash of the token is stored
                $token = bin2hex(random_bytes(32));

                db_exec(
                    $conn,
                    "INSERT INTO password_resets (user_id, token_hash, expires_at)
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
                    "is",
                    [(int) $user["user_id"], hash("sha256", $token)]
                );

                lt_send_mail(
                    $email,
                    "Reset your Little Treats Bakery password",
                    "Hello " . $user["name"] . ",\n\n" .
                    "We received a request to reset your password. " .
                    "Open this link within 1 hour to choose a new one:\n\n" .
                    site_origin() . "/reset_password.php?token=" . $token . "\n\n" .
                    "If you did not ask for this, you can ignore this email."
                );

            }

        }

        // Same answer whether or not the email has an account
        $sent = true;

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Forgot Password - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="login-container">

    <div class="login-card">

        <p class="section-label">ACCOUNT HELP</p>

        <h2>Forgot Password</h2>

        <p class="login-subtitle">
            Enter your email and we will send you a link to choose a new password.
        </p>

        <?php if ($sent) { ?>

            <div class="register-success" role="status">
                If an account exists for that email, a reset link has been sent.
                The link is valid for 1 hour.
            </div>

        <?php } elseif ($message !== "") { ?>

            <div class="login-error" role="alert">
                <?php echo e($message); ?>
            </div>

        <?php } ?>

        <form method="POST" class="login-form">

            <?php echo csrf_field(); ?>

            <div class="form-group">

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <button type="submit" class="login-button">
                Send Reset Link
            </button>

        </form>

        <p class="login-subtitle">
            <a href="login.php">Back to login</a>
        </p>

    </div>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>