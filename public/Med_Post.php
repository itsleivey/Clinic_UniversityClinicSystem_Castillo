<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['ClientID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medicalDentalSubmit'])) {
    try {
        $pdo = pdo_connect_mysql();

        $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' Order By historyID Desc LIMIT 1");
        $getHistory->execute([$_SESSION['ClientID']]);
        $historyID = $getHistory->fetchColumn();

        if (!$historyID) {
            $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
            $insertHistory->execute([$_SESSION['ClientID']]);
            $historyID = $pdo->lastInsertId();
        }

        $data = [
            'historyID'                    => $historyID,
            'KnownIllness'                 => isset($_POST['knownIllness']) ? 1 : 0,
            'KnownIllnessDetails'         => !empty(trim($_POST['knownIllnessDetails'] ?? '')) ? trim($_POST['knownIllnessDetails']) : null,
            'Hospitalization'              => isset($_POST['hospitalization']) ? 1 : 0,
            'HospitalizationDetails'      => !empty(trim($_POST['hospitalizationDetails'] ?? '')) ? trim($_POST['hospitalizationDetails']) : null,
            'Allergies'                   => isset($_POST['allergies']) ? 1 : 0,
            'AllergiesDetails'            => !empty(trim($_POST['allergiesDetails'] ?? '')) ? trim($_POST['allergiesDetails']) : null,
            'ChildImmunization'           => isset($_POST['childImmunization']) ? 1 : 0,
            'ChildImmunizationDetails'    => !empty(trim($_POST['childImmunizationDetails'] ?? '')) ? trim($_POST['childImmunizationDetails']) : null,
            'PresentImmunizations'        => isset($_POST['presentImmunizations']) ? 1 : 0,
            'PresentImmunizationsDetails' => !empty(trim($_POST['presentImmunizationsDetails'] ?? '')) ? trim($_POST['presentImmunizationsDetails']) : null,
            'CurrentMedicines'            => isset($_POST['currentMedicines']) ? 1 : 0,
            'CurrentMedicinesDetails'    => !empty(trim($_POST['currentMedicinesDetails'] ?? '')) ? trim($_POST['currentMedicinesDetails']) : null,
            'DentalProblems'              => isset($_POST['dentalProblems']) ? 1 : 0,
            'DentalProblemsDetails'       => !empty(trim($_POST['dentalProblemsDetails'] ?? '')) ? trim($_POST['dentalProblemsDetails']) : null,
            'PrimaryPhysician'            => isset($_POST['primaryPhysician']) ? 1 : 0,
            'PrimaryPhysicianDetails'    => !empty(trim($_POST['primaryPhysicianDetails'] ?? '')) ? trim($_POST['primaryPhysicianDetails']) : null,
        ];

        // Check if there's an existing medical record for this historyID
        $check = $pdo->prepare("SELECT COUNT(*) FROM medicaldentalhistory WHERE historyID = ?");
        $check->execute([$historyID]);
        $exists = $check->fetchColumn();

        if ($exists) {

            $sql = "UPDATE medicaldentalhistory SET
                        KnownIllness = :KnownIllness,
                        KnownIllnessDetails = :KnownIllnessDetails,
                        Hospitalization = :Hospitalization,
                        HospitalizationDetails = :HospitalizationDetails,
                        Allergies = :Allergies,
                        AllergiesDetails = :AllergiesDetails,
                        ChildImmunization = :ChildImmunization,
                        ChildImmunizationDetails = :ChildImmunizationDetails,
                        PresentImmunizations = :PresentImmunizations,
                        PresentImmunizationsDetails = :PresentImmunizationsDetails,
                        CurrentMedicines = :CurrentMedicines,
                        CurrentMedicinesDetails = :CurrentMedicinesDetails,
                        DentalProblems = :DentalProblems,
                        DentalProblemsDetails = :DentalProblemsDetails,
                        PrimaryPhysician = :PrimaryPhysician,
                        PrimaryPhysicianDetails = :PrimaryPhysicianDetails
                    WHERE historyID = :historyID";
        } else {
            $data['ClientID'] = $_SESSION['ClientID'];
            $sql = "INSERT INTO medicaldentalhistory (
                        ClientID,
                        historyID,
                        KnownIllness, KnownIllnessDetails,
                        Hospitalization, HospitalizationDetails,
                        Allergies, AllergiesDetails,
                        ChildImmunization, ChildImmunizationDetails,
                        PresentImmunizations, PresentImmunizationsDetails,
                        CurrentMedicines, CurrentMedicinesDetails,
                        DentalProblems, DentalProblemsDetails,
                        PrimaryPhysician, PrimaryPhysicianDetails
                    ) VALUES (
                        :ClientID,
                        :historyID,
                        :KnownIllness, :KnownIllnessDetails,
                        :Hospitalization, :HospitalizationDetails,
                        :Allergies, :AllergiesDetails,
                        :ChildImmunization, :ChildImmunizationDetails,
                        :PresentImmunizations, :PresentImmunizationsDetails,
                        :CurrentMedicines, :CurrentMedicinesDetails,
                        :DentalProblems, :DentalProblemsDetails,
                        :PrimaryPhysician, :PrimaryPhysicianDetails
                    )";
        }

        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($data);

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Data saved successfully!']);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Database error: " . print_r($errorInfo, true));
            echo json_encode(['status' => 'error', 'message' => 'Failed to save data. Please try again.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
