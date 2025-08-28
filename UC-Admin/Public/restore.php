<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "181414";
$db   = "University_Clinic_System";
$port = 4307;

if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "msg"    => "❌ No backup file uploaded"
    ]);
    exit;
}

$backupFile = $_FILES['backup_file']['tmp_name'];

// Use mysql client to restore
$mysqlPath = "mysql"; // adjust path if needed, e.g. C:\\xampp\\mysql\\bin\\mysql.exe

$command = "\"$mysqlPath\" --user=\"$user\" --password=\"$pass\" --host=\"$host\" --port=$port $db < \"$backupFile\"";

$output = [];
$return_var = null;
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo json_encode([
        "status" => "success",
        "msg"    => "✅ Database restored successfully!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg"    => "❌ Restore failed: " . implode("\n", $output)
    ]);
}
exit;
