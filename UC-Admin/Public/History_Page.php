<?php
require_once 'config/database.php';
require 'manageclients.dbf/view-personalform.php';

$pdo = pdo_connect_mysql();


if (isset($_GET['client_id']) && isset($_GET['history_id'])) {
    $clientID = $_GET['client_id'];
    $historyID = $_GET['history_id'];
    $date = $_GET['date'] ?? null;

    if (!$date) {
        die('Date is required');
    }

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo "Client not found!";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM history WHERE historyID = ?");
    $stmt->execute([$historyID]);
    $history = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$history) {
        echo "Medical history not found!";
        exit;
    }
} else {
    echo "Client or history ID not provided!";
    exit;
}
//=======================================================================
$medicalHistory = null;
$familymedicalHistory = null;
$socialHistoryData = null;
$data = null;

if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE ClientID = ?");
    $stmt2->execute([$clientID]);
    $medicalHistory = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
}

$expectedKeys = [
    'KnownIllness',
    'KnownIllnessDetails',
    'Hospitalization',
    'HospitalizationDetails',
    'Allergies',
    'AllergiesDetails',
    'ChildImmunization',
    'ChildImmunizationDetails',
    'PresentImmunizations',
    'PresentImmunizationsDetails',
    'CurrentMedicines',
    'CurrentMedicinesDetails',
    'DentalProblems',
    'DentalProblemsDetails',
    'PrimaryPhysician',
    'PrimaryPhysicianDetails'
];

// Ensure all keys exist
foreach ($expectedKeys as $key) {
    if (!isset($medicalHistory[$key])) {
        $medicalHistory[$key] = null;
    }
}
//=============================================
if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM familymedicalhistory WHERE ClientID = ?");
    $stmt2->execute([$clientID]);
    $familymedicalHistory = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
}

$expectedFamilyKeys = [
    'Allergy',
    'AllergyDetails',
    'Asthma',
    'AsthmaDetails',
    'Tuberculosis',
    'TuberculosisDetails',
    'Hypertension',
    'HypertensionDetails',
    'BloodDisease',
    'BloodDiseaseDetails',
    'Stroke',
    'StrokeDetails',
    'Diabetes',
    'DiabetesDetails',
    'Cancer',
    'CancerDetails',
    'LiverDisease',
    'LiverDiseaseDetails',
    'KidneyBladder',
    'KidneyBladderDetails',
    'BloodDisorder',
    'BloodDisorderDetails',
    'Epilepsy',
    'EpilepsyDetails',
    'MentalDisorder',
    'MentalDisorderDetails',
    'OtherIllness',
    'OtherIllnessDetails'
];

foreach ($expectedFamilyKeys as $key) {
    if (!isset($familymedicalHistory[$key])) {
        $familymedicalHistory[$key] = null;
    }
}
//======================================================
if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM personalsocialhistory WHERE ClientID = ?");
    $stmt2->execute([$clientID]);
    $socialHistoryData = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
}
//======================================================
if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM femalehealthhistory WHERE ClientID = ?");
    $stmt2->execute([$clientID]);
    $data = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
}

//======================================================
$stmt = $pdo->prepare('SELECT * FROM medicalcertificate WHERE historyID = ?');
$stmt->execute([$history['historyID']]);
$medicalCertData = $stmt->fetch(PDO::FETCH_ASSOC) ?: '';

if (!$medicalCertData) {
    $medicalCertData = [
        'PatientName'   => '',
        'PatientAge'    => '',
        'Gender'        => '',
        'ExamDate'      => '',
        'Findings'      => '',
        'Impression'    => '',
        'NoteContent'   => '',
        'LicenseNo'     => '',
        'DateIssued'    => ''
    ];
}
//========================================================================
$sql = "SELECT * FROM medicaldentalhistory WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$medicalHistory = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM familymedicalhistory WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$familymedicalHistory = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM personalsocialhistory WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$socialHistoryData = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM femalehealthhistory WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM physicalexamination WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$physicalExam = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM diagnosticresults WHERE historyID = :history_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['history_id' => $historyID]);
$diagnostic = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
//==========================================================================================

