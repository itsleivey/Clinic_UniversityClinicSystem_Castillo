<?php
// Profile_db.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

$pdo = pdo_connect_mysql();

function getUserDataFromDatabase(PDO $pdo): ?array
{
    if (!isset($_SESSION['ClientID'])) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT 
            Surname AS surname,
            GivenName AS given_name,
            MiddleName AS middle_name,
            Age AS age,
            Gender AS gender,
            DateOfBirth AS dob,
            Status AS status,
            Course AS course,
            SchoolYearEntered AS school_year_entered,
            CurrentAddress AS current_address,
            ContactNumber AS contact_number,
            MothersName AS mothers_name,
            FathersName AS fathers_name,
            GuardiansName AS guardians_name,
            EmergencyContactName AS emergency_contact_name,
            EmergencyContactRelationship AS emergency_contact_relationship
            FROM PersonalInfo 
            WHERE ClientID = ? 
            LIMIT 1");

        $stmt->execute([$_SESSION['ClientID']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

function getClientHistoryData(PDO $pdo): array
{
    if (!isset($_SESSION['ClientID'])) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("SELECT 
            historyID,
            actionDate,
            actionTime,
            progress
            FROM history
            WHERE ClientID = ?
            ORDER BY actionDate DESC, actionTime DESC");

        $stmt->execute([$_SESSION['ClientID']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error (history): " . $e->getMessage());
        return [];
    }
}
