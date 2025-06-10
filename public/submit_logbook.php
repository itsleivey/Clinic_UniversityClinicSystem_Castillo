<?php
session_start();
require_once '../config/database.php';  

date_default_timezone_set('Asia/Manila'); 

$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientID             = $_POST['ClientID'] ?? null;
    $name                 = trim($_POST['name'] ?? '');
    $course               = trim($_POST['course'] ?? '');
    $year                 = trim($_POST['year'] ?? '');
    $section              = trim($_POST['section'] ?? '');
    $medication_treatment = trim($_POST['medication_treatment'] ?? '');
    $illness              = trim($_POST['illness'] ?? '');
    $remarks              = trim($_POST['remarks'] ?? '');

    if (!$clientID || !$name || !$course || !$year || !$section || !$medication_treatment || !$illness) {
        die("Missing required fields.");
    }

    $log_date = date('Y-m-d');
    $time_finished = date('h:i:s A'); 

    $stmt = $pdo->prepare("SELECT actionTime FROM history WHERE ClientID = ? ORDER BY historyID DESC LIMIT 1");
    $stmt->execute([$clientID]);
    $time_started = $stmt->fetchColumn();

    if (!$time_started) {
        $time_started = null;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO logbook (
            ClientID, log_date, name, course, year, section,
            time_started, time_finished, medication_treatment, illness, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $clientID,
            $log_date,
            $name,
            $course,
            $year,
            $section,
            $time_started,
            $time_finished,
            $medication_treatment,
            $illness,
            $remarks
        ]);

        header("Location: profile.php?logbook=success");
        exit();
    } catch (PDOException $e) {
        die("Error saving logbook: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
