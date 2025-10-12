<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['AdminID'])) {
    header('Location: index.php');
    exit;
}

$pdo = pdo_connect_mysql();

// === CLIENT COUNTS ===
$counts = ['Student' => 0, 'Freshman' => 0, 'Faculty' => 0, 'Personnel' => 0, 'NewPersonnel' => 0];

$stmt = $pdo->query("SELECT ClientType, COUNT(*) AS total FROM clients GROUP BY ClientType");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $counts[$row['ClientType']] = (int)$row['total'];
}
$counts['Total'] = array_sum($counts);

// === GENDER COUNTS ===
$genderCounts = [];
$stmt = $pdo->query("SELECT Gender, COUNT(*) AS total FROM personalinfo GROUP BY Gender");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $genderCounts[$row['Gender']] = (int)$row['total'];
}

// === CONSULTATION COUNTS ===
$yearStmt = $pdo->query("SELECT COUNT(*) FROM consultations WHERE YEAR(consultation_date) = YEAR(CURDATE())");
$perYear = (int)$yearStmt->fetchColumn();

$month = date('n');
$semesterStart = ($month >= 1 && $month <= 6) ? 1 : 7;
$semesterEnd = ($month >= 1 && $month <= 6) ? 6 : 12;

$semesterStmt = $pdo->prepare("
    SELECT COUNT(*) FROM consultations 
    WHERE YEAR(consultation_date) = YEAR(CURDATE()) 
    AND MONTH(consultation_date) BETWEEN ? AND ?
");
$semesterStmt->execute([$semesterStart, $semesterEnd]);
$perSemester = (int)$semesterStmt->fetchColumn();

$monthStmt = $pdo->query("
    SELECT COUNT(*) FROM consultations 
    WHERE MONTH(consultation_date) = MONTH(CURDATE()) 
    AND YEAR(consultation_date) = YEAR(CURDATE())
");
$perMonth = (int)$monthStmt->fetchColumn();
