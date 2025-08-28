<?php
$host = "localhost";
$user = "root";
$pass = "181414";
$db   = "University_Clinic_System";
$port = 4307;

$backupDir = __DIR__ . "/backups/";
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$backupFile = $backupDir . "clinic_backup_" . date("Y-m-d_H-i-s") . ".sql";

// path to mysqldump (adjust if needed, e.g. C:\\xampp\\mysql\\bin\\mysqldump.exe)
$mysqldumpPath = "mysqldump";

// Windows or Linux/macOS handle stderr differently
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // On Windows: discard errors with 2>nul
    $command = "\"$mysqldumpPath\" --user=\"$user\" --password=\"$pass\" --host=\"$host\" --port=$port $db > \"$backupFile\" 2>nul";
} else {
    // On Linux/Mac: discard errors with 2>/dev/null
    $command = "\"$mysqldumpPath\" --user=\"$user\" --password=\"$pass\" --host=\"$host\" --port=$port $db > \"$backupFile\" 2>/dev/null";
}

$output = [];
$return_var = null;
exec($command, $output, $return_var);

// respond with clean JSON (not the dump itself!)
if ($return_var === 0 && file_exists($backupFile)) {
    echo "success|backups/" . basename($backupFile);
} else {
    echo "error|Backup failed\n" . implode("\n", $output);
}
