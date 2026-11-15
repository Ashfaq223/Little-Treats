<?php

require_once __DIR__ . "/includes/bootstrap.php";



$message = "";
$message_type = "";

$name = "";
$email = "";
$subject = "";
$user_message = "";


/* =========================================
   HANDLE CONTACT FORM
========================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $user_message = trim($_POST["message"] ?? "");


    /* =========================================
       VALIDATION
    ========================================= */

    if (
        $name === "" ||
        $email === "" ||
        $subject === "" ||
        $user_message === ""
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($name) > 100) {

        $message = "Name is too long.";
        $message_type = "error";

    } elseif (strlen($subject) > 200) {

        $message = "Subject is too long.";
        $message_type = "error";

    } elseif (strlen($user_message) > 2000) {

        $message = "Message is too long. Please keep it under 2000 characters.";
        $message_type = "error";

    } else {

        /* =========================================
           INSERT MESSAGE
        ========================================= */

        $sql = "INSERT INTO contact_messages
                (name, email, subject, message, status)
                VALUES (?, ?, ?, ?, 'Unread')";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $subject,
                $user_message
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Thank you! Your message has been sent successfully.";
                $message_type = "success";

                $name = "";
                $email = "";
                $subject = "";
                $user_message = "";

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";

            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Unable to process your message. Please try again.";
            $message_type = "error";

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
        content="Contact Little Treats Bakery for cakes, desserts, orders and enquiries."
    >

    <title>Contact Us | Little Treats Bakery</title>

    <link rel="stylesheet" href="/little_treats/assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>


        <!-- =========================================
     CONTACT PAGE
========================================= -->

<main id="main-content">

    <section class="contact-page">

        <div class="contact-heading">

            <span class="section-label">
                We'd Love To Hear From You
            </span>

            <h2>
                Get In Touch
            </h2>

            <p>
                Have a question about our cakes, desserts,
                orders or services? Send us a message and
                our team will be happy to help.
            </p>

        </div>



        <div class="contact-container">


            <!-- =====================================
                 CONTACT INFORMATION
            ====================================== -->

            <div class="contact-info">

                <h2>
                    Contact Information
                </h2>

                <p class="contact-intro">

                    We're here to make your Little Treats
                    experience even sweeter.

                </p>


                <div class="contact-item">

                    <div class="contact-icon" aria-hidden="true">
                        📍
                    </div>

                    <div>

                        <h3>
                            Location
                        </h3>

                        <p>
                            Little Treats Bakery
                        </p>

                    </div>

                </div>


                <div class="contact-item">

                    <div class="contact-icon" aria-hidden="true">
                        📞
                    </div>

                    <div>

                        <h3>
                            Phone
                        </h3>

                        <p>
                            +94 77 123 4567
                        </p>

                    </div>

                </div>


                <div class="contact-item">

                    <div class="contact-icon" aria-hidden="true">
                        ✉️
                    </div>

                    <div>

                        <h3>
                            Email
                        </h3>

                        <p>
                            littletreats@example.com
                        </p>

                    </div>

                </div>


                <div class="contact-item">

                    <div class="contact-icon" aria-hidden="true">
                        🕒
                    </div>

                    <div>

                        <h3>
                            Opening Hours
                        </h3>

                        <p>
                            Monday - Saturday
                        </p>

                        <p>
                            9:00 AM - 7:00 PM
                        </p>

                    </div>

                </div>


                <div class="contact-note">

                    <strong>
                        Freshly Baked Happiness ❤️
                    </strong>

                    <p>
                        Thank you for choosing Little Treats Bakery.
                    </p>

                </div>

            </div>



            <!-- =====================================
                 CONTACT FORM
            ====================================== -->

            <div class="contact-form">

                <div class="form-header">

                    <span class="section-label">
                        Send Us A Message
                    </span>

                    <h2>
                        How Can We Help?
                    </h2>

                    <p>
                        Fill out the form below and we'll
                        get back to you as soon as possible.
                    </p>

                </div>


                <?php if ($message !== "") { ?>

                    <div
                        class="message <?php echo htmlspecialchars($message_type); ?>"
                    >

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php } ?>


                <form
                    method="POST"
                    action="contact.php"
                >

        <?php echo csrf_field(); ?>


                    <!-- NAME -->

                    <div class="form-group">

                        <label for="name">
                            Your Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Enter your full name"
                            maxlength="100"
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
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="Enter your email address"
                            maxlength="150"
                            required
                        >

                    </div>



                    <!-- SUBJECT -->

                    <div class="form-group">

                        <label for="subject">
                            Subject
                        </label>

                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            value="<?php echo htmlspecialchars($subject); ?>"
                            placeholder="What can we help you with?"
                            maxlength="200"
                            required
                        >

                    </div>



                    <!-- MESSAGE -->

                    <div class="form-group">

                        <label for="message">
                            Your Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            maxlength="2000"
                            placeholder="Write your message here..."
                            required
                        ><?php echo htmlspecialchars($user_message); ?></textarea>

                    </div>



                    <!-- BUTTON -->

                    <button
                        type="submit"
                        class="contact-button"
                    >

                        Send Message

                        <span>
                            →
                        </span>

                    </button>

                </form>

            </div>

        </div>

    </section>

</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>