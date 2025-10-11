<?php
session_start();

if (!isset($_SESSION['ClientID'])) {
    return null;
}

require_once '../../config/database.php';

$pdo = pdo_connect_mysql();

// Profile_db.php
function getMedicalDentalHistory(PDO $pdo, int $clientId): array
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM medicaldentalhistory WHERE ClientID = ? LIMIT 1");
        $stmt->execute([$clientId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [
                'KnownIllness' => 0,
                'KnownIllnessDetails' => '',
                'Hospitalization' => 0,
                'HospitalizationDetails' => '',
                'Allergies' => 0,
                'AllergiesDetails' => '',
                'ChildImmunization' => 0,
                'ChildImmunizationDetails' => '',
                'PresentImmunizations' => 0,
                'PresentImmunizationsDetails' => '',
                'CurrentMedicines' => 0,
                'CurrentMedicinesDetails' => '',
                'DentalProblems' => 0,
                'DentalProblemsDetails' => '',
                'PrimaryPhysician' => 0,
                'PrimaryPhysicianDetails' => ''
            ];
        }
        return $result;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function saveMedicalDentalHistory(PDO $pdo, array $data): bool
{
    try {
        // Check if record exists
        $stmt = $pdo->prepare("SELECT 1 FROM medicaldentalhistory WHERE ClientID = ?");
        $stmt->execute([$data['ClientID']]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE medicaldentalhistory SET
                KnownIllness = ?, KnownIllnessDetails = ?,
                Hospitalization = ?, HospitalizationDetails = ?,
                Allergies = ?, AllergiesDetails = ?,
                ChildImmunization = ?, ChildImmunizationDetails = ?,
                PresentImmunizations = ?, PresentImmunizationsDetails = ?,
                CurrentMedicines = ?, CurrentMedicinesDetails = ?,
                DentalProblems = ?, DentalProblemsDetails = ?,
                PrimaryPhysician = ?, PrimaryPhysicianDetails = ?
                WHERE ClientID = ?");

            return $stmt->execute([
                $data['KnownIllness'],
                $data['KnownIllnessDetails'],
                $data['Hospitalization'],
                $data['HospitalizationDetails'],
                $data['Allergies'],
                $data['AllergiesDetails'],
                $data['ChildImmunization'],
                $data['ChildImmunizationDetails'],
                $data['PresentImmunizations'],
                $data['PresentImmunizationsDetails'],
                $data['CurrentMedicines'],
                $data['CurrentMedicinesDetails'],
                $data['DentalProblems'],
                $data['DentalProblemsDetails'],
                $data['PrimaryPhysician'],
                $data['PrimaryPhysicianDetails'],
                $data['ClientID']
            ]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO medicaldentalhistory (
                ClientID, KnownIllness, KnownIllnessDetails,
                Hospitalization, HospitalizationDetails,
                Allergies, AllergiesDetails,
                ChildImmunization, ChildImmunizationDetails,
                PresentImmunizations, PresentImmunizationsDetails,
                CurrentMedicines, CurrentMedicinesDetails,
                DentalProblems, DentalProblemsDetails,
                PrimaryPhysician, PrimaryPhysicianDetails
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            return $stmt->execute([
                $data['ClientID'],
                $data['KnownIllness'],
                $data['KnownIllnessDetails'],
                $data['Hospitalization'],
                $data['HospitalizationDetails'],
                $data['Allergies'],
                $data['AllergiesDetails'],
                $data['ChildImmunization'],
                $data['ChildImmunizationDetails'],
                $data['PresentImmunizations'],
                $data['PresentImmunizationsDetails'],
                $data['CurrentMedicines'],
                $data['CurrentMedicinesDetails'],
                $data['DentalProblems'],
                $data['DentalProblemsDetails'],
                $data['PrimaryPhysician'],
                $data['PrimaryPhysicianDetails']
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
