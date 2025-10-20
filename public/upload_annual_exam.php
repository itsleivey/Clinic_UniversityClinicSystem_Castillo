<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pdo = pdo_connect_mysql();

header('Content-Type: application/json');

// Only handle POST requests with a file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['exam_files'])) {
    $client_id = $_POST['client_id'] ?? $_SESSION['ClientID'] ?? null;
    if (!$client_id) exit(json_encode(['status' => 'error', 'message' => 'Client ID missing.']));

    $uploadedFiles = [];
    foreach ($_FILES['exam_files']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['exam_files']['name'][$key]);
        $fileTmp  = $tmpName;
        $fileSize = $_FILES['exam_files']['size'][$key];
        $fileType = mime_content_type($fileTmp);
        $uploadDate = date('Y-m-d H:i:s');

        // validate file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedTypes)) continue;

        $uniqueName = uniqid('exam_', true) . '_' . $fileName;
        $filePath = __DIR__ . '/../uploads/annual_exams/' . $uniqueName;
        if (move_uploaded_file($fileTmp, $filePath)) {
            $stmt = $pdo->prepare("INSERT INTO annual_exams (client_id, file_name, file_path, file_size, file_type, upload_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$client_id, $fileName, $uniqueName, $fileSize, $fileType, $uploadDate]);
            $uploadedFiles[] = $fileName;
        }
    }

    echo json_encode(['status' => 'success', 'files' => $uploadedFiles]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
}
