<?php
require_once 'config/database.php';
require_once('Profile/Profile_db.php');

$pdo = pdo_connect_mysql();
$user_data = getUserDataFromDatabase($pdo);

if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clientid) {
        echo "Client not found!";
        exit;
    }

    require 'manageclients.dbf/view-personalform.php';
    require 'manageclients.dbf/medicalhistory.php';
} else {
    echo "No client selected!";
    exit;
}

$clientTypeText = '--.--.--';  // default text
$department = '--.--.--';

if ($clientID) {
    $conn = pdo_connect_mysql();
    $query = $conn->prepare("SELECT ClientType, Department, Course FROM clients WHERE ClientID = ?");
    $query->execute([$clientID]);
    $client = $query->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $clientTypeRaw = $client['ClientType'] ?: '--.--.--';
        $department = $client['Department'] ?: '--.--.--';
        $course = $client['Course'] ?: '--.--.--';

        // Map clientType values to display text
        if ($clientTypeRaw === 'Faculty') {
            $clientTypeText = 'Teaching-Personnel';
        } elseif ($clientTypeRaw === 'Personnel') {
            $clientTypeText = 'Non-Teaching Personnel';
        } else {
            $clientTypeText = $clientTypeRaw;
        }
    }
}
//============================================
$currentStatus = 'undone'; // Default status
$actionDate = '--.--.--';
$actionTime = '--:--:--';

