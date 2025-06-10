<?php
require('C:/Xampp.f/htdocs/UC-System/config/database.php');

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
                <img id="lspulogo" src="assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Profile Management System</h1>
                <p id="loginsmg"></p>
            </div>
        </div>

        <div class="right-section" id="login-section">
            <h2 id="login">Admin Login</h2>
            <form action="index.php" method="POST">
                <input type="text" class="inputs" name="username" placeholder="Username" required>
                <input type="password" class="inputs" name="password" placeholder="Password" required>
                <button type="submit" class="buttons">Login</button>
                <p><a href="register.php" class="register-link">Don't have an account? Sign up here</a></p> 
            </form>
        </div>
    </div>
</body>

</html>