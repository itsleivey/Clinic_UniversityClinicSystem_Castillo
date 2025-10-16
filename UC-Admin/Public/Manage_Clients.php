<?php
require_once 'config/database.php';
require_once 'manageclients.dbf/get_user.php';

$students = fetchStudents();
$faculties = fetchFaculty();
$personnel = fetchPersonnel();
$freshman =  fetchFreshman();
$newpersonnel = fetchNewPersonnel();

if (isset($_GET['error'])) {
    echo '<div class="alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="assets/css/manageusers.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">

    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/manageclients.js" defer></script>

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
    </div>

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
            <a href="">
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
                            <div class="select-wrapper">
                                <i class="fas fa-filter"></i>
                                <select id="clientTypeDropdown" class="client-type-dropdown">
                                    <option value="students-content">Regular Students</option>
                                    <option value="freshman-content">Incoming Freshman Students</option>
                                    <option value="employees-content">Teaching Personnels</option>
                                    <option value="personnel-content">Non-Teaching Personnels</option>
                                    <option value="newpersonnel-content">Newly Hired Personnels</option>
                                </select>
                            </div>


                            <div class="search-input-container rectangular-search">
                                <div class="input-wrapper">
                                    <i class="fas fa-search search-icon-inset"></i>
                                    <input type="text"
                                        id="searchInput"
                                        name="id_filter"
                                        placeholder="Search ID, Name, Email, Department, ClientType"
                                        value="<?= htmlspecialchars($_GET['id_filter'] ?? '') ?>"
                                        maxlength="200">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-add-patient" onclick="openAddPatientModal()">
                            <i class="fas fa-user-plus"></i> Add Patient
                        </button>
                    </div>

                    <div id="addPatientModal" class="modal">
                        <div class="modal-content">
                            <span onclick="closeAddPatientModal()" class="close-btn">&times;</span>
                            <h3 class="modal-title">
                                <i class="fas fa-user-plus title-icon"></i> Add Patient
                            </h3>

                            <form method="POST" action="manageclients.dbf/add-patient.php" id="addPatientForm">

                                <div class="form-group">
                                    <label><i class="fas fa-user icon-blue"></i> Full Name</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" name="fullname" placeholder="Enter patient's full name" class="form-control" required autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-envelope icon-blue"></i> Email</label>
                                    <div id="emailError" class="error-message">
                                        <i class="fas fa-exclamation-triangle"></i> Email already exists
                                    </div>
                                    <div class="input-wrapper">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email"
                                            name="email"
                                            id="emailInput"
                                            class="form-control"
                                            required
                                            autocomplete="off"
                                            placeholder="Enter patient's email">

                                    </div>
                                </div>


                                <div class="form-group">
                                    <label><i class="fas fa-lock icon-blue"></i> Password</label>
                                    <div class="pass-input-wrapper">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input
                                            type="password"
                                            name="password"
                                            id="passwordInput"
                                            class="form-control"
                                            required
                                            minlength="8"
                                            placeholder="Enter a strong password">

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

                                <div id="departmentField" class="form-group" style="display: none;">
                                    <label for="department"><i class="fas fa-building-columns icon-blue"></i> Department</label>
                                    <select id="department" name="department" class="form-control">
                                        <option value="">Select a Department</option>
                                        <option value="None">None</option>
                                        <option value="College of Computer Studies">College of Computer Studies</option>
                                        <option value="College of Food Nutrition and Dietetics">College of Food Nutrition and Dietetics</option>
                                        <option value="College of Industrial Technology">College of Industrial Technology</option>
                                        <option value="College of Teacher Education">College of Teacher Education</option>
                                        <option value="College of Agriculture">College of Agriculture</option>
                                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                                        <option value="College of Business Administration and Accountancy">College of Business Administration and Accountancy</option>
                                        <option value="College of Engineering">College of Engineering</option>
                                        <option value="College of Criminal Justice Education">College of Criminal Justice Education</option>
                                        <option value="College of Fisheries">College of Fisheries</option>
                                        <option value="College of Hospitality Management and Tourism">College of Hospitality Management and Tourism</option>
                                        <option value="College of Nursing and Allied Health">College of Nursing and Allied Health</option>
                                    </select>
                                </div>

                                <button type="submit" id="saveButton" class="btn-save">
                                    <i class="fas fa-save"></i> Save Patient
                                </button>
                            </form>
                        </div>
                    </div>



                </div>
                <script>
                    document.getElementById("emailInput").addEventListener("blur", generateAutoPassword);
                    document.querySelector("input[name='fullname']").addEventListener("blur", generateAutoPassword);

                    function generateAutoPassword() {
                        const fullname = document.querySelector("input[name='fullname']").value.trim();
                        const email = document.getElementById("emailInput").value.trim();

                        if (!fullname && !email) return;

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

                                    passwordInput.dispatchEvent(new Event("input"));
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

                <div class="clients-table-container">
                    <div class="tabs">
                        <div class="tab active" data-target="students-content">
                            <i class="fas fa-user-graduate"></i> Regular Students
                        </div>
                        <div class="tab" data-target="freshman-content">
                            <i class="fas fa-child"></i>Incoming Freshman Students
                        </div>
                        <div class="tab" data-target="employees-content">
                            <i class="fas fa-chalkboard-teacher"></i> Teaching Personnels
                        </div>
                        <div class="tab" data-target="personnel-content">
                            <i class="fas fa-users-cog"></i> Non-Teaching Personnels
                        </div>
                        <div class="tab" data-target="newpersonnel-content">
                            <i class="fas fa-user-plus"></i> Newly Hired Personnels
                        </div>
                    </div>
                    <!--====================================================================================-->
                    <div id="freshman-content" class="tab-content" style="display: none;">
                        <div class="table-container">
                            <div class="table-div">
                                <table class="table table-bordered table-hover align-middle" id="freshmanstudentsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Department</th>
                                            <th>Client Type</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="clientTableBody">
                                        <?php foreach ($freshman as $freshman): ?>
                                            <tr class="client-row">
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
                                                <td>
                                                    <?= htmlspecialchars($freshman['Email']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($freshman['Course']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($freshman['Department']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($freshman['ClientType']) ?>
                                                </td>
                                                <td class="actions-column">
                                                    <div class="action-buttons">
                                                        <a href="ClientProfile.php?id=<?= $freshman['ClientID'] ?>" title="Edit User">
                                                            <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                                        </a>
                                                        <a href="ClientProfile.php?id=<?= $freshman['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>
                                                        <a href="manageclients.dbf/delete_client.php?id=<?= $freshman['ClientID'] ?>"
                                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                                            title="Delete User">
                                                            <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
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
                    <!--====================================================================================-->
                    <div id="newpersonnel-content" class="tab-content" style="display: none;">
                        <div class="table-container">
                            <div class="table-div">
                                <table class="table table-bordered table-hover align-middle" id="newpersonnelTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Department</th>
                                            <th>Client Type</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="clientTableBody">
                                        <?php foreach ($newpersonnel as $newpersonnel): ?>
                                            <tr class="client-row">
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
                                                <td>
                                                    <?= htmlspecialchars($newpersonnel['Email']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($newpersonnel['Course']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($newpersonnel['Department']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($newpersonnel['ClientType']) ?>
                                                </td>
                                                <td class="actions-column">
                                                    <div class="action-buttons">
                                                        <a href="ClientProfile.php?id=<?= $newpersonnel['ClientID'] ?>" title="Edit User">
                                                            <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                                        </a>
                                                        <a href="ClientProfile.php?id=<?= $newpersonnel['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>
                                                        <a href="manageclients.dbf/delete_client.php?id=<?= $newpersonnel['ClientID'] ?>"
                                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                                            title="Delete User">
                                                            <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
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

                    <!--====================================================================================-->
                    <div id="students-content" class="tab-content" style="display: block;">
                        <div class="table-container">

                            <div class="table-div">
                                <table class="table table-bordered table-hover align-middle" id="studentsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Department</th>
                                            <th>Client Type</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="clientTableBody">
                                        <?php foreach ($students as $students): ?>
                                            <tr class="client-row">
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
                                                <td>
                                                    <?= htmlspecialchars($students['Email']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($students['Course']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($students['Department']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($students['ClientType']) ?>
                                                </td>
                                                <td class="actions-column">
                                                    <div class="action-buttons">
                                                        <a href="ClientProfile.php?id=<?= $students['ClientID'] ?>" title="Edit User">
                                                            <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                                        </a>
                                                        <a href="ClientProfile.php?id=<?= $students['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>
                                                        <a href="manageclients.dbf/delete_client.php?id=<?= $students['ClientID'] ?>"
                                                            onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                                            <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
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
                    <!--====================================================================================-->
                    <div id="employees-content" class="tab-content" style="display: none;">
                        <div class="table-container">
                            <div class="table-div">
                                <table class="table table-bordered table-hover align-middle" id="facultiesTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Department</th>
                                            <th>Client Type</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="clientTableBody">
                                        <?php foreach ($faculties as $faculties): ?>
                                            <tr class="client-row">
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
                                                <td>
                                                    <?= htmlspecialchars($faculties['Email']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($faculties['Course']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($faculties['Department']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($faculties['ClientType']) ?>
                                                </td>
                                                <td class="actions-column">
                                                    <div class="action-buttons">
                                                        <a href="ClientProfile.php?id=<?= $faculties['ClientID'] ?>" title="Edit User">
                                                            <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                                        </a>
                                                        <a href="ClientProfile.php?id=<?= $faculties['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>
                                                        <a href="manageclients.dbf/delete_client.php?id=<?= $faculties['ClientID'] ?>"
                                                            onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                                            <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
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
                    <!--====================================================================================-->
                    <div id="personnel-content" class="tab-content" style="display: none;">
                        <div class="table-container">
                            <div class="table-div">
                                <table class="table table-bordered table-hover align-middle " id="personnelTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Client Type</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="clientTableBody">
                                        <?php foreach ($personnel as $personnel): ?>
                                            <tr class="client-row">
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
                                                <td>
                                                    <?= htmlspecialchars($personnel['Email']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($personnel['Course']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($personnel['ClientType']) ?>
                                                </td>
                                                <td class="actions-column">
                                                    <div class="action-buttons">
                                                        <a href="ClientProfile.php?id=<?= $personnel['ClientID'] ?>" title="Edit User">
                                                            <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                                                        </a>
                                                        <a href="ClientProfile.php?id=<?= $personnel['ClientID'] ?>" title="View Profile">
                                                            <i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i>
                                                        </a>
                                                        <a href="manageclients.dbf/delete_client.php?id=<?= $personnel['ClientID'] ?>"
                                                            onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                                            <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
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
                </div>
            </div>
        </main>
    </div>
    <script>
        const searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('input', function() {
            const searchId = searchInput.value.trim();

            if (searchId === '') {
                // Clear button behavior:
                const baseUrl = window.location.href.split('?')[0];
                window.history.pushState({}, '', baseUrl);

                ['students-content', 'employees-content', 'personnel-content', 'freshman-content', 'newpersonnel-content']
                .forEach(tabId => {
                    document.querySelector(`#${tabId} tbody`).innerHTML = '';
                });

                return;
            }

            const url = new URL(window.location);
            url.searchParams.set('id_filter', searchId);
            window.history.pushState({}, '', url);

            loadFilteredData('students-content', 'Student', searchId);
            loadFilteredData('employees-content', 'Faculty', searchId);
            loadFilteredData('personnel-content', 'Personnel', searchId);
            loadFilteredData('freshman-content', 'Freshman', searchId);
            loadFilteredData('newpersonnel-content', 'NewPersonnel', searchId);
        });


        function loadFilteredData(tabId, clientType, searchId) {
            fetch(`manageclients.dbf/get_user.php?client_type=${clientType}&id_filter=${encodeURIComponent(searchId)}`)
                .then(response => response.text())
                .then(html => {
                    document.querySelector(`#${tabId} tbody`).innerHTML = html;
                });
        }

        document.getElementById('resetSearch').addEventListener('click', function() {
            const baseUrl = window.location.href.split('?')[0];
            window.location.href = baseUrl;
        });

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const idFilter = urlParams.get('id_filter');

            if (idFilter) {
                searchInput.value = idFilter;
                searchInput.dispatchEvent(new Event('input'));
            }

            const activeTab = sessionStorage.getItem('activeTab');
            if (activeTab) {
                const tab = document.querySelector(`.nav-tabs .nav-link[data-bs-target="${activeTab}"]`);
                if (tab) tab.click();
            }
        });
    </script>


</body>

</html