//==========================================================================================
// Fetch consultation record based on historyID and date
$sql = "SELECT * FROM consultationrecords WHERE historyid = :history_id OR DATE(datecreated) = :date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'history_id' => $historyID,
    'date' => $date
]);
$consultationrecords = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch prescription based on historyID and date
$sql = "SELECT * FROM prescriptions WHERE historyID = :history_id OR date_created = :date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'history_id' => $historyID,
    'date' => $date
]);
$prescriptions = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
//===============================================================================================
$stmt = $pdo->prepare("SELECT Surname, GivenName, MiddleName, Age, CurrentAddress, Gender, Course FROM personalinfo WHERE ClientID = ?");
$stmt->execute([$clientID]);
$personalinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$givenName = $personalinfo['GivenName'] ?? '--.--.--';
$middleName = $personalinfo['MiddleName'] ?? '--.--.--';
$surname = $personalinfo['Surname'] ?? '--.--.--';
$age = $personalinfo['Age'] ?? '';
$gender = $personalinfo['Gender'] ?? '';
$address = $personalinfo['CurrentAddress'] ?? '';
$course = $personalinfo['Course'] ?? '';

$fullName = trim($givenName . $surname);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="assets/css/manageusers.css">
    <link rel="stylesheet" href="assets/css/profileclients.css">
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="assets/css/historystyles.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/clientprofile.js" defer></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <title>Manage Profile</title>
</head>

