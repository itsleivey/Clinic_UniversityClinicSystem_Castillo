<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['ClientID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

$pdo = pdo_connect_mysql();
$clientId = $_SESSION['ClientID'];

try {
    // Get or create history record
    $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' ORDER BY historyID DESC LIMIT 1");
    $getHistory->execute([$clientId]);
    $historyID = $getHistory->fetchColumn();

    if (!$historyID) {
        $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
        $insertHistory->execute([$clientId]);
        $historyID = $pdo->lastInsertId();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = $pdo->prepare("SELECT 
            AlcoholIntake as alcoholIntake,
            AlcoholDetails as alcoholDetails,
            TobaccoUse as tobaccoUse,
            TobaccoDetails as tobaccoDetails,
            DrugUse as drugUse,
            DrugDetails as drugDetails
        FROM personalsocialhistory WHERE ClientID = ?");
        $query->execute([$clientId]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            $data = [
                'alcoholIntake' => 'no',
                'alcoholDetails' => '',
                'tobaccoUse' => 'no',
                'tobaccoDetails' => '',
                'drugUse' => 'no',
                'drugDetails' => ''
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $data = [
            'ClientID' => $clientId,
            'historyID' => $historyID,
            'AlcoholIntake' => $input['alcoholIntake'] ?? 'no',
            'AlcoholDetails' => trim($input['alcoholDetails'] ?? ''),
            'TobaccoUse' => $input['tobaccoUse'] ?? 'no',
            'TobaccoDetails' => trim($input['tobaccoDetails'] ?? ''),
            'DrugUse' => $input['drugUse'] ?? 'no',
            'DrugDetails' => trim($input['drugDetails'] ?? '')
        ];

        $check = $pdo->prepare("SELECT COUNT(*) FROM personalsocialhistory WHERE ClientID = ?");
        $check->execute([$clientId]);
        $exists = $check->fetchColumn();

        if ($exists) {
            $sql = "UPDATE personalsocialhistory SET 
                        historyID = :historyID,
                        AlcoholIntake = :AlcoholIntake,
                        AlcoholDetails = :AlcoholDetails,
                        TobaccoUse = :TobaccoUse,
                        TobaccoDetails = :TobaccoDetails,
                        DrugUse = :DrugUse,
                        DrugDetails = :DrugDetails
                    WHERE ClientID = :ClientID";
        } else {
            $sql = "INSERT INTO personalsocialhistory (
                        ClientID, historyID,
                        AlcoholIntake, AlcoholDetails,
                        TobaccoUse, TobaccoDetails,
                        DrugUse, DrugDetails
                    ) VALUES (
                        :ClientID, :historyID,
                        :AlcoholIntake, :AlcoholDetails,
                        :TobaccoUse, :TobaccoDetails,
                        :DrugUse, :DrugDetails
                    )";
        }

        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($data);

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Data saved successfully!']);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . print_r($errorInfo, true));
            echo json_encode(['status' => 'error', 'message' => 'Failed to save data.', 'error' => $errorInfo]);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
}