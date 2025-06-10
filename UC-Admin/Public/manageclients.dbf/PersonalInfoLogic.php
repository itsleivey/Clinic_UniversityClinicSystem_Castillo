<?php
session_start();

require_once 'config/database.php';

$pdo = pdo_connect_mysql();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json'); // All responses will be JSON

/**
 * Check if ClientID exists in clients table
 */
function clientExists(PDO $pdo, int $clientId): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientId]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Fetch personal info for a client
 */
function getUserDataFromDatabase(PDO $pdo, int $clientId): ?array {
    $stmt = $pdo->prepare("
        SELECT 
            Surname, GivenName, MiddleName, Age, Gender,
            DateOfBirth, Status, Course, SchoolYearEntered,
            CurrentAddress, ContactNumber,
            MothersName, FathersName, GuardiansName,
            EmergencyContactName, EmergencyContactRelationship
        FROM personalinfo
        WHERE ClientID = :ClientID
    ");
    $stmt->execute(['ClientID' => $clientId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Insert new personal info record
 */
function insertUserData(PDO $pdo, int $clientId, array $d): bool {
    $sql = "INSERT INTO personalinfo (
                ClientID, Surname, GivenName, MiddleName, Age, Gender,
                DateOfBirth, Status, Course, SchoolYearEntered,
                CurrentAddress, ContactNumber,
                MothersName, FathersName, GuardiansName,
                EmergencyContactName, EmergencyContactRelationship
            ) VALUES (
                :ClientID, :Surname, :GivenName, :MiddleName, :Age, :Gender,
                :DateOfBirth, :Status, :Course, :SchoolYearEntered,
                :CurrentAddress, :ContactNumber,
                :MothersName, :FathersName, :GuardiansName,
                :EmergencyContactName, :EmergencyContactRelationship
            )";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_merge($d, ['ClientID' => $clientId]));
}

/**
 * Update existing personal info record
 */
function updateUserData(PDO $pdo, int $clientId, array $d): bool {
    $sql = "UPDATE personalinfo SET
                Surname = :Surname,
                GivenName = :GivenName,
                MiddleName = :MiddleName,
                Age = :Age,
                Gender = :Gender,
                DateOfBirth = :DateOfBirth,
                Status = :Status,
                Course = :Course,
                SchoolYearEntered = :SchoolYearEntered,
                CurrentAddress = :CurrentAddress,
                ContactNumber = :ContactNumber,
                MothersName = :MothersName,
                FathersName = :FathersName,
                GuardiansName = :GuardiansName,
                EmergencyContactName = :EmergencyContactName,
                EmergencyContactRelationship = :EmergencyContactRelationship
            WHERE ClientID = :ClientID";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_merge($d, ['ClientID' => $clientId]));
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Surname'])) {
    $clientId = filter_input(INPUT_POST, 'ClientID', FILTER_VALIDATE_INT);
    if (!$clientId && isset($_SESSION['ClientID'])) {
        $clientId = (int)$_SESSION['ClientID'];
    }

    if (!$clientId) {
        echo json_encode(['success' => false, 'message' => 'Client ID is missing or invalid.']);
        exit;
    }

    if (!clientExists($pdo, $clientId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Client ID: client does not exist.']);
        exit;
    }

    $d = [
        'Surname' => filter_input(INPUT_POST, 'Surname', FILTER_SANITIZE_SPECIAL_CHARS),
        'GivenName' => filter_input(INPUT_POST, 'GivenName', FILTER_SANITIZE_SPECIAL_CHARS),
        'MiddleName' => filter_input(INPUT_POST, 'MiddleName', FILTER_SANITIZE_SPECIAL_CHARS),
        'Age' => filter_input(INPUT_POST, 'Age', FILTER_VALIDATE_INT),
        'Gender' => filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_SPECIAL_CHARS),
        'DateOfBirth' => $_POST['DateOfBirth'] ?? null,
        'Status' => filter_input(INPUT_POST, 'Status', FILTER_SANITIZE_SPECIAL_CHARS),
        'Course' => filter_input(INPUT_POST, 'Course', FILTER_SANITIZE_SPECIAL_CHARS),
        'SchoolYearEntered' => filter_input(INPUT_POST, 'SchoolYearEntered', FILTER_SANITIZE_SPECIAL_CHARS),
        'CurrentAddress' => filter_input(INPUT_POST, 'CurrentAddress', FILTER_SANITIZE_SPECIAL_CHARS),
        'ContactNumber' => filter_input(INPUT_POST, 'ContactNumber', FILTER_SANITIZE_SPECIAL_CHARS),
        'MothersName' => filter_input(INPUT_POST, 'MothersName', FILTER_SANITIZE_SPECIAL_CHARS),
        'FathersName' => filter_input(INPUT_POST, 'FathersName', FILTER_SANITIZE_SPECIAL_CHARS),
        'GuardiansName' => filter_input(INPUT_POST, 'GuardiansName', FILTER_SANITIZE_SPECIAL_CHARS),
        'EmergencyContactName' => filter_input(INPUT_POST, 'EmergencyContactName', FILTER_SANITIZE_SPECIAL_CHARS),
        'EmergencyContactRelationship' => filter_input(INPUT_POST, 'EmergencyContactRelationship', FILTER_SANITIZE_SPECIAL_CHARS),
    ];

    if (empty($d['Surname']) || empty($d['GivenName']) || $d['Age'] === false) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields correctly.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personalinfo WHERE ClientID = ?");
        $stmt->execute([$clientId]);
        $exists = (bool)$stmt->fetchColumn();

        if ($exists) {
            $ok = updateUserData($pdo, $clientId, $d);
            $action = 'updated';
        } else {
            $ok = insertUserData($pdo, $clientId, $d);
            $action = 'inserted';
        }

        if ($ok) {
            echo json_encode(['success' => true, 'message' => "Personal info {$action} successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Failed to {$action} personal info."]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}

// Optional: return existing data for AJAX pre-fill (not needed unless requested)
