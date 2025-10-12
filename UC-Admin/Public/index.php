<?php
require 'config/database.php';

function verify_password($password, $stored_hash)
{
    return password_verify($password, $stored_hash);
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = pdo_connect_mysql();

        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM Admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && verify_password($password, $admin['password'])) {
            $_SESSION['AdminID'] = $admin['id'];
            header("Location: Dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid username or password');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database connection failed: " . $e->getMessage() . "');</script>";
    }
}
?>

<style>
    .input-group {
        position: relative;
        margin-bottom: 15px;
    }

    /* Left-side icons (code, lock) */
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

    /* Inputs */
    .input-group input {
        width: 100%;
        padding: 10px 40px;
        padding-left: 35px;
        /* space for left icon */
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <title>University Clinic Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <script src="assets/js/script.js" defer></script>
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
</head>

<body onload="autoScrollToLogin()">
    <div class="container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Profile Management System</h1>
                <p id="loginsmg"></p>
            </div>
        </div>

        <div class="right-section" id="login-section">
            <h2 id="login">Admin Login</h2>
            <form action="index.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-user left-icon"></i>
                    <input type="text" class="inputs" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" class="inputs" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword" data-target="password"></i>
                </div>

                <button type="submit" class="buttons">Login</button>
                <p>Don't have an account? <a href="register.php" class="register-link"> Sign up here</a></p>
            </form>
        </div>
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
</body>

</html>