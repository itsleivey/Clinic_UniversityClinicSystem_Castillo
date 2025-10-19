<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pdo = pdo_connect_mysql();

header('Content-Type: application/json');

// Only handle POST requests with a file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['exam_file'])) {

    // Get client ID from POST or from session
    $client_id = $_POST['client_id'] ?? $_SESSION['ClientID'] ?? null;
    if (!$client_id) {
        echo json_encode(['status' => 'error', 'message' => 'Client ID missing.']);
        exit;
    }

    $file = $_FILES['exam_file'];
    $uploadDir = __DIR__ . '/../uploads/annual_exams/';

    // Create folder if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = mime_content_type($fileTmp);
    $uploadDate = date('Y-m-d H:i:s');

    // Allowed file types
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
        exit;
    }

    // Generate unique file name to avoid overwriting
    $uniqueName = uniqid('exam_', true) . '_' . $fileName;
    $filePath = $uploadDir . $uniqueName;

    // Move uploaded file
    if (move_uploaded_file($fileTmp, $filePath)) {

        // Save record in database
        $stmt = $pdo->prepare("INSERT INTO annual_exams (client_id, file_name, file_path, file_size, file_type, upload_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$client_id, $fileName, $uniqueName, $fileSize, $fileType, $uploadDate]);

        echo json_encode([
            'status' => 'success',
            'file_name' => $fileName,
            'file_size' => round($fileSize / 1024, 2), // in KB
            'file_type' => $fileType,
            'upload_date' => $uploadDate,
            'file_path' => 'uploads/annual_exams/' . $uniqueName
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
}
