<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Set proper content type
header('Content-Type: application/json');

try {
    // Get the raw POST data and decode JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data)) {
        throw new Exception("No data received.");
    }

    // Get database connection
    $pdo = pdo_connect_mysql();

    // Start transaction
    $pdo->beginTransaction();

    // Set default values for checkboxes
    $clientID = $data['ClientID'] ?? 12; // Use provided ClientID or default
    $historyID = $data['historyID'] ?? null;

    // ✅ FIXED: Get or create history record (no more progress column)
    if (!$historyID) {
        $getHistory = $pdo->prepare("
            SELECT historyID 
            FROM history 
            WHERE ClientID = ? 
            ORDER BY historyID DESC 
            LIMIT 1
        ");
        $getHistory->execute([$clientID]);
        $history = $getHistory->fetch(PDO::FETCH_ASSOC);

        if (!$history) {
            // No existing history record — create one automatically
            $insert = $pdo->prepare("
                INSERT INTO history (ClientID, actionDate, actionTime)
                VALUES (?, CURDATE(), CURTIME())
            ");
            $insert->execute([$clientID]);
            $historyID = $pdo->lastInsertId();
        } else {
            $historyID = $history['historyID'];
        }
    }

    // Convert checkbox values to integers (0 or 1)
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
        $data[$field] = isset($data[$field]) && $data[$field] ? 1 : 0;
    }

    /* ---------------------------
       1️⃣ Insert/Update personalinfo
    ----------------------------*/
    $stmt1 = $pdo->prepare("
        INSERT INTO personalinfo (
            ClientID, Surname, GivenName, MiddleName, Age, Gender, DateOfBirth,
            Status, Course, SchoolYearEntered, CurrentAddress, ContactNumber,
            MothersName, FathersName, GuardiansName, EmergencyContactPerson,
            EmergencyContactName, EmergencyContactRelationship
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            Surname = VALUES(Surname),
            GivenName = VALUES(GivenName),
            MiddleName = VALUES(MiddleName),
            Age = VALUES(Age),
            Gender = VALUES(Gender),
            DateOfBirth = VALUES(DateOfBirth),
            Status = VALUES(Status),
            Course = VALUES(Course),
            SchoolYearEntered = VALUES(SchoolYearEntered),
            CurrentAddress = VALUES(CurrentAddress),
            ContactNumber = VALUES(ContactNumber),
            MothersName = VALUES(MothersName),
            FathersName = VALUES(FathersName),
            GuardiansName = VALUES(GuardiansName),
            EmergencyContactPerson = VALUES(EmergencyContactPerson),
            EmergencyContactName = VALUES(EmergencyContactName),
            EmergencyContactRelationship = VALUES(EmergencyContactRelationship)
    ");

    $stmt1->execute([
        $clientID,
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
        $data['EmergencyGuardiansName'] ?? '', // Note: field name difference
        $data['EmergencyContactName'] ?? '',
        $data['EmergencyContactRelationship'] ?? ''
    ]);

    /* ------------------------------------
       2️⃣ Insert/Update familymedicalhistory
    -------------------------------------*/
    $stmt2 = $pdo->prepare("
        INSERT INTO familymedicalhistory (
            ClientID, historyID,
            Allergy, AllergyDetails, Asthma, AsthmaDetails, Tuberculosis, TuberculosisDetails,
            Hypertension, HypertensionDetails, BloodDisease, BloodDiseaseDetails,
            Stroke, StrokeDetails, Diabetes, DiabetesDetails, Cancer, CancerDetails,
            LiverDisease, LiverDiseaseDetails, KidneyBladder, KidneyBladderDetails,
            BloodDisorder, BloodDisorderDetails, Epilepsy, EpilepsyDetails,
            MentalDisorder, MentalDisorderDetails, OtherIllness, OtherIllnessDetails
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            Allergy = VALUES(Allergy),
            AllergyDetails = VALUES(AllergyDetails),
            Asthma = VALUES(Asthma),
            AsthmaDetails = VALUES(AsthmaDetails),
            Tuberculosis = VALUES(Tuberculosis),
            TuberculosisDetails = VALUES(TuberculosisDetails),
            Hypertension = VALUES(Hypertension),
            HypertensionDetails = VALUES(HypertensionDetails),
            BloodDisease = VALUES(BloodDisease),
            BloodDiseaseDetails = VALUES(BloodDiseaseDetails),
            Stroke = VALUES(Stroke),
            StrokeDetails = VALUES(StrokeDetails),
            Diabetes = VALUES(Diabetes),
            DiabetesDetails = VALUES(DiabetesDetails),
            Cancer = VALUES(Cancer),
            CancerDetails = VALUES(CancerDetails),
            LiverDisease = VALUES(LiverDisease),
            LiverDiseaseDetails = VALUES(LiverDiseaseDetails),
            KidneyBladder = VALUES(KidneyBladder),
            KidneyBladderDetails = VALUES(KidneyBladderDetails),
            BloodDisorder = VALUES(BloodDisorder),
            BloodDisorderDetails = VALUES(BloodDisorderDetails),
            Epilepsy = VALUES(Epilepsy),
            EpilepsyDetails = VALUES(EpilepsyDetails),
            MentalDisorder = VALUES(MentalDisorder),
            MentalDisorderDetails = VALUES(MentalDisorderDetails),
            OtherIllness = VALUES(OtherIllness),
            OtherIllnessDetails = VALUES(OtherIllnessDetails)
    ");

    $stmt2->execute([
        $clientID,
        $historyID,
        $data['allergy'] ?? 0,
        $data['allergyDetails'] ?? '',
        $data['asthma'] ?? 0,
        $data['asthmaDetails'] ?? '',
        $data['tuberculosis'] ?? 0,
        $data['tuberculosisDetails'] ?? '',
        $data['hypertension'] ?? 0,
        $data['hypertensionDetails'] ?? '',
        $data['bloodDisease'] ?? 0,
        $data['bloodDiseaseDetails'] ?? '',
        $data['stroke'] ?? 0,
        $data['strokeDetails'] ?? '',
        $data['diabetes'] ?? 0,
        $data['diabetesDetails'] ?? '',
        $data['cancer'] ?? 0,
        $data['cancerDetails'] ?? '',
        $data['liverDisease'] ?? 0,
        $data['liverDiseaseDetails'] ?? '',
        $data['kidneyBladder'] ?? 0,
        $data['kidneyBladderDetails'] ?? '',
        $data['bloodDisorder'] ?? 0,
        $data['bloodDisorderDetails'] ?? '',
        $data['epilepsy'] ?? 0,
        $data['epilepsyDetails'] ?? '',
        $data['mentalDisorder'] ?? 0,
        $data['mentalDisorderDetails'] ?? '',
        $data['otherIllness'] ?? 0,
        $data['otherIllnessDetails'] ?? ''
    ]);

    /* -------------------------------------
       3️⃣ Insert/Update medicaldentalhistory
    --------------------------------------*/
    $stmt3 = $pdo->prepare("
        INSERT INTO medicaldentalhistory (
            ClientID, historyID, KnownIllness, KnownIllnessDetails, Hospitalization, HospitalizationDetails,
            Allergies, AllergiesDetails, ChildImmunization, ChildImmunizationDetails,
            PresentImmunizations, PresentImmunizationsDetails, CurrentMedicines, CurrentMedicinesDetails,
            DentalProblems, DentalProblemsDetails, PrimaryPhysician, PrimaryPhysicianDetails
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            KnownIllness = VALUES(KnownIllness),
            KnownIllnessDetails = VALUES(KnownIllnessDetails),
            Hospitalization = VALUES(Hospitalization),
            HospitalizationDetails = VALUES(HospitalizationDetails),
            Allergies = VALUES(Allergies),
            AllergiesDetails = VALUES(AllergiesDetails),
            ChildImmunization = VALUES(ChildImmunization),
            ChildImmunizationDetails = VALUES(ChildImmunizationDetails),
            PresentImmunizations = VALUES(PresentImmunizations),
            PresentImmunizationsDetails = VALUES(PresentImmunizationsDetails),
            CurrentMedicines = VALUES(CurrentMedicines),
            CurrentMedicinesDetails = VALUES(CurrentMedicinesDetails),
            DentalProblems = VALUES(DentalProblems),
            DentalProblemsDetails = VALUES(DentalProblemsDetails),
            PrimaryPhysician = VALUES(PrimaryPhysician),
            PrimaryPhysicianDetails = VALUES(PrimaryPhysicianDetails)
    ");

    $stmt3->execute([
        $clientID,
        $historyID,
        $data['knownIllness'] ?? 0,
        $data['knownIllnessDetails'] ?? '',
        $data['hospitalization'] ?? 0,
        $data['hospitalizationDetails'] ?? '',
        $data['allergies'] ?? 0,
        $data['allergiesDetails'] ?? '',
        $data['childImmunization'] ?? 0,
        $data['childImmunizationDetails'] ?? '',
        $data['presentImmunizations'] ?? 0,
        $data['presentImmunizationsDetails'] ?? '',
        $data['currentMedicines'] ?? 0,
        $data['currentMedicinesDetails'] ?? '',
        $data['dentalProblems'] ?? 0,
        $data['dentalProblemsDetails'] ?? '',
        $data['primaryPhysician'] ?? 0,
        $data['primaryPhysicianDetails'] ?? ''
    ]);

    /* -----------------------------------
       4️⃣ Insert/Update personalsocialhistory
    ------------------------------------*/
    $stmt4 = $pdo->prepare("
        INSERT INTO personalsocialhistory (
            ClientID, historyID, AlcoholIntake, AlcoholDetails,
            TobaccoUse, TobaccoDetails, DrugUse, DrugDetails
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            AlcoholIntake = VALUES(AlcoholIntake),
            AlcoholDetails = VALUES(AlcoholDetails),
            TobaccoUse = VALUES(TobaccoUse),
            TobaccoDetails = VALUES(TobaccoDetails),
            DrugUse = VALUES(DrugUse),
            DrugDetails = VALUES(DrugDetails)
    ");

    $stmt4->execute([
        $clientID,
        $historyID,
        $data['alcoholIntake'] ?? 'no',
        $data['alcoholDetails'] ?? '',
        $data['tobaccoUse'] ?? 'no',
        $data['tobaccoDetails'] ?? '',
        $data['drugUse'] ?? 'no',
        $data['drugDetails'] ?? ''
    ]);

    /* --------------------------------
       5️⃣ Insert/Update femalehealthhistory (if female)
    ---------------------------------*/
    if (isset($data['Gender']) && strtolower($data['Gender']) === 'female') {
        $stmt5 = $pdo->prepare("
            INSERT INTO femalehealthhistory (
                ClientID, historyID, LastPeriod, Regularity, Duration,
                PadsPerDay, Dysmenorrhea, DysmenorrheaSeverity, LastOBVisit,
                AbnormalBleeding, PreviousPregnancy, PregnancyDetails,
                HasChildren, ChildrenCount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                LastPeriod = VALUES(LastPeriod),
                Regularity = VALUES(Regularity),
                Duration = VALUES(Duration),
                PadsPerDay = VALUES(PadsPerDay),
                Dysmenorrhea = VALUES(Dysmenorrhea),
                DysmenorrheaSeverity = VALUES(DysmenorrheaSeverity),
                LastOBVisit = VALUES(LastOBVisit),
                AbnormalBleeding = VALUES(AbnormalBleeding),
                PreviousPregnancy = VALUES(PreviousPregnancy),
                PregnancyDetails = VALUES(PregnancyDetails),
                HasChildren = VALUES(HasChildren),
                ChildrenCount = VALUES(ChildrenCount)
        ");

        $stmt5->execute([
            $clientID,
            $historyID,
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
        ]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "All data saved successfully.",
        "clientID" => $clientID,
        "historyID" => $historyID
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Form submission error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Error saving data: " . $e->getMessage()
    ]);
}
