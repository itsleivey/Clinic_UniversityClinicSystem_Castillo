<?php
session_start();
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data)) {
        throw new Exception("No data received.");
    }

    $pdo = pdo_connect_mysql();
    $pdo->beginTransaction();

    $clientID = $data['ClientID'] ?? null;
    $historyID = $data['historyID'] ?? null;

    if (!$clientID) {
        throw new Exception("ClientID missing.");
    }

    // ---------------------------
    // 1️⃣ Get or create history record
    // ---------------------------
    if (!$historyID) {
        $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? ORDER BY historyID DESC LIMIT 1");
        $getHistory->execute([$clientID]);
        $historyID = $getHistory->fetchColumn();

        if (!$historyID) {
            $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, actionTime) VALUES (?, CURDATE(), CURTIME())");
            $insertHistory->execute([$clientID]);
            $historyID = $pdo->lastInsertId();
        }
    }

    if (!$historyID) {
        throw new Exception("Cannot determine historyID for ClientID $clientID");
    }

    // ---------------------------
    // 2️⃣ Convert checkbox fields to 0 or 1
    // ---------------------------
    $checkboxFields = [
        'KnownIllness',
        'Hospitalization',
        'Allergies',
        'ChildImmunization',
        'PresentImmunizations',
        'CurrentMedicines',
        'DentalProblems',
        'PrimaryPhysician',
        'Allergy',
        'Asthma',
        'Tuberculosis',
        'Hypertension',
        'BloodDisease',
        'Stroke',
        'Diabetes',
        'Cancer',
        'LiverDisease',
        'KidneyBladder',
        'BloodDisorder',
        'Epilepsy',
        'MentalDisorder',
        'OtherIllness'
    ];

    foreach ($checkboxFields as $field) {
        if (isset($data[$field])) {
            $data[$field] = ($data[$field] === '1' || $data[$field] === 1 || $data[$field] === true) ? 1 : 0;
        } else {
            $data[$field] = 0;
        }
    }

    // ---------------------------
    // 3️⃣ personalinfo
    // ---------------------------
    $check = $pdo->prepare("SELECT ClientID FROM personalinfo WHERE ClientID = ?");
    $check->execute([$clientID]);

    $personalParams = [
        $data['Surname'] ?? '',
        $data['GivenName'] ?? '',
        $data['MiddleName'] ?? '',
        $data['Age'] ?? 0,
        $data['Gender'] ?? '',
        $data['DateOfBirth'] ?? '',
        $data['Status'] ?? '',
        $data['Course'] ?? '',
        $data['SchoolYearEntered'] ?? '',
        $data['CurrentAddress'] ?? '',
        $data['ContactNumber'] ?? '',
        $data['MothersName'] ?? '',
        $data['FathersName'] ?? '',
        $data['GuardiansName'] ?? '',
        $data['EmergencyContactPerson'] ?? '',
        $data['EmergencyContactName'] ?? '',
        $data['EmergencyContactRelationship'] ?? '',
        $clientID
    ];

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE personalinfo SET 
                Surname=?, GivenName=?, MiddleName=?, Age=?, Gender=?, DateOfBirth=?,
                Status=?, Course=?, SchoolYearEntered=?, CurrentAddress=?, ContactNumber=?,
                MothersName=?, FathersName=?, GuardiansName=?, EmergencyContactPerson=?,
                EmergencyContactName=?, EmergencyContactRelationship=?
            WHERE ClientID=?
        ");
        $stmt->execute($personalParams);
    } else {
        array_pop($personalParams); // remove clientID at the end
        array_unshift($personalParams, $clientID); // add clientID at start
        $stmt = $pdo->prepare("
            INSERT INTO personalinfo (
                ClientID, Surname, GivenName, MiddleName, Age, Gender, DateOfBirth,
                Status, Course, SchoolYearEntered, CurrentAddress, ContactNumber,
                MothersName, FathersName, GuardiansName, EmergencyContactPerson,
                EmergencyContactName, EmergencyContactRelationship
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($personalParams);
    }

    // ---------------------------
    // 4️⃣ familymedicalhistory
    // ---------------------------
    $check = $pdo->prepare("SELECT historyID FROM familymedicalhistory WHERE historyID = ?");
    $check->execute([$historyID]);

    $familyParams = [
        $data['Allergy'] ?? 0,
        $data['AllergyDetails'] ?? '',
        $data['Asthma'] ?? 0,
        $data['AsthmaDetails'] ?? '',
        $data['Tuberculosis'] ?? 0,
        $data['TuberculosisDetails'] ?? '',
        $data['Hypertension'] ?? 0,
        $data['HypertensionDetails'] ?? '',
        $data['BloodDisease'] ?? 0,
        $data['BloodDiseaseDetails'] ?? '',
        $data['Stroke'] ?? 0,
        $data['StrokeDetails'] ?? '',
        $data['Diabetes'] ?? 0,
        $data['DiabetesDetails'] ?? '',
        $data['Cancer'] ?? 0,
        $data['CancerDetails'] ?? '',
        $data['LiverDisease'] ?? 0,
        $data['LiverDiseaseDetails'] ?? '',
        $data['KidneyBladder'] ?? 0,
        $data['KidneyBladderDetails'] ?? '',
        $data['BloodDisorder'] ?? 0,
        $data['BloodDisorderDetails'] ?? '',
        $data['Epilepsy'] ?? 0,
        $data['EpilepsyDetails'] ?? '',
        $data['MentalDisorder'] ?? 0,
        $data['MentalDisorderDetails'] ?? '',
        $data['OtherIllness'] ?? 0,
        $data['OtherIllnessDetails'] ?? ''
    ];

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE familymedicalhistory SET
                Allergy=?, AllergyDetails=?, Asthma=?, AsthmaDetails=?, Tuberculosis=?, TuberculosisDetails=?,
                Hypertension=?, HypertensionDetails=?, BloodDisease=?, BloodDiseaseDetails=?, Stroke=?, StrokeDetails=?,
                Diabetes=?, DiabetesDetails=?, Cancer=?, CancerDetails=?, LiverDisease=?, LiverDiseaseDetails=?,
                KidneyBladder=?, KidneyBladderDetails=?, BloodDisorder=?, BloodDisorderDetails=?, Epilepsy=?, EpilepsyDetails=?,
                MentalDisorder=?, MentalDisorderDetails=?, OtherIllness=?, OtherIllnessDetails=?
            WHERE historyID=?
        ");
        $stmt->execute([...$familyParams, $historyID]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO familymedicalhistory (
                ClientID, historyID, Allergy, AllergyDetails, Asthma, AsthmaDetails,
                Tuberculosis, TuberculosisDetails, Hypertension, HypertensionDetails,
                BloodDisease, BloodDiseaseDetails, Stroke, StrokeDetails, Diabetes, DiabetesDetails,
                Cancer, CancerDetails, LiverDisease, LiverDiseaseDetails, KidneyBladder,
                KidneyBladderDetails, BloodDisorder, BloodDisorderDetails, Epilepsy, EpilepsyDetails,
                MentalDisorder, MentalDisorderDetails, OtherIllness, OtherIllnessDetails
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$clientID, $historyID, ...$familyParams]);
    }

    // ---------------------------
    // 5️⃣ medicaldentalhistory
    // ---------------------------
    $check = $pdo->prepare("SELECT historyID FROM medicaldentalhistory WHERE historyID = ?");
    $check->execute([$historyID]);

    $medicalParams = [
        $data['KnownIllness'] ?? 0,
        $data['KnownIllnessDetails'] ?? '',
        $data['Hospitalization'] ?? 0,
        $data['HospitalizationDetails'] ?? '',
        $data['Allergies'] ?? 0,
        $data['AllergiesDetails'] ?? '',
        $data['ChildImmunization'] ?? 0,
        $data['ChildImmunizationDetails'] ?? '',
        $data['PresentImmunizations'] ?? 0,
        $data['PresentImmunizationsDetails'] ?? '',
        $data['CurrentMedicines'] ?? 0,
        $data['CurrentMedicinesDetails'] ?? '',
        $data['DentalProblems'] ?? 0,
        $data['DentalProblemsDetails'] ?? '',
        $data['PrimaryPhysician'] ?? 0,
        $data['PrimaryPhysicianDetails'] ?? ''
    ];

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE medicaldentalhistory SET
                KnownIllness=?, KnownIllnessDetails=?, Hospitalization=?, HospitalizationDetails=?,
                Allergies=?, AllergiesDetails=?, ChildImmunization=?, ChildImmunizationDetails=?,
                PresentImmunizations=?, PresentImmunizationsDetails=?, CurrentMedicines=?, CurrentMedicinesDetails=?,
                DentalProblems=?, DentalProblemsDetails=?, PrimaryPhysician=?, PrimaryPhysicianDetails=?
            WHERE historyID=?
        ");
        $stmt->execute([...$medicalParams, $historyID]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO medicaldentalhistory (
                ClientID, historyID, KnownIllness, KnownIllnessDetails, Hospitalization, HospitalizationDetails,
                Allergies, AllergiesDetails, ChildImmunization, ChildImmunizationDetails,
                PresentImmunizations, PresentImmunizationsDetails, CurrentMedicines, CurrentMedicinesDetails,
                DentalProblems, DentalProblemsDetails, PrimaryPhysician, PrimaryPhysicianDetails
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$clientID, $historyID, ...$medicalParams]);
    }

    // ---------------------------
    // 6️⃣ personalsocialhistory
    // ---------------------------
    $check = $pdo->prepare("SELECT historyID FROM personalsocialhistory WHERE historyID = ?");
    $check->execute([$historyID]);

    $socialParams = [
        $data['AlcoholIntake'] ?? 'no',
        $data['AlcoholDetails'] ?? '',
        $data['TobaccoUse'] ?? 'no',
        $data['TobaccoDetails'] ?? '',
        $data['DrugUse'] ?? 'no',
        $data['DrugDetails'] ?? ''
    ];

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("
            UPDATE personalsocialhistory SET
                AlcoholIntake=?, AlcoholDetails=?, TobaccoUse=?, TobaccoDetails=?, DrugUse=?, DrugDetails=?
            WHERE historyID=?
        ");
        $stmt->execute([...$socialParams, $historyID]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO personalsocialhistory (
                ClientID, historyID, AlcoholIntake, AlcoholDetails, TobaccoUse, TobaccoDetails, DrugUse, DrugDetails
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$clientID, $historyID, ...$socialParams]);
    }

    // ---------------------------
    // 7️⃣ femalehealthhistory (if female)
    // ---------------------------
    if (!empty($data['Gender']) && strtolower($data['Gender'])[0] === 'f') {
        $check = $pdo->prepare("SELECT historyID FROM femalehealthhistory WHERE historyID = ?");
        $check->execute([$historyID]);

        $femaleParams = [
            $data['LastPeriod'] ?? '',
            $data['Regularity'] ?? '',
            $data['Duration'] ?? '',
            $data['PadsPerDay'] ?? 0,
            $data['Dysmenorrhea'] ?? 'no',
            $data['DysmenorrheaSeverity'] ?? '',
            $data['LastOBVisit'] ?? '',
            $data['AbnormalBleeding'] ?? 'no',
            $data['PreviousPregnancy'] ?? 'no',
            $data['PregnancyDetails'] ?? '',
            $data['HasChildren'] ?? 'no',
            $data['ChildrenCount'] ?? 0
        ];

        if ($check->rowCount() > 0) {
            $stmt = $pdo->prepare("
                UPDATE femalehealthhistory SET
                    LastPeriod=?, Regularity=?, Duration=?, PadsPerDay=?, Dysmenorrhea=?, DysmenorrheaSeverity=?,
                    LastOBVisit=?, AbnormalBleeding=?, PreviousPregnancy=?, PregnancyDetails=?, HasChildren=?, ChildrenCount=?
                WHERE historyID=?
            ");
            $stmt->execute([...$femaleParams, $historyID]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO femalehealthhistory (
                    ClientID, historyID, LastPeriod, Regularity, Duration, PadsPerDay, Dysmenorrhea, DysmenorrheaSeverity,
                    LastOBVisit, AbnormalBleeding, PreviousPregnancy, PregnancyDetails, HasChildren, ChildrenCount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$clientID, $historyID, ...$femaleParams]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "All data saved successfully.",
        "clientID" => $clientID,
        "historyID" => $historyID
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log("Form submission error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error saving data: " . $e->getMessage()
    ]);
}
