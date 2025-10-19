<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../MedicalDB/PersonalInfoLogic.php';

$pdo = pdo_connect_mysql();

// Ensure ClientID exists
if (!isset($_SESSION['ClientID'])) {
    // Redirect to login or register page if not set
    header("Location: register.php");
    exit();
}

$clientId = (int) $_SESSION['ClientID']; // cast to int to satisfy the function
$userData = getUserDataFromDatabase($pdo, $clientId);
// Get the most recent in-progress historyID
$getHistory = $pdo->prepare("
    SELECT historyID 
    FROM history 
    WHERE ClientID = ? 
    ORDER BY historyID DESC 
    LIMIT 1
");

$getHistory->execute([$clientId]);
$historyID = $getHistory->fetchColumn();

try {
    $clientId = $_SESSION['ClientID']; // make sure ClientID is set in session
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientId]);
    $UserInfoData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$UserInfoData || !isset($UserInfoData['Sex'])) {
        $_SESSION['error_message'] = "No gender data found for this client.";
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load gender data.";
}


// ==================== Medical Dental History ====================
$medicalData = [
    'KnownIllness' => 0,
    'KnownIllnessDetails' => '',
    'Hospitalization' => 0,
    'HospitalizationDetails' => '',
    'Allergies' => 0,
    'AllergiesDetails' => '',
    'ChildImmunization' => 0,
    'ChildImmunizationDetails' => '',
    'PresentImmunizations' => 0,
    'PresentImmunizationsDetails' => '',
    'CurrentMedicines' => 0,
    'CurrentMedicinesDetails' => '',
    'DentalProblems' => 0,
    'DentalProblemsDetails' => '',
    'PrimaryPhysician' => 0,
    'PrimaryPhysicianDetails' => ''
];

try {
    $stmt = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE ClientID = ? AND historyID = ?");
    $stmt->execute([$clientId, $historyID]);
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingData) {
        $medicalData = array_merge($medicalData, $existingData);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load medical history.";
}

// Display messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}

// ==================== Family Medical History ====================
$stmt = $pdo->prepare("SELECT * FROM familymedicalhistory WHERE ClientID = ? AND historyID = ?");
$stmt->execute([$clientId, $historyID]);
$familymedicalhistory = $stmt->fetch(PDO::FETCH_ASSOC);

// Checkbox helper
function is_checked($field)
{
    global $familymedicalhistory;
    return isset($familymedicalhistory[$field]) && $familymedicalhistory[$field] == 1 ? 'checked' : '';
}

