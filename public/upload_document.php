<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . "/uploads";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "Upload error code: " . $file['error']]);
        exit;
    }

    $allowedExtensions = ['doc', 'docx', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(["success" => false, "message" => "Invalid file type."]);
        exit;
    }

    $safeName = uniqid("doc_", true) . "." . $ext;
    $target = $uploadDir . $safeName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        echo json_encode([
            "success" => true,
            "message" => "File uploaded successfully.",
            "filename" => $safeName,
            "originalName" => $file['name']
        ]);
        exit;
    }

    echo json_encode(["success" => false, "message" => "Failed to save file."]);
    exit;
}

echo json_encode(["success" => false, "message" => "No file uploaded."]);
