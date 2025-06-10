<?php
session_start();
require_once '../config/database.php';
date_default_timezone_set('Asia/Manila'); 

$pdo = pdo_connect_mysql();

function convertTo12HourFormat($time24) {
    $time = DateTime::createFromFormat('H:i', $time24);
    return $time ? $time->format('h:i A') : $time24;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientID             = $_POST['ClientID'] ?? null;
    $name                 = trim($_POST['name'] ?? '');
    $course               = trim($_POST['course'] ?? '');
    $year                 = trim($_POST['year'] ?? '');
    $section              = trim($_POST['section'] ?? '');
    $medication_treatment = trim($_POST['medication_treatment'] ?? '');
    $illness              = trim($_POST['illness'] ?? '');
    $remarks              = trim($_POST['remarks'] ?? '');
    $time_started         = trim($_POST['time_started'] ?? '');
    $time_finished        = trim($_POST['time_finished'] ?? '');

    if (!$clientID || !$name || !$course || !$year || !$section || !$medication_treatment || !$illness || !$time_started || !$time_finished) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

    $log_date = date('Y-m-d');

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

        echo json_encode([
            "date" => $log_date,
            "name" => $name,
            "course" => $course,
            "year" => $year,
            "section" => $section,
            "time_started" => convertTo12HourFormat($time_started),
            "time_finished" => convertTo12HourFormat($time_finished),
            "medication_treatment" => $medication_treatment,
            "illness" => $illness,
            "remarks" => $remarks
        ]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Database error: " . $e->getMessage();
        exit;
    }
} else {
    http_response_code(405);
    echo "Invalid request.";
}
