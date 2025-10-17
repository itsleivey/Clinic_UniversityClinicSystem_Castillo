<?php
session_start();
require 'config/database.php';

$error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } else {
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
                header('Location: index.php?signup=success');
                exit;
            } else {
                $error = "Error creating admin account. Please try again.";
            }
        }
    }
}
?>

<style>
    .input-group {
        position: relative;
        margin-bottom: 5px;
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

    /* Error message styling */
    .error-message {
        color: #d93025;
        font-size: 15px;
        margin-top: 10px;
        text-align: left;
    }

    .password-error {
        color: #d93025;
        font-size: 13px;
        margin-top: 5px;
        margin-bottom: 10px;
        text-align: left;
        padding-left: 5px;
    }

    /* Input error state */
    .input-error {
        border-color: #d93025 !important;
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
                    <input type="password" class="inputs <?php echo !empty($password_error) ? 'input-error' : ''; ?>"
                        id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword" data-target="password"></i>
                </div>


                <?php if (!empty($password_error)): ?>
                    <div class="password-error"><?php echo htmlspecialchars($password_error); ?></div>
                <?php endif; ?>

                <button type="submit">Create Account</button>
                <p>Already have an account? <a class="register-link" href="index.php">Sign in</a></p>
            </form>
        </div>
    </div>
    <script>
        document.querySelectorAll(".toggle-password").forEach(toggle => {
            toggle.addEventListener("click", function() {
                const input = document.getElementById(this.dataset.target);
                const type = input.type === "password" ? "text" : "password";
                input.type = type;

                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const errorElement = this.parentElement.nextElementSibling;

            if (password.length > 0 && password.length < 8) {
                if (!errorElement || !errorElement.classList.contains('password-error')) {
                    const newError = document.createElement('div');
                    newError.className = 'password-error';
                    newError.textContent = 'Password must be at least 8 characters long.';
                    this.parentElement.after(newError);
                } else {
                    errorElement.textContent = 'Password must be at least 8 characters long.';
                }
                this.classList.add('input-error');
            } else {

                if (errorElement && errorElement.classList.contains('password-error')) {
                    errorElement.remove();
                }
                this.classList.remove('input-error');
            }
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                e.preventDefault();

                const errorElement = document.getElementById('password').parentElement.nextElementSibling;
                if (!errorElement || !errorElement.classList.contains('password-error')) {
                    const newError = document.createElement('div');
                    newError.className = 'password-error';
                    newError.textContent = 'Password must be at least 8 characters long.';
                    document.getElementById('password').parentElement.after(newError);
                }
                document.getElementById('password').classList.add('input-error');
            }
        });
    </script>
</body>

</html>