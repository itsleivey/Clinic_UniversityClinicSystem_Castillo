<?php
require_once('../config/database.php');
require_once('../Profile/Profile_db.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$clientId = $_SESSION['ClientID'];
$pdo = pdo_connect_mysql();
$user_data = getUserDataFromDatabase($pdo);
//==================================================================

function checkProfileCompletion($pdo, $clientId)
{
    try {
        $stmt = $pdo->prepare("SELECT ClientType, Department FROM Clients WHERE ClientID = ?");
        $stmt->execute([$clientId]);
        $clientData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($clientData['ClientType'])) {
            return true;
        }

        if ($clientData['ClientType'] !== 'NewPersonnel' && empty($clientData['Department'])) {
            return true;
        }

        return false;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return true;
    }
}

$requireProfileUpdate = checkProfileCompletion($pdo, $_SESSION['ClientID']);

if (isset($_SESSION['profile_completed'])) {
    unset($_SESSION['profile_completed']);
    $requireProfileUpdate = false;
} else {
    $requireProfileUpdate = checkProfileCompletion($pdo, $_SESSION['ClientID']);
}

$clientType = '--.--.--';
$department = '--.--.--';

if ($clientId) {
    $conn = pdo_connect_mysql();
    $query = $conn->prepare("SELECT ClientType, Department, Course FROM clients WHERE ClientID = ?");
    $query->execute([$clientId]);
    $client = $query->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $clientType = $client['ClientType'] ?: '--.--.--';
        $department = $client['Department'] ?: '--.--.--';
        $course = $client['Course'] ?: '--.--.--';
    }
}
//==================================================================
if (!isset($_SESSION['ClientID'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit'])) {
    if (!isset($_FILES['image']['error']) || is_array($_FILES['image']['error'])) {
        header("Location: Profile.php?upload=fail");
        exit();
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header("Location: Profile.php?upload=fail");
        exit();
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $valid_mimes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($finfo->file($_FILES['image']['tmp_name']), $valid_mimes)) {
        header("Location: Profile.php?upload=fail");
        exit();
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $ext;
    $folder = '../uploads/' . $file_name;

    if (!file_exists('../uploads/')) {
        mkdir('../uploads/', 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $folder)) {
        $query = $pdo->prepare("UPDATE Clients SET profilePicturePath = ? WHERE ClientID = ?");
        if ($query->execute([$file_name, $_SESSION['ClientID']])) {
            header("Location: Profile.php?upload=success");
            exit();
        }
    }

    header("Location: Profile.php?upload=fail");
    exit();
}

$stmt = $pdo->prepare("SELECT Firstname, Lastname, Email, ProfilePicturePath FROM Clients WHERE ClientID = ?");
$stmt->execute([$_SESSION['ClientID']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profilePic = '../uploads/' . $user['ProfilePicturePath'];
//if (!file_exists($profilePic)) {
//   $profilePic = 'UC-Client/assets/images/default-profile.png';
//}

$fullName = htmlspecialchars($user['Firstname'] . ' ' . $user['Lastname']);
$email = htmlspecialchars($user['Email']);
//=========================================================================================================
$medicalHistory = [];
$pdo = pdo_connect_mysql();
$stmt = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE ClientID = ?");
$stmt->execute([$_SESSION['ClientID']]);
$medicalHistory = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

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

foreach ($expectedKeys as $key) {
    if (!isset($medicalHistory[$key])) {
        $medicalHistory[$key] = null;
    }
}

//=========================================================================================================
$familymedicalHistory = [];

try {
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("SELECT * FROM familymedicalhistory WHERE ClientID = ?");
    $stmt->execute([$_SESSION['ClientID']]);
    $familymedicalHistory = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load medical history. Please try again.";
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

//=========================================================================================================
$socialHistoryData = [];

try {
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("SELECT * FROM personalsocialhistory WHERE ClientID = ?");
    $stmt->execute([$_SESSION['ClientID']]);
    $socialHistoryData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load medical history. Please try again.";
}
//=========================================================================================================
$data = [];

try {
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("SELECT * FROM femalehealthhistory WHERE ClientID = ?");
    $stmt->execute([$_SESSION['ClientID']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load medical history. Please try again.";
}
//========================================================================================
$stmt = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'completed' ORDER BY historyID DESC");
$stmt->execute([$clientId]);
$history = $stmt->fetch(PDO::FETCH_ASSOC);

$historyID = $history ? $history['historyID'] : null;
$clientID = $_SESSION['ClientID'] ?? null;
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

if ($clientID) {
    $stmt = $pdo->prepare('SELECT * FROM medicalcertificate WHERE ClientID = ?');
    $stmt->execute([$clientID]);
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetched) {
        $medicalCertData = $fetched;
    }
}

//=========================================================================

$currentStatus = 'undone'; // Default status
$actionDate = null;
$actionTime = null;

if ($clientId) {
    try {
        $pdo = pdo_connect_mysql();
        $stmt = $pdo->prepare("
            SELECT progress, actionDate, actionTime 
            FROM history 
            WHERE ClientID = ? 
            ORDER BY historyID DESC , actionDate DESC, actionTime DESC 
            LIMIT 1
        ");
        $stmt->execute([$clientId]);
        $result = $stmt->fetch();

        $currentStatus = $result['progress'] ?? 'canceled';
        $actionDate = $result['actionDate'] ?? null;
        $actionTime = $result['actionTime'] ?? null;

        // Combine date and time if both are available
        $actionDateTime = ($actionDate && $actionTime) ? "$actionDate $actionTime" : null;
    } catch (PDOException $e) {
        error_log("Status check error: " . $e->getMessage());
    }
}
$headerText = 'Previous Progress'; // Default text
if ($currentStatus === 'inprogress') {
    $headerText = 'Current Progress';
}
//==============================================================================
if (!$clientId) {
    die("Client ID is required");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM history WHERE ClientID = ? ORDER BY historyID DESC");
    $stmt->execute([$clientId]);
    $historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching history data: " . $e->getMessage());
}
//==============================================================================
/*date_default_timezone_set('Asia/Manila'); 

$currentTime = date('h:i:s A'); 

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM medicalcertificate WHERE ClientID = ? AND historyID = ?");
$checkStmt->execute([$clientId, $historyID]);
$medicalCertCompleted = $checkStmt->fetchColumn();

if ($medicalCertCompleted > 0) {
    $updateStmt = $pdo->prepare("UPDATE history SET progress = 'completed', actionTime = ? WHERE historyID = ?");
    $updateStmt->execute([$currentTime, $historyID]);
}
*/
//==============================================================================


//==============================================================================
$clientID = $_SESSION['ClientID'] ?? null;

$checkProgressStmt = $pdo->prepare("SELECT progress FROM history WHERE ClientID = ? AND progress = 'completed'ORDER BY historyID DESC LIMIT 1");
$checkProgressStmt->execute([$clientID]);
$progressStatus = $checkProgressStmt->fetchColumn();

$showLogbookModal = ($progressStatus === 'completed');

//==============================================================================
if ($clientID && $historyID) {
    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=University_Clinic_System;port=4307',
            'root',
            '181414',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $stmt = $pdo->prepare("SELECT progress FROM history WHERE ClientID = ? AND progress = 'completed' ORDER BY historyID DESC LIMIT 1");
        $stmt->execute([$clientID]);
        $progress = $stmt->fetchColumn();

        if ($progress === 'completed') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM logbook WHERE ClientID = ?");
            $stmt->execute([$clientID]);
            $hasLogbook = $stmt->fetchColumn() > 0;

            $showLogbookModal = !$hasLogbook;
        } else {
            $showLogbookModal = false;
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

//==============================================================================
$isDownloaded = false;

try {
    $stmt = $pdo->prepare("SELECT IsDownload FROM medicalcertificate WHERE historyID = ? ORDER BY MedicalCertID DESC LIMIT 1");
    $stmt->execute([$historyID]);
    $row = $stmt->fetch();

    if ($row && isset($row['IsDownload'])) {
        $isDownloaded = (bool)$row['IsDownload'];
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
//==============================================================================
$name = $agency = $address = $age = $sex = $civil_status = $position = '';
$blood_test = $urinalysis = $chest_xray = $drug_test = $psych_test = $neuro_test = 0;
$physician_signature = $physician_agency = $other_info = $license_no = $height = $weight = $blood_type = '';
$date_created = date('Y-m-d'); // default today

if ($clientID) {
    $stmt = $pdo->prepare("SELECT * FROM newpersonnel_form WHERE client_id = :client_id ORDER BY form_id Desc");
    $stmt->execute(['client_id' => $clientID]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $name = $data['full_name'] ?? '';
        $agency = $data['agency_address'] ?? '';
        $address = $data['address'] ?? '';
        $age = $data['age'] ?? '';
        $sex = $data['sex'] ?? '';
        $civil_status = $data['civil_status'] ?? '';
        $position = $data['proposed_position'] ?? '';

        $blood_test = !empty($data['blood_test']);
        $urinalysis = !empty($data['urinalysis']);
        $chest_xray = !empty($data['chest_xray']);
        $drug_test = !empty($data['drug_test']);
        $psych_test = !empty($data['psych_test']);
        $neuro_test = !empty($data['neuro_test']);

        $physician_signature = $data['physician_signature'] ?? '';
        $physician_agency = $data['physician_agency'] ?? '';
        $other_info = $data['OtherInfo'] ?? '';
        $license_no = $data['physician_license'] ?? '';
        $height = $data['height'] ?? '';
        $weight = $data['weight'] ?? '';
        $blood_type = $data['blood_type'] ?? '';
        $date_created = $data['date_created'] ?? date('Y-m-d');
        $official_designation = $data['physician_designation'] ?? '';
    }
}
//==============================================================================
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="UC-Client/assets/css/ClientStyles.css">
    <link rel="stylesheet" href="UC-Client/assets/css/MediaQuery.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="UC-Client/assets/js/script.js" defer></script>
    <script src="UC-Client/assets/js/profile.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <title>Manage Profile</title>
</head>
</head>

<body>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($showLogbookModal): ?>
                document.getElementById('logbookModal').style.display = 'block';
            <?php endif; ?>
        });
    </script>

    <!--==========================================================================================================================================-->
    <script>
        function openModal() {
            document.getElementById("logbookModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("logbookModal").style.display = "none";
        }
        <?php if ($progressStatus === 'completed'): ?>
            window.addEventListener('DOMContentLoaded', () => {
                openModal();
            });
        <?php endif; ?>
    </script>

    <!--==========================================================================================================================================-->
    <script>
        const clientType = <?= json_encode($clientType) ?>;

        document.addEventListener("DOMContentLoaded", function() {
            const content1 = document.querySelector('.Content1-Div');
            const content2 = document.querySelector('.Content2-Div');

            if (["Student", "Faculty", "Personnel"].includes(clientType)) {
                if (content1) content1.style.display = 'flex';
                if (content2) content2.style.display = 'none';
            } else if (clientType === 'Freshman' || 'NewPersonnel') {
                if (content1) content1.style.display = 'none';
                if (content2) content2.style.display = 'flex';
            } else {
                // For other client types, hide both or handle as needed
                if (content1) content1.style.display = 'none';
                if (content2) content2.style.display = 'none';
            }
        });
    </script>
    <!--==========================================================================================================================================-->
    <script>
        const clientGender = String("<?= htmlspecialchars($user_data['Gender']) ?>").toLowerCase();
        const clientType = String("<?= htmlspecialchars($clientType) ?>").toLowerCase();

        function controlSectionVisibility() {
            console.log("Gender:", clientGender);
            console.log("Client Type:", clientType);

            if (clientGender === 'male') {
                const menstrualSection = document.getElementById('dropdown-header-style');
                if (menstrualSection) {
                    menstrualSection.style.display = 'none';
                }
            }
        }

        window.addEventListener('DOMContentLoaded', controlSectionVisibility);
    </script>
    <h1 id="genderDisplay"></h1>
    <!--==========================================================================================================================================-->
    <?php if ($requireProfileUpdate): ?>
        <div id="profileAlertOverlay" class="alert-overlay">
            <div class="alert-modal">
                <div class="alert-header">
                    <h3>Profile Completion Required</h3>
                </div>
                <div class="alert-body">
                    <p>Please complete your profile information to continue using the system.</p>
                    <form id="profileAlertForm" action="Required_form.php" method="POST">
                        <div class="form-group">
                            <label for="clientType">Client Type*</label>
                            <select id="clientType" name="clientType" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="Freshman" <?= isset($_POST['clientType']) && $_POST['clientType'] == 'Freshman' ? 'selected' : '' ?>>Freshman Student</option>
                                <option value="Student" <?= isset($_POST['clientType']) && $_POST['clientType'] == 'Student' ? 'selected' : '' ?>>Student (Enrolled/Regular)</option>
                                <option value="Faculty" <?= isset($_POST['clientType']) && $_POST['clientType'] == 'Faculty' ? 'selected' : '' ?>>Teaching Personnel</option>
                                <option value="Personnel" <?= isset($_POST['clientType']) && $_POST['clientType'] == 'Personnel' ? 'selected' : '' ?>>Non-Teaching Personnel</option>
                                <option value="NewPersonnel" <?= isset($_POST['clientType']) && $_POST['clientType'] == 'NewPersonnel' ? 'selected' : '' ?>>Newly Hired Personnel</option>
                            </select>
                        </div>

                        <div class="form-group" id="departmentGroup">
                            <label for="department">Department*</label>
                            <select id="department" name="department" class="form-control">
                                <option value="">Select a Department</option>
                                <option value="None" <?= (isset($_POST['department']) && $_POST['department'] == 'None') ? 'selected' : '' ?>>None</option>
                                <option value="College of Computer Studies" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Computer Studies') ? 'selected' : '' ?>>College of Computer Studies</option>
                                <option value="College of Food Nutrition and Dietetics" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Food Nutrition and Dietetics') ? 'selected' : '' ?>>College of Food Nutrition and Dietetics</option>
                                <option value="College of Industrial Technology" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Industrial Technology') ? 'selected' : '' ?>>College of Industrial Technology</option>
                                <option value="College of Teacher Education" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Teacher Education') ? 'selected' : '' ?>>College of Teacher Education</option>
                                <option value="College of Agriculture" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Agriculture') ? 'selected' : '' ?>>College of Agriculture</option>
                                <option value="College of Arts and Sciences" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Arts and Sciences') ? 'selected' : '' ?>>College of Arts and Sciences</option>
                                <option value="College of Business Administration and Accountancy" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Business Administration and Accountancy') ? 'selected' : '' ?>>College of Business Administration and Accountancy</option>
                                <option value="College of Engineering" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Engineering') ? 'selected' : '' ?>>College of Engineering</option>
                                <option value="College of Criminal Justice Education" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Criminal Justice Education') ? 'selected' : '' ?>>College of Criminal Justice Education</option>
                                <option value="College of Fisheries" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Fisheries') ? 'selected' : '' ?>>College of Fisheries</option>
                                <option value="College of Hospitality Management and Tourism" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Hospitality Management and Tourism') ? 'selected' : '' ?>>College of Hospitality Management and Tourism</option>
                                <option value="College of Nursing and Allied Health" <?= (isset($_POST['department']) && $_POST['department'] == 'College of Nursing and Allied Health') ? 'selected' : '' ?>>College of Nursing and Allied Health</option>
                            </select>
                        </div>

                        <div class="form-group" id="courseGroup">
                            <label for="course">Course*</label>
                            <select id="course" name="course" class="form-control" required>
                                <option value="">Select a Course</option>
                            </select>
                        </div>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const clientType = document.getElementById("clientType");
                                const departmentGroup = document.getElementById("departmentGroup");
                                const courseGroup = document.getElementById("courseGroup");

                                function toggleFields() {
                                    const type = clientType.value;

                                    if (type === "Freshman" || type === "Student") {
                                        departmentGroup.style.display = "block";
                                        courseGroup.style.display = "block";
                                    } else if (type === "Faculty") {
                                        departmentGroup.style.display = "block";
                                        courseGroup.style.display = "none";
                                    } else {
                                        departmentGroup.style.display = "none";
                                        courseGroup.style.display = "none";
                                    }
                                }

                                clientType.addEventListener("change", toggleFields);

                                // Run on page load (for edit mode / postback)
                                toggleFields();
                            });
                        </script>
                        <div class="alert-buttons">
                            <a href="Medical_Form.php">
                                <button type="submit" class="btn btn-primary">Save & Continue</button>
                            </a>
                        </div>

                        <?php if (isset($success_message)) : ?>
                            <div id='topAlert' class="top-alert success">
                                <?= htmlspecialchars($success_message) ?>
                                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)) : ?>
                            <div id='topAlert' class="top-alert error">
                                <?= htmlspecialchars($error_message) ?>
                                <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
                            </div>
                        <?php endif; ?>
                    </form>
                    <script>
                        document.getElementById('profileAlertForm').addEventListener('submit',
                            function() {
                                setTimeout(function() {
                                        window.location.reload();
                                    },
                                    100);
                            });
                        const
                            clientTypeSelect =
                            document.getElementById("clientType");
                        const
                            departmentGroup =
                            document.getElementById("departmentGroup");

                        function
                        toggleDepartmentVisibility() {
                            if (clientTypeSelect.value ===
                                "Personnel") {
                                departmentGroup.style.display =
                                    "none";
                            } else {
                                departmentGroup.style.display =
                                    "block";
                            }
                        }
                        toggleDepartmentVisibility();
                        clientTypeSelect.addEventListener("change",
                            toggleDepartmentVisibility);

                        function
                        toggleDepartmentVisibility() {
                            if (clientTypeSelect.value ===
                                "Personnel") {
                                departmentGroup.style.display =
                                    "none";
                                document.getElementById("department").removeAttribute("required");
                            } else {
                                departmentGroup.style.display =
                                    "block";
                                document.getElementById("department").setAttribute("required",
                                    "required");
                            }
                        }

                        function
                        toggleDepartmentVisibility() {
                            if (clientTypeSelect.value ===
                                "Personnel" ||
                                clientTypeSelect.value ===
                                "NewPersonnel") {
                                departmentGroup.style.display =
                                    "none";
                                document.getElementById("department").removeAttribute("required");
                            } else {
                                departmentGroup.style.display =
                                    "block";
                                document.getElementById("department").setAttribute("required",
                                    "required");
                            }
                        }
                    </script>
                    <script>
                        const courseSelect = document.getElementById("course");
                        const departmentSelect = document.getElementById("department");

                        const coursesByDepartment = {
                            "College of Computer Studies": [
                                "Bachelor of Science in Information Technology",
                                "Bachelor of Science in Computer Science"
                            ],
                            "College of Food Nutrition and Dietetics": [
                                "Bachelor of Science in Food Nutrition",
                                "Bachelor of Science in Dietetics"
                            ],
                            "College of Industrial Technology": [
                                "Bachelor of Industrial Technology major in Electrical Technology",
                                "Bachelor of Industrial Technology major in Automotive Technology",
                                "Bachelor of Industrial Technology major in Food Processing Technology"
                            ],
                            "College of Teacher Education": [
                                "Bachelor of Secondary Education",
                                "Bachelor of Elementary Education"
                            ],
                            "College of Agriculture": [
                                "Bachelor of Science in Agriculture",
                                "Bachelor of Science in Agricultural Technology"
                            ],
                            "College of Arts and Sciences": [
                                "Bachelor of Arts in English",
                                "Bachelor of Science in Mathematics"
                            ],
                            "College of Business Administration and Accountancy": [
                                "Bachelor of Science in Business Administration",
                                "Bachelor of Science in Accountancy"
                            ],
                            "College of Engineering": [
                                "Bachelor of Science in Electronics Engineering",
                                "Bachelor of Science in Mechanical Engineering",
                                "Bachelor of Science in Civil Engineering"
                            ],
                            "College of Criminal Justice Education": [
                                "Bachelor of Science in Criminology"
                            ],
                            "College of Fisheries": [
                                "Bachelor of Science in Fisheries"
                            ],
                            "College of Hospitality Management and Tourism": [
                                "Bachelor of Science in Hospitality Management",
                                "Bachelor of Science in Tourism Management"
                            ],
                            "College of Nursing and Allied Health": [
                                "Bachelor of Science in Nursing",
                                "Bachelor of Science in Medical Technology"
                            ],
                            "None": []
                        };


                        function updateCourses() {
                            const selectedDept = departmentSelect.value;
                            const courses = coursesByDepartment[selectedDept] || [];

                            courseSelect.innerHTML = '<option value="">Select a Course</option>';

                            courses.forEach(course => {
                                const option = document.createElement("option");
                                option.value = course;
                                option.textContent = course;
                                courseSelect.appendChild(option);
                            });

                            courseSelect.required = courses.length > 0;
                        }

                        departmentSelect.addEventListener("change", updateCourses);

                        updateCourses();

                        function toggleDepartmentVisibility() {
                            const deptHidden = clientTypeSelect.value === "Personnel" || clientTypeSelect.value === "NewPersonnel";

                            departmentGroup.style.display = deptHidden ? "none" : "block";
                            document.getElementById("department").required = !deptHidden;

                            document.getElementById("courseGroup").style.display = deptHidden ? "none" : "block";
                            courseSelect.required = !deptHidden;
                        }
                    </script>


                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.body.classList.add('modal-open');

                document.getElementById('profileAlertOverlay').style.zIndex = '9999';

                document.getElementById('profileAlertOverlay').addEventListener('click', function(e) {
                    if (e.target === this) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });
        </script>
    <?php endif; ?>
    <!--==========================================================================================================================================-->
    <div class="header">
        <img src="UC-Client/assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
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
                    <img src="UC-Client/assets/images/Usericon2.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Manage Profile</span>
                </button>
            </a>

            <a href="Medical_Form.php">
                <button class="buttons" id="medicalBtn">
                    <img src="UC-Client/assets/images/Form-icon.svg" class="button-icon-nav" loading="lazy">
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
        <script>

        </script>
        <main class="content" loading="lazy">
            <div class="Content2-Div" style="display: none;">

                <div id="profile">
                    <!-- Modal -->
                    <!--This modal will only shown if the clienttype is freshman -->
                    <?php if (strtolower($clientType) === 'freshman'): ?>
                        <div id="exam-modal" class="modal" style="display: none;">
                            <div class="modal-content">
                                <span class="close-btn" onclick="closeExamModal()">&times;</span>
                                <h2 class="modal-title">Physical Examination Instructions</h2>

                                <div class="steps-container">
                                    <div class="step">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <h3>Fill Out the Medical Form</h3>
                                            <p>Go to the Medical Form page and complete the required fields: Personal Information and Medical History.</p>
                                        </div>
                                    </div>

                                    <div class="step">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <h3>Submit all labolatory results</h3>
                                            <p>cbc, Urinalysis, X-ray, and Drugtest. Submit to Clinic Staff.</p>
                                        </div>
                                    </div>

                                    <div class="step">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <h3>Take the Physical Examination</h3>
                                            <p>Proceed to the physical examination as instructed by the medical personnel.</p>
                                        </div>
                                    </div>

                                    <div class="step">
                                        <div class="step-number">4</div>
                                        <div class="step-content">
                                            <h3>Recieve Medical Certificate</h3>
                                            <p>After being assest by the university physician.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div id="profile-div-2">
                        <!--
                        <form id="profile-pic-form2" method="POST" enctype="multipart/form-data" action="Profile.php">
                            <div class="profile-pic-div">

                                <div class="profile-pic-wrapper">

                                    <img id="profile-pic2" src="<?= $profilePic ?>" alt="Profile Picture"
                                        onerror="this.onerror=null;this.src='../uploads/profilepic2.png'">
                                </div>
                                <input type="file" id="image-upload" name="image" accept="image/*"
                                    onchange="previewImage();" style="display: none;" required>

                            </div>
                            <div class="profile-actions2">
                                <button type="submit2" name="submit2" class="page-buttons">Save Profile Picture</button>
                            </div>
                        </form>
        -->
                        <p id="Name" class="ptext"><?= $fullName ?></p>
                        <p id="Email" class="ptext"><?= $email ?></p>

                        <?php if (isset($_GET['upload'])): ?>
                            <?php if ($_GET['upload'] == 'success'): ?>
                                <div class="alert-success"></div>
                            <?php elseif ($_GET['upload'] == 'fail'): ?>
                                <div class="alert-fail"></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div id="left-profile-sec-freshman">
                        <div id="status-info">
                            <div id="left-text" class="divtext">
                                <p class="info-label">
                                    <i class="fa-solid fa-id-badge"></i> Client ID:
                                    <span class="info-value"><?= htmlspecialchars($clientId) ?></span>
                                </p>
                                <p class="info-label">
                                    <i class="fa-solid fa-building-user"></i> Department:
                                    <span class="info-value"><?= htmlspecialchars($department) ?></span>
                                </p>
                                <?php if (strtolower($clientType) === 'freshman'): ?>
                                    <p class="info-label">
                                        <i class="fa-solid fa-book"></i> Course:
                                        <span class="info-value"><?= htmlspecialchars($course) ?></span>
                                    </p>
                                <?php endif; ?>
                                <p class="info-label">
                                    <i class="fa-solid fa-user-tag"></i> Client Type:
                                    <span class="info-value"><?= htmlspecialchars($clientType) ?></span>
                                </p>
                            </div>

                        </div>
                        <!--<button class="page-buttons" id="view-docs-btn">View Documents</button>-->
                        <?php if ($progressStatus !== 'completed'): ?>
                            <button class="page-buttons" onclick="showExamInstructions()">
                                <i class="fas fa-notes-medical" style="margin-right: 8px;"></i>
                                View Instructions
                            </button>
                        <?php endif; ?>
                        <?php if ($progressStatus === 'completed'): ?>
                            <div class="exam-complete">
                                <i class="fas fa-check-circle"></i>
                                <span class="exam-text">Physical Examination Completed</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="freshman-medcert" style="display: <?= ($progressStatus === 'completed') ? 'flex' : 'none' ?>;">

                    <div id="medical-certificate-form">
                        <div class="medcertheader">
                            <img src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo">
                            <div class="headertextdiv">
                                <div>Republic of the Philippines</div>
                                <div>Laguna State Polytechnic University</div>
                                <div>Province of Laguna</div>
                            </div>
                        </div>

                        <div class="medcertitle">MEDICAL CERTIFICATE</div>

                        <div class="medcertcontent">
                            <div class="form-field">
                                This is to certify that
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['PatientName'] ?? '') ?>
                                </span>,
                                a
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['PatientAge'] ?? '') ?>
                                </span>
                                year old F/M,
                                has been seen and examined on
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['ExamDate'] ?? '') ?>
                                </span>
                                at the Medical Clinic.
                            </div>

                            <div class="form-field">
                                Pertinent findings:
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['Findings'] ?? '') ?>
                                </span>
                            </div>

                            <div class="form-field">
                                Impression on examination:
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['Impression'] ?? '') ?>
                                </span>
                            </div>

                            <div class="form-field">
                                NOTE:
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['NoteContent'] ?? '') ?>
                                </span>
                            </div>

                            <div class="signature-section">
                                Visiting Physician/University Nurse<br>
                                License No.
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['LicenseNo'] ?? '') ?>
                                </span><br>
                                Date Issued:
                                <span class="underline">
                                    <?= htmlspecialchars($medicalCertData['DateIssued'] ?? '') ?>
                                </span>
                            </div>

                            <div class="form-number">
                                LSPU-OSAS-SF-M08 | Rev. 0 | 10 Aug. 2016
                            </div>

                            <div class="cert-controls">
                                <?php if (!$isDownloaded): ?>
                                    <a href="generate_pdf_client.php?historyID=<?= urlencode($historyID) ?>" class="btn btn-success btn-sm" onclick="return confirmDownload();">
                                        <i class="fa-solid fa-download"></i> Download PDF
                                    </a>
                                <?php endif; ?>

                                <script>
                                    function confirmDownload() {
                                        return confirm("This certificate can only be downloaded once. Do you want to proceed?");
                                    }
                                </script>

                            </div>
                        </div>
                    </div>
                </div>
                <!---->
                <?php if (strtolower($clientType) === 'freshman'): ?>
                    <div class="progress-container" style="display: <?= ($progressStatus === 'completed') ? 'none' : 'flex' ?>;">
                        <h2 class="progress-title">Your Progress</h2>
                        <div class="progress-visual">
                            <div class="vertical-steps-container">
                                <?php
                                $gender = strtolower($user_data['gender'] ?? '--.--.--');

                                $steps = [
                                    'Personal Information' => 'personalinfo',
                                    'Medical & Dental History' => 'medicaldentalhistory',
                                    'Family Medical History' => 'familymedicalhistory',
                                    'Personal Social History' => 'personalsocialhistory',
                                    'Female Health History' => 'femalehealthhistory',
                                    'Physical Examination' => 'physicalexamination',
                                    'Diagnostic Results' => 'diagnosticresults',
                                    'Medical Certificate' => 'medicalcertificate'
                                ];

                                $completed = [];
                                $stepIndex = 0;
                                $currentStepIndex = -1;

                                foreach ($steps as $label => $table) {
                                    if ($table === 'femalehealthhistory' && $gender !== 'female') {
                                        continue;
                                    }

                                    // Check if 'historyID' exists in the table
                                    $checkColumn = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE 'historyID'");
                                    $checkColumn->execute();
                                    $hasHistoryID = $checkColumn->fetch() !== false;

                                    if ($hasHistoryID) {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE ClientID = ?");
                                        $stmt->execute([$clientId]);
                                    } else {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE ClientID = ?");
                                        $stmt->execute([$clientId]);
                                    }

                                    $count = $stmt->fetchColumn();

                                    $completed[$label] = ($count > 0);
                                    if (!$count && $currentStepIndex == -1) {
                                        $currentStepIndex = $stepIndex;
                                    }
                                    $stepIndex++;
                                }

                                $stepNumber = 1;
                                $stepIndex = 0;
                                foreach ($completed as $label => $is_done) {
                                    $status_class = $is_done ? 'done' : ($stepIndex === $currentStepIndex ? 'current' : 'pending');
                                    echo "<div class='vertical-step $status_class'>";
                                    echo "<span class='step-icon'>";
                                    if ($status_class === 'done') {
                                        echo "<i class='fas fa-circle-check'></i>";
                                    } elseif ($status_class === 'current') {
                                        echo "<i class='fas fa-hourglass-half'></i>";
                                    } else {
                                        echo "<i class='fas fa-clock'></i>";
                                    }

                                    echo "</span>";
                                    $displayLabel = ($label === 'Medical Certificate') ? "$label (Optional)" : $label;
                                    echo "<div class='step-text'><div class='step-numbers'>STEP $stepNumber</div><div class='step-label'>$displayLabel</div></div>";

                                    echo "</div>";
                                    $stepNumber++;
                                    $stepIndex++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (($clientType) === 'NewPersonnel'): ?>
                    <div class="form-container">
                        <form id="medicalForm" method="post">
                            <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientID ?? '') ?>">
                            <input type="hidden" id="print_action" name="print_action" value="">

                            <div style="display: flex; width: 100%; justify-content: right; align-items: center;">
                                <button style="display: flex; width: 10%;" type="button" class="page-buttons " onclick="printMedicalForm()">Print</button>
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

                                <div class="checkbox-group">
                                    <input type="checkbox" id="blood-test" name="blood_test" value="1" <?= !empty($blood_test) ? 'checked' : '' ?>>
                                    <label for="blood-test">Blood Test</label>

                                    <input type="checkbox" id="urinalysis" name="urinalysis" value="1" <?= !empty($urinalysis) ? 'checked' : '' ?>>
                                    <label for="urinalysis">Urinalysis</label>

                                    <input type="checkbox" id="xray" name="chest_xray" value="1" <?= !empty($chest_xray) ? 'checked' : '' ?>>
                                    <label for="xray">Chest X-Ray</label>

                                    <input type="checkbox" id="drug-test" name="drug_test" value="1" <?= !empty($drug_test) ? 'checked' : '' ?>>
                                    <label for="drug-test">Drug Test</label>

                                    <input type="checkbox" id="psych-test" name="psych_test" value="1" <?= !empty($psych_test) ? 'checked' : '' ?>>
                                    <label for="psych-test">Psychological Test</label>

                                    <input type="checkbox" id="neuro-test" name="neuro_test" value="1" <?= !empty($neuro_test) ? 'checked' : '' ?>>
                                    <label for="neuro-test">Neuro-Psychiatric Examination</label>
                                </div>
                            </div>

                            <div class="section">
                                <div class="section-title">FOR THE PROPOSED APPOINTEE</div>

                                <div class="form-group">
                                    <label for="name">NAME</label>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
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
                                        <input type="text" id="address" name="address" value="<?= htmlspecialchars($address ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="age">AGE</label>
                                        <input type="number" id="age" name="age" value="<?= htmlspecialchars($age ?? '') ?>" required>
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
                                        <input type="text" id="physician_signature" name="physician_signature" value="<?= htmlspecialchars($physician_signature ?? '') ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="physician_agency">AGENCY/Affiliation:</label>
                                        <input type="text" id="physician_agency" name="physician_agency" value="<?= htmlspecialchars($physician_agency ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="otherinfo">Other Information About The Proposed Appointee:</label>
                                        <input type="text" id="otherinfo" name="otherinfo" value="<?= htmlspecialchars($other_info ?? '') ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="license_no">LICENSE NO.</label>
                                        <input type="text" id="license_no" name="license_no" value="<?= htmlspecialchars($license_no ?? '') ?>" readonly>
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
                                        <input type="text" id="official_designation" name="official_designation" value="<?= htmlspecialchars($official_designation ?? '') ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date_created">Date:</label>
                                        <input type="date" id="date_created" name="date_created" value="<?= htmlspecialchars($date_created ?? date('Y-m-d')) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button class="buttons" type="submit" id="submitBtn">Submit</button>
                            </div>
                        </form>
                        <script>
                            function printMedicalForm() {
                                document.getElementById('print_action').value = '1'; // set to "1" to trigger printing
                                document.getElementById('medicalForm').action = 'generate_np_medform.php'; // send to PHP file
                                document.getElementById('medicalForm').submit();
                            }
                        </script>
                    </div>

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
                                const response = await fetch('submit_np_form.php', {
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
                            height: 60%;
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
                <?php endif; ?>
            </div>
            <!--This part is the form of the NewPersonnel type of client.
                            ========================================================================================
                            ========================================================================================-->


            <!--=======================================================================================================-->
            <div class="Content1-Div" style="display: none;" loading="lazy">
                <div id="left-content" loading="lazy">
                    <div id="profile">
                        <div id="profile-div">
                            <form id="profile-pic-form" method="POST" enctype="multipart/form-data" action="Profile.php">
                                <div class="profile-pic-div">
                                    <div class="profile-pic-wrapper">
                                        <img id="profile-pic" src="<?= $profilePic ?>" alt="Profile Picture"
                                            onerror="this.onerror=null;this.src='../uploads/profilepic2.png'">
                                    </div>
                                    <input type="file" id="image-upload" name="image" accept="image/*"
                                        onchange="previewImage();" style="display: none;" required>

                                </div>
                                <div class="profile-actions">

                                    <!--<button type="button" class="page-buttons" onclick="document.getElementById('image-upload').click()">
                                    Upload Profile Picture
                                </button>-->

                                    <button type="submit" name="submit" class="page-buttons">Save Profile Picture</button>
                                </div>
                            </form>

                            <p id="Name" class="ptext"><?= $fullName ?></p>
                            <p id="Email" class="ptext"><?= $email ?></p>

                            <?php if (isset($_GET['upload'])): ?>
                                <?php if ($_GET['upload'] == 'success'): ?>
                                    <div class="alert-success"></div>
                                <?php elseif ($_GET['upload'] == 'fail'): ?>
                                    <div class="alert-fail"></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div id="left-profile-sec">
                            <div id="status-info">
                                <div id="left-text" class="divtext">
                                    <p class="info-label">Client ID: <span class="info-value"><?php echo htmlspecialchars($clientId); ?></span></p>
                                    <p class="info-label">Department: <span class="info-value"><?= htmlspecialchars($department) ?></span></p>
                                    <p class="info-label">Course: <span class="info-value"><?= htmlspecialchars($course) ?></span></p>
                                    <p class="info-label">Client Type: <span class="info-value"><?= htmlspecialchars($clientType) ?></span></p>
                                </div>

                            </div>
                            <!--<button class="page-buttons" id="view-docs-btn">View Documents</button>-->
                        </div>
                    </div>

                    <div id="info-div">
                        <div class="tabs">
                            <button class="tab active" onclick="switchTab(event, 'personal-info')"><img id="person-info-icon" class="cp-btn-img" src="UC-Client/assets/images/id-card.png">Personal
                                Information</button>
                            <button class="tab" onclick="switchTab(event, 'medical-history')"><img id="person-info-icon" class="cp-btn-img" src="UC-Client/assets/images/diagnosis.png">Medical History</button>
                            <button class="tab" onclick="switchTab(event, 'medical-certificate')"><img id="person-info-icon" class="cp-btn-img" src="UC-Client/assets/images/medcert.png">Medical
                                Certificate</button>
                        </div>
                        <style>
                            .tab {
                                display: flex;
                                flex-direction: row;
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
                                height: 25px;
                                width: 25px;
                            }

                            @media (max-width: 768px) {
                                .tab {
                                    display: flex;
                                    flex-direction: column;
                                }
                            }
                        </style>
                        <div id="personal-info" class="tab-content active">
                            <div class="info-grid">
                                <p><span class="person-info-label">Surname:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['surname'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Given Name:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['given_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Middle Name:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['middle_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Age:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['age'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Sex:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['gender'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Birthday:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['dob'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Status:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['status'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Course:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($course) ?></span>
                                </p>

                                <p><span class="person-info-label">School Year Entered:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['school_year_entered'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Phone Number:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['contact_number'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Current Address:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['current_address'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Mother's Name:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['mothers_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Father's Name:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['fathers_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Guardian's Name:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['guardians_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Name of Emergency Contact:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['emergency_contact_name'] ?? '--.--.--') ?></span>
                                </p>

                                <p><span class="person-info-label">Relationship:</span><br>
                                    <span class="person-value-label"><?= htmlspecialchars($user_data['emergency_contact_relationship'] ?? '--.--.--') ?></span>
                                </p>
                            </div>
                        </div>

                        <div id="medical-history" class="tab-content">

                            <div id="visit-history" class="department-table-container " style="display: block;">
                                <table class="department-table">
                                    <thead>
                                        <tr>
                                            <th class="id-col">ID</th>
                                            <th>Client ID</th>
                                            <th class="action-datetime">Action Date</th>
                                            <th class="action-datetime">Action Time</th>
                                            <th>Progress</th>
                                            <th class="visual-col">Visual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historyData as $index => $row): ?>
                                            <tr>
                                                <td class="id-col"><?= htmlspecialchars($row['historyID']) ?></td>
                                                <td><?= htmlspecialchars($row['ClientID']) ?></td>
                                                <td class="action-datetime"><?= htmlspecialchars($row['actionDate']) ?></td>
                                                <td class="action-datetime"><?= htmlspecialchars($row['actionTime']) ?></td>
                                                <td class="progress-<?= htmlspecialchars($row['progress']) ?>">
                                                    <?= ucfirst(htmlspecialchars($row['progress'])) ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['progress'] === 'completed'): ?>
                                                        <div class="percentage-bar">
                                                            <div class="percentage-fill" style="width: 100%"></div>
                                                        </div>
                                                    <?php elseif ($row['progress'] === 'inprogress'): ?>
                                                        <div class="percentage-bar">
                                                            <div class="percentage-fill" style="width: 50%"></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="percentage-bar">
                                                            <div class="percentage-fill" style="width: 10%"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="medical-certificate" class="tab-content">
                            <div class="medcert-conparent">
                                <div id="medical-certificate-form">
                                    <div class="medcertheader">
                                        <img src="UC-Client/assets/images/Lspu logo.png" alt="LSPU Logo">
                                        <div class="headertextdiv">
                                            <div>Republic of the Philippines</div>
                                            <div>Laguna State Polytechnic University</div>
                                            <div>Province of Laguna</div>
                                        </div>
                                    </div>

                                    <div class="medcertitle">MEDICAL CERTIFICATE</div>

                                    <div class="medcertcontent">
                                        <div class="form-field">
                                            This is to certify that
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['PatientName'] ?? '') ?>
                                            </span>,
                                            a
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['PatientAge'] ?? '') ?>
                                            </span>
                                            year old F/M,
                                            has been seen and examined on
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['ExamDate'] ?? '') ?>
                                            </span>
                                            at the Medical Clinic.
                                        </div>

                                        <div class="form-field">
                                            Pertinent findings:
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['Findings'] ?? '') ?>
                                            </span>
                                        </div>

                                        <div class="form-field">
                                            Impression on examination:
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['Impression'] ?? '') ?>
                                            </span>
                                        </div>

                                        <div class="form-field">
                                            NOTE:
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['NoteContent'] ?? '') ?>
                                            </span>
                                        </div>

                                        <div class="signature-section">
                                            Visiting Physician/University Nurse<br>
                                            License No.
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['LicenseNo'] ?? '') ?>
                                            </span><br>
                                            Date Issued:
                                            <span class="underline">
                                                <?= htmlspecialchars($medicalCertData['DateIssued'] ?? '') ?>
                                            </span>
                                        </div>

                                        <div class="form-number">
                                            LSPU-OSAS-SF-M08 | Rev. 0 | 10 Aug. 2016
                                        </div>

                                        <div class="cert-controls">
                                            <?php if (!$isDownloaded): ?>
                                                <a href="generate_pdf_client.php?historyID=<?= urlencode($historyID) ?>" class="btn btn-success btn-sm" onclick="return confirmDownload();">
                                                    <i class="fa-solid fa-download"></i> Download PDF
                                                </a>
                                            <?php endif; ?>

                                            <script>
                                                function confirmDownload() {
                                                    return confirm("This certificate can only be downloaded once. Do you want to proceed?");
                                                }
                                            </script>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="div-info-buttons">
                            <!-- <button class="btn save">
                            <img src="UC-Client/assets/images/File-icon.svg" class="button-icon-div" loading="lazy">
                            Save Documents
                        </button>-->
                            <a href="Medical_Form.php">
                                <button class="btn edit">
                                    <img src="UC-Client/assets/images/Edit-icon.svg" class="button-icon-div" loading="lazy">
                                    Edit Information
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div id="right-content">
                    <div id="calendar">
                        <div class="Calendarheader">
                            <p id="calendar-header-text">My Calendar</h2>
                                <select id="date-select"></select>
                        </div>

                        <div class="calendar">
                            <div class="weekdays"></div>
                            <div class="days"></div>
                        </div>
                        <div class="time-display" id="time"></div>
                        <div class="status">
                            <div class="status-icon"></div>
                            <p class="status-text"><span id="selected-date"><?= htmlspecialchars($medicalCertData['DateIssued'] ?? '   ') ?></span>Medical Certificate Issuance</p>
                        </div>
                    </div>
                    <div id="call-div">
                        <div id="text-overlay">
                            <h3>Emergency Contacts:</h3>
                            <p>Clinic Hotline: (+63) 912-345-6789</p>
                            <p>Campus Emergency: (+63) 911-123-4567</p>
                            <p>Email: support@universityclinic.edu</p>
                        </div>
                        <div id="pic-div">
                            <img id="picimg" src="UC-Client/assets/images/office-pic.svg" type="image/webp" loading="lazy">
                        </div>
                        <h2>Need help?</h2>
                        <a href="#">
                            <p><span class="clinic-policies-label">View Clinic Policies | FAQs:</span>
                        </a>
                    </div>
                </div>
            </div>
            <!--===================================================================================================-->
        </main>
    </div>

</body>

</html>