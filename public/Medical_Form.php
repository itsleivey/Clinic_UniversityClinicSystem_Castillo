<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['ClientID'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../MedicalDB/PersonalInfoLogic.php';

$pdo = pdo_connect_mysql();
$clientId = $_SESSION['ClientID'];

// Get the most recent in-progress historyID
$getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' ORDER BY historyID DESC LIMIT 1");
$getHistory->execute([$clientId]);
$historyID = $getHistory->fetchColumn();

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="UC-Client/assets/css/ClientStyles.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="UC-Client/assets/css/MediaQuery.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="UC-Client/assets/css/CleintMedForm.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="UC-Client/assets/css/CFormMedQuery.css?v=<?= time(); ?>">
    <script type="text/javascript" src="UC-Client/assets/js/script.js" defer></script>
    <script type="text/javascript" src="UC-Client/assets/js/Medform.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>

<body>

    <div class="header">

        <img src="UC-Client/assets/images/Lspu logo.png" type="image/webp" alt="Logo">
        <div class="title">
            <span>University</span>
            <span>Clinic</span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="UC-Client/assets/images/menu-icon.svg">
        </button>
    </div>

    <div class="main-container">
        <nav class="navbar">
            <a href="Profile.php">
                <button class="buttons" id="profileBtn">
                    <img src="UC-Client/assets/images/Usericon1.svg" class="button-icon-nav">
                    <span class="nav-text">Manage Profile</span>
                </button>
            </a>
            <a href="Medical_Form.php">
                <button class="buttons" id="medicalBtn">
                    <img src="UC-Client/assets/images/Form-icon2.svg" class="button-icon-nav">
                    <span class="nav-text">Medical Form</span>
                </button>
            </a>
            <form action="logout.php" method="post">
                <button type="submit" class="buttons" id="logoutbtn">
                    <img src="UC-Client/assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </form>
        </nav>
        <main class="content">
            <!-- <div class="step-nav">
                <div class="step" id="step1">
                    <span class="step-number">1</span>
                    <span class="step-text">Personal Information</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step2">
                    <span class="step-number">2</span>
                    <span class="step-text">Past Medical & Dental History</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step3">
                    <span class="step-number">3</span>
                    <span class="step-text">Family Medical History</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step4">
                    <span class="step-number">4</span>
                    <span class="step-text">Personal & Social History</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step5">
                    <span class="step-number">5</span>
                    <span class="step-text">For Females</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step6">
                    <span class="step-number">6</span>
                    <span class="step-text">Physical Examination</span>
                    <span class="step-check">&#10003;</span>
                </div>
                <div class="step" id="step7">
                    <span class="step-number">7</span>
                    <span class="step-text">Diagnostic Result</span>
                    <span class="step-check">&#10003;</span>
                </div>
            </div>

           
            <div class="form-container active" id="personalForm"> </div>
                <div class="form-header">
                    <h2>Personal Information</h2>
                </div>-->
            <!-- Personal Info Form -->
            <div class="content-forms-seprator">
                <!-- <div class="med-form-nav">
                    <ul>
                        <li><a href="#personal-info-input">Personal Information</a></li>
                        <li><a href="#medical-dental-history-input">Medical & Dental History</a></li>
                        <li><a href="#family-medical-history-input">Family Medical History</a></li>
                        <li><a href="#personal-social-history-input">Personal & Social History</a></li>    
                        <li><a href="#personal-info-input">Personal Information</a></li>
                        <li><a href="#for-females-input">Female Menstrual History</a></li>
                        <li><a href="#physical-examination-input">Physical Examination</a></li>
                        <li><a href="#Diagnostic-Results">Diagnostic Results</li>
                </div>-->
                <div id="personal-info-input" class="scroll-input-div">
                    <form class="form-in-forms" id="personalInfoForm" action="../MedicalDB/PersonalInfoLogic.php" method="POST" autocomplete="off">
                        <h1 class="h1-style">Personal Information</h1>
                        <div class="form-row">
                            <div>
                                <label for="Surname"><i class="fa-solid fa-user"></i> Surname</label>
                                <input type="text" id="Surname" name="Surname" placeholder="Surname" value="<?= htmlspecialchars($formData['Surname'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="GivenName"><i class="fa-solid fa-user"></i> Given Name</label>
                                <input type="text" id="GivenName" name="GivenName" placeholder="Given Name" value="<?= htmlspecialchars($formData['GivenName'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="MiddleName"><i class="fa-solid fa-user"></i> Middle Name</label>
                                <input type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?= htmlspecialchars($formData['MiddleName'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="Age"><i class="fa-solid fa-hourglass-half"></i> Age</label>
                                <input type="number" id="Age" name="Age" placeholder="Age" min="1" max="120" value="<?= htmlspecialchars($formData['Age'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="genderSelect"><i class="fa-solid fa-venus-mars"></i> Sex</label>
                                <select id="genderSelect" name="Gender" required>
                                    <option value="">Gender</option>
                                    <option value="male" <?= (isset($formData['Gender']) && $formData['Gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= (isset($formData['Gender']) && $formData['Gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="DateOfBirth"><i class="fa-solid fa-calendar-day"></i> Date of Birth</label>
                                <input type="date" id="DateOfBirth" name="DateOfBirth" value="<?= htmlspecialchars($formData['DateOfBirth'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="Status"><i class="fa-solid fa-ring"></i> Status</label>
                                <select id="Status" name="Status" required>
                                    <option value="">Status</option>
                                    <option value="single" <?= (isset($formData['Status']) && $formData['Status'] === 'single') ? 'selected' : '' ?>>Single</option>
                                    <option value="married" <?= (isset($formData['Status']) && $formData['Status'] === 'married') ? 'selected' : '' ?>>Married</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="Course"><i class="fa-solid fa-book"></i> Course</label>
                                <input type="text" id="Course" name="Course" placeholder="Course" value="<?= htmlspecialchars($formData['Course'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="SchoolYearEntered"><i class="fa-solid fa-calendar-alt"></i> School Year Entered</label>
                                <input type="text" id="SchoolYearEntered" name="SchoolYearEntered" placeholder="School Year Entered" value="<?= htmlspecialchars($formData['SchoolYearEntered'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="CurrentAddress"><i class="fa-solid fa-house"></i> Current Address</label>
                                <input type="text" id="CurrentAddress" name="CurrentAddress" placeholder="Current Address" value="<?= htmlspecialchars($formData['CurrentAddress'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="ContactNumber"><i class="fa-solid fa-phone"></i> Contact Number</label>
                                <input type="text" id="ContactNumber" name="ContactNumber" placeholder="Contact Number" value="<?= htmlspecialchars($formData['ContactNumber'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="MothersName"><i class="fa-solid fa-person-dress"></i> Mother's Name</label>
                                <input type="text" id="MothersName" name="MothersName" placeholder="Mother's Name" value="<?= htmlspecialchars($formData['MothersName'] ?? '') ?>">
                            </div>
                            <div>
                                <label for="FathersName"><i class="fa-solid fa-person"></i> Father's Name</label>
                                <input type="text" id="FathersName" name="FathersName" placeholder="Father's Name" value="<?= htmlspecialchars($formData['FathersName'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="GuardiansName"><i class="fa-solid fa-user-shield"></i> Guardian's Name</label>
                                <input type="text" id="GuardiansName" name="GuardiansName" placeholder="Guardian's Name" value="<?= htmlspecialchars($formData['GuardiansName'] ?? '') ?>">
                            </div>

                            <div>
                                <label for="EmergencyContactName"><i class="fa-solid fa-triangle-exclamation"></i> Emergency Contact Name</label>
                                <input type="text" id="EmergencyContactName" name="EmergencyContactName" placeholder="Emergency Contact Name" value="<?= htmlspecialchars($formData['EmergencyContactName'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label for="EmergencyContactRelationship"><i class="fa-solid fa-people-arrows"></i> Emergency Contact Relationship</label>
                                <input type="text" id="EmergencyContactRelationship" name="EmergencyContactRelationship" placeholder="Relationship" value="<?= htmlspecialchars($formData['EmergencyContactRelationship'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div>
                                <label for="EmergencyGuardiansName"><i class="fa-solid fa-user-shield"></i> Name of Contact Person in CASE OF EMERGENCY</label>
                                <input type="text" id="GuardiansName" name="EmergencyGuardiansName" placeholder="(REQUIRED)" value="<?= htmlspecialchars($formData['EmergencyContactPerson'] ?? '') ?>">
                            </div>
                        </div>


                        <button class="form-buttons" type="submit">Save</button>
                    </form>

                </div>
                <!-- Medical & Dental History Form -->
                <div id="medical-dental-history-input" class="scroll-input-div">
                    <form class="form-in-forms" id="medicalDentalForm" method="POST" action="Med_Post.php" autocomplete="off">
                        <h1 class="h1-style">Medical & Dental History</h1>
                        <input type="hidden" name="medicalDentalSubmit" value="1">

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="knownIllness" name="knownIllness" <?= $medicalData['KnownIllness'] ? 'checked' : '' ?>>
                            <label for="knownIllness">Previous/present KNOWN illness</label>
                            <input type="text" class="details-input" placeholder="Details" name="knownIllnessDetails"
                                value="<?= htmlspecialchars($medicalData['KnownIllnessDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="hospitalization" name="hospitalization" <?= $medicalData['Hospitalization'] ? 'checked' : '' ?>>
                            <label for="hospitalization">Past hospitalization/confinement</label>
                            <input type="text" class="details-input" placeholder="Details" name="hospitalizationDetails"
                                value="<?= htmlspecialchars($medicalData['HospitalizationDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="allergies" name="allergies" <?= $medicalData['Allergies'] ? 'checked' : '' ?>>
                            <label for="allergies">Known allergies to food or medicine</label>
                            <input type="text" class="details-input" placeholder="Details" name="allergiesDetails"
                                value="<?= htmlspecialchars($medicalData['AllergiesDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="childImmunization" name="childImmunization" <?= $medicalData['ChildImmunization'] ? 'checked' : '' ?>>
                            <label for="childImmunization">Childhood immunization</label>
                            <input type="text" class="details-input" placeholder="Details" name="childImmunizationDetails"
                                value="<?= htmlspecialchars($medicalData['ChildImmunizationDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="presentImmunizations" name="presentImmunizations" <?= $medicalData['PresentImmunizations'] ? 'checked' : '' ?>>
                            <label for="presentImmunizations">Present immunizations (ex. Flu, Hepa B, etc.)</label>
                            <input type="text" class="details-input" placeholder="Details" name="presentImmunizationsDetails"
                                value="<?= htmlspecialchars($medicalData['PresentImmunizationsDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="currentMedicines" name="currentMedicines" <?= $medicalData['CurrentMedicines'] ? 'checked' : '' ?>>
                            <label for="currentMedicines">Currently taking medicines/vitamins</label>
                            <input type="text" class="details-input" placeholder="Details" name="currentMedicinesDetails"
                                value="<?= htmlspecialchars($medicalData['CurrentMedicinesDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="dentalProblems" name="dentalProblems" <?= $medicalData['DentalProblems'] ? 'checked' : '' ?>>
                            <label for="dentalProblems">Dental problems (ex. Gingivitis, etc.)</label>
                            <input type="text" class="details-input" placeholder="Details" name="dentalProblemsDetails"
                                value="<?= htmlspecialchars($medicalData['DentalProblemsDetails']) ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="primaryPhysician" name="primaryPhysician" <?= $medicalData['PrimaryPhysician'] ? 'checked' : '' ?>>
                            <label for="primaryPhysician">Primary care physician (name, specialty, clinic location and date of last check-up/follow-up)</label>
                            <input type="text" class="details-input" placeholder="Details" name="primaryPhysicianDetails"
                                value="<?= htmlspecialchars($medicalData['PrimaryPhysicianDetails']) ?>">
                        </div>

                        <button type="submit" class="form-buttons">Save Medical & Dental History</button>
                    </form>

                </div>

                <!-- Family Medical History Inputs-->
                <div id="family-medical-history-input" class="scroll-input-div">
                    <form class="form-in-forms" id="family-med-historyForm" method="POST" action="familymedhis_Post.php" autocomplete="off">
                        <h1 class="h1-style">Family Medical History</h1>
                        <input type="hidden" name="familymedicalSubmit" value="1">

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="allergy" name="allergy" <?php echo is_checked('Allergy'); ?>>
                            <label for="allergy">Allergy</label>
                            <input type="text" class="details-input" placeholder="Specify" name="allergyDetails" value="<?php echo $familymedicalhistory['AllergyDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="asthma" name="asthma" <?php echo is_checked('Asthma'); ?>>
                            <label for="asthma">Asthma/Thias</label>
                            <input type="text" class="details-input" placeholder="Specify" name="asthmaDetails" value="<?php echo $familymedicalhistory['AsthmaDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="tuberculosis" name="tuberculosis" <?php echo is_checked('Tuberculosis'); ?>>
                            <label for="tuberculosis">Tuberculosis</label>
                            <input type="text" class="details-input" placeholder="Specify" name="tuberculosisDetails" value="<?php echo $familymedicalhistory['TuberculosisDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="hypertension" name="hypertension" <?php echo is_checked('Hypertension'); ?>>
                            <label for="hypertension">Hypertension</label>
                            <input type="text" class="details-input" placeholder="Specify" name="hypertensionDetails" value="<?php echo $familymedicalhistory['HypertensionDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="bloodDisease" name="bloodDisease" <?php echo is_checked('BloodDisease'); ?>>
                            <label for="bloodDisease">Blood Disease</label>
                            <input type="text" class="details-input" placeholder="Specify" name="bloodDiseaseDetails" value="<?php echo $familymedicalhistory['BloodDiseaseDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="stroke" name="stroke" <?php echo is_checked('Stroke'); ?>>
                            <label for="stroke">Stroke</label>
                            <input type="text" class="details-input" placeholder="Specify" name="strokeDetails" value="<?php echo $familymedicalhistory['StrokeDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="diabetes" name="diabetes" <?php echo is_checked('Diabetes'); ?>>
                            <label for="diabetes">Diabetes</label>
                            <input type="text" class="details-input" placeholder="Specify" name="diabetesDetails" value="<?php echo $familymedicalhistory['DiabetesDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="cancer" name="cancer" <?php echo is_checked('Cancer'); ?>>
                            <label for="cancer">Cancer</label>
                            <input type="text" class="details-input" placeholder="Specify" name="cancerDetails" value="<?php echo $familymedicalhistory['CancerDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="liverDisease" name="liverDisease" <?php echo is_checked('LiverDisease'); ?>>
                            <label for="liverDisease">Liver Disease</label>
                            <input type="text" class="details-input" placeholder="Specify" name="liverDiseaseDetails" value="<?php echo $familymedicalhistory['LiverDiseaseDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="kidneyBladder" name="kidneyBladder" <?php echo is_checked('KidneyBladder'); ?>>
                            <label for="kidneyBladder">Kidney/Bladder Disease</label>
                            <input type="text" class="details-input" placeholder="Specify" name="kidneyBladderDetails" value="<?php echo $familymedicalhistory['KidneyBladderDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="bloodDisorder" name="bloodDisorder" <?php echo is_checked('BloodDisorder'); ?>>
                            <label for="bloodDisorder">Blood Disorder</label>
                            <input type="text" class="details-input" placeholder="Specify" name="bloodDisorderDetails" value="<?php echo $familymedicalhistory['BloodDisorderDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="epilepsy" name="epilepsy" <?php echo is_checked('Epilepsy'); ?>>
                            <label for="epilepsy">Epilepsy</label>
                            <input type="text" class="details-input" placeholder="Specify" name="epilepsyDetails" value="<?php echo $familymedicalhistory['EpilepsyDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="mentalDisorder" name="mentalDisorder" <?php echo is_checked('MentalDisorder'); ?>>
                            <label for="mentalDisorder">Mental Disorder</label>
                            <input type="text" class="details-input" placeholder="Specify" name="mentalDisorderDetails" value="<?php echo $familymedicalhistory['MentalDisorderDetails'] ?? ''; ?>">
                        </div>

                        <div class="form-row checkbox-row">
                            <input type="checkbox" id="otherIllness" name="otherIllness" <?php echo is_checked('OtherIllness'); ?>>
                            <label for="otherIllness">Other Illness</label>
                            <input type="text" class="details-input" placeholder="Specify" name="otherIllnessDetails" value="<?php echo $familymedicalhistory['OtherIllnessDetails'] ?? ''; ?>">
                        </div>

                        <button type="submit" class="form-buttons">Save Family Medical History</button>
                    </form>
                </div>

                <!-- Personal & Social History Inputs-->
                <div id="personal-social-history-input" class="scroll-input-div">
                    <form class="form-in-forms" id="personal-social-historyForm" autocomplete="off">
                        <h1 class="h1-style">Personal & Social History</h1>

                        <div class="form-row">
                            <label for="alcoholIntake">Alcohol intake:</label>
                            <select id="alcoholIntake" name="alcoholIntake" required>
                                <option value="no" <?= ($socialHistoryData['AlcoholIntake'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['AlcoholIntake'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Frequency/Amount (if applicable)"
                                name="alcoholDetails" value="<?= htmlspecialchars($socialHistoryData['AlcoholDetails'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <label for="tobaccoUse">Tobacco use:</label>
                            <select id="tobaccoUse" name="tobaccoUse" required>
                                <option value="no" <?= ($socialHistoryData['TobaccoUse'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['TobaccoUse'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Frequency/Amount (if applicable)"
                                name="tobaccoDetails" value="<?= htmlspecialchars($socialHistoryData['TobaccoDetails'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <label for="drugUse">Illicit drug use:</label>
                            <select id="drugUse" name="drugUse" required>
                                <option value="no" <?= ($socialHistoryData['DrugUse'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                <option value="yes" <?= ($socialHistoryData['DrugUse'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            </select>
                            <input type="text" class="details-input" placeholder="Type/Frequency (if applicable)"
                                name="drugDetails" value="<?= htmlspecialchars($socialHistoryData['DrugDetails'] ?? '') ?>">
                        </div>

                        <button type="submit" class="form-buttons">Save</button>
                        <div id="social-history-message" class="form-message"></div>
                    </form>
                </div>



                <!-- For Females only Inputs-->
                <div id="for-females-input" class="scroll-input-div">
                    <form class="form-in-forms" id="for-female-form" autocomplete="off" method="POST">
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

                        <div class="fem-form-row">
                            <button class="form-buttons" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const genderSelect = document.getElementById("genderSelect");
                        const femaleInputs = document.getElementById("for-females-input");

                        function toggleFemaleInputs() {
                            if (genderSelect.value === "female") {
                                femaleInputs.style.display = "block";
                            } else {
                                femaleInputs.style.display = "none";
                            }
                        }

                        // Initial check on page load
                        toggleFemaleInputs();

                        // Update on change
                        genderSelect.addEventListener("change", toggleFemaleInputs);
                    });
                </script>


                <!-- Physical Examination -->
                <div id="physical-examination-input" class="scroll-input-div">
                    <div class="exam-notice">
                        <p class="notice-text"><strong>Note:</strong> Clients/Patients should stop filling up the form here.
                            This section must be completed by medical personnel after consultation.</p>
                    </div>

                    <h1 class="h1-style">Physical Examination</h1>
                    <!--<h4 class="form-section-title">PHYSICAL EXAMINATION</h4>-->

                    <div class="exam-table-container">
                        <table class="physical-exam-table">
                            <thead>
                                <tr>
                                    <th>Height (m)</th>
                                    <th>Weight (kg)</th>
                                    <th>BMI (kg/m²)</th>
                                    <th>BP (mmHg)</th>
                                    <th>HR (bpm)</th>
                                    <th>RR (cpm)</th>
                                    <th>Temp (°C)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($physicalExam['Height'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['Weight'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['BMI'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['BP'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['HR'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['RR'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($physicalExam['Temp'] ?? '') ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="physical-exam-table">
                            <thead>
                                <tr>
                                    <th>Examination Area</th>
                                    <th>Status</th>
                                    <th>Findings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Gen. Appearance and Skin</td>
                                    <td><?= isset($physicalExam['GenAppearanceAndSkinNormal']) ? ($physicalExam['GenAppearanceAndSkinNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['GenAppearanceAndSkinFindings'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td>Head and Neck</td>
                                    <td><?= isset($physicalExam['HeadAndNeckNormal']) ? ($physicalExam['HeadAndNeckNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['HeadAndNeckFindings'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td>Chest and Back</td>
                                    <td><?= isset($physicalExam['ChestAndBackNormal']) ? ($physicalExam['ChestAndBackNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['ChestAndBackFindings'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td>Abdomen</td>
                                    <td><?= isset($physicalExam['AbdomenNormal']) ? ($physicalExam['AbdomenNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['AbdomenFindings'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td>Extremities</td>
                                    <td><?= isset($physicalExam['ExtremitiesNormal']) ? ($physicalExam['ExtremitiesNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['ExtremitiesFindings'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <td>Others</td>
                                    <td><?= isset($physicalExam['OthersNormal']) ? ($physicalExam['OthersNormal'] ? 'Normal' : 'Abnormal') : '' ?></td>
                                    <td><?= htmlspecialchars($physicalExam['OthersFindings'] ?? '') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Diagnostic Results -->
                <div id="Diagnostic-Results" class="scroll-input-div">
                    <div class="form-container-diagnostic">
                        <h1 class="h1-style">Medical Diagnostic Information</h1>

                        <h2>V. Diagnostic Results</h2>
                        <div class="form-section">
                            <p><strong>Date of Examination:</strong> <?= htmlspecialchars($diagnostic['ExamDate'] ?? 'N/A') ?></p>

                            <p><strong>Chest X-ray Performed:</strong> <?= (!empty($diagnostic['ChestXrayPerformed']) ? 'Yes' : 'No') ?></p>
                            <p><strong>Chest X-ray Findings:</strong><?= nl2br(htmlspecialchars($diagnostic['XrayFindings'] ?? 'N/A')) ?></p>
                        </div>

                        <h2>VI. Impression</h2>
                        <div class="form-section">
                            <p><?= nl2br(htmlspecialchars($diagnostic['Impression'] ?? 'N/A')) ?></p>
                        </div>

                        <h2>VII. Plan</h2>
                        <div class="form-section">
                            <p><strong>Discussions with Patient:</strong> <?= (!empty($diagnostic['Discussions']) ? 'Yes' : 'No') ?></p>
                            <p><strong>Discussion Details:</strong> <?= nl2br(htmlspecialchars($diagnostic['DiscussionDetails'] ?? 'N/A')) ?></p>

                            <p><strong>Home Medication Prescribed:</strong> <?= (!empty($diagnostic['HomeMedication']) ? 'Yes' : 'No') ?></p>
                            <p><strong>Medication Details:</strong> <?= nl2br(htmlspecialchars($diagnostic['MedicationDetails'] ?? 'N/A')) ?></p>

                            <p><strong>Home Instructions Given:</strong> <?= (!empty($diagnostic['HomeInstructions']) ? 'Yes' : 'No') ?></p>
                            <p><strong>Instruction Details:</strong> <?= nl2br(htmlspecialchars($diagnostic['InstructionDetails'] ?? 'N/A')) ?></p>
                        </div>

                        <h2>Other Details</h2>
                        <div class="form-section">
                            <p><strong>Abbreviations Used:</strong> <?= htmlspecialchars($diagnostic['AbbreviationsUsed'] ?? 'N/A') ?></p>
                            <p><strong>F-1 Date:</strong> <?= htmlspecialchars($diagnostic['F1Date'] ?? 'N/A') ?></p>
                            <p><strong>Medical Certificate Issued:</strong> <?= (!empty($diagnostic['MedicalCertIssued']) ? 'Yes' : 'No') ?></p>
                            <p><strong>Referred To:</strong> <?= htmlspecialchars($diagnostic['ReferredTo'] ?? 'N/A') ?></p>
                        </div>

                        <h2>Recommendation</h2>
                        <div class="form-section">
                            <p><strong>Recommendation:</strong>
                                <?php
                                switch ($diagnostic['Recommendation'] ?? '') {
                                    case 'fit':
                                        echo 'Fit to Work';
                                        break;
                                    case 'fit_sports':
                                        echo 'Fit to Participate in Sports';
                                        break;
                                    case 'fit_enroll':
                                        echo 'Fit to Enroll but requires further evaluation';
                                        break;
                                    case 'fit_work_eval':
                                        echo 'Fit to Work but requires further evaluation';
                                        break;
                                    case 'fit_sports_eval':
                                        echo 'Fit to Participate in Sports but requires further evaluation';
                                        break;
                                    default:
                                        echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>

                        <h2>Physician's Details</h2>
                        <div class="form-section">
                            <p><strong>Physician's Name:</strong> <?= htmlspecialchars($diagnostic['PhysicianName'] ?? 'N/A') ?></p>
                            <p><strong>License Number:</strong> <?= htmlspecialchars($diagnostic['LicenseNo'] ?? 'N/A') ?></p>
                            <p><strong>Date Signed:</strong> <?= htmlspecialchars($diagnostic['SignatureDate'] ?? 'N/A') ?></p>
                            <p><strong>Institution:</strong> <?= htmlspecialchars($diagnostic['Institution'] ?? 'LAGUNA STATE POLYTECHNIC UNIVERSITY, UNIVERSITY CLINIC') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="toggle-btn-div">
                        <button class="toggle-btn left-btn" disabled><img
                                src="UC-Client/assets/images/left-arrow-icon.svg"></button>
                        <button class="toggle-btn right-btn" onclick="showForm('medical')"><img
                                src="UC-Client/assets/images/right-arrow-icon.svg"></button>
                    </div>-->

            <!--
                <div class="right-content-div">
                    <div class="notice-div">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Process</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Physical Examination</td>
                                        <td id="status1">Complete all forms</td>
                                    </tr>
                                    <tr>
                                        <td>Diagnostic Results</td>
                                        <td id="status2">Complete Consultation</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="reminder" id="reminder">
                                <strong>Reminder:</strong> Complete all forms to proceed with the consultation and receive your medical certificate.
                            </div>
                        </div>
                    </div>

                    <div id="call-div">
                        <div id="pic-div">
                            <img id="picimg" src="UC-Client/assets/images/office-pic.svg" alt="University Clinic" loading="lazy">
                            <div id="text-overlay">
                                <h3>Emergency Contacts:</h3>
                                <p>Clinic Hotline: (+63) 912-345-6789</p>
                                <p>Campus Emergency: (+63) 911-123-4567</p>
                                <p>Email: support@universityclinic.edu</p>
                            </div>
                        </div>
                        <h2>Need help?</h2>
                        <a href="#" class="clinic-policies-label">View Clinic Policies | FAQs</a>
                    </div>
                </div>
                                -->
        </main>
    </div>
</body>

</html>