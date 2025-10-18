<?php
require_once '../config/database.php';

require_once '../Profile/Profile_db.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="UC-Client/assets/css/new_profile_style.css">

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
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="UC-Client/assets/js/new_profile_function.js" defer></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <title>Profile</title>
</head>

<body>

    <div class="header">
        <img src="UC-Client/assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title"> University Clinic </span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="UC-Client/assets/images/menu.png">
        </button>
        <div class="page-title">
            <h4>Profile</h4>
        </div>

        <!-- Profile dropdown -->
        <div class="profile-container">

            <img id="profileBtn" src="../uploads/profilepic2.png" alt="Profile Picture">

            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-item">
                    <i class="fas fa-envelope"></i> user@email.com
                </div>
                <div class="profile-item">
                    <i class="fas fa-cog"></i> Settings
                </div>
                <div class="profile-item" onclick="document.getElementById('logoutForm').submit()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </div>
                <form id="logoutForm" action="logout.php" method="post"></form>
            </div>
        </div>
    </div>

    <div class="main-container">
        <nav class="navbar">
            <a href="Freshman_Profile.php">
                <button class="buttons" id="medicalBtn">
                    <i class="fas fa-file-lines button-icon-nav"></i>
                    <span class="nav-text">Medical Forms</span>
                </button>
            </a>
            <a href="Settings.php">
                <button class="active-buttons" id="settingBtn">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </button>
            </a>
        </nav>

        <main class="content" loading="lazy">
        </main>
    </div>

</body>



</html