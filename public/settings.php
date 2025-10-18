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
    <title>Settings</title>
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
            <h4>Settings</h4>
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

            <div class="profile-main-container">
                <!-- Profile Picture -->
                <div class="profile-picture-section">
                    <img src="../uploads/profilepic2.png" alt="Profile Picture" class="profile-pic">
                    <button class="btn-upload" onclick="alert('Upload feature not implemented')">
                        <i class="fas fa-upload"></i> Change Picture
                    </button>
                </div>

                <!-- Personal Info -->
                <div class="profile-info-section">
                    <h2>User Profile</h2>

                    <div class="profile-field">
                        <label for="fullName">Full Name:</label>
                        <input type="text" id="fullName" value="<?= htmlspecialchars($userData['FullName'] ?? 'John Doe') ?>">
                    </div>

                    <div class="profile-field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($userData['Email'] ?? 'user@email.com') ?>">
                    </div>

                    <div class="profile-field">
                        <label for="password">Password:</label>
                        <input type="password" id="password" placeholder="Enter new password">
                    </div>

                    <div class="profile-field">
                        <label for="birthdate">Birthdate:</label>
                        <input type="date" id="birthdate" value="<?= htmlspecialchars($userData['Birthdate'] ?? '') ?>">
                    </div>

                    <div class="profile-field">
                        <label for="gender">Gender:</label>
                        <input type="text" id="gender" value="<?= htmlspecialchars($userData['Sex'] ?? 'Not specified') ?>" readonly>
                    </div>

                    <button class="btn-save" onclick="alert('Save feature not implemented')">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>

            <style>
                .profile-main-container {
                    display: flex;
                    gap: 2rem;
                    padding: 2rem;
                    background-color: #f8f9fa;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
                }

                .profile-picture-section {
                    flex: 1;
                    text-align: center;
                }

                .profile-pic {
                    width: 150px;
                    height: 150px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 3px solid #2767c0;
                }

                .btn-upload {
                    margin-top: 10px;
                    background-color: #2767c0;
                    color: white;
                    border: none;
                    padding: 8px 12px;
                    border-radius: 3px;
                    cursor: pointer;
                    font-size: 0.9rem;
                }

                .btn-upload i {
                    margin-right: 5px;
                }

                .profile-info-section {
                    flex: 2;
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }

                .profile-info-section h2 {
                    margin-bottom: 1rem;
                    color: #1547b3;
                }

                .profile-field {
                    display: flex;
                    flex-direction: column;
                }

                .profile-field label {
                    font-weight: 600;
                    margin-bottom: 0.3rem;
                }

                .profile-field input {
                    padding: 8px 10px;
                    border-radius: 6px;
                    border: 1px solid #ccc;
                    font-size: 1rem;
                }

                .profile-field input:focus {
                    border-color: #2767c0;
                    outline: none;
                }

                .btn-save {
                    align-self: flex-start;
                    background-color: #2767c0;
                    color: white;
                    border: none;
                    padding: 10px 16px;
                    border-radius: 3px;
                    cursor: pointer;
                    font-size: 1rem;
                    margin-top: 1rem;
                }

                .btn-save i {
                    margin-right: 5px;
                }

                @media (max-width: 768px) {
                    .profile-main-container {
                        flex-direction: column;
                        align-items: center;
                    }

                    .profile-info-section {
                        width: 100%;
                    }
                }
            </style>


        </main>
    </div>

</body>



</html