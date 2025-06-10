<?php
require_once 'config/database.php';

$pdo = pdo_connect_mysql();

$clientID = isset($_GET['id']) ? $_GET['id'] : null;
$clientid = null;
$medicalHistory = [];
$familymedicalHistory = [];
$socialHistoryData = [];
$data = [];
$historyID = null;

if ($clientID) {
    // Fetch client details
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get latest historyID for that client
    $stmt = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? ORDER BY historyID DESC LIMIT 1");
    $stmt->execute([$clientID]);
    $history = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($history) {
        $historyID = $history['historyID'];

        // Fetch medical & dental history
        $stmt2 = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $medicalHistory = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        // Ensure all expected keys exist with default null values
        $expectedKeys = [
            'KnownIllness',
            'KnownIllnessDetails',
            'Hospitalization',
            'HospitalizationDetails',
            'Allergies',
            'AllergiesDetails',
            'ChildImmunization',
            'ChildImmunizationDetails',
            'PresentImmunizations',
            'PresentImmunizationsDetails',
            'CurrentMedicines',
            'CurrentMedicinesDetails',
            'DentalProblems',
            'DentalProblemsDetails',
            'PrimaryPhysician',
            'PrimaryPhysicianDetails'
        ];

        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $medicalHistory)) {
                $medicalHistory[$key] = null;
            }
        }

        // Fetch family medical history
        $stmt2 = $pdo->prepare("SELECT * FROM familymedicalhistory WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $familymedicalHistory = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        $expectedFamilyKeys = [
            'Allergy',
            'AllergyDetails',
            'Asthma',
            'AsthmaDetails',
            'Tuberculosis',
            'TuberculosisDetails',
            'Hypertension',
            'HypertensionDetails',
            'BloodDisease',
            'BloodDiseaseDetails',
            'Stroke',
            'StrokeDetails',
            'Diabetes',
            'DiabetesDetails',
            'Cancer',
            'CancerDetails',
            'LiverDisease',
            'LiverDiseaseDetails',
            'KidneyBladder',
            'KidneyBladderDetails',
            'BloodDisorder',
            'BloodDisorderDetails',
            'Epilepsy',
            'EpilepsyDetails',
            'MentalDisorder',
            'MentalDisorderDetails',
            'OtherIllness',
            'OtherIllnessDetails'
        ];

        foreach ($expectedFamilyKeys as $key) {
            if (!array_key_exists($key, $familymedicalHistory)) {
                $familymedicalHistory[$key] = null;
            }
        }

        // Fetch personal social history
        $stmt2 = $pdo->prepare("SELECT * FROM personalsocialhistory WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $socialHistoryData = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        // Fetch female health history
        $stmt2 = $pdo->prepare("SELECT * FROM femalehealthhistory WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $data = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt2 = $pdo->prepare("SELECT * FROM physicalexamination WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $physicalExam = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt2 = $pdo->prepare("SELECT * FROM diagnosticresults WHERE historyID = ?");
        $stmt2->execute([$historyID]);
        $diagnostic = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
    } else {
        $historyID = null;
    }
}
