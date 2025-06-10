<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function getUserDataFromDatabase(): ?array
{
    $pdo = pdo_connect_mysql();
    
    if (!isset($_SESSION['ClientID'])) {
        error_log("No ClientID in session");
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
        
        if (!$stmt->rowCount()) {
            error_log("No data found for ClientID: " . $_SESSION['ClientID']);
            return null;
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}