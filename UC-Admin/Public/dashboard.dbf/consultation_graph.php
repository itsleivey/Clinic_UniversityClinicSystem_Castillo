<?php
require 'config/database.php';
$conn = pdo_connect_mysql();

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');


$monthlyCounts = [];

for ($month = 1; $month <= 12; $month++) {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT DATE(consultation_date)) FROM consultations
        WHERE MONTH(consultation_date) = ? AND YEAR(consultation_date) = ?
    ");
    $stmt->execute([$month, $year]);
    $monthlyCounts[] = (int)$stmt->fetchColumn();
}

header('Content-Type: application/json');
echo json_encode($monthlyCounts); 
?>
