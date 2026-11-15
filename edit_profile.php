<?php

require_once __DIR__ . "/includes/auth.php";

$user_id = (int) $_SESSION["user_id"];

$errors = [];

$user = db_select(
    $conn,
    "SELECT name, email, phone, address FROM users WHERE user_id = ?",
    "i",
    [$user_id]
)->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $action = $_POST["action"] ?? "";


    // ---------- Update details ----------

    if ($action === "profile") {

        $name = trim($_POST["name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $address = trim($_POST["address"] ?? "");

        if ($name === "" || $phone === "" || $address === "") {

            $errors["profile"] = "Please fill in all fields.";

        } elseif (mb_strlen($name) > 100) {

            $errors["profile"] = "Name must be 100 characters or less.";

        } elseif (!preg_match('/^\+?[0-9][0-9 \-]{7,18}$/', $phone)) {

            $errors["profile"] = "Please enter a valid phone number.";

        } elseif (mb_strlen($address) > 500) {

            $errors["profile"] = "Address is too long.";

        } else {

            db_exec(
                $conn,
                "UPDATE users SET name = ?, phone = ?, address = ? WHERE user_id = ?",
                "sssi",
                [$name, $phone, $address, $user_id]
            );

            $_SESSION["name"] = $name;

            flash_set("success", "Your details have been updated.");

            redirect("profile.php");

        }

        $user["name"] = $name;
        $user["phone"] = $phone;
        $user["address"] = $address;

    }


    // ---------- Change password ----------

    if ($action === "password") {

        $current = $_POST["current_password"] ?? "";
        $new = $_POST["new_password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";

        $lock_key = "user-" . $user_id;

        if (login_is_locked($conn, "pwchange", $lock_key)) {

            $errors["password"] = "Too many attempts. Please try again later.";

        } else {

            $row = db_select(
                $conn,
                "SELECT password FROM users WHERE user_id = ?",
                "i",
                [$user_id]
            )->fetch_assoc();

            if (!password_verify($current, (string) $row["password"])) {

                login_record_failure($conn, "pwchange", $lock_key);

                $errors["password"] = "Your current password is not correct.";

            } elseif (strlen($new) < 8) {

                $errors["password"] = "New password must be at least 8 characters.";

            } elseif (strlen($new) > 72) {

                $errors["password"] = "New password must be 72 characters or less.";

            } elseif ($new !== $confirm) {

                $errors["password"] = "The two new passwords do not match.";

            } else {

                db_exec(
                    $conn,
                    "UPDATE users SET password = ? WHERE user_id = ?",
                    "si",
                    [password_hash($new, PASSWORD_DEFAULT), $user_id]
                );

                login_clear_failures($conn, "pwchange", $lock_key);

                session_regenerate_id(true);

                flash_set("success", "Your password has been changed.");

                redirect("profile.php");

            }

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit Profile - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="login-container">

    <div class="login-card">

        <p class="section-label">MY ACCOUNT</p>

        <h2>Edit Profile</h2>

        <?php if (isset($errors["profile"])) { ?>
            <div class="login-error" role="alert"><?php echo e($errors["profile"]); ?></div>
        <?php } ?>

        <form method="POST" class="login-form">

            <?php echo csrf_field(); ?>

            <input type="hidden" name="action" value="profile">

            <div class="form-group">
                <label for="email">Email Address (cannot be changed)</label>
                <input type="email" id="email" value="<?php echo e($user["email"]); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($user["name"]); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" maxlength="20" value="<?php echo e($user["phone"]); ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Delivery Address</label>
                <textarea id="address" name="address" rows="3" maxlength="500" required><?php echo e($user["address"]); ?></textarea>
            </div>

            <button type="submit" class="login-button">Save Details</button>

        </form>

    </div>

    <div class="login-card" style="margin-top: 24px;">

        <h2>Change Password</h2>

        <?php if (isset($errors["password"])) { ?>
            <div class="login-error" role="alert"><?php echo e($errors["password"]); ?></div>
        <?php } ?>

        <form method="POST" class="login-form">

            <?php echo csrf_field(); ?>

            <input type="hidden" name="action" value="password">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="8" maxlength="72" autocomplete="new-password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
            </div>

            <button type="submit" class="login-button">Change Password</button>

        </form>

        <p class="login-subtitle"><a href="profile.php">Back to profile</a></p>

    </div>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>