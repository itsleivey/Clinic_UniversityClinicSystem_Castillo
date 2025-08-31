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

$backupFile = $backupDir . "university_clinic_backup_" . date("Y-m-d_H-i-s") . ".sql";

$mysqldumpPath = "mysqldump";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $command = "\"$mysqldumpPath\" --user=\"$user\" --password=\"$pass\" --host=\"$host\" --port=$port $db > \"$backupFile\" 2>nul";
} else {
    $command = "\"$mysqldumpPath\" --user=\"$user\" --password=\"$pass\" --host=\"$host\" --port=$port $db > \"$backupFile\" 2>/dev/null";
}

$output = [];
$return_var = null;
exec($command, $output, $return_var);

if ($return_var === 0 && file_exists($backupFile)) {
    echo "success|backups/" . basename($backupFile);

    // ✅ Insert log into backup_logs table
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO backup_logs (file_name, backup_date, backup_time, status) VALUES (?, ?, ?, ?)");
        $date = date("Y-m-d");
        $time = date("h:i:s A");
        $status = "success";
        $fileName = basename($backupFile);

        $stmt->bind_param("ssss", $fileName, $date, $time, $status);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
} else {
    echo "error|Backup failed\n" . implode("\n", $output);

    // ✅ Insert failed attempt into logs
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO backup_logs (file_name, backup_date, backup_time, status) VALUES (?, ?, ?, ?)");
        $date = date("Y-m-d");
        $time = date("H:i:s");
        $status = "failed";
        $fileName = basename($backupFile);

        $stmt->bind_param("ssss", $fileName, $date, $time, $status);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}
