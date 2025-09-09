<?php
session_start();
require 'config/database.php';

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
    <title>University Clinic Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
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

                <div class="input-group">
                    <i class="fas fa-user left-icon"></i>
                    <input type="text" class="inputs" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock left-icon"></i>
                    <input type="password" class="inputs" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword" data-target="password"></i>
                </div>

                <button type="submit">Create Account</button>
                <p>Already have an account? <a href="index.php">Sign in</a></p>
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