// ==================== Personal Social History ====================
function getSocialHistory($clientId, $historyID)
{
    $conn = pdo_connect_mysql();
    $stmt = $conn->prepare("SELECT * FROM personalsocialhistory WHERE ClientID = ? AND historyID = ?");
    $stmt->execute([$clientId, $historyID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: [];
}

$socialHistoryData = getSocialHistory($clientId, $historyID);

// ==================== Female Health History ====================
function getFemaleHealthData($clientId, $historyID)
{
    $conn = pdo_connect_mysql();
    if (!$conn) {
        die("Database connection failed.");
    }

    $query = $conn->prepare("SELECT * FROM femalehealthhistory WHERE ClientID = ? AND historyID = ?");
    $query->execute([$clientId, $historyID]);
    $data = $query->fetch(PDO::FETCH_ASSOC);

    return $data ?: [];
}

$data = getFemaleHealthData($clientId, $historyID);

// ==================== Physical Examination ====================
$sql = "SELECT * FROM physicalexamination WHERE ClientID = :client_id AND historyID = :historyID";
$stmt = $pdo->prepare($sql);
$stmt->execute(['client_id' => $clientId, 'historyID' => $historyID]);
$physicalExam = $stmt->fetch(PDO::FETCH_ASSOC);

// ==================== Diagnostic Results ====================
$sql = "SELECT * FROM diagnosticresults WHERE ClientID = :client_id AND historyID = :historyID";
$stmt = $pdo->prepare($sql);
$stmt->execute(['client_id' => $clientId, 'historyID' => $historyID]);
$diagnostic = $stmt->fetch(PDO::FETCH_ASSOC);


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
    <title>Medical Forms</title>
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
            <h4>Medical Forms</h4>
        </div>

        <!-- Profile dropdown -->
        <div class="profile-container">

            <img id="profileBtn" src="../uploads/profilepic2.png" alt="Profile Picture">

            <div class="profile-dropdown" id="profileDropdown">
                <div class="fixed-profile-item">
                    <i class="fas fa-envelope"></i> user@email.com
                </div>
                <a href="settings.php">
                    <div class="profile-item">
                        <i class="fas fa-cog"></i> Settings
                    </div>
                </a>
                <div class="profile-item" onclick="document.getElementById('logoutForm').submit()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </div>
                <form id="logoutForm" action="logout.php" method="post"></form>
            </div>
        </div>
    </div>

    <div class="main-container">
        <nav class="navbar">

            <button class="active-buttons" id="medicalBtn">
                <i class="fas fa-file-lines button-icon-nav"></i>
                <span class="nav-text">Medical Forms</span>
            </button>
            <a href="Settings.php">
                <button class="buttons" id="settingBtn">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </button>
            </a>
        </nav>

        <main class="content" loading="lazy">
            <!-- Confirm Submission Modal -->
            <div id="confirmModal" class="modal">
                <div class="modal-content">
                    <h2>Confirm Submission</h2>
                    <p>Are you sure all entered details are correct?</p>
                    <div class="modal-buttons">
                        <button id="confirmYes" class="btn-primary">Yes, Submit</button>
                        <button id="confirmNo" class="btn-secondary">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div id="successModal" class="modal">
                <div class="modal-content">
                    <h2>✅ Submitted Successfully!</h2>
                    <p>Your information has been saved.</p>
                    <div class="modal-buttons">
                        <button id="successClose" class="btn-primary">OK</button>
                    </div>
                </div>
            </div>

            <section class="card">
                <form id="completeMedicalForm" action="AllFormSubmission.php" method="POST" autocomplete="off">

                    <input type="hidden" name="unifiedSubmit" value="1">

                    <!-- PERSONAL INFORMATION -->
                    <h1 class="h1-style">Personal Information</h1>
                    <div class="form-row">
                        <div>
                            <label for="Surname"><i class="fa-solid fa-user"></i> Surname</label>
                            <input type="text" id="Surname" name="Surname" placeholder="Surname" value="<?= htmlspecialchars($UserInfoData['Lastname'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="GivenName"><i class="fa-solid fa-user"></i> Given Name</label>
                            <input type="text" id="GivenName" name="GivenName" placeholder="Given Name" value="<?= htmlspecialchars($UserInfoData['Firstname'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="MiddleName"><i class="fa-solid fa-user"></i> Middle Name</label>
                            <input type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?= htmlspecialchars($userData['MiddleName'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="Age"><i class="fa-solid fa-hourglass-half"></i> Age</label>
                            <input type="number" id="Age" name="Age" placeholder="Age" min="1" max="120" value="<?= htmlspecialchars($userData['Age'] ?? '') ?>" required>
                        </div>

                        <div>
                            <label for="Gender"><i class="fa-solid fa-venus-mars"></i> Sex</label>
                            <select id="Gender" name="Gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= (isset($UserInfoData['Sex']) && $UserInfoData['Sex'] === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (isset($UserInfoData['Sex']) && $UserInfoData['Sex'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>


                        <div>
                            <label for="DateOfBirth"><i class="fa-solid fa-calendar-day"></i> Date of Birth</label>
                            <input type="date" id="DateOfBirth" name="DateOfBirth" value="<?= htmlspecialchars($UserInfoData['BirthDate'] ?? '') ?>" required>
                        </div>

                        <div>
                            <label for="Status"><i class="fa-solid fa-ring"></i> Status</label>
                            <select id="Status" name="Status" required>
                                <option value="">Status</option>
                                <option value="single" <?= (isset($userData['Status']) && $userData['Status'] === 'single') ? 'selected' : '' ?>>Single</option>
                                <option value="married" <?= (isset($userData['Status']) && $userData['Status'] === 'married') ? 'selected' : '' ?>>Married</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="Course"><i class="fa-solid fa-book"></i> Course</label>
                            <input type="text" id="Course" name="Course" placeholder="Course" value="<?= htmlspecialchars($UserInfoData['Course'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="SchoolYearEntered"><i class="fa-solid fa-calendar-alt"></i> School Year Entered</label>
                            <input type="text" id="SchoolYearEntered" name="SchoolYearEntered" placeholder="School Year Entered" value="<?= htmlspecialchars($userData['SchoolYearEntered'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="CurrentAddress"><i class="fa-solid fa-house"></i> Current Address</label>
                            <input type="text" id="CurrentAddress" name="CurrentAddress" placeholder="Current Address" value="<?= htmlspecialchars($userData['CurrentAddress'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="ContactNumber"><i class="fa-solid fa-phone"></i> Contact Number</label>
                            <input type="text" id="ContactNumber" name="ContactNumber" placeholder="Contact Number" value="<?= htmlspecialchars($userData['ContactNumber'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="MothersName"><i class="fa-solid fa-person-dress"></i> Mother's Name</label>
                            <input type="text" id="MothersName" name="MothersName" placeholder="Mother's Name" value="<?= htmlspecialchars($userData['MothersName'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="FathersName"><i class="fa-solid fa-person"></i> Father's Name</label>
                            <input type="text" id="FathersName" name="FathersName" placeholder="Father's Name" value="<?= htmlspecialchars($userData['FathersName'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="GuardiansName"><i class="fa-solid fa-user-shield"></i> Guardian's Name</label>
                            <input type="text" id="GuardiansName" name="GuardiansName" placeholder="Guardian's Name" value="<?= htmlspecialchars($userData['GuardiansName'] ?? '') ?>">
                        </div>

                        <div>
                            <label for="EmergencyContactName"><i class="fa-solid fa-triangle-exclamation"></i> Emergency Contact Number</label>
                            <input type="text" id="EmergencyContactName" name="EmergencyContactName" placeholder="Emergency Contact Name" value="<?= htmlspecialchars($userData['EmergencyContactName'] ?? '') ?>" required>
                        </div>

                        <div>
                            <label for="EmergencyContactRelationship"><i class="fa-solid fa-people-arrows"></i> Emergency Contact Relationship</label>
                            <input type="text" id="EmergencyContactRelationship" name="EmergencyContactRelationship" placeholder="Relationship" value="<?= htmlspecialchars($userData['EmergencyContactRelationship'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="EmergencyGuardiansName"><i class="fa-solid fa-user-shield"></i> Name of Contact Person in CASE OF EMERGENCY</label>
                            <input type="text" id="EmergencyGuardiansName" name="EmergencyContactPerson" placeholder="(REQUIRED)" value="<?= htmlspecialchars($userData['EmergencyContactPerson'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- MEDICAL & DENTAL HISTORY -->
                    <h1 class="h1-style">Medical & Dental History</h1>

                    <?php
                    $medicalFields = [
                        'KnownIllness' => 'Previous/present KNOWN illness',
                        'Hospitalization' => 'Past hospitalization/confinement',
                        'Allergies' => 'Known allergies to food or medicine',
                        'ChildImmunization' => 'Childhood immunization',
                        'PresentImmunizations' => 'Present immunizations (ex. Flu, Hepa B, etc.)',
                        'CurrentMedicines' => 'Currently taking medicines/vitamins',
                        'DentalProblems' => 'Dental problems (ex. Gingivitis, etc.)',
                        'PrimaryPhysician' => 'Primary care physician (name, specialty, clinic location and date of last check-up/follow-up)'
                    ];

                    foreach ($medicalFields as $field => $label) {
                        $detailsField = $field . 'Details';
                        echo '<div class="form-row checkbox-row">';
                        echo '<input type="checkbox" id="' . $field . '" name="' . $field . '" ' . (!empty($medicalData[$field]) ? 'checked' : '') . '>';
                        echo '<label for="' . $field . '">' . $label . '</label>';
                        echo '<input type="text" class="details-input" placeholder="Details" name="' . $detailsField . '" value="' . htmlspecialchars($medicalData[$detailsField] ?? '') . '">';
                        echo '</div>';
                    }
                    ?>

                    <!-- FAMILY MEDICAL HISTORY -->
                    <h1 class="h1-style">Family Medical History</h1>

                    <?php
                    $familyFields = [
                        'Allergy',
                        'Asthma',
                        'Tuberculosis',
                        'Hypertension',
                        'BloodDisease',
                        'Stroke',
                        'Diabetes',
                        'Cancer',
                        'LiverDisease',
                        'KidneyBladder',
                        'BloodDisorder',
                        'Epilepsy',
                        'MentalDisorder',
                        'OtherIllness'
                    ];

                    foreach ($familyFields as $field) {
                        $detailsField = $field . 'Details';
                        echo '<div class="form-row checkbox-row">';
                        echo '<input type="checkbox" id="' . $field . '" name="' . $field . '" ' . (!empty($familymedicalhistory[$field]) ? 'checked' : '') . '>';
                        echo '<label for="' . $field . '">' . $field . '</label>';
                        echo '<input type="text" class="details-input" placeholder="Specify" name="' . $detailsField . '" value="' . htmlspecialchars($familymedicalhistory[$detailsField] ?? '') . '">';
                        echo '</div>';
                    }
                    ?>

                    <!-- PERSONAL & SOCIAL HISTORY -->
                    <h1 class="h1-style">Personal & Social History</h1>

                    <div class="form-row">
                        <div>
                            <label for="alcoholIntake">Alcohol intake:</label>
                            <select id="alcoholIntake" name="AlcoholIntake" required>
                                <option value="no" <?= ($socialHistoryData['AlcoholIntake'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['AlcoholIntake'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Frequency/Amount (if applicable)"
                                name="AlcoholDetails" value="<?= htmlspecialchars($socialHistoryData['AlcoholDetails'] ?? '') ?>">
                        </div>

                        <div>
                            <label for="tobaccoUse">Tobacco use:</label>
                            <select id="tobaccoUse" name="TobaccoUse" required>
                                <option value="no" <?= ($socialHistoryData['TobaccoUse'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['TobaccoUse'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Frequency/Amount (if applicable)"
                                name="TobaccoDetails" value="<?= htmlspecialchars($socialHistoryData['TobaccoDetails'] ?? '') ?>">
                        </div>

                        <div>
                            <label for="drugUse">Illicit drug use:</label>
                            <select id="drugUse" name="DrugUse" required>
                                <option value="no" <?= ($socialHistoryData['DrugUse'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['DrugUse'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Type/Frequency (if applicable)"
                                name="DrugDetails" value="<?= htmlspecialchars($socialHistoryData['DrugDetails'] ?? '') ?>">
                        </div>
                    </div>
                    <!-- FOR FEMALES (hidden unless Gender = female) -->
                    <?php
                    // set initial display based on saved gender value
                    $femaleDisplayStyle = (isset($UserInfoData['Sex']) && strtolower($UserInfoData['Sex']) === 'female')
                        ? 'display:block;'
                        : 'display:none;';

                    ?>
                    <div id="for-females-input" class="scroll-input-div" style="<?= $femaleDisplayStyle ?>">
                        <h1 class="h1-style">Female Menstrual History</h1>

                        <div class="fem-form-section">
                            <h4>Menstrual Period</h4>

                            <div class="fem-form-row">
                                <label for="lastPeriod" class="fem-label">Date of first day of LAST menstrual period:</label>
                                <input type="date" id="lastPeriod" name="LastPeriod" class="fem-details-input"
                                    value="<?= htmlspecialchars($data['LastPeriod'] ?? '') ?>">
                            </div>

                            <div class="fem-form-row fem-radio-group">
                                <span class="fem-radio-label">Regularity:</span>
                                <div class="fem-radio-options">
                                    <label><input type="radio" name="Regularity" value="regular" <?= ($data['Regularity'] ?? '') == 'regular' ? 'checked' : '' ?>> Regular</label>
                                    <label><input type="radio" name="Regularity" value="irregular" <?= ($data['Regularity'] ?? '') == 'irregular' ? 'checked' : '' ?>> Irregular</label>
                                </div>
                            </div>

                            <div class="fem-form-row">
                                <label for="duration" class="fem-label">Duration:</label>
                                <input type="text" id="duration" name="Duration" class="fem-details-input fem-short-input" placeholder="days/weeks"
                                    value="<?= htmlspecialchars($data['Duration'] ?? '') ?>">
                            </div>

                            <div class="fem-form-row">
                                <label for="padsPerDay" class="fem-label">No. of pads/day:</label>
                                <input type="number" id="padsPerDay" name="PadsPerDay" class="fem-details-input fem-short-input" min="0"
                                    value="<?= htmlspecialchars($data['PadsPerDay'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="fem-form-section">
                            <div class="fem-form-row fem-radio-group">
                                <span class="fem-radio-label">History of dysmenorrhea:</span>
                                <div class="fem-radio-options">
                                    <label><input type="radio" name="Dysmenorrhea" value="yes" <?= ($data['Dysmenorrhea'] ?? '') == 'yes' ? 'checked' : '' ?>> Yes</label>
                                    <label><input type="radio" name="Dysmenorrhea" value="no" <?= ($data['Dysmenorrhea'] ?? '') == 'no' ? 'checked' : '' ?>> No</label>
                                </div>
                            </div>

                            <div class="fem-form-row" id="fem-severityRow" style="<?= ($data['Dysmenorrhea'] ?? '') == 'yes' ? '' : 'display: none;' ?>">
                                <label for="severity" class="fem-label">If YES, how severe is your dysmenorrhea?</label>
                                <select id="severity" name="DysmenorrheaSeverity" class="fem-details-input">
                                    <option value="">Select severity</option>
                                    <option value="mild" <?= ($data['DysmenorrheaSeverity'] ?? '') == 'mild' ? 'selected' : '' ?>>Mild</option>
                                    <option value="moderate" <?= ($data['DysmenorrheaSeverity'] ?? '') == 'moderate' ? 'selected' : '' ?>>Moderate</option>
                                    <option value="severe" <?= ($data['DysmenorrheaSeverity'] ?? '') == 'severe' ? 'selected' : '' ?>>Severe</option>
                                </select>
                            </div>

                            <div class="fem-form-row">
                                <label for="lastOBVisit" class="fem-label">Date of last check-up with an OB-gynecologist:</label>
                                <input type="date" id="lastOBVisit" name="LastOBVisit" class="fem-details-input"
                                    value="<?= htmlspecialchars($data['LastOBVisit'] ?? '') ?>">
                            </div>

                            <div class="fem-form-row fem-radio-group">
                                <span class="fem-radio-label">History of excessive/abnormal bleeding?</span>
                                <div class="fem-radio-options">
                                    <label><input type="radio" name="AbnormalBleeding" value="yes" <?= ($data['AbnormalBleeding'] ?? '') == 'yes' ? 'checked' : '' ?>> Yes</label>
                                    <label><input type="radio" name="AbnormalBleeding" value="no" <?= ($data['AbnormalBleeding'] ?? '') == 'no' ? 'checked' : '' ?>> No</label>
                                </div>
                            </div>
                        </div>

                        <div class="fem-form-section">
                            <div class="fem-form-row fem-radio-group">
                                <span class="fem-radio-label">Previous pregnancy?</span>
                                <div class="fem-radio-options">
                                    <label><input type="radio" name="PreviousPregnancy" value="yes" <?= ($data['PreviousPregnancy'] ?? '') == 'yes' ? 'checked' : '' ?>> Yes</label>
                                    <label><input type="radio" name="PreviousPregnancy" value="no" <?= ($data['PreviousPregnancy'] ?? '') == 'no' ? 'checked' : '' ?>> No</label>
                                </div>
                            </div>

                            <div class="fem-form-row" id="fem-pregnancyDetailsRow" style="<?= ($data['PreviousPregnancy'] ?? '') == 'yes' ? '' : 'display: none;' ?>">
                                <label for="pregnancyDetails" class="fem-label">Details (number, normal/C-section, home/hospital, etc.):</label>
                                <input type="text" id="pregnancyDetails" name="PregnancyDetails" class="fem-details-input"
                                    value="<?= htmlspecialchars($data['PregnancyDetails'] ?? '') ?>">
                            </div>

                            <div class="fem-form-row fem-radio-group">
                                <span class="fem-radio-label">Children?</span>
                                <div class="fem-radio-options">
                                    <label><input type="radio" name="HasChildren" value="yes" <?= ($data['HasChildren'] ?? '') == 'yes' ? 'checked' : '' ?>> Yes</label>
                                    <label><input type="radio" name="HasChildren" value="no" <?= ($data['HasChildren'] ?? '') == 'no' ? 'checked' : '' ?>> No</label>
                                </div>
                            </div>

                            <div class="fem-form-row" id="fem-childrenDetailsRow" style="<?= ($data['HasChildren'] ?? '') == 'yes' ? '' : 'display: none;' ?>">
                                <label for="childrenCount" class="fem-label">How many?</label>
                                <input type="number" id="childrenCount" name="ChildrenCount" class="fem-details-input fem-short-input" min="0"
                                    value="<?= htmlspecialchars($data['ChildrenCount'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="form-actions">
                        <button type="submit" id="submitBtn" class="form-buttons">Save All</button>
                        <div id="formMessage" class="form-message" role="status" aria-live="polite"></div>
                    </div>
                </form>
            </section>
            <div id="confirmModal" class="modal">
                <div class="modal-content">
                    <h2>Confirm Submission</h2>
                    <p>Are you sure all entered details are correct?</p>
                    <div class="modal-buttons">
                        <button id="confirmYes" class="btn-primary">Yes, Submit</button>
                        <button id="confirmNo" class="btn-secondary">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div id="successModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h2><i class="fas fa-check-circle" style="color: green;"></i> Submitted Successfully!</h2>
                    <p>Your information has been saved.</p>
                    <div class="modal-buttons">
                        <button id="successClose" class="btn-primary">OK</button>
                    </div>
                </div>
            </div>

        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // elements
                const genderSelect = document.getElementById('genderSelect');
                const femaleBlock = document.getElementById('for-females-input');

                const dysRadios = document.getElementsByName('Dysmenorrhea');
                const dysSeverityRow = document.getElementById('fem-severityRow');

                const prevPregRadios = document.getElementsByName('PreviousPregnancy');
                const pregnancyDetailsRow = document.getElementById('fem-pregnancyDetailsRow');

                const hasChildrenRadios = document.getElementsByName('HasChildren');
                const childrenDetailsRow = document.getElementById('fem-childrenDetailsRow');

                // toggle female section
                function toggleFemale() {
                    if (!genderSelect) return;
                    femaleBlock.style.display = (genderSelect.value === 'female') ? 'block' : 'none';
                }

                if (genderSelect) {
                    genderSelect.addEventListener('change', toggleFemale);
                    toggleFemale(); // initial
                }

                // dysmenorrhea toggles
                function handleDysChange() {
                    const v = Array.from(dysRadios).find(r => r.checked);
                    dysSeverityRow.style.display = (v && v.value === 'yes') ? '' : 'none';
                }
                dysRadios.forEach(r => r.addEventListener('change', handleDysChange));
                handleDysChange();

                // previous pregnancy toggle
                function handlePregChange() {
                    const v = Array.from(prevPregRadios).find(r => r.checked);
                    pregnancyDetailsRow.style.display = (v && v.value === 'yes') ? '' : 'none';
                }
                prevPregRadios.forEach(r => r.addEventListener('change', handlePregChange));
                handlePregChange();

                // has children toggle
                function handleChildrenChange() {
                    const v = Array.from(hasChildrenRadios).find(r => r.checked);
                    childrenDetailsRow.style.display = (v && v.value === 'yes') ? '' : 'none';
                }
                hasChildrenRadios.forEach(r => r.addEventListener('change', handleChildrenChange));
                handleChildrenChange();

                // AJAX submit

                const form = document.getElementById('completeMedicalForm');
                const submitBtn = document.getElementById('submitBtn');
                const messageBox = document.getElementById('formMessage');

                // Modals
                const confirmModal = document.getElementById('confirmModal');
                const confirmYes = document.getElementById('confirmYes');
                const confirmNo = document.getElementById('confirmNo');
                const successModal = document.getElementById('successModal');
                const successClose = document.getElementById('successClose');

                // Step 1️⃣: Intercept submit -> show confirm modal
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    confirmModal.style.display = 'flex';
                });

                // Step 2️⃣: If user cancels
                confirmNo.addEventListener('click', () => {
                    confirmModal.style.display = 'none';
                });

                // Step 3️⃣: If user confirms, run AJAX
                confirmYes.addEventListener('click', async function() {
                    confirmModal.style.display = 'none';

                    messageBox.textContent = '';
                    messageBox.className = 'form-message';
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';

                    try {
                        const formData = new FormData(form);
                        const data = Object.fromEntries(formData.entries());

                        data.ClientID = <?= isset($clientId) ? (int)$clientId : 'null' ?>;
                        data.historyID = <?= isset($historyID) ? (int)$historyID : 'null' ?>;

                        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            data[checkbox.name] = checkbox.checked ? 1 : 0;
                        });

                        const response = await fetch('AllFormSubmission.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.success) {
                            messageBox.style.color = '#0a6b2e';
                            messageBox.textContent = result.message || 'Data saved successfully!';
                            messageBox.className = 'form-message success';
                            successModal.style.display = 'flex';
                        } else {
                            messageBox.style.color = '#b22222';
                            messageBox.textContent = result.message || 'Failed to save data.';
                            messageBox.className = 'form-message error';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        messageBox.style.color = '#b22222';
                        messageBox.textContent = 'Network error. Please try again.';
                        messageBox.className = 'form-message error';
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save All';
                        setTimeout(() => {
                            messageBox.textContent = '';
                            messageBox.className = 'form-message';
                        }, 5000);
                    }
                });

                // Step 4️⃣: Close success modal (optional redirect)
                successClose.addEventListener('click', () => {
                    successModal.style.display = 'none';
                    // window.location.href = 'Freshman_Profile.php';
                });


            });
        </script>

    </div>

</body>

</html