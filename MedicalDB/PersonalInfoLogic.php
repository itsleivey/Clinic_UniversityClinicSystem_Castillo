<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$pdo = pdo_connect_mysql();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch data
function getUserDataFromDatabase(PDO $pdo, int $clientId): ?array
{
    $stmt = $pdo->prepare("
        SELECT 
            Surname, GivenName, MiddleName, Age, Gender,
            DateOfBirth, Status, Course, SchoolYearEntered,
            CurrentAddress, ContactNumber,
            MothersName, FathersName, GuardiansName,
            EmergencyContactName, EmergencyContactRelationship, EmergencyContactPerson
        FROM personalinfo
        WHERE ClientID = :ClientID
    ");
    $stmt->execute(['ClientID' => $clientId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Insert data
function insertUserData(PDO $pdo, int $clientId, array $d): bool
{
    $sql = "INSERT INTO personalinfo (
        ClientID, Surname, GivenName, MiddleName, Age, Gender,
        DateOfBirth, Status, Course, SchoolYearEntered,
        CurrentAddress, ContactNumber,
        MothersName, FathersName, GuardiansName,
        EmergencyContactName, EmergencyContactRelationship, EmergencyContactPerson
    ) VALUES (
        :ClientID, :Surname, :GivenName, :MiddleName, :Age, :Gender,
        :DateOfBirth, :Status, :Course, :SchoolYearEntered,
        :CurrentAddress, :ContactNumber,
        :MothersName, :FathersName, :GuardiansName,
        :EmergencyContactName, :EmergencyContactRelationship, :EmergencyContactPerson
    )";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_merge($d, ['ClientID' => $clientId]));
}

// Update data
function updateUserData(PDO $pdo, int $clientId, array $d): bool
{
    $sql = "UPDATE personalinfo SET
                Surname                       = :Surname,
                GivenName                     = :GivenName,
                MiddleName                    = :MiddleName,
                Age                           = :Age,
                Gender                        = :Gender,
                DateOfBirth                   = :DateOfBirth,
                Status                        = :Status,
                Course                        = :Course,
                SchoolYearEntered             = :SchoolYearEntered,
                CurrentAddress                = :CurrentAddress,
                ContactNumber                 = :ContactNumber,
                MothersName                   = :MothersName,
                FathersName                   = :FathersName,
                GuardiansName                 = :GuardiansName,
                EmergencyContactName          = :EmergencyContactName,
                EmergencyContactRelationship  = :EmergencyContactRelationship,
                EmergencyContactPerson        = :EmergencyContactPerson
            WHERE ClientID = :ClientID";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_merge($d, ['ClientID' => $clientId]));
}

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Surname'])) {
    if (!isset($_SESSION['ClientID'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Client ID not in session']);
        exit;
    }

    $clientId = (int) $_SESSION['ClientID'];

    $d = [
        'Surname'                      => filter_input(INPUT_POST, 'Surname', FILTER_SANITIZE_SPECIAL_CHARS),
        'GivenName'                    => filter_input(INPUT_POST, 'GivenName', FILTER_SANITIZE_SPECIAL_CHARS),
        'MiddleName'                   => filter_input(INPUT_POST, 'MiddleName', FILTER_SANITIZE_SPECIAL_CHARS),
        'Age'                          => filter_input(INPUT_POST, 'Age', FILTER_VALIDATE_INT),
        'Gender'                       => filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_SPECIAL_CHARS),
        'DateOfBirth'                  => filter_input(INPUT_POST, 'DateOfBirth', FILTER_DEFAULT),
        'Status'                       => filter_input(INPUT_POST, 'Status', FILTER_SANITIZE_SPECIAL_CHARS),
        'Course'                       => filter_input(INPUT_POST, 'Course', FILTER_SANITIZE_SPECIAL_CHARS),
        'SchoolYearEntered'            => filter_input(INPUT_POST, 'SchoolYearEntered', FILTER_SANITIZE_SPECIAL_CHARS),
        'CurrentAddress'               => filter_input(INPUT_POST, 'CurrentAddress', FILTER_SANITIZE_SPECIAL_CHARS),
        'ContactNumber'                => filter_input(INPUT_POST, 'ContactNumber', FILTER_SANITIZE_SPECIAL_CHARS),
        'MothersName'                  => filter_input(INPUT_POST, 'MothersName', FILTER_SANITIZE_SPECIAL_CHARS),
        'FathersName'                  => filter_input(INPUT_POST, 'FathersName', FILTER_SANITIZE_SPECIAL_CHARS),
        'GuardiansName'                => filter_input(INPUT_POST, 'GuardiansName', FILTER_SANITIZE_SPECIAL_CHARS),
        'EmergencyContactName'         => filter_input(INPUT_POST, 'EmergencyContactName', FILTER_SANITIZE_SPECIAL_CHARS),
        'EmergencyContactRelationship' => filter_input(INPUT_POST, 'EmergencyContactRelationship', FILTER_SANITIZE_SPECIAL_CHARS),
        'EmergencyContactPerson'       => filter_input(INPUT_POST, 'EmergencyGuardiansName', FILTER_SANITIZE_SPECIAL_CHARS) // fixed key
    ];

    if (empty($d['Surname']) || empty($d['GivenName']) || $d['Age'] === false) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields correctly.'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personalinfo WHERE ClientID = ?");
        $stmt->execute([$clientId]);
        $exists = (bool) $stmt->fetchColumn();

        if ($exists) {
            $ok = updateUserData($pdo, $clientId, $d);
            $action = 'updated';
        } else {
            $ok = insertUserData($pdo, $clientId, $d);
            $action = 'inserted';
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Personal info {$action} successfully!" : "Failed to {$action} personal info."
        ]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// For loading form data
if (isset($_SESSION['ClientID'])) {
    $formData = getUserDataFromDatabase($pdo, $_SESSION['ClientID']) ?: [];
} elseif (isset($_SESSION['formData'])) {
    $formData = $_SESSION['formData'];
    unset($_SESSION['formData']);
} else {
    $formData = [];
}
