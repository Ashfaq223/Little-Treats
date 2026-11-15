<?php

require_once __DIR__ . "/includes/bootstrap.php";

$meta_description = "How Little Treats Bakery collects, uses and protects your personal information.";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Privacy Policy - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="legal-page">
    <h2>Privacy Policy</h2>

    <p>
        This page explains what personal information Little Treats Bakery
        collects when you use this website and how we use it.
    </p>

    <h3>Information we collect</h3>
    <ul>
        <li>Account details: your name, email address, phone number, delivery address and a securely hashed password.</li>
        <li>Order details: the items you order, delivery or pickup choice, preferred date and time, notes, payment method and any bank transfer slip you upload.</li>
        <li>Loyalty details: your points balance and rewards you redeem.</li>
        <li>Messages you send through the contact form.</li>
        <li>Technical information needed to keep the site secure, such as your IP address in login-attempt records.</li>
    </ul>

    <h3>How we use it</h3>
    <ul>
        <li>To prepare, deliver and confirm your orders and contact you about them.</li>
        <li>To run the loyalty rewards programme.</li>
        <li>To reply to your questions and to help you recover your account.</li>
        <li>To protect the site against misuse.</li>
    </ul>

    <h3>Cookies</h3>
    <p>
        We use a single session cookie that keeps you signed in and remembers
        your cart. It does not track you across other websites.
    </p>

    <h3>Sharing</h3>
    <p>
        We do not sell your information. It is only shared with people who
        need it to fulfil your order (for example a delivery driver), or when
        the law requires it.
    </p>

    <h3>Your choices</h3>
    <p>
        You can update your details at any time from your profile. To ask
        for your account and data to be deleted, please
        <a href="contact.php">contact us</a>.
    </p>
</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>