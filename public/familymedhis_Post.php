<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['ClientID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired.']);
    exit;
}
$clientId = $_SESSION['ClientID'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['familymedicalSubmit'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

try {
    $pdo = pdo_connect_mysql();

        $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' Order By historyID Desc LIMIT 1");
    $getHistory->execute([$clientId]);
    $historyID = $getHistory->fetchColumn();

    if (!$historyID) {
        $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
        $insertHistory->execute([$clientId]);
        $historyID = $pdo->lastInsertId();
    }

    // Collect data
    $data = [
        'Allergy'                 => isset($_POST['allergy']) ? 1 : 0,
        'AllergyDetails'          => trim($_POST['allergyDetails'] ?? '') ?: null,
        'Asthma'                  => isset($_POST['asthma']) ? 1 : 0,
        'AsthmaDetails'           => trim($_POST['asthmaDetails'] ?? '') ?: null,
        'Tuberculosis'            => isset($_POST['tuberculosis']) ? 1 : 0,
        'TuberculosisDetails'     => trim($_POST['tuberculosisDetails'] ?? '') ?: null,
        'Hypertension'            => isset($_POST['hypertension']) ? 1 : 0,
        'HypertensionDetails'     => trim($_POST['hypertensionDetails'] ?? '') ?: null,
        'BloodDisease'            => isset($_POST['bloodDisease']) ? 1 : 0,
        'BloodDiseaseDetails'     => trim($_POST['bloodDiseaseDetails'] ?? '') ?: null,
        'Stroke'                  => isset($_POST['stroke']) ? 1 : 0,
        'StrokeDetails'           => trim($_POST['strokeDetails'] ?? '') ?: null,
        'Diabetes'                => isset($_POST['diabetes']) ? 1 : 0,
        'DiabetesDetails'         => trim($_POST['diabetesDetails'] ?? '') ?: null,
        'Cancer'                  => isset($_POST['cancer']) ? 1 : 0,
        'CancerDetails'           => trim($_POST['cancerDetails'] ?? '') ?: null,
        'LiverDisease'            => isset($_POST['liverDisease']) ? 1 : 0,
        'LiverDiseaseDetails'     => trim($_POST['liverDiseaseDetails'] ?? '') ?: null,
        'KidneyBladder'           => isset($_POST['kidneyBladder']) ? 1 : 0,
        'KidneyBladderDetails'    => trim($_POST['kidneyBladderDetails'] ?? '') ?: null,
        'BloodDisorder'           => isset($_POST['bloodDisorder']) ? 1 : 0,
        'BloodDisorderDetails'    => trim($_POST['bloodDisorderDetails'] ?? '') ?: null,
        'Epilepsy'                => isset($_POST['epilepsy']) ? 1 : 0,
        'EpilepsyDetails'         => trim($_POST['epilepsyDetails'] ?? '') ?: null,
        'MentalDisorder'          => isset($_POST['mentalDisorder']) ? 1 : 0,
        'MentalDisorderDetails'   => trim($_POST['mentalDisorderDetails'] ?? '') ?: null,
        'OtherIllness'            => isset($_POST['otherIllness']) ? 1 : 0,
        'OtherIllnessDetails'     => trim($_POST['otherIllnessDetails'] ?? '') ?: null,
    ];

    // Check if entry exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM familymedicalhistory WHERE historyID = ?");
    $check->execute([$historyID]);
    $exists = $check->fetchColumn();

    if ($exists) {
        // Update query
        $sql = "UPDATE familymedicalhistory SET
            Allergy = :Allergy, AllergyDetails = :AllergyDetails,
            Asthma = :Asthma, AsthmaDetails = :AsthmaDetails,
            Tuberculosis = :Tuberculosis, TuberculosisDetails = :TuberculosisDetails,
            Hypertension = :Hypertension, HypertensionDetails = :HypertensionDetails,
            BloodDisease = :BloodDisease, BloodDiseaseDetails = :BloodDiseaseDetails,
            Stroke = :Stroke, StrokeDetails = :StrokeDetails,
            Diabetes = :Diabetes, DiabetesDetails = :DiabetesDetails,
            Cancer = :Cancer, CancerDetails = :CancerDetails,
            LiverDisease = :LiverDisease, LiverDiseaseDetails = :LiverDiseaseDetails,
            KidneyBladder = :KidneyBladder, KidneyBladderDetails = :KidneyBladderDetails,
            BloodDisorder = :BloodDisorder, BloodDisorderDetails = :BloodDisorderDetails,
            Epilepsy = :Epilepsy, EpilepsyDetails = :EpilepsyDetails,
            MentalDisorder = :MentalDisorder, MentalDisorderDetails = :MentalDisorderDetails,
            OtherIllness = :OtherIllness, OtherIllnessDetails = :OtherIllnessDetails
            WHERE historyID = :historyID";

        $data['historyID'] = $historyID; // Add for WHERE clause
    } else {
        // Insert query
        $sql = "INSERT INTO familymedicalhistory (
            ClientID, historyID, Allergy, AllergyDetails, Asthma, AsthmaDetails,
            Tuberculosis, TuberculosisDetails, Hypertension, HypertensionDetails,
            BloodDisease, BloodDiseaseDetails, Stroke, StrokeDetails,
            Diabetes, DiabetesDetails, Cancer, CancerDetails,
            LiverDisease, LiverDiseaseDetails, KidneyBladder, KidneyBladderDetails,
            BloodDisorder, BloodDisorderDetails, Epilepsy, EpilepsyDetails,
            MentalDisorder, MentalDisorderDetails, OtherIllness, OtherIllnessDetails
        ) VALUES (
            :ClientID, :historyID, :Allergy, :AllergyDetails, :Asthma, :AsthmaDetails,
            :Tuberculosis, :TuberculosisDetails, :Hypertension, :HypertensionDetails,
            :BloodDisease, :BloodDiseaseDetails, :Stroke, :StrokeDetails,
            :Diabetes, :DiabetesDetails, :Cancer, :CancerDetails,
            :LiverDisease, :LiverDiseaseDetails, :KidneyBladder, :KidneyBladderDetails,
            :BloodDisorder, :BloodDisorderDetails, :Epilepsy, :EpilepsyDetails,
            :MentalDisorder, :MentalDisorderDetails, :OtherIllness, :OtherIllnessDetails
        )";

        $data['ClientID'] = $clientId;
        $data['historyID'] = $historyID;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    echo json_encode([
        'status' => 'success',
        'message' => $exists
            ? 'Updated family medical history.'
            : 'Saved family medical history.'
    ]);
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
}
