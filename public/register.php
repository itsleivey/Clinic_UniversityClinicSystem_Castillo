<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['consent']) || $_POST['consent'] !== 'agree') {
        $error = "You must agree to the data privacy consent to register";
    } else {
        $firstname = trim($_POST['firstname']);
        $lastname  = trim($_POST['lastname']);
        $email     = trim($_POST['email']);
        $sex       = trim($_POST['sex']);
        $dob       = trim($_POST["dob"]);
        $password  = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // 🧩 VALIDATION SECTION
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (empty($sex) || empty($dob)) {
            $error = "Please select your sex and birth date.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // 🧩 DATABASE CHECK
            $pdo  = pdo_connect_mysql();
            $stmt = $pdo->prepare("SELECT * FROM Clients WHERE Email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $error = "This email is already registered. Please use a different email.";
            } else {
                // 🧩 INSERT NEW CLIENT
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO Clients (Firstname, Lastname, Email, Sex, Birthdate, Password)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                if ($stmt->execute([$firstname, $lastname, $email, $sex, $dob, $hashed_password])) {
                    $clientId = $pdo->lastInsertId();
                    $_SESSION['ClientID'] = $clientId;
                    header('Location: client_type_selection.php');
                    exit;
                } else {
                    $error = "Error creating account. Please try again.";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

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
        font-size: clamp(0.75rem, 1vw, 1rem);
        font-family: "Poppins", sans-serif;
        font-weight: 350;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Clinic Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <script src="assets/js/script.js" defer></script>
    <script src="UC-Client/assets/js/validation.js" defer></script>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body onload="autoScrollToLogin(); showConsentModal()">
    <div class="register-container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Online Form Submission</h1>
                <p id="loginsmg"></p>
            </div>
        </div>
        <div class="register-right-section">
            <div class="login-header">
                <h2 id="login">Create your account</h2>
                <!--  <p class="login-subtitle">Securely access your medical records and manage your health profile online.</p>-->
            </div>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- The Consent Modal -->
            <div id="consentModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>DATA PRIVACY CONSENT</h2>
                    </div>
                    <div class="modal-body">
                        <p>In compliance with the Data Privacy Act of 2012, (DPA) and its Implementing Rules and Regulations (IRR) effective on August 24, 2016.</p>

                        <p>By registering, you agree and authorize University Clinic to:</p>

                        <ol>
                            <li>Collect your personal information for University Clinic services.</li>
                            <li>Retain your information for a period of five years from the date of registration or until you submit a written cancellation of this consent, whichever is earlier. Your information will be deleted/destroyed after this period.</li>
                            <li>Contact you about future University Clinic events and services using the provided contact information.</li>
                        </ol>

                        <p>You acknowledge that you have read and understood this consent form and that you voluntarily agree to its terms.</p>
                    </div>
                    <button class="consent-btn" onclick="agreeConsent()">I AGREE TO THESE TERMS</button>
                </div>
            </div>

            <form id="registerForm" action="register.php" method="POST">
                <input type="hidden" name="consent" id="consentField" value="">

                <div class="name-input-group">
                    <div class="input-container">
                        <label for="firstname">First Name</label>
                        <input class="inputs" type="text" id="firstname" name="firstname" placeholder="First name" required>
                    </div>
                    <div class="input-container">
                        <label for="lastname">Last Name</label>
                        <input class="inputs" type="text" id="lastname" name="lastname" placeholder="Last name" required>
                    </div>
                </div>

                <div class="age-sex-input-group">
                    <div class="input-container">
                        <label for="sex">Sex</label>
                        <select class="inputs" id="sex" name="sex" required>
                            <option value="" disabled selected>Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="input-container">
                        <label for="dob">Date of Birth</label>
                        <input type="date" class="inputs" id="dob" name="dob" required>
                    </div>
                </div>
                <div id="dobError" class="error-message error-hidden"></div>
                <div class="input-container">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope left-icon"></i>
                        <input type="email" class="inputs" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="parent-pass-input-group">
                    <div class="input-container">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock left-icon"></i>
                            <input type="password" class="inputs" id="password" name="password" placeholder="••••••" required>
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="input-container">
                        <label for="confirm-password">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock left-icon"></i>
                            <input type="password" class="inputs" id="confirm-password" name="confirm_password" placeholder="••••••" required>
                            <i class="fas fa-eye toggle-password" id="confirm-togglePassword"></i>
                        </div>
                    </div>
                </div>
                <div id="passwordError" class="error-message error-hidden"></div>
                <div id="confirmPasswordError" class="error-message error-hidden"></div>

                <button type="submit">Create Account</button>
                <p>Already have an account? <a class="register-link" href="index.php">Sign in</a></p>
            </form>

        </div>
    </div>
    <script>
        const passwordInput = document.getElementById("password");
        const confirmpassInput = document.getElementById("confirm-password");
        const togglePassword = document.getElementById("togglePassword");
        const confirmtogglepass = document.getElementById("confirm-togglePassword");

        togglePassword.addEventListener("click", function() {
            const type = passwordInput.type === "password" ? "text" : "password";
            passwordInput.type = type;
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });

        confirmtogglepass.addEventListener("click", function() {
            const type = confirmpassInput.type === "password" ? "text" : "password";
            confirmpassInput.type = type;
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    </script>
    <script>
        function showConsentModal() {
            document.getElementById('consentModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function agreeConsent() {
            document.getElementById('consentField').value = 'agree';
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('consentModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }

        // Prevent closing by clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('consentModal')) {
                return false;
            }
        }

        // Prevent closing with ESC key
        document.onkeydown = function(evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) {
                return false;
            }
        };
    </script>

</body>

</html>