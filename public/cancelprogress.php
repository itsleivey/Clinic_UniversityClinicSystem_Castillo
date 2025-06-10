<?php
session_start();
require_once '../config/database.php';

$clientId = $_SESSION['ClientID'] ?? null;

if (!$clientId) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

try {
    $pdo = pdo_connect_mysql();
    
    // Get current time for the cancellation record in Manila Time (UTC +8)
    $manilaTimeZone = new DateTimeZone('Asia/Manila');
    $cancelTime = new DateTime('now', $manilaTimeZone);
    $cancelTimeFormatted = $cancelTime->format('H:i:s');
    
    // Update the most recent progress to 'undone' with proper time ordering
    $stmt = $pdo->prepare("
        UPDATE history 
        SET progress = 'undone',
            actionTime = :cancelTime
        WHERE ClientID = :clientId 
        ORDER BY actionDate DESC, actionTime DESC
        LIMIT 1
    ");
    
    $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
    $stmt->bindParam(':cancelTime', $cancelTimeFormatted);
    
    if ($stmt->execute()) {
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Progress cancelled',
                'cancelledAt' => $cancelTime->format('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No active progress to cancel'
            ]);
        }
    } else {
        throw new Exception('Failed to cancel progress');
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Cancel Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
