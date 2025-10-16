<?php
require '../config/database.php';

function verify_password($password, $stored_hash)
{
    return password_verify($password, $stored_hash);
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = pdo_connect_mysql();

        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM Clients WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && verify_password($password, $user['Password'])) {
            $_SESSION['ClientID'] = $user['ClientID'];
            header("Location: Profile.php");
            exit();
            echo "<script>alert('Invalid email or password');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database connection failed: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <title>University Clinic Login</title>
    <link rel="stylesheet" href="styles.css">
    <script src="assets/js/script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: "Montserrat";
            src: url("assets/fonts/Montserrat/Montserrat-VariableFont_wght.ttf") format("woff2");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: "Poppins";
            src: url("assets/fonts/Poppins/Poppins-Medium.ttf") format("woff2");
            font-weight: 400;
            font-style: normal;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome to <br>University Clinic Online Form</h1>
                <p id="loginsmg">Securely access your medical records, view your examinations, and manage your health profile online.</p>
            </div>
        </div>

        <div class="right-section" id="login-section">
            <div class="login-header">
                <h2 id="login">Login</h2>
                <p class="login-subtitle">Securely access your medical records and manage your health profile online.</p>
            </div>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope left-icon"></i>
                    <input type="email" class="inputs" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" class="inputs" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>

                <button type="submit" class="buttons">Login</button>

                <p><a href="request_reset.php" class="register-link">Forgot Password?</a></p>
                <p><a href="register.php" class="register-link">Don't have an account? Sign up here</a></p>
            </form>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function() {
            const type = passwordInput.type === "password" ? "text" : "password";
            passwordInput.type = type;
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    </script>
</body>


</html>