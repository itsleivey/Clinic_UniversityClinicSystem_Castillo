<?php
// FemaleHealthLogic.php
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

        try {
            $getHistory = $conn->prepare("SELECT historyID FROM history 
                                        WHERE ClientID = ? AND progress = 'inprogress' 
                                        ORDER BY historyID DESC LIMIT 1");
            $getHistory->execute([$clientID]);
            $historyID = $getHistory->fetchColumn();

            $response['debug']['initial_history_id'] = $historyID;

            if (!$historyID) {
                $insertHistory = $conn->prepare("INSERT INTO history 
                                               (ClientID, actionDate, progress) 
                                               VALUES (?, NOW(), 'inprogress')");
                if (!$insertHistory->execute([$clientID])) {
                    throw new Exception("Failed to create history record.");
                }
                $historyID = $conn->lastInsertId();
                $response['debug']['new_history_id'] = $historyID;
            }

            $response['historyId'] = $historyID;

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $response['debug']['received_data'] = $input;

            $requiredFields = ['LastPeriod', 'Regularity'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // REMOVED THE DEFAULT 'no' VALUES - use exactly what comes from the form
            $data = [
                'clientId' => $clientID,
                'historyId' => $historyID,
                'lastPeriod' => $input['LastPeriod'] ?? null,
                'regularity' => $input['Regularity'] ?? null,
                'duration' => $input['Duration'] ?? null,
                'padsPerDay' => $input['PadsPerDay'] ?? null,
                'dysmenorrhea' => $input['Dysmenorrhea'] ?? null, // Changed from default 'no'
                'dysmenorrheaSeverity' => $input['DysmenorrheaSeverity'] ?? null,
                'lastOBVisit' => $input['LastOBVisit'] ?? null,
                'abnormalBleeding' => $input['AbnormalBleeding'] ?? null, // Changed from default 'no'
                'previousPregnancy' => $input['PreviousPregnancy'] ?? null, // Changed from default 'no'
                'pregnancyDetails' => $input['PregnancyDetails'] ?? null,
                'hasChildren' => $input['HasChildren'] ?? null, // Changed from default 'no'
                'childrenCount' => $input['ChildrenCount'] ?? null // Changed from default 0
            ];

            // Verify required radio buttons were submitted
            $radioFields = ['Dysmenorrhea', 'AbnormalBleeding', 'PreviousPregnancy', 'HasChildren'];
            foreach ($radioFields as $field) {
                if (!isset($input[$field])) {
                    throw new Exception("Radio button group '$field' was not submitted");
                }
            }

            $checkQuery = $conn->prepare("SELECT COUNT(*) FROM femalehealthhistory 
                                        WHERE ClientID = ? AND historyID = ?");
            $checkQuery->execute([$clientID, $historyID]);
            $exists = $checkQuery->fetchColumn();

            if ($exists) {
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

                $updateSuccess = $updateQuery->execute($data);
                
                if (!$updateSuccess) {
                    throw new Exception("Failed to update female health history: " . 
                                      implode(", ", $updateQuery->errorInfo()));
                }
            } else {
                $insertQuery = $conn->prepare("INSERT INTO femalehealthhistory 
                    (ClientID, historyID, LastPeriod, Regularity, Duration, PadsPerDay, 
                    Dysmenorrhea, DysmenorrheaSeverity, LastOBVisit, AbnormalBleeding, 
                    PreviousPregnancy, PregnancyDetails, HasChildren, ChildrenCount) 
                    VALUES 
                    (:clientId, :historyId, :lastPeriod, :regularity, :duration, :padsPerDay, 
                    :dysmenorrhea, :dysmenorrheaSeverity, :lastOBVisit, :abnormalBleeding, 
                    :previousPregnancy, :pregnancyDetails, :hasChildren, :childrenCount)");

                $insertSuccess = $insertQuery->execute($data);
                
                if (!$insertSuccess) {
                    throw new Exception("Failed to insert female health history: " . 
                                      implode(", ", $insertQuery->errorInfo()));
                }
            }

            $conn->commit();
            $response['success'] = true;
            $response['message'] = $exists ? "Data updated successfully." : "Data saved successfully.";

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } else {
        $response['message'] = "Invalid request method. Only POST accepted.";
    }
} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("PDO Error in FemaleHealthLogic: " . $e->getMessage() . "\n" . $e->getTraceAsString());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error in FemaleHealthLogic: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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