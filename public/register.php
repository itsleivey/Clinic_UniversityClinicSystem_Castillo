<?php
session_start();

require('C:/Xampp.f/htdocs/UC-System/config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['consent']) || $_POST['consent'] !== 'agree') {
        $error = "You must agree to the data privacy consent to register";
    } else {
        $firstname = trim($_POST['firstname']);
        $lastname  = trim($_POST['lastname']);
        $email     = trim($_POST['email']);
        $password  = trim($_POST['password']);

        // Check if the email already exists in the database
        $pdo  = pdo_connect_mysql();
        $stmt = $pdo->prepare("SELECT * FROM Clients WHERE Email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error = "This email is already registered. Please use a different email.";
        } else {
            // If email doesn't exist, proceed with registration
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO Clients (Firstname, Lastname, Email, Password)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$firstname, $lastname, $email, $hashed_password])) {
                $clientId = $pdo->lastInsertId();
                $_SESSION['ClientID'] = $clientId;
                header('Location: Profile.php');
                exit;
            } else {
                $error = "Error creating account. Please try again.";
            }
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
    <script src="assets/js/script.js" defer></script>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: #f8f9fa;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 85%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
            text-align: center;
        }

        .modal-body {
            padding: 10px 0;
            line-height: 1.6;
            color: #495057;
        }

        .modal-body ol {
            padding-left: 20px;
        }

        .modal-body li {
            margin-bottom: 10px;
        }

        .consent-btn {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 12px 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #3498db;
            color: white;
            text-align: center;
        }

        .consent-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fadbd8;
            border-radius: 4px;
            text-align: center;
        }

        #registerForm {
            display: none;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }

            .consent-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body onload="autoScrollToLogin(); showConsentModal()">
    <div class="container">
        <div class="left-section">
            <div class="overlay">
                <img id="lspulogo" src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo" class="logo">
                <h1 id="welcomesmg">Welcome!<br>To University Clinic Online Form Submission</h1>
                <p id="loginsmg"></p>
            </div>
        </div>
        <div class="right-section">
            <h2 id="signup">Sign Up</h2>
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

                <div class="input-group">
                    <input class="inputs" type="text" name="firstname" placeholder="First Name" required>
                    <input class="inputs" type="text" name="lastname" placeholder="Last Name" required>
                </div>

                <input class="inputs" type="email" name="email" placeholder="Email" required>
                <input class="inputs" type="password" name="password" placeholder="Password" required>

                <button type="submit">Create Account</button>
                <p>Already have an account? <a href="index.php">Sign in</a></p>
            </form>
        </div>
    </div>

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