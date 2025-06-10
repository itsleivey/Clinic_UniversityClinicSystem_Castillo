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

        return empty($clientData['ClientType']) || empty($clientData['Department']);
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
    $query = $conn->prepare("SELECT ClientType, Department FROM clients WHERE ClientID = ?");
    $query->execute([$clientId]);
    $client = $query->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $clientType = $client['ClientType'] ?: '--.--.--';
        $department = $client['Department'] ?: '--.--.--';
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