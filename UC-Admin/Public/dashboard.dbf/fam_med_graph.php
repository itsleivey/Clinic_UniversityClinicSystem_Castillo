<?php
require 'config/database.php';
$conn = pdo_connect_mysql();

// Illness fields to check
$illnesses = [
    'Allergy', 'Asthma', 'Tuberculosis', 'Hypertension', 'BloodDisease',
    'Stroke', 'Diabetes', 'Cancer', 'LiverDisease', 'KidneyBladder',
    'BloodDisorder', 'Epilepsy', 'MentalDisorder', 'OtherIllness'
];

$data = [];

foreach ($illnesses as $illness) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM familymedicalhistory WHERE $illness = 1");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    $data[$illness] = (int)$count;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
