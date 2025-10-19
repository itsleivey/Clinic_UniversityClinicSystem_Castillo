<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
$pdo = pdo_connect_mysql();

$UserInfoData = [];
$clientId = $_SESSION['ClientID'] ?? null;

$name = $agency = $npaddress = $npage = $sex = $civil_status = $position = '';
$blood_test = $urinalysis = $chest_xray = $drug_test = $psych_test = $neuro_test = 0;
$physician_signature = $physician_agency = $other_info = $license_no = $height = $weight = $blood_type = '';
$date_created = date('Y-m-d'); // default today

if ($clientId) {
    $stmt = $pdo->prepare("SELECT * FROM newpersonnel_form WHERE client_id = :client_id ORDER BY form_id Desc");
    $stmt->execute(['client_id' => $clientId]);
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

            <a href="settings.php">
                <button class="buttons" id="settingBtn">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </button>
            </a>
        </nav>

        <main class="content" loading="lazy">
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

            <div class="form-container">
                <form id="medicalForm" method="post">
                    <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientId ?? '') ?>">
                    <input type="hidden" id="print_action" name="print_action" value="">

                    <div class="header-div">
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
                    <div id="messageBox" class="form-message"></div>
                    <div class="form-footer" style="padding: 30px">
                        <button type="submit" class="buttonsdp" id="submitBtn">Submit</button>
                    </div>
                </form>
                <script>
                    function printMedicalForm() {
                        document.getElementById('print_action').value = '1';
                        document.getElementById('medicalForm').action = 'generate_np_medform.php';
                        document.getElementById('medicalForm').submit();
                    }
                </script>

            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.getElementById('medicalForm');
                    const confirmModal = document.getElementById('confirmModal');
                    const confirmYes = document.getElementById('confirmYes');
                    const confirmNo = document.getElementById('confirmNo');
                    const submitBtn = document.getElementById('submitBtn');
                    const messageBox = document.getElementById('messageBox'); // ✅ Add this
                    const successModal = document.getElementById('successModal'); // ✅ Add this
                    const successClose = document.getElementById('successClose'); // ✅ Add this

                    // Step 1️⃣: Show confirm modal before submitting
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        confirmModal.style.display = 'flex';
                    });

                    // Step 2️⃣: Cancel submission
                    confirmNo.addEventListener('click', () => {
                        confirmModal.style.display = 'none';
                    });

                    // Step 3️⃣: Confirm submission
                    confirmYes.addEventListener('click', async function() {
                        confirmModal.style.display = 'none';
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';

                        const formData = new FormData(form);

                        try {
                            const response = await fetch('submit_np_form.php', {
                                method: 'POST',
                                body: formData
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

                    // Step 4️⃣: Close success modal
                    successClose.addEventListener('click', () => {
                        successModal.style.display = 'none';
                        // Optional: redirect back to profile
                        // window.location.href = 'Newly_Hired_Profile.php';
                    });
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
                    font-family: "Poppins", sans-serif;
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
                    margin-bottom: 15px;
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
                    grid-template-columns: repeat(auto, 33%);
                    grid-template-rows: repeat(auto, 33%);
                    gap: 10px;
                    padding: 15px;

                }

                .checkbox-group input[type="checkbox"] {
                    margin-right: 8px;
                    accent-color: var(--primary-color);
                }

                .checkboxes {
                    display: flex;
                    flex-direction: row;
                    gap: 8px;

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

                .header-div {
                    display: flex;
                    justify-content: flex-end;
                }

                .check-box-parent-div {
                    display: flex;
                    height: 100%;
                    width: 100%;
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

                p {
                    font-family: "Montserrat", sans-serif;
                }

                .form-message {
                    text-align: center;
                    margin-top: 10px;
                    font-weight: 500;
                }

                .form-message.success {
                    color: #0a6b2e;
                }

                .form-message.error {
                    color: #b22222;
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