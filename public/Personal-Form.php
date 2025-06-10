<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require('../config/database.php');
require_once('C:/Xampp.f/htdocs/UC-System/Profile/Profile_db.php');

$pdo = pdo_connect_mysql();
$user_data = getUserDataFromDatabase($pdo);

// Check login session
if (!isset($_SESSION['ClientID'])) {
    header("Location: index.php");
    exit();
}

$clientID = $_SESSION['ClientID'];

try {
    $conn = pdo_connect_mysql();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $_SESSION['form_data'] = $_POST;

    $requiredFields = [
        'surname', 'given_name', 'age', 'gender', 'dob',
        'status', 'current_address', 'contact_number', 
        'emergency_contact_name', 'emergency_contact_relationship'
    ];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['form_error'] = 'Please fill all required fields';
            header("Location: Personal_Form.php");
            exit();
        }
    }

    // Sanitize and assign variables
    $userData = [
        'surname' => htmlspecialchars(trim($_POST['surname'])),
        'given_name' => htmlspecialchars(trim($_POST['given_name'])),
        'middle_name' => $_POST['middle_name'] ? htmlspecialchars(trim($_POST['middle_name'])) : null,
        'age' => (int)$_POST['age'],
        'gender' => htmlspecialchars(trim($_POST['gender'])),
        'dob' => htmlspecialchars(trim($_POST['dob'])),
        'status' => htmlspecialchars(trim($_POST['status'])),
        'course' => $_POST['course'] ? htmlspecialchars(trim($_POST['course'])) : null,
        'school_year_entered' => $_POST['school_year_entered'] ? htmlspecialchars(trim($_POST['school_year_entered'])) : null,
        'current_address' => htmlspecialchars(trim($_POST['current_address'])),
        'contact_number' => htmlspecialchars(trim($_POST['contact_number'])),
        'mothers_name' => $_POST['mothers_name'] ? htmlspecialchars(trim($_POST['mothers_name'])) : null,
        'fathers_name' => $_POST['fathers_name'] ? htmlspecialchars(trim($_POST['fathers_name'])) : null,
        'guardians_name' => $_POST['guardians_name'] ? htmlspecialchars(trim($_POST['guardians_name'])) : null,
        'emergency_contact_name' => htmlspecialchars(trim($_POST['emergency_contact_name'])),
        'emergency_contact_relationship' => htmlspecialchars(trim($_POST['emergency_contact_relationship']))
    ];

    try {
        $checkClient = $conn->prepare("SELECT ClientID FROM clients WHERE ClientID = ?");
        $checkClient->execute([$clientID]);

        if (!$checkClient->fetch()) {
            throw new Exception("Invalid ClientID: User not found in database");
        }

        $checkClientData = $conn->prepare("SELECT ClientID FROM personalinfo WHERE ClientID = ?");
        $checkClientData->execute([$clientID]);

        if ($checkClientData->fetch()) {
            // UPDATE
            $query = "UPDATE personalinfo SET 
                Surname = ?, GivenName = ?, MiddleName = ?, Age = ?, Gender = ?, 
                DateOfBirth = ?, Status = ?, Course = ?, SchoolYearEntered = ?, CurrentAddress = ?, 
                ContactNumber = ?, MothersName = ?, FathersName = ?, GuardiansName = ?, 
                EmergencyContactName = ?, EmergencyContactRelationship = ? 
                WHERE ClientID = ?";
            $params = array_values($userData);
            $params[] = $clientID;
        } else {
            // INSERT
            $query = "INSERT INTO personalinfo (
                Surname, GivenName, MiddleName, Age, Gender, 
                DateOfBirth, Status, Course, SchoolYearEntered, CurrentAddress, 
                ContactNumber, MothersName, FathersName, GuardiansName, 
                EmergencyContactName, EmergencyContactRelationship, ClientID
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array_values($userData);
            $params[] = $clientID;
        }

        $stmt = $conn->prepare($query);
        if ($stmt->execute($params)) {
            unset($_SESSION['form_data']);
            unset($_SESSION['form_error']);
            header("Location: Medical_Form.php");
            exit();
        } else {
            throw new Exception("Error saving data.");
        }

    } catch (PDOException $e) {
        $_SESSION['form_error'] = 'Database error occurred. Please try again.';
        error_log("PDO Error: " . $e->getMessage());
        header("Location: Personal_Form.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['form_error'] = $e->getMessage();
        error_log("Application Error: " . $e->getMessage());
        header("Location: Personal_Form.php");
        exit();
    } finally {
        $conn = null;
    }
} else {
    header("Location: Personal_Form.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_data'] = $_POST;
    header("Location: Personal-Form.php"); 
    exit();
}

?>
