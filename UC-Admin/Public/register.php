<?php
session_start();
require('C:/Xampp.f/htdocs/UC-System/config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $pdo = pdo_connect_mysql();
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $existingAdmin = $stmt->fetch();

    if ($existingAdmin) {
        $error = "This username is already taken. Please choose another.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");

        if ($stmt->execute([$username, $hashed_password])) {
            $_SESSION['admin_id'] = $pdo->lastInsertId();
            header('Location: Dashboard.php'); // Redirect to admin area
            exit;
        } else {
            $error = "Error creating admin account. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Clinic Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Profile Management System</h1>
            </div>
        </div>
        <div class="right-section">
            <h2 id="signup">Admin Sign up</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="POST">

                <input class="inputs" type="text" name="username" placeholder="Username" required>
                <input class="inputs" type="password" name="password" placeholder="Password" required>

                <button type="submit">Create Account</button>
                <p>Already have an account? <a href="index.php">Sign in</a></p>
            </form>
        </div>
    </div>
</body>

</html>