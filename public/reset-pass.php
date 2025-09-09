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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <h2 id="login">Reset Your Password</h2>
            <?php if (!empty($message)) echo "<p>$message</p>"; ?>
            <form action="reset-pass.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-key left-icon"></i>
                    <input class="inputs" type="text" name="reset_code" placeholder="Enter Reset Code" required>
                </div>

                <!-- New Password -->
                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input class="inputs" type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
                    <i class="fas fa-eye toggle-password" data-target="new_password"></i>
                </div>

                <!-- Confirm Password -->
                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input class="inputs" type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                    <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                </div>

                <button type="submit">Reset Password</button>
            </form>
        </div>
        <script>
            // Toggle password visibility for multiple fields
            document.querySelectorAll(".toggle-password").forEach(toggle => {
                toggle.addEventListener("click", function() {
                    const input = document.getElementById(this.dataset.target);
                    const type = input.type === "password" ? "text" : "password";
                    input.type = type;

                    this.classList.toggle("fa-eye");
                    this.classList.toggle("fa-eye-slash");
                });
            });
        </script>
    </div>
</body>

</html>