if ($clientID) {
    try {
        $pdo = pdo_connect_mysql();
        $stmt = $pdo->prepare("SELECT actionDate, actionTime, progress 
                               FROM history 
                               WHERE ClientID = ? 
                               ORDER BY historyID DESC 
                               LIMIT 1");
        $stmt->execute([$clientID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $currentStatus = $result['progress'] ?? 'undone';
            $actionDate = $result['actionDate'] ?? '--.--.--';
            $actionTime = $result['actionTime'] ?? '--:--:--';
        }
    } catch (PDOException $e) {
        error_log("Status check error: " . $e->getMessage());
    }
}

$statusClass = '';
if ($currentStatus === 'completed') {
    $statusClass = 'status-completed';
} elseif ($currentStatus === 'inprogress') {
    $statusClass = 'status-inprogress';
} elseif ($currentStatus === 'undone') {
    $statusClass = 'status-undone';
}
/*
$headerText = 'Recent Progress'; // Default text
if ($currentStatus === 'inprogress') {
    $headerText = 'Current Progress';
}*/
//=========================================\<?php
$clientID = $clientID ?? null;
$historyData = [];
$error = null;

if (!$clientID) {
    $error = "Client ID is missing.";
} else {
    try {
        $pdo = pdo_connect_mysql();
        $stmt = $pdo->prepare("SELECT historyID, actionDate, actionTime, progress FROM history WHERE ClientID = ? ORDER BY actionDate DESC, actionTime DESC");
        $stmt->execute([$clientID]);
        $historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching history: " . $e->getMessage());
        $error = "Error loading history data.";
    }
}
//=========================================
if (!$clientID) {
    die("Client ID is required");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM history WHERE ClientID = ? ORDER BY historyID DESC");
    $stmt->execute([$clientID]);
    $historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching history data: " . $e->getMessage());
}
//===========================================================================
$stmt = $pdo->prepare("SELECT * FROM logbook WHERE ClientID = ?");
$stmt->execute([$clientID]);
$logbookEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
//===========================================================================
date_default_timezone_set('Asia/Manila');

$currentTime = date('h:i:s A');

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM medicalcertificate WHERE ClientID = ? AND historyID = ?");
$checkStmt->execute([$clientID, $historyID]);
$medicalCertCompleted = $checkStmt->fetchColumn();

if ($medicalCertCompleted > 0) {
    $updateStmt = $pdo->prepare("UPDATE history SET progress = 'completed', actionTime = ? WHERE historyID = ?");
    $updateStmt->execute([$currentTime, $historyID]);
}
//============================================================================

//=============================================================================
$stmt = $pdo->prepare("SELECT ClientType FROM Clients WHERE ClientID = ?");
$stmt->execute([$clientID]);
$clienttype = $stmt->fetchColumn();

$isstudent = ($clienttype === 'Student');
$isfreshman = ($clienttype === 'Freshman');
$showLimitedTabs = ($clienttype === 'Freshman' || $clienttype === 'NewPersonnel');
//=============================================================================
$name = $agency = $npaddress = $npage = $sex = $civil_status = $position = '';
$blood_test = $urinalysis = $chest_xray = $drug_test = $psych_test = $neuro_test = 0;
$physician_signature = $physician_agency = $other_info = $license_no = $height = $weight = $blood_type = '';
$date_created = date('Y-m-d'); // default today

if ($clientID) {
    $stmt = $pdo->prepare("SELECT * FROM newpersonnel_form WHERE client_id = :client_id ORDER BY form_id Desc");
    $stmt->execute(['client_id' => $clientID]);
    $npdata = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($npdata) {
        $name = $npdata['full_name'] ?? '';
        $agency = $npdata['agency_address'] ?? '';
        $npaddress = $npdata['address'] ?? '';
        $npage = $npdata['age'] ?? '';
        $sex = $npdata['sex'] ?? '';
        $civil_status = $npdata['civil_status'] ?? '';
        $position = $npdata['proposed_position'] ?? '';

        $blood_test = !empty($npdata['blood_test']);
        $urinalysis = !empty($npdata['urinalysis']);
        $chest_xray = !empty($npdata['chest_xray']);
        $drug_test = !empty($npdata['drug_test']);
        $psych_test = !empty($npdata['psych_test']);
        $neuro_test = !empty($npdata['neuro_test']);

        $physician_signature = $npdata['physician_signature'] ?? '';
        $physician_agency = $npdata['physician_agency'] ?? '';
        $other_info = $npdata["OtherInfo"] ?? '';
        $license_no = $npdata['physician_license'] ?? '';
        $height = $npdata['height'] ?? '';
        $weight = $npdata['weight'] ?? '';
        $blood_type = $npdata['blood_type'] ?? '';
        $date_created = $npdata['date_created'] ?? date('Y-m-d');
        $official_designation = $npdata['physician_designation'] ?? '';
    }
}
//===============================================================================================================
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
//=====================================================
$stmt = $pdo->prepare("SELECT * FROM femalehealthhistory WHERE ClientID = ?");
$stmt->execute([$clientID]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="assets/css/manageusers.css">
    <link rel="stylesheet" href="assets/css/patientformstyles.css">
    <link rel="stylesheet" href="assets/css/profileclients.css">
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">

    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/clientprofile.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
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
            <h4>Patient's Profile</h4>
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
            <div class="profile-div">
                <div class="profile-div2">
                    <!--    <button id="backbtn" onclick="window.history.back()"><img src="assets/images//back-icon.svg"></button>-->
                    <div class="profile-pic-info-div">
                        <p><strong>Patient ID:</strong> <?= htmlspecialchars($clientid['ClientID']) ?></p>

                        <div id="profile-pic-div">
                            <?php
                            $profilePath = !empty($clientid['profilePicturePath'])
                                ? '../../uploads/' . $clientid['profilePicturePath']
                                : '../../uploads/profilepic2.png';
                            ?>
                            <div class="profile-pic-wrapper">
                                <img id="profile-pic" src="<?= htmlspecialchars($profilePath) ?>" alt="Profile Picture">
                            </div>
                            <div class="profilediv">
                                <h5><?= htmlspecialchars(($clientid['Firstname'] ?? '')) ?></h5>
                                <h5><?= htmlspecialchars($clientid['Email']) ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-info-div">
                    <div class="profile-info">
                        <div id="left-text" class="divtext">
                            <p class="info-label">Department: <span class="info-value"><?= htmlspecialchars($department) ?></span></p>
                            <?= ($isstudent || $isfreshman) ? '<p class="info-label">Course: <span class="info-value">' . htmlspecialchars($course) . '</span></p>' : '' ?>

                            <p class="info-label">Client Type: <span class="info-value"><?= htmlspecialchars($clientTypeText) ?></span></p>
                        </div>
                    </div>

                </div>
            </div>

            <div id="nav-div-1" class="nav-div" style="display: block;">
                <div class="tabs">
                    <div class="tabs-child">
                        <?php if (!$showLimitedTabs): ?>
                            <div class="tab" data-target="visit-history">
                                <img class="cp-btn-img"
                                    src="assets/images/time-past 2.svg"
                                    data-active="assets/images/time-past 2.svg"
                                    data-inactive="assets/images/time-past 3.svg">
                                Visit History
                            </div>
                        <?php endif; ?>

                        <div class="tab <?php echo $showLimitedTabs ? 'active' : 'active'; ?>" data-target="personal-info-div">
                            <img class="cp-btn-img"
                                src="assets/images/personalinfo2.svg"
                                data-active="assets/images/personalinfo1.svg"
                                data-inactive="assets/images/personalinfo2.svg">
                            Personal Information
                        </div>

                        <?php if ($clienttype === 'Freshman'): ?>

                            <div class="tab" data-target="medical-history">
                                <img class="cp-btn-img"
                                    src="assets/images/medicalhistory2.svg"
                                    data-active="assets/images/medicalhistory1.svg"
                                    data-inactive="assets/images/medicalhistory2.svg">
                                Medical Records
                            </div>
                        <?php endif; ?>

                        <?php if ($clienttype === 'NewPersonnel') : ?>
                            <div class="tab" data-target="np_medical-history">
                                <img class="cp-btn-img"
                                    src="assets/images/medicalhistory2.svg"
                                    data-active="assets/images/medicalhistory1.svg"
                                    data-inactive="assets/images/medicalhistory2.svg">
                                For Employment
                            </div>
                        <?php endif; ?>


                        <div class="tab" data-target="medical-cert">
                            <img class="cp-btn-img"
                                src="assets/images/medcert2.svg"
                                data-active="assets/images/medcert1.svg"
                                data-inactive="assets/images/medcert2.svg">
                            Medical Certificate Request
                        </div>
                    </div>
                    <script>
                        const tabs = document.querySelectorAll('.tab');

                        tabs.forEach(tab => {
                            tab.addEventListener('click', () => {
                                tabs.forEach(t => {
                                    t.classList.remove('active');
                                    const img = t.querySelector('img');
                                    img.src = img.getAttribute('data-inactive');
                                });

                                tab.classList.add('active');
                                const img = tab.querySelector('img');
                                img.src = img.getAttribute('data-active');
                            });
                        });
                    </script>
                </div>

                <style>
                    .tabs {
                        gap: 25px;
                    }

                    .tab {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 10px;
                        font-size: clamp(9px, 1vw, 14px);
                    }

                    .cp-btn-img {
                        height: 25px;
                        width: 25px;
                    }

                    #person-info-icon {
                        height: 29px;
                        width: 29px;
                    }
                </style>

                <?php if (!$showLimitedTabs): ?>
                    <div id="visit-history" class="department-table-container" style="display: none;">

                        <div class="filter-container">
                            <div class="filter-group">
                                <label class="person-value-label-history" for="idSearch">Search by ID:</label>
                                <input class="history-filter" type="text" id="idSearch" placeholder="Enter ID">
                            </div>
                            <div class="filter-group">
                                <label class="person-value-label-history" for="dateSearch">Search by Date:</label>
                                <input class="history-filter" type="date" id="dateSearch">
                            </div>
                            <button class="add-btn" onclick="toggleNavDivs()">
                                <i class="fas fa-plus"></i> Add Consultation
                            </button>
                            <script>
                                function toggleNavDivs() {
                                    document.querySelector('.nav-div').style.display = 'none';
                                    document.querySelector('.nav-div2').style.display = 'flex';

                                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

                                    const visitTab = document.querySelector('.tab[data-target="medrec"]');
                                    if (visitTab) {
                                        visitTab.classList.add('active');
                                    }

                                    document.querySelectorAll('#personal-info-div, #medical-history, #medical-cert, #visit-history, #medrec, #rx').forEach(content => {
                                        content.style.display = 'none';
                                    });

                                    const visitSection = document.getElementById('medrec');
                                    if (visitSection) {
                                        visitSection.style.display = 'block';
                                    }
                                }
                            </script>
                            <div style="display: flex; width: 100%; height: 500px; justify-content: flex-end; margin-right: 20px; overflow-y: auto; padding: 10px;">
                                <table class="department-table">
                                    <thead>
                                        <tr>
                                            <th class="id-col">ID</th>
                                            <th class="id-col">Client ID</th>
                                            <th class="action-datetime">Date</th>
                                            <th class="action-datetime">Time</th>
                                            <th class="id-col">Remarks</th>
                                            <th id="viewth" class="visual-col">View History</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historyData as $index => $row): ?>
                                            <?php
                                            $url = "History_Page.php?client_id=" . urlencode($row['ClientID']) .
                                                "&history_id=" . urlencode($row['historyID']) .
                                                "&date=" . urlencode($row['actionDate']);
                                            ?>
                                            <tr class="history-row"
                                                data-tooltip="Click to view"
                                                data-href="<?= htmlspecialchars($url) ?>"
                                                data-history-id="<?= htmlspecialchars($row['historyID']) ?>"
                                                data-client-id="<?= htmlspecialchars($row['ClientID']) ?>">

                                                <td class="id-col"><?= htmlspecialchars($row['historyID']) ?></td>
                                                <td class="id-col"><?= htmlspecialchars($row['ClientID']) ?></td>
                                                <td class="action-datetime"><?= htmlspecialchars($row['actionDate']) ?></td>
                                                <td class="action-datetime"><?= htmlspecialchars($row['actionTime']) ?></td>
                                                <td class="progress-<?= htmlspecialchars($row['progress']) ?>">
                                                    <?= ucfirst(htmlspecialchars($row['progress'])) ?>
                                                </td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($url) ?>" class="btn btn-primary btn-sm">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>

                        <script>
                            const idInput = document.getElementById('idSearch');
                            const dateInput = document.getElementById('dateSearch');

                            idInput.addEventListener('input', filterTable);
                            dateInput.addEventListener('input', filterTable);

                            function filterTable() {
                                const idFilter = idInput.value.trim().toLowerCase();
                                const dateFilter = dateInput.value.trim().toLowerCase();
                                const rows = document.querySelectorAll('#visit-history table tbody tr');

                                rows.forEach(row => {
                                    const idText = row.querySelector('td:nth-child(1)').textContent.trim().toLowerCase(); // ID
                                    const dateText = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase(); // Action Date

                                    const idMatches = idText.includes(idFilter);
                                    const dateMatches = dateText.includes(dateFilter);

                                    row.style.display = (idMatches && dateMatches) ? '' : 'none';
                                });
                            }
                        </script>


                        <!-- JavaScript to make row clickable -->
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                document.querySelectorAll(".history-row").forEach(row => {
                                    row.addEventListener("click", function(e) {
                                        // Avoid redirecting if the View button was clicked
                                        if (!e.target.closest("a")) {
                                            window.location.href = this.dataset.href;
                                        }
                                    });
                                });
                            });
                        </script>

                    </div>
                <?php endif; ?>
                <div id="personal-info-div" style="display: flex">

                    <div class="button-group">
                        <button id="editButton" type="button" class="personalform-buttons">Edit</button>
                        <button id="backButton" type="button" class="personalform-buttons" style="display: none;">Back</button>
                    </div>
                    <div class="info-grid">
                        <p><span class="person-info-label">Surname:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['Surname'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Given Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['GivenName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Middle Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['MiddleName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Age:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['Age'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Gender:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['Gender'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Date of Birth:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['DateOfBirth'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Status:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['Status'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Course:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['Course'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">School Year Entered:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['SchoolYearEntered'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Contact Number:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['ContactNumber'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Current Address:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['CurrentAddress'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Mother's Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['MothersName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Father's Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['FathersName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Guardian's Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['GuardiansName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Emergency Contact Name:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['EmergencyContactName'] ?? '--.--.--') ?></span>
                        </p>

                        <p><span class="person-info-label">Emergency Contact Relationship:</span><br>
                            <span class="person-value-label"><?= htmlspecialchars($personalInfo['EmergencyContactRelationship'] ?? '--.--.--') ?></span>
                        </p>
                    </div>

                    <div id="personal-info-input" class="scroll-input-div" style="display: none;">
                        <form class="form-in-forms" id="personalInfoForm" autocomplete="off">
                            <input type="hidden" name="ClientID" value="<?= htmlspecialchars($clientID) ?>">
                            <div class="reminder-banner">
                                ⚠️ Please be careful when editing the patient's personal information.
                                Use edit mode **only if the patient is unable to update it themselves**.
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="Surname"><i class="fa-solid fa-user"></i> Surname</label>
                                    <input type="text" id="Surname" name="Surname" placeholder="Surname" value="<?= htmlspecialchars($personalInfo['Surname'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="GivenName"><i class="fa-solid fa-user"></i> Given Name</label>
                                    <input type="text" id="GivenName" name="GivenName" placeholder="Given Name" value="<?= htmlspecialchars($personalInfo['GivenName'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="MiddleName"><i class="fa-solid fa-user"></i> Middle Name</label>
                                    <input type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?= htmlspecialchars($personalInfo['MiddleName'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="Age"><i class="fa-solid fa-hourglass-half"></i> Age</label>
                                    <input type="number" id="Age" name="Age" placeholder="Age" min="1" max="120" value="<?= htmlspecialchars($personalInfo['Age'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="genderSelect"><i class="fa-solid fa-venus-mars"></i> Sex</label>
                                    <select id="genderSelect" name="Gender" required>
                                        <option value="">Gender</option>
                                        <option value="male" <?= (isset($personalInfo['Gender']) && $personalInfo['Gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= (isset($personalInfo['Gender']) && $personalInfo['Gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="DateOfBirth"><i class="fa-solid fa-calendar-day"></i> Date of Birth</label>
                                    <input type="date" id="DateOfBirth" name="DateOfBirth" value="<?= htmlspecialchars($personalInfo['DateOfBirth'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="Status"><i class="fa-solid fa-ring"></i> Status</label>
                                    <select id="Status" name="Status" required>
                                        <option value="">Status</option>
                                        <option value="single" <?= (isset($personalInfo['Status']) && $personalInfo['Status'] === 'single') ? 'selected' : '' ?>>Single</option>
                                        <option value="married" <?= (isset($personalInfo['Status']) && $personalInfo['Status'] === 'married') ? 'selected' : '' ?>>Married</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="Course"><i class="fa-solid fa-book"></i> Course</label>
                                    <input type="text" id="Course" name="Course" placeholder="Course" value="<?= htmlspecialchars($personalInfo['Course'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="SchoolYearEntered"><i class="fa-solid fa-calendar-alt"></i> School Year Entered</label>
                                    <input type="text" id="SchoolYearEntered" name="SchoolYearEntered" placeholder="School Year Entered" value="<?= htmlspecialchars($personalInfo['SchoolYearEntered'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="CurrentAddress"><i class="fa-solid fa-house"></i> Current Address</label>
                                    <input type="text" id="CurrentAddress" name="CurrentAddress" placeholder="Current Address" value="<?= htmlspecialchars($personalInfo['CurrentAddress'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="ContactNumber"><i class="fa-solid fa-phone"></i> Contact Number</label>
                                    <input type="text" id="ContactNumber" name="ContactNumber" placeholder="Contact Number" value="<?= htmlspecialchars($personalInfo['ContactNumber'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="MothersName"><i class="fa-solid fa-person-dress"></i> Mother's Name</label>
                                    <input type="text" id="MothersName" name="MothersName" placeholder="Mother's Name" value="<?= htmlspecialchars($personalInfo['MothersName'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="FathersName"><i class="fa-solid fa-person"></i> Father's Name</label>
                                    <input type="text" id="FathersName" name="FathersName" placeholder="Father's Name" value="<?= htmlspecialchars($personalInfo['FathersName'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="GuardiansName"><i class="fa-solid fa-user-shield"></i> Guardian's Name</label>
                                    <input type="text" id="GuardiansName" name="GuardiansName" placeholder="Guardian's Name" value="<?= htmlspecialchars($personalInfo['GuardiansName'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="EmergencyContactName"><i class="fa-solid fa-triangle-exclamation"></i> Emergency Contact Name</label>
                                    <input type="text" id="EmergencyContactName" name="EmergencyContactName" placeholder="Emergency Contact Name" value="<?= htmlspecialchars($personalInfo['EmergencyContactName'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label for="EmergencyContactRelationship"><i class="fa-solid fa-people-arrows"></i> Emergency Contact Relationship</label>
                                    <input type="text" id="EmergencyContactRelationship" name="EmergencyContactRelationship" placeholder="Relationship" value="<?= htmlspecialchars($personalInfo['EmergencyContactRelationship'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label for="EmergencyGuardiansName"><i class="fa-solid fa-user-shield"></i> Name of Contact Person in CASE OF EMERGENCY</label>
                                    <input type="text" id="EmergencyContactPerson" name="EmergencyContactPerson" placeholder="(REQUIRED)" value="<?= htmlspecialchars($personalInfo['EmergencyContactPerson'] ?? '') ?>">

                                </div>
                            </div>
                            <button class="form-buttons" type="submit">Save</button>
                        </form>

                    </div>
                    <script>
                        document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
                            e.preventDefault(); // Prevent full page reload

                            const form = e.target;
                            const formData = new FormData(form);

                            fetch('manageclients.dbf/PersonalInfoLogic.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(data => {
                                    alert(data.message);
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred. Please try again.');
                                });
                        });
                    </script>
                    <script>
                        document.getElementById('editButton').addEventListener('click', function() {
                            document.querySelector('.info-grid').style.display = 'none';
                            document.getElementById('personal-info-input').style.display = 'block';
                            document.getElementById('editButton').style.display = 'none';
                            document.getElementById('backButton').style.display = 'inline-block';
                        });

                        document.getElementById('backButton').addEventListener('click', function() {
                            document.querySelector('.info-grid').style.display = 'grid';
                            document.getElementById('personal-info-input').style.display = 'none';
                            document.getElementById('editButton').style.display = 'inline-block';
                            document.getElementById('backButton').style.display = 'none';
                        });
                    </script>


                </div>


                <div id="medical-history" style="display: none;">
                    <div class="medtabs">
                        <div class="medtab active" data-target="medicaldentalhistory">Medical & Dental History</div>
                        <div class="medtab" data-target="familymedicalhistory">Family Medical History</div>
                        <div class="medtab" data-target="personalsocialhistory">Personal & Social History</div>
                        <div class="medtab" data-target="menstrualHistory">Mentrual History</div>
                        <div class="medtab" data-target="physicalExamination">Physical Examination</div>
                        <div class="medtab" data-target="diagnosticResults">Diagnostic Results</div>
                        <a href="manageclients.dbf/medrecords_generatepdf.php?ClientID=<?= $clientID ?>" target="_blank">
                            <button style="background-color: #3498db; color: white; border: none;" class="medtab" type="button">
                                Save as PDF
                            </button>
                        </a>

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
                        <form id="phy-exam-form" action="manageclients.dbf/submit-phy-exam.php" method="POST">
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
                                        <td><input type="text" class="custom-input" name="height" id="height" value="<?= htmlspecialchars($physicalExam['Height'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="weight" id="weight" value="<?= htmlspecialchars($physicalExam['Weight'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="bmi" id="bmi" value="<?= htmlspecialchars($physicalExam['BMI'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="bp" id="bp" value="<?= htmlspecialchars($physicalExam['BP'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="hr" id="hr" value="<?= htmlspecialchars($physicalExam['HR'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="rr" id="rr" value="<?= htmlspecialchars($physicalExam['RR'] ?? '') ?>" /></td>
                                        <td><input type="text" class="custom-input" name="temp" id="temp" value="<?= htmlspecialchars($physicalExam['Temp'] ?? '') ?>" /></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="physical-exam-table">
                                <thead>
                                    <tr>
                                        <th>Examination Area</th>
                                        <th>Normal</th>
                                        <th>Findings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Gen. Appearance and Skin</td>
                                        <td>
                                            <select name="skin_normal">
                                                <option value="1" <?= (isset($physicalExam['GenAppearanceAndSkinNormal']) && $physicalExam['GenAppearanceAndSkinNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['GenAppearanceAndSkinNormal']) && $physicalExam['GenAppearanceAndSkinNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="skin_findings" value="<?= htmlspecialchars($physicalExam['GenAppearanceAndSkinFindings'] ?? '') ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Head and Neck</td>
                                        <td>
                                            <select name="head_normal">
                                                <option value="1" <?= (isset($physicalExam['HeadAndNeckNormal']) && $physicalExam['HeadAndNeckNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['HeadAndNeckNormal']) && $physicalExam['HeadAndNeckNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="head_findings" value="<?= htmlspecialchars($physicalExam['HeadAndNeckFindings'] ?? '') ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Chest and Back</td>
                                        <td>
                                            <select name="chest_normal">
                                                <option value="1" <?= (isset($physicalExam['ChestAndBackNormal']) && $physicalExam['ChestAndBackNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['ChestAndBackNormal']) && $physicalExam['ChestAndBackNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="chest_findings" value="<?= htmlspecialchars($physicalExam['ChestAndBackFindings'] ?? '') ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Abdomen</td>
                                        <td>
                                            <select name="abdomen_normal">
                                                <option value="1" <?= (isset($physicalExam['AbdomenNormal']) && $physicalExam['AbdomenNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['AbdomenNormal']) && $physicalExam['AbdomenNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="abdomen_findings" value="<?= htmlspecialchars($physicalExam['AbdomenFindings'] ?? '') ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Extremities</td>
                                        <td>
                                            <select name="extremities_normal">
                                                <option value="1" <?= (isset($physicalExam['ExtremitiesNormal']) && $physicalExam['ExtremitiesNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['ExtremitiesNormal']) && $physicalExam['ExtremitiesNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="extremities_findings" value="<?= htmlspecialchars($physicalExam['ExtremitiesFindings'] ?? '') ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Others</td>
                                        <td>
                                            <select name="others_normal">
                                                <option value="1" <?= (isset($physicalExam['OthersNormal']) && $physicalExam['OthersNormal'] == '1') ? 'selected' : '' ?>>Yes</option>
                                                <option value="0" <?= (isset($physicalExam['OthersNormal']) && $physicalExam['OthersNormal'] == '0') ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="custom-input" name="others_findings" value="<?= htmlspecialchars($physicalExam['OthersFindings'] ?? '') ?>" /></td>
                                    </tr>
                                </tbody>
                            </table>

                            <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientID) ?>" />
                            <button class="buttonsdp" type="button" onclick="submitForm()">Submit Exam</button>
                        </form>

                    </div>

                    <div class="medinfotable-div" id="diagnosticResults" style="display: none;">
                        <div class="form-container">
                            <h1>Medical Diagnostic Form</h1>

                            <form id="diagnosticform" method="POST" action="manageclients.dbf/submit_diagnostic.php">
                                <h2>V. Diagnostic Results</h2>
                                <div class="form-section">
                                    <div class="form-group">
                                        <label>Date of Examination:</label>
                                        <input type="date" name="exam_date" value="<?= $diagnostic['ExamDate'] ?? '' ?>">
                                    </div>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="chest_xray" <?= !empty($diagnostic['ChestXrayPerformed']) ? 'checked' : '' ?>> Chest X-ray performed
                                        </label>
                                        <div class="form-group">
                                            <label>Findings:</label>
                                            <textarea class="custom-textarea" name="xray_findings"><?= htmlspecialchars($diagnostic['XrayFindings'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <h2>VI. Impression</h2>
                                <div class="form-section">
                                    <div class="form-group">
                                        <textarea class="custom-textarea" name="impression"><?= htmlspecialchars($diagnostic['Impression'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <h2>VII. Plan</h2>
                                <div class="form-section">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="discussions" <?= !empty($diagnostic['Discussions']) ? 'checked' : '' ?>> Discussions with patient
                                        </label>
                                        <div class="form-group">
                                            <textarea class="custom-textarea" name="discussion_details"><?= htmlspecialchars($diagnostic['DiscussionDetails'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="home_medication" <?= !empty($diagnostic['HomeMedication']) ? 'checked' : '' ?>> Home medication prescribed
                                        </label>
                                        <div class="form-group">
                                            <textarea class="custom-textarea" name="medication_details"><?= htmlspecialchars($diagnostic['MedicationDetails'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="home_instructions" <?= !empty($diagnostic['HomeInstructions']) ? 'checked' : '' ?>> Home instructions given
                                        </label>
                                        <div class="form-group">
                                            <textarea class="custom-textarea" name="instruction_details"><?= htmlspecialchars($diagnostic['InstructionDetails'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="form-group">
                                        <label>Abbreviations Used:</label>
                                        <input type="text" class="custom-input" name="abbreviations" value="<?= htmlspecialchars($diagnostic['AbbreviationsUsed'] ?? '') ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>F-f (Date):</label>
                                        <input type="date" name="f1_date" value="<?= $diagnostic['F1Date'] ?? '' ?>">
                                    </div>

                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="med_cert_issued" <?= !empty($diagnostic['MedicalCertIssued']) ? 'checked' : '' ?>> Medical Certificate issued
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>Referred to:</label>
                                        <input type="text" class="custom-input" name="referred_to" value="<?= htmlspecialchars($diagnostic['ReferredTo'] ?? '') ?>">
                                    </div>
                                </div>

                                <h2>Recommendation</h2>
                                <div class="form-section">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="radio" name="recommendation" value="fit" <?= ($diagnostic['Recommendation'] ?? '') === 'fit' ? 'checked' : '' ?>> Fit to Work/Enroll
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="radio" name="recommendation" value="fit_sports" <?= ($diagnostic['Recommendation'] ?? '') === 'fit_sports' ? 'checked' : '' ?>> Fit to Participate in Sports
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="radio" name="recommendation" value="fit_enroll" <?= ($diagnostic['Recommendation'] ?? '') === 'fit_enroll' ? 'checked' : '' ?>> Fit to Enroll but requires further evaluation
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="radio" name="recommendation" value="fit_work_eval" <?= ($diagnostic['Recommendation'] ?? '') === 'fit_work_eval' ? 'checked' : '' ?>> Fit to Work but requires further evaluation
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="radio" name="recommendation" value="fit_sports_eval" <?= ($diagnostic['Recommendation'] ?? '') === 'fit_sports_eval' ? 'checked' : '' ?>> Fit to Participate in Sports but requires further evaluation
                                        </label>
                                    </div>
                                </div>

                                <div class="signature-section">
                                    <div class="form-group">
                                        <label>Physician's Name:</label>
                                        <input type="text" class="custom-input" name="physician_name" value="<?= htmlspecialchars($diagnostic['PhysicianName'] ?? '') ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>License Number:</label>
                                        <input type="text" class="custom-input" name="license_no" value="<?= htmlspecialchars($diagnostic['LicenseNo'] ?? '') ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Date:</label>
                                        <input type="date" class="custom-input" name="signature_date" value="<?= $diagnostic['SignatureDate'] ?? '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Institution:</label>
                                        <input type="text" class="custom-input" name="institution" value="LAGUNA STATE POLYTECHNIC UNIVERSITY, UNIVERSITY CLINIC" readonly>
                                    </div>
                                </div>

                                <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientID) ?>" />
                                <button class="buttonsdp" type="submit">Submit Form</button>
                            </form>
                            <script>
                                $('#diagnosticform').on('submit', function(e) {
                                    e.preventDefault();
                                    $.ajax({
                                        type: 'POST',
                                        url: $(this).attr('action'),
                                        data: $(this).serialize(),
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                alert(response.message);

                                            } else {
                                                alert('Error: ' + response.message);
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('AJAX Error:', error);
                                            alert('An error occurred while submitting the form.');
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>

                </div>
                <div id="np_medical-history" style="display: none;">
                    <div class="form-container">
                        <form id="medicalForm" method="post">
                            <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientID ?? '') ?>">
                            <input type="hidden" id="print_action" name="print_action" value="">

                            <div style="display: flex; width: 100%; justify-content: right; align-items: center;">
                                <button type="button" class="buttonsdp" onclick="printMedicalForm()">Print</button>
                            </div>
                            <div class="form-header">
                                <h1>CS Form No. 211</h1>
                                <h2>Revised 2018</h2>
                                <h1>MEDICAL CERTIFICATE</h1>
                                <h2>(For Employment)</h2>
                            </div>

                            <div class="section">
                                <div class="section-title">INSTRUCTIONS</div>
                                <p>a. This medical certificate should be accomplished by a licensed government physician.</p>
                                <p>b. Attach this certificate to original appointment, transfer and reemployment.</p>
                                <p>c. The results of the following pre-employment medical/physical must be attached to this form:</p>

                                <div class="check-box-parent-div">
                                    <div class="checkbox-group">
                                        <div class="checkboxes">
                                            <input type="checkbox" id="blood-test" name="blood_test" value="1" <?= !empty($blood_test) ? 'checked' : '' ?>>
                                            <label for="blood-test">Blood Test</label>
                                        </div>
                                        <div class="checkboxes">
                                            <input type="checkbox" id="urinalysis" name="urinalysis" value="1" <?= !empty($urinalysis) ? 'checked' : '' ?>>
                                            <label for="urinalysis">Urinalysis</label>

                                        </div>
                                        <div class="checkboxes">
                                            <input type="checkbox" id="xray" name="chest_xray" value="1" <?= !empty($chest_xray) ? 'checked' : '' ?>>
                                            <label for="xray">Chest X-Ray</label>
                                        </div>
                                        <div class="checkboxes">
                                            <input type="checkbox" id="drug-test" name="drug_test" value="1" <?= !empty($drug_test) ? 'checked' : '' ?>>
                                            <label for="drug-test">Drug Test</label>
                                        </div>
                                        <div class="checkboxes">
                                            <input type="checkbox" id="psych-test" name="psych_test" value="1" <?= !empty($psych_test) ? 'checked' : '' ?>>
                                            <label for="psych-test">Psychological Test</label>
                                        </div>
                                        <div class="checkboxes">
                                            <input type="checkbox" id="neuro-test" name="neuro_test" value="1" <?= !empty($neuro_test) ? 'checked' : '' ?>>
                                            <label for="neuro-test">Neuro-Psychiatric Examination</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section">
                                <div class="section-title">FOR THE PROPOSED APPOINTEE</div>

                                <div class="form-group">
                                    <label for="name">NAME</label>
                                    <input type="text" id="personnel-name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="agency">AGENCY / ADDRESS</label>
                                        <input type="text" id="agency" name="agency" value="<?= htmlspecialchars($agency ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="address">ADDRESS</label>
                                        <input type="text" id="personnel-address" name="address" value="<?= htmlspecialchars($npaddress ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="age">AGE</label>
                                        <input type="number" id="personnel-age" name="age" value="<?= htmlspecialchars($npage ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="sex">SEX</label>
                                        <select id="sex" name="sex" required>
                                            <option value="">Select</option>
                                            <option value="Male" <?= ($sex ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= ($sex ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="civil-status">CIVIL STATUS</label>
                                        <select id="civil-status" name="civil-status" required>
                                            <option value="">Select</option>
                                            <option value="Single" <?= ($civil_status ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                                            <option value="Married" <?= ($civil_status ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                                            <option value="Divorced" <?= ($civil_status ?? '') === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                            <option value="Widowed" <?= ($civil_status ?? '') === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="position">PROPOSED POSITION</label>
                                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($position ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="section">

                                <div class="section-title">FOR THE LICENSED GOVERNMENT PHYSICIAN</div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <p>I hereby certify that I have reviewed and evaluated the attached examination results, personally examined the above named individual and found him/her to be physically and medically □FIT / □UNFIT for employment.</p>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="physician_signature">SIGNATURE over PRINTED NAME:</label>
                                        <input type="text" id="physician_signature" name="physician_signature" value="<?= htmlspecialchars($physician_signature ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="physician_agency">AGENCY/Affiliation:</label>
                                        <input type="text" id="physician_agency" name="physician_agency" value="<?= htmlspecialchars($physician_agency ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="otherinfo">Other Information About The Proposed Appointee:</label>
                                        <input type="text" id="otherinfo" name="otherinfo" value="<?= htmlspecialchars($other_info ?? '') ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="license_no">LICENSE NO.</label>
                                        <input type="text" id="license_no" name="license_no" value="<?= htmlspecialchars($license_no ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="height">HEIGHT (M)</label>
                                        <input type="text" id="height" name="height" value="<?= htmlspecialchars($height ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="weight">WEIGHT (KG)</label>
                                        <input type="text" id="weight" name="weight" value="<?= htmlspecialchars($weight ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="blood-type">BLOOD TYPE</label>
                                        <input type="text" id="blood-type" name="blood-type" value="<?= htmlspecialchars($blood_type ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="official_designation">OFFICIAL DESIGNATION</label>
                                        <input type="text" id="official_designation" name="official_designation" value="<?= htmlspecialchars($official_designation ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date_created">Date:</label>
                                        <input type="date" id="date_created" name="date_created" value="<?= htmlspecialchars($date_created ?? date('Y-m-d')) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer" style="padding: 30px">
                                <button type="submit" class="buttonsdp" id="submitBtn">Submit</button>
                            </div>
                        </form>
                        <script>
                            function printMedicalForm() {
                                document.getElementById('print_action').value = '1'; // set to "1" to trigger printing
                                document.getElementById('medicalForm').action = 'manageclients.dbf/generate_np_medform.php'; // send to PHP file
                                document.getElementById('medicalForm').submit();
                            }
                        </script>

                        <script>
                            document.getElementById('medicalForm').addEventListener('submit', async function(e) {
                                e.preventDefault();

                                const form = e.target;
                                const formData = new FormData(form);

                                // Disable submit button to prevent multiple submissions
                                const submitBtn = document.getElementById('submitBtn');
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Submitting...';

                                try {
                                    const response = await fetch('manageclients.dbf/submit_np_form.php', {
                                        method: 'POST',
                                        body: formData
                                    });
                                    const result = await response.json();

                                    if (result.success) {
                                        alert(result.message);
                                        form.reset();
                                    } else {
                                        alert('Error: ' + (result.message || 'Unknown error'));
                                        if (result.missing_fields) {
                                            console.warn('Missing fields:', result.missing_fields);
                                        }
                                    }
                                } catch (error) {
                                    alert('Failed to submit form. Please try again.');
                                    console.error(error);
                                } finally {
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = 'Submit';
                                }
                            });
                        </script>

                        <!--=====================================================-->
                        <style>
                            :root {
                                --primary-color: #3498db;
                                --secondary-color: #2980b9;
                                --accent-color: #e74c3c;
                                --light-gray: #f8f9fa;
                                --medium-gray: #e9ecef;
                                --dark-gray: #6c757d;
                                --text-color: #212529;
                                --border-radius: 8px;
                                --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            }

                            .form-container {
                                display: flex;
                                flex-direction: column;
                                justify-content: flex-start;
                                width: 100%;
                                height: 100%;
                                overflow: auto;
                                max-height: 600px;
                                background-color: white;
                                padding: 30px;
                                border-radius: 5px;
                                gap: 15px;
                            }

                            .form-header {
                                text-align: center;
                                margin-bottom: 40px;
                                padding-bottom: 20px;
                                border-bottom: 2px solid var(--medium-gray);
                            }

                            .form-header h1 {
                                font-size: 24px;
                                margin: 0;
                                font-weight: 600;
                                color: var(--primary-color);
                            }

                            .form-header h2 {
                                font-size: 18px;
                                margin: 10px 0 0 0;
                                font-weight: 500;
                                color: var(--dark-gray);
                            }

                            .section {
                                margin-bottom: 30px;
                                padding: 25px;
                                background-color: var(--light-gray);
                                border-radius: var(--border-radius);
                            }

                            .section-title {
                                font-weight: 600;
                                margin-bottom: 20px;
                                color: var(--primary-color);
                                font-size: 18px;
                                padding-bottom: 8px;
                                border-bottom: 2px solid var(--medium-gray);
                            }

                            .form-row {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 20px;
                                margin-bottom: 20px;
                            }

                            .form-group {
                                flex: 1;
                                min-width: 200px;
                            }

                            label {
                                display: block;
                                margin-bottom: 8px;
                                font-weight: 500;
                                color: var(--dark-gray);
                            }

                            input[type="text"],
                            input[type="number"],
                            select {
                                width: 100%;
                                padding: 12px;
                                border: 1px solid var(--medium-gray);
                                border-radius: var(--border-radius);
                                background-color: white;
                                transition: border-color 0.3s, box-shadow 0.3s;
                            }

                            input[type="text"]:focus,
                            input[type="number"]:focus,
                            select:focus {
                                outline: none;
                                border-color: var(--primary-color);
                                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
                            }

                            .checkbox-group {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                                gap: 15px;
                                margin-bottom: 20px;
                            }

                            .checkbox-item {
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }

                            .checkbox-item input[type="checkbox"] {
                                width: 18px;
                                height: 18px;
                                accent-color: var(--primary-color);
                            }

                            .checkbox-item label {
                                margin: 0;
                                font-weight: 400;
                            }

                            .display-text {
                                padding: 12px;
                                background-color: var(--medium-gray);
                                border: 1px solid var(--medium-gray);
                                border-radius: var(--border-radius);
                                min-height: 20px;
                                color: var(--dark-gray);
                            }

                            .signature-section {
                                margin-top: 40px;
                                padding-top: 20px;
                                border-top: 2px solid var(--medium-gray);
                            }

                            .signature-line {
                                width: 300px;
                                margin: 40px auto 0;
                                text-align: center;
                            }

                            .signature-line::before {
                                content: "";
                                display: block;
                                width: 100%;
                                height: 1px;
                                background-color: var(--text-color);
                                margin-bottom: 5px;
                            }

                            .text-center {
                                text-align: center;
                            }

                            .text-muted {
                                color: var(--dark-gray);
                                font-size: 0.9em;
                            }

                            @media (max-width: 768px) {
                                .form-container {
                                    padding: 20px;
                                }

                                .section {
                                    padding: 15px;
                                }

                                .form-row {
                                    flex-direction: column;
                                    gap: 15px;
                                }

                                .form-group {
                                    min-width: 100%;
                                }
                            }
                        </style>
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
                    </div>

                </div>
                <script>
                    // Convert 24-hour time string to 12-hour AM/PM format
                    function convertTo12Hour(time24) {
                        if (!time24) return '';
                        const [hourStr, minute] = time24.split(':');
                        let hour = parseInt(hourStr, 10);
                        const ampm = hour >= 12 ? 'PM' : 'AM';
                        hour = hour % 12;
                        hour = hour ? hour : 12; // '0' becomes '12'
                        return `${hour}:${minute} ${ampm}`;
                    }

                    $('#logbookForm').on('submit', function(e) {
                        e.preventDefault();

                        $.ajax({
                            url: 'manageclients.dbf/submit_logbook.php',
                            method: 'POST',
                            data: $(this).serialize(),
                            success: function(response) {
                                try {
                                    const data = JSON.parse(response);

                                    $('#successAlert').fadeIn().delay(2000).fadeOut();

                                    $('#logbookTable tbody').append(`
                        <tr>
                            <td>${data.date}</td>
                            <td>${data.name}</td>
                            <td>${data.course}</td>
                            <td>${data.year}</td>
                            <td>${data.section}</td>
                            <td>${convertTo12Hour(data.time_started)}</td>
                            <td>${convertTo12Hour(data.time_finished)}</td>
                            <td>${data.medication_treatment}</td>
                            <td>${data.illness}</td>
                            <td>${data.remarks}</td>
                        </tr>
                    `);

                                    $('#logbookForm')[0].reset();
                                } catch (err) {
                                    alert('Invalid response: ' + response);
                                }
                            },
                            error: function(xhr) {
                                alert('Submission failed: ' + xhr.responseText);
                            }
                        });
                    });

                    document.getElementById("openModalBtn").onclick = function() {
                        document.getElementById("logbookModal").style.display = "block";
                    }

                    document.querySelector(".closeBtn").onclick = function() {
                        document.getElementById("logbookModal").style.display = "none";
                    }

                    window.onclick = function(event) {
                        if (event.target == document.getElementById("logbookModal")) {
                            document.getElementById("logbookModal").style.display = "none";
                        }
                    }
                </script>

            </div>

            <div class="nav-div2" style="display: none;">
                <div class="tabs2">
                    <div class="tabs-child">
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
                    </div>
                    <button id="back-to-history" class="back-btn" onclick="backto(<?= htmlspecialchars($clientID) ?>)">
                        <i class="fas fa-arrow-left"></i> back
                    </button>

                    <script>
                        window.onload = function() {
                            backto();

                        };

                        document.getElementById("back-to-history").addEventListener("click", function() {
                            const urlParams = new URLSearchParams(window.location.search);
                            const clientID = urlParams.get('id');

                            if (clientID) {
                                const baseUrl = window.location.href.split('?')[0];
                                window.location.href = `${baseUrl}?id=${clientID}`;
                            } else {
                                window.location.href = window.location.href.split('?')[0]; // fallback
                            }
                        });

                        function backto() {
                            document.querySelector('.nav-div').style.display = 'flex';
                            document.querySelector('.nav-div2').style.display = 'none';

                            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

                            const visitTab = document.querySelector('.tab[data-target="visit-history"]');
                            if (visitTab) {
                                visitTab.classList.add('active');
                            }

                            document.querySelectorAll('#personal-info-div, #medical-history, #medical-cert, #visit-history, #medrec, #rx')
                                .forEach(content => content.style.display = 'none');

                            const visitSection = document.getElementById('visit-history');
                            if (visitSection) {
                                visitSection.style.display = 'block';
                            }

                            fetch('ClientProfile.php')
                                .then(response => response.text())
                                .then(html => {
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = html;

                                    const newNavDiv2 = tempDiv.querySelector('.nav-div2');
                                    if (newNavDiv2) {
                                        const currentNavDiv2 = document.querySelector('.nav-div2');
                                        currentNavDiv2.innerHTML = newNavDiv2.innerHTML;
                                    }
                                })
                                .catch(err => console.error('Error reloading .nav-div2:', err));
                        }
                    </script>
                </div>

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
                                        <input type="text" id="bp_input" name="bp" placeholder="BP" required>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">HR/PR:</span>
                                        <input type="text" id="hr_pr" name="hr_pr" placeholder="HR/PR" required>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">T°:</span>
                                        <input type="text" id="temp_input" name="temp" placeholder="T°" required>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">O²sat:</span>
                                        <input type="text" id="o2sat" name="o2sat" placeholder="O²sat" required>
                                    </div>
                                </div>
                            </div>

                            <div class="right-info-div">

                                <div class="cert-controls" style="margin-top: 0px;">
                                    <button class="buttonsdp" type="button" onclick="saveConsultation()">Save</button>
                                    <button class="buttonsdp2" type="button" onclick="submitPdfForm()">Dowload as PDF</button>
                                </div>
                                <div id="saveStatus" style="margin-top: 10px;"></div>

                                <div class="SOAP-div" style="align-items: left;">
                                    <h3 style="padding: 15px;">Subjective</h3>
                                    <textarea style=" font-family:Poppins, sans-serif;" id="subjective" name="subjective" rows="1" cols="50" placeholder="..." oninput="autoGrow(this)"></textarea>
                                    <h3 style="padding: 15px;">Objective</h3>
                                    <textarea style="font-family:Poppins, sans-serif;" id="objective" name="objective" rows="1" cols="50" placeholder="..." oninput="autoGrow(this)"></textarea>

                                    <h3 style="padding: 15px;">Assessment</h3>
                                    <textarea style="font-family:Poppins, sans-serif;" id="assessment" name="assessment" rows="1" cols="50" placeholder="..." oninput="autoGrow(this)"></textarea>
                                    <h3 style="padding: 15px;">Plan</h3>
                                    <textarea style="font-family:Poppins, sans-serif;" id="plan" name="plan" rows="1" cols="50" placeholder="..." oninput="autoGrow(this)"></textarea>

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
                    function autoGrow(element) {
                        element.style.height = "5px"; // reset height
                        element.style.height = (element.scrollHeight) + "px"; // set new height
                    }
                </script>

                <script>
                    function saveConsultation() {
                        const requiredFields = [{
                                id: 'bp_input',
                                label: 'BP'
                            },
                            {
                                id: 'hr_pr',
                                label: 'HR/PR'
                            },
                            {
                                id: 'temp_input',
                                label: 'Temperature'
                            },
                            {
                                id: 'o2sat',
                                label: 'O²sat'
                            }
                        ];

                        for (const field of requiredFields) {
                            const value = document.getElementById(field.id).value.trim();
                            if (!value) {
                                alert(`Please enter ${field.label}.`);
                                return; // stop submission
                            }
                        }
                        const formData = {
                            client_id: document.getElementById('client-id').value,
                            name: document.getElementById('name').textContent,
                            age: document.getElementById('age').textContent,
                            address: document.getElementById('address').textContent,
                            course: document.getElementById('course').textContent,
                            bp: document.getElementById('bp_input').value,
                            hr_pr: document.getElementById('hr_pr').value,
                            temp: document.getElementById('temp_input').value,
                            o2sat: document.getElementById('o2sat').value,
                            subjective: document.getElementById('subjective').value,
                            objective: document.getElementById('objective').value,
                            assessment: document.getElementById('assessment').value,
                            plan: document.getElementById('plan').value
                        };

                        const statusDiv = document.getElementById('saveStatus');
                        statusDiv.innerHTML = '<p style="color: blue;">Saving data, please wait...</p>';

                        fetch('manageclients.dbf/save_consultation.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams(formData)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.text();
                            })
                            .then(data => {

                                statusDiv.innerHTML = '<p style="color: green;">Data saved successfully!</p>';

                                setTimeout(() => {
                                    statusDiv.innerHTML = '';
                                }, 3000);
                            })
                            .catch(error => {
                                statusDiv.innerHTML = `<p style="color: red;">Error saving data: ${error.message}</p>`;

                                setTimeout(() => {
                                    statusDiv.innerHTML = '';
                                }, 5000);
                            });
                    }

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
                                        <input type="hidden" name="patient_sex" id="input_patient_sex" value="<?= htmlspecialchars($gender) ?>" />
                                        <span><?= htmlspecialchars($gender) ?></span> <!-- still shows on screen -->
                                    </div>

                                    <!-- Removed Address -->

                                    <div class="info-row">
                                        <span class="info-label">Impression:</span>
                                        <input name="p-impression" id="impression" type="text" />
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">Date:</span>
                                        <span id="date2"></span>
                                    </div>
                                </div>

                                <div id="phyexam-div-2" class="phyexam-div">
                                    <div class="info-row">
                                        <span class="info-label">Visiting Physician:</span>
                                        <input type="text" name="physician" placeholder="Visiting Physician" />
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Lic.No:</span>
                                        <input type="text" name="LicNo" placeholder="Lic.No." />
                                    </div>
                                </div>
                            </div>

                            <div class="right-info-div">

                                <div class="cert-controls" style="margin-top: 20px;">
                                    <div class="cert-controls" style="margin-top: 0px;">
                                        <button type="button" class="buttonsdp" onclick="savePrescription()">Save</button>
                                        <button class="buttonsdp2" type="submit">Download as PDF</button>
                                    </div>
                                </div>
                                <p id="save-message" style="color: green; display: none; font-weight: normal;"></p>

                                <div class="SOAP-div" style="align-items: left;">
                                    <h3 style="font-family:Poppins, sans-serif; font-size: 28pt;">℞</h3>
                                    <textarea style="font-family:Poppins, sans-serif;" ; id="notes" name="notes" rows="20" cols="50" placeholder="..."></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="patient_name" id="input_patient_name" />
                            <input type="hidden" name="patient_age" id="input_patient_age" />
                            <input type="hidden" name="patient_sex" value="<?= htmlspecialchars($gender) ?>">
                            <input type="hidden" name="date" id="input_date" />
                            <input type="hidden" name="input_physician" id="input_physician" />
                            <input type="hidden" name="input_LicNo" id="input_LicNo" />

                        </form>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const dateSpan = document.getElementById("date2");
                        const today = new Date();
                        const formattedDate = today.toLocaleDateString("en-US", {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        dateSpan.textContent = formattedDate;
                    });

                    function preparePdfData() {
                        console.log("Sex value:", document.getElementById('sex').textContent.trim());
                        document.getElementById('input_patient_name').value = document.getElementById('name').textContent.trim();
                        document.getElementById('input_patient_age').value = document.getElementById('age').textContent.trim();
                        document.getElementById('input_patient_sex').value = document.getElementById('sex').textContent.trim();

                        document.getElementById('input_date').value = document.getElementById('date2').textContent.trim();

                        // Copy physician and LicNo input values into hidden fields
                        document.getElementById('input_physician').value = document.querySelector('input[name="physician"]').value.trim();
                        document.getElementById('input_LicNo').value = document.querySelector('input[name="LicNo"]').value.trim();

                        return true; // allow form submit
                    }
                </script>
                <script>
                    function savePrescription() {
                        // Get input values trimmed
                        const patientName = document.getElementById('name').textContent.trim();
                        const age = document.getElementById('age').textContent.trim();
                        const impression = document.querySelector('input[name="p-impression"]').value.trim();
                        const physician = document.querySelector('input[name="physician"]').value.trim();
                        const licenseNo = document.querySelector('input[name="LicNo"]').value.trim();
                        const notes = document.getElementById('notes').value.trim();

                        // Validate required fields (adjust which fields are required)
                        if (!patientName) {
                            alert("Patient name is required.");
                            return;
                        }
                        if (!age) {
                            alert("Patient age is required.");
                            return;
                        }
                        if (!impression) {
                            alert("Impression is required.");
                            return;
                        }
                        if (!physician) {
                            alert("Visiting Physician is required.");
                            return;
                        }
                        if (!licenseNo) {
                            alert("License Number is required.");
                            return;
                        }
                        if (!notes) {
                            alert("Notes cannot be empty.");
                            return;
                        }

                        const data = {
                            client_id: document.getElementById('client-id').value,
                            patient_name: document.getElementById('name').textContent.trim(),
                            age: document.getElementById('age').textContent.trim(),

                            impression: document.querySelector('input[name="p-impression"]').value.trim(),
                            physician: document.querySelector('input[name="physician"]').value.trim(),
                            license_no: document.querySelector('input[name="LicNo"]').value.trim(),
                            notes: document.getElementById('notes').value.trim(),
                            date_created: new Date().toISOString().slice(0, 10) // YYYY-MM-DD
                        };

                        fetch('manageclients.dbf/save_rx.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data)
                            })
                            .then(res => res.json())
                            .then(response => {
                                const msgElem = document.getElementById("save-message");
                                if (response.success) {
                                    msgElem.style.color = "green";
                                    msgElem.textContent = "Saved successfully.";
                                    msgElem.style.display = "block";
                                    setTimeout(() => {
                                        msgElem.style.display = "none";
                                    }, 4000); // hides after 4 seconds

                                } else {
                                    msgElem.style.color = "red";
                                    msgElem.textContent = "Error saving prescription: " + response.message;
                                    msgElem.style.display = "block";
                                }

                            })
                            .catch(err => {
                                console.error("AJAX error:", err);
                                alert("Something went wrong.");
                            });
                    }
                </script>

            </div>
    </div>
    </div>
    </main>
    </div>
    
</body>

</html