<?php


require_once __DIR__ . '/../../config/database.php';
$pdo = pdo_connect_mysql();

header('Content-Type: application/json; charset=utf-8');

// sanitize client_id
$clientId = filter_input(INPUT_GET, 'client_id', FILTER_SANITIZE_NUMBER_INT);
if (!$clientId) {
    echo json_encode(['error' => 'No client ID provided']);
    exit;
}

try {
    // Prepare and fetch records
    $stmt = $pdo->prepare("
        SELECT file_name, file_type, file_path, upload_date
        FROM annual_exams
        WHERE client_id = :client_id
        ORDER BY upload_date DESC
    ");
    $stmt->execute(['client_id' => $clientId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $uploadsDir = realpath(__DIR__ . '/../../uploads/annual_exams');

    if ($uploadsDir === false) {
        echo json_encode(['error' => 'Uploads directory not found on server']);
        exit;
    }
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']) ?: null;
    if ($docRoot !== null) {
        $docRoot = str_replace('\\', '/', $docRoot);
    }
    $uploadsDirNormalized = str_replace('\\', '/', $uploadsDir);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $baseHost = $protocol . '://' . $host;

    $result = [];

    foreach ($files as $file) {
        $storedPath = str_replace(['..', '\\'], ['', '/'], ltrim($file['file_path'], '/\\'));

        $serverFilePath = $uploadsDir . DIRECTORY_SEPARATOR . $storedPath;

        $realServerFilePath = realpath($serverFilePath);
        if ($realServerFilePath === false) {
            continue;
        }

        $realServerFilePathNorm = str_replace('\\', '/', $realServerFilePath);
        if (strpos($realServerFilePathNorm, $uploadsDirNormalized) !== 0) {
            error_log("Security: attempted access outside uploads dir: $realServerFilePathNorm");
            continue;
        }

        if ($docRoot !== null && strpos($uploadsDirNormalized, $docRoot) === 0) {
            $relativeToDocRoot = substr($uploadsDirNormalized, strlen($docRoot));
            $relativeToDocRoot = '/' . trim(str_replace('\\', '/', $relativeToDocRoot), '/');
            $webFilePath = $baseHost . rtrim($relativeToDocRoot, '/') . '/' . implode('/', array_map('rawurlencode', explode('/', $storedPath)));
        } else {

            $projectRootWebPath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\'); // e.g. /LSPU-LBC-UniversityClinic/UC-Admin

            $webFilePath = $baseHost . $projectRootWebPath . '/../uploads/annual_exams/' . implode('/', array_map('rawurlencode', explode('/', $storedPath)));
        }

        $result[] = [
            'file_name' => $file['file_name'],
            'file_type' => $file['file_type'],
            'file_path' => $webFilePath,
            'file_size_formatted' => round(filesize($realServerFilePath) / 1024, 2) . ' KB',
            'upload_date_formatted' => date('Y-m-d H:i', strtotime($file['upload_date'])),
            'icon' => '📄'
        ];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
