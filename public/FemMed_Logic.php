<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'historyId' => null,
    'debug' => []
];

try {
    $conn = pdo_connect_mysql();
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    $clientID = $_SESSION['ClientID'] ?? null;
    if (!$clientID) {
        throw new Exception("Client ID not found in session.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn->beginTransaction();

        // Get historyID (latest in-progress history)
        $getHistory = $conn->prepare("SELECT historyID FROM history 
                                      WHERE ClientID = ? AND progress = 'inprogress' 
                                      ORDER BY historyID DESC LIMIT 1");
        $getHistory->execute([$clientID]);
        $historyID = $getHistory->fetchColumn();

        if (!$historyID) {
            $insertHistory = $conn->prepare("INSERT INTO history (ClientID, actionDate, progress) 
                                             VALUES (?, NOW(), 'inprogress')");
            if (!$insertHistory->execute([$clientID])) {
                throw new Exception("Failed to create history record.");
            }
            $historyID = $conn->lastInsertId();
        }

        $response['historyId'] = $historyID;

        // Get POST or JSON input
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $response['debug']['received_input'] = $input;

        // Validate required fields
        $requiredFields = ['LastPeriod', 'Regularity'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Ensure all radio fields are submitted
        $radioFields = ['Dysmenorrhea', 'AbnormalBleeding', 'PreviousPregnancy', 'HasChildren'];
        foreach ($radioFields as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Radio button group '$field' was not submitted");
            }
        }

        // Collect and sanitize data
        $data = [
            'clientId' => $clientID,
            'historyId' => $historyID,
            'lastPeriod' => $input['LastPeriod'] ?? null,
            'regularity' => $input['Regularity'] ?? null,
            'duration' => $input['Duration'] ?? null,
            'padsPerDay' => $input['PadsPerDay'] ?? null,
            'dysmenorrhea' => $input['Dysmenorrhea'] ?? null,
            'dysmenorrheaSeverity' => $input['DysmenorrheaSeverity'] ?? null,
            'lastOBVisit' => $input['LastOBVisit'] ?? null,
            'abnormalBleeding' => $input['AbnormalBleeding'] ?? null,
            'previousPregnancy' => $input['PreviousPregnancy'] ?? null,
            'pregnancyDetails' => $input['PregnancyDetails'] ?? null,
            'hasChildren' => $input['HasChildren'] ?? null,
            'childrenCount' => $input['ChildrenCount'] ?? null
        ];

        // Check if record exists
        $checkQuery = $conn->prepare("SELECT COUNT(*) FROM femalehealthhistory 
                                      WHERE ClientID = ? AND historyID = ?");
        $checkQuery->execute([$clientID, $historyID]);
        $exists = $checkQuery->fetchColumn();

        if ($exists) {
            // Update existing record
            $updateQuery = $conn->prepare("UPDATE femalehealthhistory SET 
                LastPeriod = :lastPeriod,
                Regularity = :regularity,
                Duration = :duration,
                PadsPerDay = :padsPerDay,
                Dysmenorrhea = :dysmenorrhea,
                DysmenorrheaSeverity = :dysmenorrheaSeverity,
                LastOBVisit = :lastOBVisit,
                AbnormalBleeding = :abnormalBleeding,
                PreviousPregnancy = :previousPregnancy,
                PregnancyDetails = :pregnancyDetails,
                HasChildren = :hasChildren,
                ChildrenCount = :childrenCount
                WHERE ClientID = :clientId AND historyID = :historyId");

            if (!$updateQuery->execute($data)) {
                throw new Exception("Failed to update female health history: " . implode(', ', $updateQuery->errorInfo()));
            }
        } else {
            // Insert new record
            $insertQuery = $conn->prepare("INSERT INTO femalehealthhistory 
                (ClientID, historyID, LastPeriod, Regularity, Duration, PadsPerDay, 
                Dysmenorrhea, DysmenorrheaSeverity, LastOBVisit, AbnormalBleeding, 
                PreviousPregnancy, PregnancyDetails, HasChildren, ChildrenCount) 
                VALUES 
                (:clientId, :historyId, :lastPeriod, :regularity, :duration, :padsPerDay, 
                :dysmenorrhea, :dysmenorrheaSeverity, :lastOBVisit, :abnormalBleeding, 
                :previousPregnancy, :pregnancyDetails, :hasChildren, :childrenCount)");

            if (!$insertQuery->execute($data)) {
                throw new Exception("Failed to insert female health history: " . implode(', ', $insertQuery->errorInfo()));
            }
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = $exists ? "Data updated successfully." : "Data saved successfully.";
    } else {
        $response['message'] = "Invalid request method. Only POST accepted.";
    }

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("PDO Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (isset($conn)) $conn->rollBack();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Logic Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (isset($conn)) $conn->rollBack();
} finally {
    if (isset($_GET['debug'])) {
        $response['debug']['session'] = $_SESSION;
    } else {
        unset($response['debug']);
    }

    echo json_encode($response);
    if (isset($conn)) {
        $conn = null;
    }
}
