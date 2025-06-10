<?php
session_start();
require_once '../config/database.php';

// Get ClientID from session (more secure than POST)
$clientId = $_SESSION['ClientID'] ?? null;

if (!$clientId) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized - Client ID missing']));
}

try {
    $pdo = pdo_connect_mysql();

    // Set timezone to Philippine time
    date_default_timezone_set('Asia/Manila');

    // Get current date and time in PHT
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO history (ClientID, actionDate, actionTime, progress) 
        VALUES (:clientId, :actionDate, :actionTime, 'inprogress')
    ");

    $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
    $stmt->bindParam(':actionDate', $currentDate);
    $stmt->bindParam(':actionTime', $currentTime);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Progress status set to "In Progress"',
            'newEntry' => [
                'historyID' => $pdo->lastInsertId(),
                'actionDate' => $currentDate,
                'actionTime' => $currentTime,
                'progress' => 'inprogress'
            ]
        ]);
    } else {
        throw new Exception('Failed to execute query');
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
