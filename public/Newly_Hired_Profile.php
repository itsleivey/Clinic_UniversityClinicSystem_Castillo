<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../MedicalDB/PersonalInfoLogic.php';

$pdo = pdo_connect_mysql();
$clientId = $_SESSION['ClientID'] ?? null;
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


$formData = [];
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
            <a href="Medical_Form.php">
                <button class="active-buttons" id="medicalBtn">
                    <i class="fas fa-file-lines button-icon-nav"></i>
                    <span class="nav-text">Medical Forms</span>
                </button>
            </a>
            <a href="Settings.php">
                <button class="buttons" id="settingBtn">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </button>
            </a>
        </nav>

        <main class="content" loading="lazy">
            <div class="form-container">
                <form id="medicalForm" method="post">
                    <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientID ?? '') ?>">
                    <input type="hidden" id="print_action" name="print_action" value="">

                    <div class="header-div" style="display: flex; width: 100%; justify-content: right; align-items: center;">
                        <button type="button" class="page-buttons " onclick="printMedicalForm()">Print</button>
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
                                <input type="text" id="height" name="height" value="<?= htmlspecialchars($height ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="weight">WEIGHT (KG)</label>
                                <input type="text" id="weight" name="weight" value="<?= htmlspecialchars($weight ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="blood-type">BLOOD TYPE</label>
                                <input type="text" id="blood-type" name="blood-type" value="<?= htmlspecialchars($blood_type ?? '') ?>">
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
                        <button type="submit" id="submitBtn">Submit</button>
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
                    --primary-color: #0b62c9;
                    --secondary-color: #094d9a;
                    --light-gray: #f5f7fa;
                    --medium-gray: #dfe3e8;
                    --dark-gray: #555;
                    --border-radius: 4px;
                }

                .form-container {
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-start;
                    width: 100%;
                    max-height: 3200px;
                    background-color: #fff;
                    padding: 40px;
                    border-radius: var(--border-radius);
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                }

                .form-header {
                    text-align: center;
                    margin-bottom: 35px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid var(--medium-gray);
                }

                .form-header h1 {
                    font-size: 22px;
                    font-weight: 600;
                    color: var(--primary-color);
                    margin: 0;
                }

                .form-header h2 {
                    font-size: 16px;
                    font-weight: 500;
                    color: var(--dark-gray);
                    margin: 5px 0 0 0;
                }

                .section {
                    margin-bottom: 25px;
                    padding: 25px;
                    background-color: var(--light-gray);
                    border: 1px solid var(--medium-gray);
                    border-radius: var(--border-radius);
                }

                .section-title {
                    font-weight: 600;
                    color: var(--primary-color);
                    font-size: 17px;
                    margin-bottom: 20px;
                    border-bottom: 1px solid var(--medium-gray);
                    padding-bottom: 6px;
                }

                .form-row {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 18px;
                    margin-bottom: 20px;
                }

                .form-group {
                    flex: 1;
                    min-width: 220px;
                }

                label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 500;
                    color: var(--dark-gray);
                    font-size: 14px;
                }

                input[type="text"],
                input[type="number"],
                input[type="date"],
                select {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid var(--medium-gray);
                    border-radius: 4px;
                    background-color: #fff;
                    font-size: 14px;
                    color: #333;
                    transition: border-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
                }

                input[type="text"]:focus,
                input[type="number"]:focus,
                input[type="date"]:focus,
                select:focus {
                    outline: none;
                    border-color: var(--primary-color);
                    background-color: #f9fbff;
                }

                .checkbox-group {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                    gap: 10px;
                }

                .checkbox-group input[type="checkbox"] {
                    margin-right: 8px;
                    accent-color: var(--primary-color);
                }

                .buttons,
                .page-buttons {
                    background-color: var(--primary-color);
                    color: #fff;
                    border: none;
                    border-radius: 5px;
                    padding: 12px 25px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background-color 0.25s ease;
                }

                .buttons:hover,
                .page-buttons:hover {
                    background-color: var(--secondary-color);
                }

                .buttons:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                }

                .form-footer {
                    margin-top: 25px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #submitBtn,
                .header-div button {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    background-color: var(--primary-color);
                    color: #fff;
                    border: none;
                    border-radius: 3px;
                    font-size: clamp(1rem, 1vw, 1.5rem);
                    width: 150px;
                    height: 50px;
                    font-weight: 500;
                    font-family: "Poppins", sans-serif;
                    cursor: pointer;
                    transition: background-color 0.25s ease;
                }

                #settingBtn {
                    background-color: #397dda;
                }

                @media (max-width: 768px) {
                    .form-container {
                        padding: 25px;
                    }

                    .form-row {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </main>
    </div>
</body>

</html