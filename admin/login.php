<?php

require_once __DIR__ . "/../includes/bootstrap.php";



$message = "";


// =============================
// HANDLE ADMIN LOGIN
// =============================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // Validate fields
    if ($email === "" || $password === "") {

        $message = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (login_is_locked($conn, "admin", $email)) {

        $message =
            "Too many failed attempts. Please try again in " .
            LOGIN_LOCK_MINUTES . " minutes.";

    } else {


        // =============================
        // FIND ADMIN
        // =============================

        $sql = "SELECT *
                FROM admins
                WHERE email = ?";


        $stmt = mysqli_prepare($conn, $sql);


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );


            mysqli_stmt_execute($stmt);


            $result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) == 1) {

                $admin = mysqli_fetch_assoc($result);


                // =============================
                // VERIFY PASSWORD
                // =============================

                if (
                    password_verify(
                        $password,
                        $admin["password"]
                    )
                ) {

                    // Prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION["admin_id"] =
                        $admin["admin_id"];

                    $_SESSION["admin_name"] =
                        $admin["name"];

                    // owner or staff (controls what the admin may do)
                    $_SESSION["admin_role"] =
                        $admin["role"] ?? "staff";

                    login_clear_failures($conn, "admin", $email);

                    audit_log($conn, "login", "admin", (int) $admin["admin_id"], "Admin signed in");


                    header("Location: dashboard.php");

                    exit();

                } else {

                    login_record_failure($conn, "admin", $email);

                    $message =
                        "Invalid email or password.";

                }

            } else {

                login_record_failure($conn, "admin", $email);

                $message =
                    "Invalid email or password.";

            }


            mysqli_stmt_close($stmt);

        } else {

            $message =
                "Unable to process login. Please try again.";

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Little Treats Bakery administration login"
    >

    <title>
        Admin Login - Little Treats Bakery
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="admin-login-body">


<!-- ============================= -->
<!-- ADMIN LOGIN -->
<!-- ============================= -->

<main class="admin-login-page">


    <div class="admin-login-card">


        <!-- Logo / Brand -->

        <div class="admin-login-brand">

            <div class="admin-logo" aria-hidden="true">

                🧁

            </div>


            <h1>
                Little Treats Bakery
            </h1>


            <p>
                Administration Panel
            </p>

        </div>



        <!-- Login Heading -->

        <div class="admin-login-heading">

            <h2>
                Welcome Back
            </h2>

            <p>
                Sign in to manage your bakery.
            </p>

        </div>



        <!-- Error Message -->

        <?php if ($message != "") { ?>

            <div class="admin-login-error">

                ⚠️

                <?php

                echo htmlspecialchars($message);

                ?>

            </div>

        <?php } ?>



        <!-- Login Form -->

        <form
            method="POST"
            action="login.php"
            class="admin-login-form"
        >

        <?php echo csrf_field(); ?>


            <!-- Email -->

            <div class="admin-form-group">

                <label for="email">

                    Email Address

                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    placeholder="Enter admin email"
                    autocomplete="email"
                    required
                >

            </div>



            <!-- Password -->

            <div class="admin-form-group">

                <label for="password">

                    Password

                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>



            <!-- Login Button -->

            <button
                type="submit"
                class="admin-login-button"
            >

                Login to Dashboard

            </button>


        </form>



        <!-- Back to Website -->

        <div class="admin-login-footer">

            <a href="../index.php">

                ← Back to Little Treats Bakery

            </a>

        </div>


    </div>


</main>


</body>

</html>