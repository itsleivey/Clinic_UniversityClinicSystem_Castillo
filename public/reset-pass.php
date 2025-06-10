<?php
session_start();

require '../config/database.php';

$pdo = pdo_connect_mysql();
$message = '';

if (!isset($_SESSION['reset_email'])) {
    header('Location: request_reset.php'); // If no email stored, go back
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['reset_email'];
    $reset_code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE Email = ? AND ResetCode = ?");
        $stmt->execute([$email, $reset_code]);
        $user = $stmt->fetch();

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE clients SET Password = ?, ResetCode = NULL WHERE Email = ?");
            $stmt->execute([$hashed_password, $email]);

            unset($_SESSION['reset_email']);
            $message = "Password reset successfully! <a href='index.php'>Sign In</a>";
        } else {
            $message = "Invalid reset code!";
        }
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
    <script src="assets/js/script.js" defer></script>
</head>

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
            <h2  id="login">Reset Your Password</h2>
            <?php if (!empty($message)) echo "<p>$message</p>"; ?>
            <form action="reset-pass.php" method="POST">
                <input class="inputs" type="text" name="reset_code" placeholder="Enter Reset Code" required>
                <input class="inputs" type="password" name="new_password" placeholder="Enter New Password" required>
                <input class="inputs" type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</body>

</html>