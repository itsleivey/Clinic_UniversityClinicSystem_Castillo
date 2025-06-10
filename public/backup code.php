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
        
        // First, get the current inprogress historyID for this client
        $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' LIMIT 1");
        $getHistory->execute([$_SESSION['ClientID']]);
        $historyID = $getHistory->fetchColumn();

        // If no inprogress record exists, create one
        if (!$historyID) {
            $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
            $insertHistory->execute([$_SESSION['ClientID']]);
            $historyID = $pdo->lastInsertId();
        }

        // build the data array
        $data = [
            'ClientID'                     => $_SESSION['ClientID'],
            'historyID'                    => $historyID,
            'KnownIllness'                 => isset($_POST['knownIllness']) ? 1 : 0,
            'KnownIllnessDetails'         => trim($_POST['knownIllnessDetails'] ?? '') ?: null,
            'Hospitalization'              => isset($_POST['hospitalization']) ? 1 : 0,
            'HospitalizationDetails'      => trim($_POST['hospitalizationDetails'] ?? '') ?: null,
            'Allergies'                   => isset($_POST['allergies']) ? 1 : 0,
            'AllergiesDetails'            => trim($_POST['allergiesDetails'] ?? '') ?: null,
            'ChildImmunization'           => isset($_POST['childImmunization']) ? 1 : 0,
            'ChildImmunizationDetails'    => trim($_POST['childImmunizationDetails'] ?? '') ?: null,
            'PresentImmunizations'        => isset($_POST['presentImmunizations']) ? 1 : 0,
            'PresentImmunizationsDetails' => trim($_POST['presentImmunizationsDetails'] ?? '') ?: null,
            'CurrentMedicines'            => isset($_POST['currentMedicines']) ? 1 : 0,
            'CurrentMedicinesDetails'    => trim($_POST['currentMedicinesDetails'] ?? '') ?: null,
            'DentalProblems'              => isset($_POST['dentalProblems']) ? 1 : 0,
            'DentalProblemsDetails'       => trim($_POST['dentalProblemsDetails'] ?? '') ?: null,
            'PrimaryPhysician'            => isset($_POST['primaryPhysician']) ? 1 : 0,
            'PrimaryPhysicianDetails'    => trim($_POST['primaryPhysicianDetails'] ?? '') ?: null,
        ];

        // Check if there's an existing inprogress medical record for this historyID
        $check = $pdo->prepare("SELECT 1 FROM medicaldentalhistory WHERE historyID = ?");
        $check->execute([$historyID]);
        $exists = $check->fetchColumn();

        if ($exists) {
            // Update only the current inprogress record
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
            // Create a new medical record
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
        $stmt->execute($data);

        echo json_encode(['status' => 'success', 'message' => 'Data saved successfully!']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to save medical history. Please try again.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);