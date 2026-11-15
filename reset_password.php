<?php

require_once __DIR__ . "/includes/bootstrap.php";

$message = "";

$token = $_POST["token"] ?? ($_GET["token"] ?? "");

$reset = null;

if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {

    $reset = db_select(
        $conn,
        "SELECT reset_id, user_id
         FROM password_resets
         WHERE token_hash = ?
         AND used = 0
         AND expires_at > NOW()",
        "s",
        [hash("sha256", $token)]
    )->fetch_assoc();

}


if ($reset && $_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";

    } elseif (strlen($password) > 72) {

        $message = "Password must be 72 characters or less.";

    } elseif ($password !== $confirm) {

        $message = "The two passwords do not match.";

    } else {

        try {

            $conn->begin_transaction();

            db_exec(
                $conn,
                "UPDATE users SET password = ? WHERE user_id = ?",
                "si",
                [password_hash($password, PASSWORD_DEFAULT), (int) $reset["user_id"]]
            );

            // The link works once; remove every other open link too
            db_exec(
                $conn,
                "DELETE FROM password_resets WHERE user_id = ?",
                "i",
                [(int) $reset["user_id"]]
            );

            $conn->commit();

        } catch (Throwable $ex) {

            $conn->rollback();

            error_log("Password reset failed: " . $ex->getMessage());

            $message = "Unable to reset the password. Please try again.";

        }

        if ($message === "") {

            flash_set("success", "Your password has been changed. You can now log in.");

            redirect("login.php");

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Reset Password - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="login-container">

    <div class="login-card">

        <p class="section-label">ACCOUNT HELP</p>

        <h2>Choose a New Password</h2>

        <?php if (!$reset) { ?>

            <div class="login-error" role="alert">
                This reset link is invalid or has expired.
            </div>

            <p class="login-subtitle">
                <a href="forgot_password.php">Request a new link</a>
            </p>

        <?php } else { ?>

            <?php if ($message !== "") { ?>
                <div class="login-error" role="alert">
                    <?php echo e($message); ?>
                </div>
            <?php } ?>

            <form method="POST" class="login-form">

                <?php echo csrf_field(); ?>

                <input type="hidden" name="token" value="<?php echo e($token); ?>">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="login-button">
                    Change Password
                </button>

            </form>

        <?php } ?>

    </div>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>