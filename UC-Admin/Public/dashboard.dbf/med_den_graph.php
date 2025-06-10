<?php
require_once 'config/database.php';
$conn = pdo_connect_mysql();

$data = [];

// List of fields you want to include
$fields = [
    'KnownIllnessDetails' => 'Known Illness',
    'HospitalizationDetails' => 'Hospitalization',
    'AllergiesDetails' => 'Allergies',
    'ChildImmunizationDetails' => 'Child Immunization',
    'PresentImmunizationsDetails' => 'Present Immunizations',
    'CurrentMedicinesDetails' => 'Current Medicines',
    'DentalProblemsDetails' => 'Dental Problems',
    'PrimaryPhysicianDetails' => 'Primary Physician'
];

foreach ($fields as $column => $label) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count 
        FROM medicaldentalhistory 
        WHERE `$column` IS NOT NULL AND `$column` != ''
    ");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    $data[$label] = (int)$count;
}

header('Content-Type: application/json');
echo json_encode($data);
