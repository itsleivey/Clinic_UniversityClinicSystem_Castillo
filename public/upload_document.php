<?php
header('Content-Type: application/json');
require_once '../config/database.php';
$pdo = pdo_connect_mysql();

$uploadDir = __DIR__ . "/uploads";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];

    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "Upload error code: " . $file['error']]);
        exit;
    }

    // Validate file extension
    $allowedExtensions = ['doc', 'docx', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(["success" => false, "message" => "Invalid file type."]);
        exit;
    }

    // Generate safe unique filename
    $safeName = uniqid("doc_", true) . "." . $ext;
    $target = $uploadDir . "/" . $safeName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        try {
            $userId = $_POST['user_id'] ?? 0;
            if (!$userId) {
                echo json_encode(["success" => false, "message" => "Missing user_id."]);
                exit;
            }

            // Insert into DB
            $stmt = $pdo->prepare("INSERT INTO uploaded_files (user_id, original_name, stored_name) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $file['name'], $safeName]);

            echo json_encode([
                "success" => true,
                "message" => "File uploaded and saved successfully.",
                "filename" => $safeName,
                "originalName" => $file['name']
            ]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
            exit;
        }
    }

    echo json_encode(["success" => false, "message" => "Failed to save file."]);
    exit;
}

echo json_encode(["success" => false, "message" => "No file uploaded."]);
