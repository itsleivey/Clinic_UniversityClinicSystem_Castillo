<?php
require_once 'config/database.php';
require_once 'manageclients.dbf/get_user.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}


$students = fetchStudents();
$faculties = fetchFaculty();
$personnel = fetchPersonnel();
$freshman =  fetchFreshman();
$newpersonnel = fetchNewPersonnel();

if (isset($_GET['error'])) {
    echo '<div class="alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
}


$pdo = pdo_connect_mysql();
// Get admin ID from session
$admin_id = $_SESSION['user_id'] ?? null;

// Query admin table safely
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = :adminID");
$stmt->execute(['adminID' => $admin_id]);

$current_user = $stmt->fetch(PDO::FETCH_ASSOC); // false if no row found

$role = $current_user['user_type'] ?? 'Admin';
$name = $current_user['username'] ?? '';
$email = $current_user['email'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="assets/css/manageusers.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/manageclients.js" defer></script>

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

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <title>Manage Profile</title>
</head>
</head>

<body>
    <div class="header">
        <img src="assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title"> University Clinic </span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="assets/images/menu.png">
        </button>
        </button>
        <div class="page-title">
            <h4>Manage Patients</h4>
        </div>
        <div class="profile-container">
            <img id="profileBtn" src="<?= htmlspecialchars(!empty($rec['profilePicturePath']) ? '../../uploads/' . $rec['profilePicturePath'] : '../../uploads/profilepic2.png') ?>" class="profile-pic" alt="Profile">
            <div class="profile-dropdown" id="profileDropdown">
                <div class="fixed-profile-item">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($current_user['username'] ?? 'Admin') ?>
                </div>
                <a href="Clinic_settings.php">
                    <div class="fixed-profile-item"><i class="fas fa-cog"></i> Settings</div>
                </a>
                <div class="profile-item" onclick="document.getElementById('logoutForm').submit()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </div>
                <form id="logoutForm" action="admin_logout.php" method="post"></form>
            </div>
        </div>
    </div>
    <script>
        const profileBtn = document.getElementById("profileBtn");
        const profileDropdown = document.getElementById("profileDropdown");

        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            profileDropdown.style.display =
                profileDropdown.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.style.display = "none";
            }
        });
    </script>

    <div class="main-container">
        <nav class="navbar">
            <a href="Dashboard.php">
                <button class="buttons" id="dashboardBtn">
                    <img src="assets/images/dashboard_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Dashboard</span>
                </button>
            </a>
            <a href="Manage_Clients.php">
                <button class="buttons" id="manageclientsBtn">
                    <img src="assets/images/manageclients_icon2.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Manage Patients</span>
                </button>
            </a>
            <a href="Activity_logs.php">
                <button class="buttons" id="activitylogsBtn">
                    <img src="assets/images/activity_logs_icon.png" class="button-icon-nav" style="width: 23px; height: 23px" loading="lazy">
                    <span class="nav-text">Activity Logs</span>
                </button>
            </a>
            <a href="Manage_Staffs.php">
                <button class="buttons" id="managestaffsBtn">
                    <img src="assets/images/manageclients_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Manage Staffs</span>
                </button>
            </a>
            <a href="Data_Management.php">
                <button class="buttons" id="datamanagementBtn">
                    <img src="assets/images/data_manage_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Data Management</span>
                </button>
            </a>
            <!--
            <a href="Calendar.html">
                <button class="buttons" id="calendarBtn">
                    <img src="assets/images/calendar_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Caledar</span>
                </button>
            </a>
    -->
            <a href="admin_logout.php">
                <button class="buttons" id="logoutbtn">
                    <img src="assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </a>
        </nav>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <main class="content" id="mainContent">

            <div id="normalViewContainer">
                <div class="clients-table-container">
                    <div class="table-header-controls">
                        <div class="table-left-controls">

                            <div class="search-input-container rectangular-search">
                                <div class="input-wrapper">
                                    <i class="fas fa-search search-icon-inset"></i>
                                    <input type="text"
                                        id="searchInput"
                                        name="id_filter"
                                        placeholder="Search ID, Name, Email, Department, ClientType"
                                        value="<?= htmlspecialchars($_GET['id_filter'] ?? '') ?>"
                                        maxlength="400">
                                </div>
                            </div>
                            <div class="select-wrapper">
                                <i class="fas fa-filter"></i>
                                <select id="clientTypeDropdown" class="client-type-dropdown">
                                    <option value="Students">Regular Students</option>
                                    <option value="Freshman">Incoming Freshman Students</option>
                                    <option value="Faculty">Teaching Personnels</option>
                                    <option value="Personnel">Non-Teaching Personnels</option>
                                    <option value="NewPersonnel">Newly Hired Personnels</option>
                                </select>
                            </div>
                        </div>

                        <button type="button" class="add-btn" onclick="openAddPatientModal()">
                            <i class="fas fa-user-plus"></i> Add Patient
                        </button>
                    </div>

                    <div id="addPatientModal" class="modal">
                        <div id="add-patient-modal" class="modal-content">
                            <div class="loading-sec">
                                <div id="formProgressBar" class="form-progress-bar"></div>
                            </div>

                            <div class="formsection">
                                <span onclick="closeAddPatientModal()" class="close-btn">&times;</span>
                                <h3 class="modal-title">
                                    <i class="fas fa-user-plus title-icon"></i> Add Patient
                                </h3>

                                <form method="POST" action="manageclients.dbf/add-patient.php" id="addPatientForm">
                                    <div class="form-group-row" style="display: flex; gap: 15px;">
                                        <div id="fname" class="form-group" style="flex: 1;">
                                            <label><i class="fas fa-user icon-blue"></i> First Name</label>

                                            <input type="text" name="firstname" placeholder="Ex: Juan" class="form-control" required autocomplete="off">
                                        </div>

                                        <div id="lname" class="form-group" style="flex: 1;">
                                            <label><i class="fas fa-user icon-blue"></i> Last Name</label>

                                            <input type="text" name="lastname" placeholder="Ex: Cruz" class="form-control" required autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="form-group-row" style="display: flex; gap: 15px;">
                                        <div class="form-group" style="flex: 1;">
                                            <label><i class="fas fa-venus-mars icon-blue"></i> Gender</label>
                                            <select name="sex" class="form-control" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>

                                        <div class="form-group" style="flex: 1;">
                                            <label><i class="fas fa-calendar-alt icon-blue"></i> Birthdate</label>
                                            <input type="date" name="birthdate" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fas fa-user icon-blue"></i> Username</label>
                                        <div id="emailError" class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i> Username already exists
                                        </div>
                                        <input type="text" name="username" id="usernameInput" class="form-control" required placeholder="Enter patient's username">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-lock icon-blue"></i> Password</label>
                                        <div class="pass-input-wrapper">
                                            <i class="fas fa-lock input-icon"></i>
                                            <input type="password" name="password" id="passwordInput" class="form-control" required minlength="8" placeholder="Enter a strong password" readonly>
                                            <i id="togglePassword" class="fas fa-eye toggle-password"></i>
                                        </div>
                                        <div id="passwordStrength" class="password-strength">
                                            Password will be automatically generated based on user input (e.g., name or email).
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fas fa-users icon-blue"></i> Client Type</label>
                                        <select name="client_type" id="clientTypeSelect" class="form-control" onchange="toggleDepartment()" required>
                                            <option value="">Select Type</option>
                                            <option value="Freshman">Incoming Freshman Student</option>
                                            <option value="Student">Student (Enrolled/Regular)</option>
                                            <option value="Faculty">Teaching Personnel</option>
                                            <option value="Personnel">Non-Teaching Personnel</option>
                                            <option value="NewPersonnel">New Personnel</option>
                                            <option value="Default">Default</option>
                                        </select>
                                    </div>

                                    <div id="departmentField" class="form-group" style="display: none;"
                                        onmousedown="event.stopPropagation();"
                                        onclick="event.stopPropagation();"
                                        onchange="event.stopPropagation();">
                                        <label for="department"><i class="fas fa-building-columns icon-blue"></i> Department</label>
                                        <select id="department" name="department" class="form-control"
                                            onmousedown="event.stopPropagation();"
                                            onclick="event.stopPropagation(); event.stopImmediatePropagation();"
                                            onchange="event.stopPropagation(); event.stopImmediatePropagation();">
                                            <option value="">Select a Department</option>
                                            <option value="None">None</option>
                                            <option value="College of Computer Studies">College of Computer Studies</option>
                                            <option value="College of Food Nutrition and Dietetics">College of Food Nutrition and Dietetics</option>
                                            <option value="College of Teacher Education">College of Teacher Education</option>
                                            <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                                            <option value="College of Business Administration and Accountancy">College of Business Administration and Accountancy</option>
                                            <option value="College of Criminal Justice Education">College of Criminal Justice Education</option>
                                            <option value="College of Fisheries">College of Fisheries</option>
                                            <option value="College of Hospitality Management and Tourism">College of International Hospitality Management and Tourism</option>
                                        </select>
                                    </div>

                                    <button type="submit" id="saveButton" class="btn-save">
                                        <i class="fas fa-save"></i> Save Patient
                                    </button>
                                </form>
                            </div>
                            <script>
                                const clientDropdown = document.getElementById('clientTypeDropdown');

                                clientDropdown.addEventListener('change', function() {
                                    localStorage.setItem('lastClientTab', this.value);
                                });
                                window.addEventListener('DOMContentLoaded', () => {
                                    const lastTab = localStorage.getItem('lastClientTab');
                                    if (lastTab) {
                                        clientDropdown.value = lastTab;

                                        // Optionally, trigger change event to update content
                                        clientDropdown.dispatchEvent(new Event('change'));
                                    }
                                });

                                const form = document.getElementById("addPatientForm");
                                const progressBar = document.getElementById("formProgressBar");
                                const saveButton = document.getElementById("saveButton");

                                function startProgress() {
                                    progressBar.style.width = "0%";
                                    progressBar.style.display = "block";
                                    let width = 0;
                                    const interval = setInterval(() => {
                                        if (width < 90) {
                                            width += Math.random() * 10;
                                            progressBar.style.width = width + "%";
                                        }
                                    }, 200);
                                    return interval;
                                }

                                function finishProgress(interval) {
                                    clearInterval(interval);
                                    progressBar.style.width = "100%";
                                    setTimeout(() => {
                                        progressBar.style.width = "0%";
                                    }, 500);
                                }

                                form.addEventListener("submit", function(e) {
                                    e.preventDefault();
                                    saveButton.disabled = true;

                                    const interval = startProgress();
                                    const formData = new FormData(form);

                                    fetch(form.action, {
                                            method: "POST",
                                            body: formData
                                        })
                                        .then(res => res.text()) // get raw text first
                                        .then(text => {
                                            finishProgress(interval);
                                            saveButton.disabled = false;

                                            let data;
                                            try {
                                                data = JSON.parse(text); // parse manually
                                            } catch (err) {
                                                console.error("Invalid JSON response:", text);
                                                alert("Server returned invalid JSON. Check console.");
                                                return;
                                            }

                                            if (data.success) {
                                                alert(data.message);
                                                form.reset();
                                                window.location.href = "Manage_Clients.php";
                                            } else {
                                                alert(data.message);
                                            }
                                        })
                                        .catch(err => {
                                            finishProgress(interval);
                                            saveButton.disabled = false;
                                            alert("Submission failed: " + err);
                                        });
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById("usernameInput").addEventListener("blur", generateAutoPassword);
                    document.querySelector("input[name='firstname']").addEventListener("blur", generateAutoPassword);
                    document.querySelector("input[name='lastname']").addEventListener("blur", generateAutoPassword);

                    function generateAutoPassword() {
                        const firstname = document.querySelector("input[name='firstname']").value.trim();
                        const lastname = document.querySelector("input[name='lastname']").value.trim();
                        const email = document.getElementById("usernameInput").value.trim();

                        // If all are empty, do nothing
                        if (!firstname && !lastname && !email) return;

                        // Combine name parts for generation logic
                        const fullname = `${firstname} ${lastname}`.trim();

                        fetch("manageclients.dbf/generate-password.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: `fullname=${encodeURIComponent(fullname)}&email=${encodeURIComponent(email)}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.password) {
                                    const passwordInput = document.getElementById("passwordInput");
                                    passwordInput.value = data.password;
                                    passwordInput.dispatchEvent(new Event("input")); // trigger strength update, if any
                                }
                            })
                            .catch(err => console.error("Password generation failed:", err));
                    }
                </script>

                <script>
                    const passwordInput = document.getElementById("passwordInput");
                    const togglePassword = document.getElementById("togglePassword");

                    togglePassword.addEventListener("click", () => {
                        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                        passwordInput.setAttribute("type", type);

                        togglePassword.classList.toggle("fa-eye");
                        togglePassword.classList.toggle("fa-eye-slash");
                    });
                </script>


                <script>
                    function openAddPatientModal() {
                        document.getElementById('addPatientModal').style.display = 'block';
                    }

                    function closeAddPatientModal() {
                        document.getElementById('addPatientModal').style.display = 'none';
                    }

                    function toggleDepartment() {
                        const type = document.getElementById('clientTypeSelect').value;
                        const depField = document.getElementById('departmentField');
                        if (type === 'Student' || type === 'Freshman' || type === 'Faculty' || type === 'Personnel') {
                            depField.style.display = 'block';
                        } else {
                            depField.style.display = 'none';
                        }
                    }
                </script>
                <div id="loadingModal" class="loading-overlay">
                    <div class="loading-box">
                        <div class="user-preview">
                            <img id="previewImage" src='../../uploads/profilepic2.png' alt="Profile">
                            <div class="user-info">
                                <p id="previewEmail">email@example.com</p>
                                <p id="previewType">User Type</p>
                            </div>
                        </div>

                        <!-- <div class="spinner"></div>-->

                        <p id="loadingText">Processing, please wait...</p>

                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>

                    </div>
                </div>


                <style>
                    .user-preview {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        margin: 10px 0;
                        background: #f5f7fa;
                        padding: 10px;
                        border-radius: 10px;
                        height: 40%;
                    }

                    .user-preview img {
                        width: 45px;
                        height: 45px;
                        border-radius: 50%;
                        object-fit: cover;
                        margin-right: 10px;
                        border: 2px solid #e3e7ee;
                    }

                    .user-info p {
                        margin: 0;
                        font-size: 14px;
                        color: #333;
                    }

                    #previewEmail {
                        font-weight: 600;
                    }

                    #previewType {
                        font-size: 13px;
                        color: #777;
                    }

                    .loading-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.55);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(5px);
                        z-index: 99999;
                    }

                    .loading-box {
                        background: white;
                        width: 420px;
                        padding: 25px;
                        border-radius: 18px;
                        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
                        text-align: center;
                    }

                    .spinner {
                        width: 42px;
                        height: 42px;
                        border: 5px solid #d1d1d1;
                        border-top-color: #2e68cc;
                        border-radius: 50%;
                        margin: 0 auto 15px;
                        animation: spin 0.8s linear infinite;
                    }

                    @keyframes spin {
                        to {
                            transform: rotate(360deg);
                        }
                    }

                    .progress-bar {
                        width: 100%;
                        height: 12px;
                        background: #e5e5e5;
                        border-radius: 10px;
                        margin-top: 10px;
                        overflow: hidden;
                    }

                    .progress-fill {
                        width: 0%;
                        height: 100%;
                        background: #2e68cc;
                        transition: width 0.5s ease;
                    }


                    @keyframes barLoad {
                        0% {
                            width: 0%;
                        }

                        60% {
                            width: 90%;
                        }

                        100% {
                            width: 100%;
                        }
                    }
                </style>

                <!--====================================================================================-->

                <div id="Freshman" class="tab-content" style="display: none;">

                    <table class="table table-bordered table-hover align-middle" id="freshmanstudentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="clientTableBody">
                            <?php foreach ($freshman as $freshman): ?>
                                <tr class="client-row" data-href="ClientProfile.php?id=<?= urlencode($freshman['ClientID']) ?>">
                                    <td class="searchable-id"><?= htmlspecialchars($freshman['ClientID']) ?></td>
                                    <td>
                                        <?php
                                        $profilePath = !empty($freshman['profilePicturePath']) ? '../../uploads/' . $freshman['profilePicturePath'] : '../../uploads/profilepic2.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                    </td>
                                    <td class="searchable-name">
                                        <?= htmlspecialchars($freshman['FullName']) ?>
                                    </td>
                                    <td class="email-td">
                                        <?= htmlspecialchars($freshman['Email']) ?>
                                    </td>
                                    <td class="course-td">
                                        <?= htmlspecialchars($freshman['Course']) ?>
                                    </td>
                                    <td class="department-td">
                                        <?= htmlspecialchars($freshman['Department']) ?>
                                    </td>

                                    <td class="actions-column">
                                        <div class="action-buttons">
                                            <a href="ClientProfile.php?id=<?= $freshman['ClientID'] ?>" title="Edit User">
                                                <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                            </a>
                                            <!--     <a href="ClientProfile.php?id=<?= $freshman['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>-->
                                            <a class="row-delete-btn"
                                                data-id="<?= $freshman['ClientID'] ?>"
                                                data-url="manageclients.dbf/delete_client.php"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash delete-icon"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <script>
                    // Make table rows clickable
                    document.addEventListener("DOMContentLoaded", function() {
                        const rows = document.querySelectorAll(".client-row");
                        rows.forEach(row => {
                            row.addEventListener("click", function() {
                                const url = this.dataset.href;
                                window.location.href = url;
                            });
                        });
                    });
                </script>
                <!--====================================================================================-->

                <div id="NewPersonnel" class="tab-content" style="display: none;">
                    <table class="table table-bordered table-hover align-middle" id="newpersonnelTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="clientTableBody">
                            <?php foreach ($newpersonnel as $newpersonnel): ?>
                                <tr class="client-row" data-href="ClientProfile.php?id=<?= urlencode($newpersonnel['ClientID']) ?>">
                                    <td class="searchable-id"><?= htmlspecialchars($newpersonnel['ClientID']) ?></td>
                                    <td>
                                        <?php
                                        $profilePath = !empty($newpersonnel['profilePicturePath']) ? '../../uploads/' . $newpersonnel['profilePicturePath'] : '../../uploads/profilepic2.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                    </td>
                                    <td class="searchable-name">
                                        <?= htmlspecialchars($newpersonnel['FullName']) ?>
                                    </td>
                                    <td class="email-td">
                                        <?= htmlspecialchars($newpersonnel['Email']) ?>
                                    </td>
                                    <td class="department-td">
                                        <?= htmlspecialchars($newpersonnel['Department']) ?>
                                    </td>

                                    <td class="actions-column">
                                        <div class="action-buttons">
                                            <a href="ClientProfile.php?id=<?= $newpersonnel['ClientID'] ?>" title="Edit User">
                                                <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                            </a>

                                            <a class="row-delete-btn"
                                                data-id="<?= $newpersonnel['ClientID'] ?>"
                                                data-url="manageclients.dbf/delete_client.php"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash delete-icon"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!--====================================================================================-->
                <div id="Students" class="tab-content" style="display: block;">

                    <table class="table table-bordered table-hover align-middle" id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="clientTableBody">
                            <?php foreach ($students as $students): ?>
                                <tr class="client-row" data-href="ClientProfile.php?id=<?= urlencode($students['ClientID']) ?>">
                                    <td class="searchable-id"><?= htmlspecialchars($students['ClientID']) ?></td>
                                    <td>
                                        <?php
                                        $profilePath = !empty($students['profilePicturePath']) ? '../../uploads/' . $students['profilePicturePath'] : '../../uploads/profilepic2.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                    </td>
                                    <td class="searchable-name">
                                        <?= htmlspecialchars($students['FullName']) ?>
                                    </td>
                                    <td class="email-td">
                                        <?= htmlspecialchars($students['Email']) ?>
                                    </td>
                                    <td class="course-td">
                                        <?= htmlspecialchars($students['Course']) ?>
                                    </td>
                                    <td class="department-td">
                                        <?= htmlspecialchars($students['Department']) ?>
                                    </td>

                                    <td class="actions-column">
                                        <div class="action-buttons">
                                            <a href="ClientProfile.php?id=<?= $students['ClientID'] ?>" title="Edit User">
                                                <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                            </a>

                                            <a class="row-delete-btn"
                                                data-id="<?= $students['ClientID'] ?>"
                                                data-url="manageclients.dbf/delete_client.php"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash delete-icon"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!--====================================================================================-->
                <div id="Faculty" class="tab-content" style="display: none;">

                    <table class="table table-bordered table-hover align-middle" id="facultiesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="clientTableBody">
                            <?php foreach ($faculties as $faculties): ?>
                                <tr class="client-row" data-href="ClientProfile.php?id=<?= urlencode($faculties['ClientID']) ?>">
                                    <td class="searchable-id"><?= htmlspecialchars($faculties['ClientID']) ?></td>
                                    <td>
                                        <?php
                                        $profilePath = !empty($faculties['profilePicturePath']) ? '../../uploads/' . $faculties['profilePicturePath'] : '../../uploads/profilepic2.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                    </td>
                                    <td class="searchable-name">
                                        <?= htmlspecialchars($faculties['FullName']) ?>
                                    </td>
                                    <td class="email-td">
                                        <?= htmlspecialchars($faculties['Email']) ?>
                                    </td>
                                    <td class="department-td">
                                        <?= htmlspecialchars($faculties['Department']) ?>
                                    </td>

                                    <td class="actions-column">
                                        <div class="action-buttons">
                                            <a href="ClientProfile.php?id=<?= $faculties['ClientID'] ?>" title="Edit User">
                                                <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                            </a>

                                            <a class="row-delete-btn"
                                                data-id="<?= $faculties['ClientID'] ?>"
                                                data-url="manageclients.dbf/delete_client.php"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash delete-icon"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!--====================================================================================-->
                <div id="Personnel" class="tab-content" style="display: none;">

                    <table class="table table-bordered table-hover align-middle " id="personnelTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="clientTableBody">
                            <?php foreach ($personnel as $personnel): ?>
                                <tr class="client-row" data-href="ClientProfile.php?id=<?= urlencode($personnel['ClientID']) ?>">
                                    <td class="searchable-id"><?= htmlspecialchars($personnel['ClientID']) ?></td>
                                    <td>
                                        <?php
                                        $profilePath = !empty($personnel['profilePicturePath']) ? '../../uploads/' . $personnel['profilePicturePath'] : '../../uploads/profilepic2.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                    </td>
                                    <td class="searchable-name">
                                        <?= htmlspecialchars($personnel['FullName']) ?>
                                    </td>
                                    <td class="email-td">
                                        <?= htmlspecialchars($personnel['Email']) ?>
                                    </td>
                                    <td class="department-td">
                                        <?= htmlspecialchars($personnel['Department']) ?>
                                    </td>
                                    <td class="actions-column">
                                        <div class="action-buttons">
                                            <a href="ClientProfile.php?id=<?= $personnel['ClientID'] ?>" title="Edit User">
                                                <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                            </a>
                                            <a class="row-delete-btn"
                                                data-id="<?= $personnel['ClientID'] ?>"
                                                data-url="manageclients.dbf/delete_client.php"
                                                title="Delete User">
                                                <i class="fa-solid fa-trash delete-icon"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>

    <div id="deleteModal" class="delete-modal" style="display:none;">
        <div class="delete-modal-content">
            <div class="delete-modal-header">
                <h3>Delete User</h3>
                <button id="closeModal" class="btn-secondary" style="border: none; background: transparent; float: right; font-size: 18px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>


            </div>
            <p>Do you want to permanently delete or temporarily delete this user?</p>

            <div class="delete-modal-buttons">
                <button id="tempDeleteBtn" class="btn-warning">Archive</button>
                <button id="permDeleteBtn" class="btn-danger">Delete</button>
            </div>
        </div>


    </div>
    </main>
    </div>

</body>
<div id="deleteSuccessModal" class="delete-success-modal">
    <div class="delete-success-modal-content">
        <span class="closeDeleteModal">&times;</span>
        <div class="modal-icon">&#10004;</div>
        <h2>Success!</h2>
        <p id="deleteModalMessage">Message goes here</p>
        <button class="closeModalBtn">OK</button>
    </div>
</div>

</html>