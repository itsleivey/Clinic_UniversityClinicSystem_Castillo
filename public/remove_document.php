<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . "../uploads-documents/";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = basename($_POST['filename']); // secure
    $filePath = $uploadDir . $filename;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(["success" => true, "message" => "File removed."]);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Could not delete file."]);
            exit;
        }
    }
    echo json_encode(["success" => false, "message" => "File not found."]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request."]);
