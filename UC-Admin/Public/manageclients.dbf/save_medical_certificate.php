<?php
require '../config/database.php';
session_start();

$pdo = pdo_connect_mysql();

$client_id = $_POST['client_id'];
$patient_name = $_POST['patient_name'];
$patient_age = $_POST['patient_age'];
$exam_date = $_POST['exam_date'];
$findings = $_POST['findings'];
$impression = $_POST['impression'];
$note_content = $_POST['note'];
$license_no = $_POST['license_no'];
$date_issued = $_POST['date_issued'];

if (!strtotime($exam_date)) {
    die("Invalid date format for exam date");
}
$exam_date = date('Y-m-d', strtotime($exam_date));

$getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' Order By historyID Desc LIMIT 1");
$getHistory->execute([$client_id]);
$historyID = $getHistory->fetchColumn();

if (!$historyID) {
    $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'completed')");
    $insertHistory->execute([$client_id]);
    $historyID = $pdo->lastInsertId();
}

$stmt = $pdo->prepare('INSERT INTO medicalcertificate 
    (ClientID, historyID, PatientName, PatientAge, ExamDate, Findings, Impression, NoteContent, LicenseNo, DateIssued) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$client_id, $historyID, $patient_name, $patient_age, $exam_date, $findings, $impression, $note_content, $license_no, $date_issued]);

$remarks = "Medical certificate issued on $date_issued";
$stmt2 = $pdo->prepare("INSERT INTO consultations (client_id, historyID, consultation_date, certificate_issued, remarks) VALUES (?, ?, CURDATE(), TRUE, ?)");
$stmt2->execute([$client_id, $historyID, $remarks]);

header('Location: ClientProfile.php');
exit;
