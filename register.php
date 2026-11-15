<?php

require_once __DIR__ . "/includes/bootstrap.php";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $password = $_POST["password"] ?? "";


    // ---------- Server-side validation ----------

    if (
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $address === "" ||
        $password === ""
    ) {

        $message = "Please fill in all fields.";

    } elseif (mb_strlen($name) > 100) {

        $message = "Name must be 100 characters or less.";

    } elseif (
        mb_strlen($email) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $message = "Please enter a valid email address.";

    } elseif (!preg_match('/^\+?[0-9][0-9 \-]{7,18}$/', $phone)) {

        $message = "Please enter a valid phone number.";

    } elseif (mb_strlen($address) > 500) {

        $message = "Address is too long.";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";

    } elseif (strlen($password) > 72) {

        $message = "Password must be 72 characters or less.";

    } else {

        // Check if email already exists
        $result = db_select(
            $conn,
            "SELECT user_id FROM users WHERE email = ?",
            "s",
            [$email]
        );


        if ($result->num_rows > 0) {

            $message = "Email already exists.";

        } else {

            // Hash the password
            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            // Create the customer and the loyalty account together
            try {

                $conn->begin_transaction();

                db_exec(
                    $conn,
                    "INSERT INTO users
                     (name, email, password, phone, address)
                     VALUES (?, ?, ?, ?, ?)",
                    "sssss",
                    [$name, $email, $hashed_password, $phone, $address]
                );

                $user_id = $conn->insert_id;

                db_exec(
                    $conn,
                    "INSERT INTO loyalty_points (user_id, points)
                     VALUES (?, 0)",
                    "i",
                    [$user_id]
                );

                $conn->commit();

                $message =
                    "Registration successful! You can now login.";

            } catch (mysqli_sql_exception $ex) {

                $conn->rollback();

                // 1062 = duplicate email (unique key, e.g. double submit)
                if ($ex->getCode() == 1062) {

                    $message = "Email already exists.";

                } else {

                    error_log("Registration failed: " . $ex->getMessage());

                    $message = "Registration failed.";

                }

            }

        }

    }

}
?>


<!DOCTYPE html>

<html>

<head>

    <title>
        Register - Little Treats Bakery
    </title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= REGISTER ================= -->

<main id="main-content" class="register-container">


    <div class="register-card">


        <!-- ICON -->

        <div class="register-icon" aria-hidden="true">
            🧁
        </div>


        <p class="section-label">
            LITTLE TREATS BAKERY
        </p>


        <h2>
            Create Your Account
        </h2>


        <p class="register-subtitle">
            Join Little Treats Bakery and start
            enjoying freshly baked treats.
        </p>



        <!-- MESSAGE -->

        <?php

        if ($message != "") {

        ?>


            <div
                class="<?php

                if (
                    strpos(
                        $message,
                        "successful"
                    ) !== false
                ) {

                    echo "register-success";

                } else {

                    echo "register-error";

                }

                ?>"
            >

                <?php

                echo htmlspecialchars($message);

                ?>

            </div>


        <?php

        }

        ?>



        <!-- REGISTER FORM -->

        <form
            method="POST"
            class="register-form"
        >

        <?php echo csrf_field(); ?>


            <!-- NAME -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>


                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >

            </div>



            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    required
                >

            </div>



            <!-- PHONE -->

            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>


                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >

            </div>



            <!-- ADDRESS -->

            <div class="form-group">

                <label for="address">
                    Address
                </label>


                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter your delivery address"
                    rows="4"
                    required
                ></textarea>

            </div>



            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="8"
                    maxlength="72"
                    required
                >

            </div>



            <!-- REGISTER BUTTON -->

            <button
                type="submit"
                class="register-button"
            >
                Create Account
            </button>


        </form>



        <!-- LOGIN LINK -->

        <div class="login-prompt">

            <p>
                Already have an account?
            </p>


            <a href="login.php">
                Login to Your Account
            </a>

        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>