<?php
require_once __DIR__ . '/../config/database.php';
$pdo = pdo_connect_mysql();

session_start();
$clientId = $_SESSION['ClientID'] ?? null;

header('Content-Type: application/json');

if (!$clientId) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    // Fetch files for the current user
    $stmt = $pdo->prepare("
        SELECT file_name, file_type, file_path, upload_date 
        FROM annual_exams 
        WHERE client_id = :client_id 
        ORDER BY upload_date DESC
    ");
    $stmt->execute(['client_id' => $clientId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($files as $file) {
        $serverFilePath = __DIR__ . '/../uploads/annual_exams/' . $file['file_path'];

        // Skip files that don't exist on the server
        if (!file_exists($serverFilePath)) {
            error_log("File not found on server: " . $serverFilePath);
            continue;
        }

        // FIX: Use relative path from public directory to uploads directory
        $webFilePath = "../uploads/annual_exams/" . $file['file_path'];

        $result[] = [
            'file_name' => $file['file_name'],
            'file_type' => $file['file_type'],
            'file_path' => $webFilePath, // This should now work
            'file_size_formatted' => round(filesize($serverFilePath) / 1024, 2) . ' KB',
            'upload_date_formatted' => date('Y-m-d H:i', strtotime($file['upload_date'])),
            'icon' => '📄'
        ];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
