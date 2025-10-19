<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/database.php';
$pdo = pdo_connect_mysql();

$clientID    = $_REQUEST['client_id'] ?? null;
$bp          = $_REQUEST['bp'] ?? '';
$hr_pr       = $_REQUEST['hr_pr'] ?? '';
$temp        = $_REQUEST['temp'] ?? '';
$o2sat       = $_REQUEST['o2sat'] ?? '';
$subjective  = $_REQUEST['subjective'] ?? '';
$objective   = $_REQUEST['objective'] ?? '';
$assessment  = $_REQUEST['assessment'] ?? '';
$plan        = $_REQUEST['plan'] ?? '';

header('Content-Type: application/json');

if (!$clientID) {
    echo json_encode(['status' => 'error', 'message' => 'Missing ClientID.']);
    exit;
}

// Check if client exists
$checkClient = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
$checkClient->execute([$clientID]);
if ($checkClient->rowCount() === 0) {
    echo json_encode(['status' => 'error', 'message' => "Error: ClientID $clientID does not exist in the clients table."]);
    exit;
}

try {
    $pdo->beginTransaction();

    date_default_timezone_set('Asia/Manila');
    $actionDate     = date('Y-m-d');
    $actionTime12hr = date('h:i:s A');

    // Insert into history table (progress removed)
    $insertHistory = $pdo->prepare("
        INSERT INTO history (ClientID, actionDate, actionTime) 
        VALUES (?, ?, ?)
    ");
    $insertHistory->execute([$clientID, $actionDate, $actionTime12hr]);
    $historyID = $pdo->lastInsertId();

    // Insert into consultationrecords table
    $stmt = $pdo->prepare("
        INSERT INTO consultationrecords 
        (ClientID, historyid, BP, HR_PR, Temp, O2sat, Subjective, Objective, Assesment, Plan) 
        VALUES 
        (:clientID, :historyID, :bp, :hr_pr, :temp, :o2sat, :subjective, :objective, :assessment, :plan)
    ");
    $stmt->execute([
        ':clientID'   => $clientID,
        ':historyID'  => $historyID,
        ':bp'         => $bp,
        ':hr_pr'      => $hr_pr,
        ':temp'       => $temp,
        ':o2sat'      => $o2sat,
        ':subjective' => $subjective,
        ':objective'  => $objective,
        ':assessment' => $assessment,
        ':plan'       => $plan
    ]);

    // Insert into consultations table
    $date_issued = date('Y-m-d');
    $remarks     = "Medical certificate issued on $date_issued";

    $stmt2 = $pdo->prepare("
        INSERT INTO consultations (client_id, historyID, consultation_date, certificate_issued, remarks) 
        VALUES (?, ?, CURDATE(), TRUE, ?)
    ");
    $stmt2->execute([$clientID, $historyID, $remarks]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'message' => 'Consultation record and new history saved successfully.'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error saving data: ' . $e->getMessage()
    ]);
}
