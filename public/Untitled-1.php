 <div id="profile">
                    <div id="profile-div">
                        <form id="profile-pic-form" method="POST" enctype="multipart/form-data" action="Profile.php">
                            <!-- Hidden file input button for image selection -->
                            <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;" onchange="previewImage();">

                            <!-- Display the current profile picture -->
                            <img id="profile-pic" src="<?= $profilePic ?>" alt="Profile Picture" loading="lazy" onerror="this.src='UC-Client/assets/images/default-icon.png'" style="max-width: 150px;">

                            <!-- User Details -->
                            <p id="Name" class="ptext"><?= $fullName ?></p>
                            <p id="Email" class="ptext"><?= $email ?></p>

                            <!-- Button to trigger the file input -->
                            <button type="button" class="page-buttons" id="upload-btn" onclick="document.getElementById('image-upload').click();">Upload Profile Picture</button>

                            <!-- Save button -->
                            <button type="submit" name="submit">Save Profile Picture</button>
                        </form>
                    </div>

                    <div id="left-profile-sec">
                        <!-- Profile information -->
                        <div id="status-info">
                            <div id="left-text" class="divtext">
                                <p class="info-label">Department: <span class="info-value">College of Computer Studies</span></p>
                                <p class="info-label">Course: <span class="info-value">Information Technology</span></p>
                                <p class="info-label">Registration Date: <span class="info-value">02.28.25</span></p>
                            </div>
                            <div id="right-text" class="divtext">
                                <p class="info-label">Client Type: <span class="info-value">Student</span></p>
                                <p class="info-label">Completion Date: <span class="info-value">03.04.2025</span></p>
                                <p class="info-label">Status: <span class="status-badge">In Progress</span></p>
                            </div>
                        </div>
                        <button class="page-buttons" id="view-docs-btn">View Documents</button>
                    </div>
                </div>




                <?php
session_start();
require_once('C:/Xampp.f/htdocs/UC-login/config/database.php'); // Ensure the database connection is correct

// Initialize the PDO connection
$pdo = pdo_connect_mysql(); // Use the PDO connection for fetching user details

if (!isset($_SESSION['ClientID'])) {
    header('Location: index.php');
    exit();
}

//===============Uploading Profile Picture==========================================================
if (isset($_POST['submit'])) {
    // Get file details
    $file_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];
    $folder = 'uploads/' . $file_name;

    // Check if the 'uploads' folder exists, if not, create it
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Try moving the uploaded file to the server folder first
    if (move_uploaded_file($temp_name, $folder)) {
        // Now, insert the image path into the 'Clients' table
        $query = $pdo->prepare("UPDATE Clients SET profilePicturePath = ? WHERE ClientID = ?");
        $query->execute([$file_name, $_SESSION['ClientID']]); // Use the session ClientID for the update

        if ($query) {
            echo "<script>alert('Image uploaded and path saved in database.');</script>";
        } else {
            echo "<script>alert('Failed to insert data into database.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload image.');</script>";
    }
}
//===================================================================================

$stmt = $pdo->prepare("SELECT Firstname, Lastname, Email, ProfilePicturePath FROM Clients WHERE ClientID = ?");
$stmt->execute([$_SESSION['ClientID']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Set the profile picture path
$profilePic = !empty($user['ProfilePicturePath']) ?
    '/UC-Login/' . $user['ProfilePicturePath'] . '?t=' . time() :
    'UC-Client/assets/images/default-profile.png';

// Set the full name and email
$fullName = htmlspecialchars($user['Firstname'] . ' ' . $user['Lastname']);
$email = htmlspecialchars($user['Email']);
?>

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medicalDentalSubmit'])) {
    try {
        $data = [
            'ClientID' => $_SESSION['ClientID'],
            'KnownIllness' => isset($_POST['knownIllness']) ? 1 : 0,
            'KnownIllnessDetails' => !empty($_POST['knownIllnessDetails']) ? trim($_POST['knownIllnessDetails']) : null,
            'Hospitalization' => isset($_POST['hospitalization']) ? 1 : 0,
            'HospitalizationDetails' => !empty($_POST['hospitalizationDetails']) ? trim($_POST['hospitalizationDetails']) : null,
            'Allergies' => isset($_POST['allergies']) ? 1 : 0,
            'AllergiesDetails' => !empty($_POST['allergiesDetails']) ? trim($_POST['allergiesDetails']) : null,
            'ChildImmunization' => isset($_POST['childImmunization']) ? 1 : 0,
            'ChildImmunizationDetails' => !empty($_POST['childImmunizationDetails']) ? trim($_POST['childImmunizationDetails']) : null,
            'PresentImmunizations' => isset($_POST['presentImmunizations']) ? 1 : 0,
            'PresentImmunizationsDetails' => !empty($_POST['presentImmunizationsDetails']) ? trim($_POST['presentImmunizationsDetails']) : null,
            'CurrentMedicines' => isset($_POST['currentMedicines']) ? 1 : 0,
            'CurrentMedicinesDetails' => !empty($_POST['currentMedicinesDetails']) ? trim($_POST['currentMedicinesDetails']) : null,
            'DentalProblems' => isset($_POST['dentalProblems']) ? 1 : 0,
            'DentalProblemsDetails' => !empty($_POST['dentalProblemsDetails']) ? trim($_POST['dentalProblemsDetails']) : null,
            'PrimaryPhysician' => isset($_POST['primaryPhysician']) ? 1 : 0,
            'PrimaryPhysicianDetails' => !empty($_POST['primaryPhysicianDetails']) ? trim($_POST['primaryPhysicianDetails']) : null
        ];

        $stmt = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE ClientID = ?");
        $stmt->execute([$_SESSION['ClientID']]);
        $medicalHistory = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $_SESSION['success_message'] = "Medical and dental history saved successfully!";
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to save medical history. Please try again.";
    }
    header('Location: Medical_Form.php');
    exit;
}