<body>
    <script>
        const clientGender = "<?= htmlspecialchars($personalInfo['Gender']) ?>".toLowerCase();
        const clientType = "<?= htmlspecialchars($clientid['ClientType']) ?>".toLowerCase();

        function controlSectionVisibility() {
            console.log("Gender:", clientGender);
            console.log("Client Type:", clientType);

            if (clientGender === 'male') {
                const menstrualTab = document.querySelector('[data-target="menstrualHistory"]');
                const menstrualSection = document.getElementById('menstrualHistory');
                if (menstrualTab) menstrualTab.style.display = 'none';
                if (menstrualSection) menstrualSection.style.display = 'none';
            }
            /*
                        if (clientType !== 'faculty') {
                            const physicalTab = document.querySelector('[data-target="physicalExamination"]');
                            const physicalSection = document.getElementById('physicalExamination');
                            if (physicalTab) physicalTab.style.display = 'none';
                            if (physicalSection) physicalSection.style.display = 'none';
                        }*/
        }

        window.addEventListener('DOMContentLoaded', controlSectionVisibility);
    </script>
    <div class="header">
        <img src="assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title"> University Clinic </span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="assets/images/menu.png">
        </button>
        <div class="page-title">
            <a href="Manage_Clients.php">Manage Clients</a>
            <i class="fas fa-angle-right"></i>
            <a href="ClientProfile.php?id=<?= urlencode($clientID) ?>">Patient's Profile</a>
            <i class="fas fa-angle-right"></i>
            <h4>Patient's Visit History</h4>
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
            <a href="index.php">
                <button class="buttons" id="logoutbtn">
                    <img src="assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </a>
        </nav>

        <main class="content" id="mainContent">

            <a href="ClientProfile.php?id=<?= urlencode($clientID) ?>" class="btn btn-primary btn-sm">Back to Profile</a>
            <div class="nav-div">
                <div class="tabs">
                    <div class="tab active" data-target="medrec">
                        <img class="cp-btn-img"
                            src="assets/images/patienthistory2.svg"
                            data-active="assets/images/patienthistory1.svg"
                            data-inactive="assets/images/patienthistory2.svg">
                        Consultation Record
                    </div>

                    <div class="tab" data-target="rx">
                        <img class="cp-btn-img"
                            src="assets/images/patienthistory2.svg"
                            data-active="assets/images/patienthistory1.svg"
                            data-inactive="assets/images/patienthistory2.svg">
                        Rx Record
                    </div>
                    <div class="tab" data-target="medical-history">Medical History</div>
                    <div class="tab" data-target="medical-cert">Medical Certificate Request</div>

                    <!--  <div class="tab" data-target="medrec">Patient Record</div>-->
                </div>

                <div id="medical-history" style="display: none;">
                    <div class="medtabs">
                        <div class="medtab active" data-target="medicaldentalhistory">Medical & Dental History</div>
                        <div class="medtab" data-target="familymedicalhistory">Family Medical History</div>
                        <div class="medtab" data-target="personalsocialhistory">Personal & Social History</div>
                        <div class="medtab" data-target="menstrualHistory">Mentrual History</div>
                        <div class="medtab" data-target="physicalExamination">Physical Examination</div>
                        <div class="medtab" data-target="diagnosticResults">Diagnostic Results</div>
                    </div>
                    <div class="medinfotable-div" id="medicaldentalhistory" style="display: block;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Medical History Item</th>
                                    <th>Response</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Known Illness</td>
                                    <td><?= !empty($medicalHistory['KnownIllness']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['KnownIllnessDetails']) ? htmlspecialchars($medicalHistory['KnownIllnessDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Hospitalization</td>
                                    <td><?= !empty($medicalHistory['Hospitalization']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['HospitalizationDetails']) ? htmlspecialchars($medicalHistory['HospitalizationDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Allergies</td>
                                    <td><?= !empty($medicalHistory['Allergies']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['AllergiesDetails']) ? htmlspecialchars($medicalHistory['AllergiesDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Childhood Immunization</td>
                                    <td><?= !empty($medicalHistory['ChildImmunization']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['ChildImmunizationDetails']) ? htmlspecialchars($medicalHistory['ChildImmunizationDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Current Immunizations</td>
                                    <td><?= !empty($medicalHistory['PresentImmunizations']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['PresentImmunizationsDetails']) ? htmlspecialchars($medicalHistory['PresentImmunizationsDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Current Medications</td>
                                    <td><?= !empty($medicalHistory['CurrentMedicines']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['CurrentMedicinesDetails']) ? htmlspecialchars($medicalHistory['CurrentMedicinesDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Dental Problems</td>
                                    <td><?= !empty($medicalHistory['DentalProblems']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['DentalProblemsDetails']) ? htmlspecialchars($medicalHistory['DentalProblemsDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Primary Physician</td>
                                    <td><?= !empty($medicalHistory['PrimaryPhysician']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($medicalHistory['PrimaryPhysicianDetails']) ? htmlspecialchars($medicalHistory['PrimaryPhysicianDetails']) : 'N/A' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="fammedinfotable-div" id="familymedicalhistory" style="display: none;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Family Illness</th>
                                    <th>Response</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Allergy</td>
                                    <td><?= !empty($familymedicalHistory['Allergy']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['AllergyDetails']) ? htmlspecialchars($familymedicalHistory['AllergyDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Asthma</td>
                                    <td><?= !empty($familymedicalHistory['Asthma']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['AsthmaDetails']) ? htmlspecialchars($familymedicalHistory['AsthmaDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Tuberculosis</td>
                                    <td><?= !empty($familymedicalHistory['Tuberculosis']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['TuberculosisDetails']) ? htmlspecialchars($familymedicalHistory['TuberculosisDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Hypertension</td>
                                    <td><?= !empty($familymedicalHistory['Hypertension']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['HypertensionDetails']) ? htmlspecialchars($familymedicalHistory['HypertensionDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Blood Disease</td>
                                    <td><?= !empty($familymedicalHistory['BloodDisease']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['BloodDiseaseDetails']) ? htmlspecialchars($familymedicalHistory['BloodDiseaseDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Stroke</td>
                                    <td><?= !empty($familymedicalHistory['Stroke']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['StrokeDetails']) ? htmlspecialchars($familymedicalHistory['StrokeDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Diabetes</td>
                                    <td><?= !empty($familymedicalHistory['Diabetes']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['DiabetesDetails']) ? htmlspecialchars($familymedicalHistory['DiabetesDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Cancer</td>
                                    <td><?= !empty($familymedicalHistory['Cancer']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['CancerDetails']) ? htmlspecialchars($familymedicalHistory['CancerDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Liver Disease</td>
                                    <td><?= !empty($familymedicalHistory['LiverDisease']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['LiverDiseaseDetails']) ? htmlspecialchars($familymedicalHistory['LiverDiseaseDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Kidney/Bladder</td>
                                    <td><?= !empty($familymedicalHistory['KidneyBladder']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['KidneyBladderDetails']) ? htmlspecialchars($familymedicalHistory['KidneyBladderDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Blood Disorder</td>
                                    <td><?= !empty($familymedicalHistory['BloodDisorder']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['BloodDisorderDetails']) ? htmlspecialchars($familymedicalHistory['BloodDisorderDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Epilepsy</td>
                                    <td><?= !empty($familymedicalHistory['Epilepsy']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['EpilepsyDetails']) ? htmlspecialchars($familymedicalHistory['EpilepsyDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Mental Disorder</td>
                                    <td><?= !empty($familymedicalHistory['MentalDisorder']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['MentalDisorderDetails']) ? htmlspecialchars($familymedicalHistory['MentalDisorderDetails']) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <td>Other Illness</td>
                                    <td><?= !empty($familymedicalHistory['OtherIllness']) ? '✅ Yes' : '❌ No' ?></td>
                                    <td><?= !empty($familymedicalHistory['OtherIllnessDetails']) ? htmlspecialchars($familymedicalHistory['OtherIllnessDetails']) : 'N/A' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="medinfotable-div" id="personalsocialhistory" style="display: none;">
                        <table id="social-history-table" class="history-table">
                            <tr>
                                <th>Social History</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td>Alcohol Intake</td>
                                <td>
                                    <?php
                                    $alcohol = $socialHistoryData['AlcoholIntake'] ?? 'no';
                                    echo ($alcohol === 'no') ? '❌ No' : (($alcohol === 'former') ? '⏱ Former' : '✅ Yes');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo !empty($socialHistoryData['AlcoholDetails']) ? htmlspecialchars($socialHistoryData['AlcoholDetails']) : '—';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tobacco Use</td>
                                <td>
                                    <?php
                                    $tobacco = $socialHistoryData['TobaccoUse'] ?? 'no';
                                    echo ($tobacco === 'no') ? '❌ No' : (($tobacco === 'former') ? '⏱ Former' : '✅ Yes');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo !empty($socialHistoryData['TobaccoDetails']) ? htmlspecialchars($socialHistoryData['TobaccoDetails']) : '—';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Illicit Drug Use</td>
                                <td>
                                    <?php
                                    $drugs = $socialHistoryData['DrugUse'] ?? 'no';
                                    echo ($drugs === 'no') ? '❌ No' : (($drugs === 'former') ? '⏱ Former' : '✅ Yes');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo !empty($socialHistoryData['DrugDetails']) ? htmlspecialchars($socialHistoryData['DrugDetails']) : '—';
                                    ?>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="medinfotable-div" id="menstrualHistory" style="display: none;">
                        <div class="mentrualhistorywrapper">
                            <table class="history-table">
                                <tr>
                                    <th colspan="2">Menstrual History</th>
                                </tr>
                                <tr>
                                    <td><strong>Last Period</strong></td>
                                    <td><?= htmlspecialchars($data['LastPeriod'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Regularity</strong></td>
                                    <td><?= ucfirst(htmlspecialchars($data['Regularity'] ?? 'N/A')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Duration</strong></td>
                                    <td><?= htmlspecialchars($data['Duration'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. of pads/day</strong></td>
                                    <td><?= htmlspecialchars($data['PadsPerDay'] ?? 'N/A') ?></td>
                                </tr>

                                <tr>
                                    <th colspan="2">Dysmenorrhea</th>
                                </tr>
                                <tr>
                                    <td><strong>History of Dysmenorrhea</strong></td>
                                    <td><?= ucfirst(htmlspecialchars($data['Dysmenorrhea'] ?? 'N/A')) ?></td>
                                </tr>
                                <?php if (($data['Dysmenorrhea'] ?? '') == 'yes'): ?>
                                    <tr>
                                        <td><strong>Severity</strong></td>
                                        <td><?= ucfirst(htmlspecialchars($data['DysmenorrheaSeverity'] ?? 'N/A')) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Last OB-Gyne Checkup</strong></td>
                                    <td><?= htmlspecialchars($data['LastOBVisit'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Abnormal Bleeding</strong></td>
                                    <td><?= ucfirst(htmlspecialchars($data['AbnormalBleeding'] ?? 'N/A')) ?></td>
                                </tr>

                                <tr>
                                    <th colspan="2">Pregnancy</th>
                                </tr>
                                <tr>
                                    <td><strong>Previous Pregnancy</strong></td>
                                    <td><?= ucfirst(htmlspecialchars($data['PreviousPregnancy'] ?? 'N/A')) ?></td>
                                </tr>
                                <?php if (($data['PreviousPregnancy'] ?? '') == 'yes'): ?>
                                    <tr>
                                        <td><strong>Pregnancy Details</strong></td>
                                        <td><?= htmlspecialchars($data['PregnancyDetails'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Has Children</strong></td>
                                    <td><?= ucfirst(htmlspecialchars($data['HasChildren'] ?? 'N/A')) ?></td>
                                </tr>
                                <?php if (($data['HasChildren'] ?? '') == 'yes'): ?>
                                    <tr>
                                        <td><strong>Number of Children</strong></td>
                                        <td><?= htmlspecialchars($data['ChildrenCount'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <div class="medinfotable-div" id="physicalExamination" style="display: none;">

                        <h1 class="h1-style">Physical Examination</h1>
                        <!--<h4 class="form-section-title">PHYSICAL EXAMINATION</h4>-->

                        <div class="exam-table-container">
                            <table class="history-table">
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

                            <table class="history-table">
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

                    <div class="medinfotable-div" id="diagnosticResults" style="display: none;">
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
                <!--  <button type="button" id="toggle-form-btn">Show Medical Certificate Form</button>-->
                <div id="medical-cert" style="display: none;">
                    <div id="med-cert-form">
                        <form method="POST" action="manageclients.dbf/save_medical_certificate.php" id="medical-cert-form">
                            <input type="hidden" name="client_id" id="client-id" value="<?= htmlspecialchars($clientid['ClientID']) ?>">

                            <div style="text-align: center; margin-bottom: 10px; width: 90%;">
                                <img src="assets/images/LSPU-HEADER.svg" alt="LSPU Logo" style="width:400px; height: 110px;">
                            </div>

                            <div style="text-align: center; font-weight: bold; margin: 20px 0;">MEDICAL CERTIFICATE</div>

                            <div style="margin-bottom: 15px;">
                                This is to certify that <span class="underline cert-field" contenteditable="true" id="patient-name" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black;"></span>,
                                a <span class="underline cert-field" contenteditable="true" id="patient-age" style="display: inline-block; min-width: 30px; border-bottom: 1px solid black;"></span> year old F/M, has been seen and examined on
                                <input type="date" class="cert-field" id="exam-date" style="border: none; border-bottom: 1px solid black; display: inline-block; width: 120px;"></span> at the Medical Clinic.
                            </div>

                            <div style="margin-bottom: 10px;">
                                Pertinent findings: <span class="underline cert-field" contenteditable="true" id="findings" style="display: inline-block; min-width: 400px; border-bottom: 1px solid black;"></span>
                            </div>

                            <div style="margin-bottom: 10px;">
                                Impression on examination: <span class="underline cert-field" contenteditable="true" id="impression" style="display: inline-block; min-width: 400px; border-bottom: 1px solid black;"></span>
                            </div>

                            <div style="margin-bottom: 20px;">
                                NOTE: <span class="underline cert-field" contenteditable="true" id="note" style="display: inline-block; min-width: 400px; border-bottom: 1px solid black;"></span>
                            </div>

                            <div style="margin-top: 50px;">
                                Visiting Physician/University Nurse<br>
                                License No. <span class="underline cert-field" contenteditable="true" id="license-no" style="display: inline-block; min-width: 100px; border-bottom: 1px solid black;"></span><br>
                                Date Issued: <input type="date" class="cert-field" id="date-issued" style="border: none; border-bottom: 1px solid black;">
                            </div>

                            <div style="text-align: left; font-size: 12px; margin-top: 30px; display: flex; flex-direction: row; justify-content: space-between">
                                <p>LSPU-OSAS-SF-M08</p>
                                <p> Rev. 0</p>
                                <p>10 Aug. 2016</p>
                            </div>

                            <!-- Hidden fields for submission -->
                            <input type="hidden" name="patient_name" id="hidden-patient-name">
                            <input type="hidden" name="patient_age" id="hidden-patient-age">
                            <input type="hidden" name="exam_date" id="hidden-exam-date">
                            <input type="hidden" name="findings" id="hidden-findings">
                            <input type="hidden" name="impression" id="hidden-impression">
                            <input type="hidden" name="note" id="hidden-note">
                            <input type="hidden" name="license_no" id="hidden-license-no">
                            <input type="hidden" name="date_issued" id="hidden-date-issued">

                            <div class="cert-controls" style="margin-top: 20px; text-align: center;">
                                <button class="buttonsdp" type="button" onclick="downloadMedicalCertPDF()">Download Certificate as PDF</button>
                                <button class="buttonsdp" type="button" onclick="clearMedicalCert()">Clear Form</button>
                                <button class="buttonsdp" type="submit">Save</button>
                            </div>
                        </form>

                        <script>
                            function downloadMedicalCertPDF() {
                                const patientName = document.getElementById('patient-name').innerText.trim();
                                const patientAge = document.getElementById('patient-age').innerText.trim();
                                const examDate = document.getElementById('exam-date').value;
                                const findings = document.getElementById('findings').innerText.trim();
                                const impression = document.getElementById('impression').innerText.trim();
                                const note = document.getElementById('note').innerText.trim();
                                const licenseNo = document.getElementById('license-no').innerText.trim();
                                const dateIssued = document.getElementById('date-issued').value;
                                const clientId = document.getElementById('client-id').value;

                                const params = new URLSearchParams({
                                    client_id: clientId,
                                    patient_name: patientName,
                                    patient_age: patientAge,
                                    exam_date: examDate,
                                    findings: findings,
                                    impression: impression,
                                    note: note,
                                    license_no: licenseNo,
                                    date_issued: dateIssued
                                });

                                window.open('manageclients.dbf/generate_pdf_admin.php?' + params.toString(), '_blank');
                            }
                        </script>


                        <script>
                            document.getElementById('medical-cert-form').addEventListener('submit', function(event) {
                                event.preventDefault();

                                document.getElementById('hidden-patient-name').value = document.getElementById('patient-name').innerText.trim();
                                document.getElementById('hidden-patient-age').value = document.getElementById('patient-age').innerText.trim();
                                document.getElementById('hidden-exam-date').value = document.getElementById('exam-date').value; // Changed to .value
                                document.getElementById('hidden-findings').value = document.getElementById('findings').innerText.trim();
                                document.getElementById('hidden-impression').value = document.getElementById('impression').innerText.trim();
                                document.getElementById('hidden-note').value = document.getElementById('note').innerText.trim();
                                document.getElementById('hidden-license-no').value = document.getElementById('license-no').innerText.trim();
                                document.getElementById('hidden-date-issued').value = document.getElementById('date-issued').value.trim();

                                const formData = new FormData(this);

                                fetch('manageclients.dbf/save_medical_certificate.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.text())
                                    .then(data => {
                                        console.log('Success:', data);
                                        alert('Medical Certificate saved successfully!');
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred while saving.');
                                    });
                            });
                        </script>

                        <script>
                            // PHP values echoed into JavaScript variables
                            const medicalCertData = {
                                patient_name: "<?= htmlspecialchars($medicalCertData['PatientName'] ?? '') ?>",
                                patient_age: "<?= htmlspecialchars($medicalCertData['PatientAge'] ?? '') ?>",
                                exam_date: "<?= htmlspecialchars($medicalCertData['ExamDate'] ?? '') ?>",
                                findings: "<?= htmlspecialchars($medicalCertData['Findings'] ?? '') ?>",
                                impression: "<?= htmlspecialchars($medicalCertData['Impression'] ?? '') ?>",
                                note: "<?= htmlspecialchars($medicalCertData['NoteContent'] ?? '') ?>",
                                license_no: "<?= htmlspecialchars($medicalCertData['LicenseNo'] ?? '') ?>",
                                date_issued: "<?= htmlspecialchars($medicalCertData['DateIssued'] ?? '') ?>"
                            };

                            // Populate contenteditable spans
                            document.getElementById('patient-name').innerText = medicalCertData.patient_name;
                            document.getElementById('patient-age').innerText = medicalCertData.patient_age;
                            document.getElementById('findings').innerText = medicalCertData.findings;
                            document.getElementById('impression').innerText = medicalCertData.impression;
                            document.getElementById('note').innerText = medicalCertData.note;
                            document.getElementById('license-no').innerText = medicalCertData.license_no;

                            // Populate date and input fields
                            document.getElementById('exam-date').value = medicalCertData.exam_date;
                            document.getElementById('date-issued').value = medicalCertData.date_issued;
                        </script>

                    </div>

                </div>


                <!--====================================================================================================================================-->
                <div id="medrec" class="medcontainer" style="display: block;">
                    <div class="medrec-subparent-div">
                        <form id="consultationForm" class="medrec-subparent-div">
                            <input type="hidden" name="client_id" id="client-id" value="<?= htmlspecialchars($clientid['ClientID']) ?>">
                            <div class="left-info-div">
                                <div class="phyexam-div">
                                    <h3 style="padding: 15px;">Patient's Info</h3>
                                    <div class="info-row">
                                        <span class="info-label">Name:</span>
                                        <span id="name" contenteditable="true"><?= htmlspecialchars($fullName) ?: ''; ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Age:</span>
                                        <span id="age" contenteditable="true"><?= htmlspecialchars($age) ?: ''; ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Address:</span>
                                        <span id="address" contenteditable="true"><?= htmlspecialchars($address) ?: ''; ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Course:</span>
                                        <span id="course" contenteditable="true"><?= htmlspecialchars($course) ?: ''; ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Date:</span>
                                        <span id="date"></span>
                                    </div>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const dateSpan = document.getElementById("date");
                                            const today = new Date();
                                            const formattedDate = today.toLocaleDateString("en-US", {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            });
                                            dateSpan.textContent = formattedDate;
                                        });
                                    </script>
                                </div>

                                <div id="phyexam-div-2" class="phyexam-div">
                                    <h3 style="padding: 15px;">Vital Signs</h3>
                                    <div class="info-row">
                                        <span class="info-label">BP:</span>
                                        <input type="text" id="bp_input" name="bp" placeholder="BP" required value="<?= htmlspecialchars($consultationrecords['BP'] ?? '') ?>">
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">HR/PR:</span>
                                        <input type="text" id="hr_pr" name="hr_pr" placeholder="HR/PR" required value="<?= htmlspecialchars($consultationrecords['HR_PR'] ?? '') ?>">
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">T°:</span>
                                        <input type="text" id="temp_input" name="temp" placeholder="T°" required value="<?= htmlspecialchars($consultationrecords['Temp'] ?? '') ?>">
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">O²sat:</span>
                                        <input type="text" id="o2sat" name="o2sat" placeholder="O²sat" required value="<?= htmlspecialchars($consultationrecords['O2sat'] ?? '') ?>">
                                    </div>

                                </div>
                            </div>

                            <div class="right-info-div">

                                <div class="cert-controls" style="margin-top: 0px;">
                                    <button class="buttonsdp2" type="button" onclick="submitPdfForm()">Dowload as PDF</button>
                                </div>
                                <div id="saveStatus" style="margin-top: 10px;"></div>

                                <div class="SOAP-div" style="align-items: left;">
                                    <h3 style="padding: 15px;">Subjective</h3>
                                    <textarea style="font-family: Roboto, sans-serif" id="subjective" name="subjective" rows="1" cols="50" placeholder="Enter notes or paragraph here..." oninput="autoGrow(this)"><?= htmlspecialchars($consultationrecords['Subjective'] ?? '') ?></textarea>

                                    <h3 style="padding: 15px;">Objective</h3>
                                    <textarea style="font-family: Roboto, sans-serif" id="objective" name="objective" rows="1" cols="50" placeholder="Enter notes or paragraph here..." oninput="autoGrow(this)"><?= htmlspecialchars($consultationrecords['Objective'] ?? '') ?></textarea>

                                    <h3 style="padding: 15px;">Assessment</h3>
                                    <textarea style="font-family: Roboto, sans-serif" id="assessment" name="assessment" rows="1" cols="50" placeholder="Enter notes or paragraph here..." oninput="autoGrow(this)"><?= htmlspecialchars($consultationrecords['Assesment'] ?? '') ?></textarea>

                                    <h3 style="padding: 15px;">Plan</h3>
                                    <textarea style="font-family: Roboto, sans-serif" id="plan" name="plan" rows="1" cols="50" placeholder="Enter notes or paragraph here..." oninput="autoGrow(this)"><?= htmlspecialchars($consultationrecords['Plan'] ?? '') ?></textarea>


                                </div>
                            </div>

                            <input type="hidden" name="patient_name" id="hidden-name">
                            <input type="hidden" name="patient_age" id="hidden-age">
                            <input type="hidden" name="patient_address" id="hidden-address">
                            <input type="hidden" name="patient_course" id="hidden-course">
                            <input type="hidden" name="date" id="hidden-date">


                        </form>
                        <form id="pdfForm" action="manageclients.dbf/patients_rec_genpdf.php" method="post" target="_blank">

                            <input type="text" id="pdf-name" name="name" hidden>
                            <input type="text" id="pdf-age" name="age" hidden>
                            <input type="text" id="pdf-address" name="address" hidden>
                            <input type="text" id="pdf-course" name="course" hidden>

                            <input type="text" id="pdf-bp" name="bp" hidden>
                            <input type="text" id="pdf-hr_pr" name="hr_pr" hidden>
                            <input type="text" id="pdf-temp" name="temp" hidden>
                            <input type="text" id="pdf-o2sat" name="o2sat" hidden>

                            <input type="hidden" id="pdf-date" name="date">

                            <textarea id="pdf-subjective" name="subjective" hidden></textarea>
                            <textarea id="pdf-objective" name="objective" hidden></textarea>
                            <textarea id="pdf-assessment" name="assessment" hidden></textarea>
                            <textarea id="pdf-plan" name="plan" hidden></textarea>

                        </form>

                    </div>

                </div>
                <script>
                    function submitPdfForm() {
                        document.getElementById('pdf-name').value = document.getElementById('name').textContent;
                        document.getElementById('pdf-age').value = document.getElementById('age').textContent;
                        document.getElementById('pdf-address').value = document.getElementById('address').textContent;
                        document.getElementById('pdf-course').value = document.getElementById('course').textContent;
                        document.getElementById('pdf-bp').value = document.getElementById('bp_input').value;
                        document.getElementById('pdf-hr_pr').value = document.getElementById('hr_pr').value;
                        document.getElementById('pdf-temp').value = document.getElementById('temp_input').value;
                        document.getElementById('pdf-o2sat').value = document.getElementById('o2sat').value;
                        document.getElementById('pdf-subjective').value = document.getElementById('subjective').value;
                        document.getElementById('pdf-objective').value = document.getElementById('objective').value;
                        document.getElementById('pdf-assessment').value = document.getElementById('assessment').value;
                        document.getElementById('pdf-plan').value = document.getElementById('plan').value;

                        const today = new Date();
                        document.getElementById('pdf-date').value = today.toLocaleDateString("en-US", {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        document.getElementById('pdfForm').submit();
                    }
                </script>


                <script>
                    function autoGrow(element) {
                        element.style.height = "5px"; // reset height
                        element.style.height = (element.scrollHeight) + "px"; // set new height
                    }
                </script>



                <div id="rx" class="medrec_container" style="display: none;">
                    <div class="medrec-subparent-div">
                        <form method="POST" action="manageclients.dbf/generate_rx_pdf.php" class="medrec-subparent-div" onsubmit="return preparePdfData()" target="_blank">

                            <input type="hidden" name="client_id" id="client-id" value="<?= htmlspecialchars($clientid['ClientID']) ?>">

                            <div class="left-info-div">
                                <div class="phyexam-div">
                                    <h3 style="padding: 15px;">Patient's Info</h3>

                                    <div class="info-row">
                                        <span class="info-label">Name:</span>
                                        <span id="name"><?= htmlspecialchars($fullName) ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Age/Sex:</span>
                                        <span id="age"><?= htmlspecialchars($age) ?></span>
                                        <p>/</p>
                                        <span id="gender"><?= htmlspecialchars($gender) ?></span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Impression:</span>
                                        <input name="p-impression" id="impression" type="text" value="<?= htmlspecialchars($prescriptions['impression'] ?? '') ?>" />
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Date:</span>
                                        <span id="date2"><?= htmlspecialchars($prescriptions['date_created'] ?? '') ?></span>
                                    </div>
                                </div>

                                <div id="phyexam-div-2" class="phyexam-div">
                                    <div class="info-row">
                                        <span class="info-label">Visiting Physician:</span>
                                        <input type="text" name="physician" placeholder="Visiting Physician" value="<?= htmlspecialchars($prescriptions['physician'] ?? '') ?>" />
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Lic.No:</span>
                                        <input type="text" name="LicNo" placeholder="Lic.No." value="<?= htmlspecialchars($prescriptions['license_no'] ?? '') ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="right-info-div">
                                <div class="cert-controls" style="margin-top: 20px;">
                                    <div class="cert-controls" style="margin-top: 0px;">
                                        <button class="buttonsdp2" type="submit">Download as PDF</button>
                                    </div>
                                </div>
                                <p id="save-message" style="color: green; display: none; font-weight: normal;"></p>

                                <div class="SOAP-div" style="align-items: left;">
                                    <h3 style="font-family: 'DejaVu Sans'; font-size: 28pt;">℞</h3>
                                    <textarea style="font-family: Roboto, sans-serif" id="notes" name="notes" rows="20" cols="50" placeholder="Enter notes or paragraph here..."><?= htmlspecialchars($prescriptions['notes'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Hidden inputs to send data -->
                            <input type="hidden" name="patient_name" id="input_patient_name" value="<?= htmlspecialchars($prescriptions['patient_name'] ?? '') ?>" />
                            <input type="hidden" name="patient_age" id="input_patient_age" value="<?= htmlspecialchars($prescriptions['age'] ?? '') ?>" />
                            <input type="hidden" name="patient_sex" value="<?= htmlspecialchars($gender) ?>">
                            <input type="hidden" name="date" id="input_date" value="<?= htmlspecialchars($prescriptions['date_created'] ?? '') ?>" />

                            <!-- Hidden inputs for physician and LicNo -->
                            <input type="hidden" name="input_physician" id="input_physician" value="<?= htmlspecialchars($prescriptions['physician'] ?? '') ?>" />
                            <input type="hidden" name="input_LicNo" id="input_LicNo" value="<?= htmlspecialchars($prescriptions['license_no'] ?? '') ?>" />

                        </form>
                    </div>
                </div>


            </div>
        </main>
    </div>
</body>

</html