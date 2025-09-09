<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/database.php';
require 'vendor/autoload.php';

$pdo = pdo_connect_mysql();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $reset_code = random_int(100000, 999999);

        $updateStmt = $pdo->prepare("UPDATE clients SET ResetCode = ? WHERE Email = ?");
        $updateStmt->execute([$reset_code, $email]);

        if ($updateStmt->rowCount() > 0) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jaymichaelcastillo18@gmail.com';
                $mail->Password = 'dmjh epxq wsiw cwnm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('jaymichaelcastillo18@gmail.com', 'University Clinic');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset Code';
                $mail->Body = "Your reset code is: <b>$reset_code</b>";

                $mail->send();

                $_SESSION['reset_email'] = $email;

                header('Location: reset-pass.php');
                exit;
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Failed to update reset code in the database.";
        }
    } else {
        $message = "Email not found!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <title>University Clinic Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="assets/js/script.js" defer></script>
</head>
<style>
    .input-group {
        position: relative;
        margin-bottom: 15px;
    }

    /* Left-side icons (email, lock) */
    .input-group i.left-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 16px;
    }

    /* Right-side eye toggle */
    .input-group i.toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #888;
        font-size: 16px;
    }

    .input-group i.toggle-password:hover {
        color: #333;
    }

    /* Add padding for both icons */
    .input-group input {
        width: 100%;
        padding: 10px 40px;
        /* space for left + right icons */
        padding-left: 35px;
        /* extra left padding for left icon */
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
</style>

<body onload="autoScrollToLogin()">
    <div class="container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Online</h1>
                <p id="loginsmg"></p>
            </div>
        </div>

        <div class="right-section">
            <h2 id="login">Request Password Reset</h2>

            <?php if (!empty($message)) echo "<p>$message</p>"; ?>

            <form action="request_reset.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope left-icon"></i>
                    <input type="email" class="inputs" name="email" placeholder="Email" required>
                </div>
                <button type="submit">Send Reset Code</button>
                <p>Remember your password? <a href="index.php">Sign in</a></p>
            </form>
        </div>
    </div>
</body>

</html>