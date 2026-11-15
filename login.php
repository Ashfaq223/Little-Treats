<?php

require_once __DIR__ . "/includes/bootstrap.php";



$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    if (login_is_locked($conn, "user", $email)) {

        $message =
            "Too many failed attempts. Please try again in " .
            LOGIN_LOCK_MINUTES . " minutes.";

    } else {

        $result = db_select(
            $conn,
            "SELECT * FROM users WHERE email = ?",
            "s",
            [$email]
        );

        $user = $result->fetch_assoc();


        if (
            $user &&
            password_verify($password, (string) $user["password"])
        ) {

            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];

            login_clear_failures($conn, "user", $email);


            // Back to the page the customer was trying to open
            header("Location: " . pull_next());

            exit();

        } else {

            login_record_failure($conn, "user", $email);

            // Same message for unknown email and wrong password
            $message = "Invalid email or password.";

        }

    }

}
?>


<!DOCTYPE html>

<html>

<head>

    <title>Login - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= LOGIN SECTION ================= -->

<main id="main-content" class="login-container">


    <div class="login-card">


        <!-- LOGIN ICON -->

        <div class="login-icon" aria-hidden="true">
            🧁
        </div>


        <p class="section-label">
            WELCOME BACK
        </p>


        <h2>
            Customer Login
        </h2>


        <p class="login-subtitle">
            Sign in to continue shopping at
            Little Treats Bakery.
        </p>



        <!-- ERROR MESSAGE -->

        <?php

        if ($message != "") {

        ?>

            <div class="login-error">

                <?php

                echo htmlspecialchars($message);

                ?>

            </div>

        <?php

        }

        ?>



        <!-- LOGIN FORM -->

        <form method="POST" class="login-form">

        <?php echo csrf_field(); ?>


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>



            <div class="form-group">

                <label for="password">
                    Password
                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

                <small>
                    <a href="forgot_password.php">Forgot your password?</a>
                </small>

            </div>



            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>


        </form>



        <!-- REGISTER -->

        <div class="register-prompt">

            <p>
                Don't have an account?
            </p>


            <a href="register.php">
                Create an Account
            </a>

        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>