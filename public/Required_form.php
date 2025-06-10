<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId = $_SESSION['ClientID'];
    $clientType = $_POST['clientType'] ?? '';
    $department = $_POST['department'] ?? '';
    $course = isset($_POST['course']) && $_POST['course'] !== '' ? $_POST['course'] : null;

    try {
        $stmtClients = $pdo->prepare("UPDATE Clients SET ClientType = ?, Department = ?, Course = ? WHERE ClientID = ?");
        $clientsUpdated = $stmtClients->execute([$clientType, $department, $course, $clientId]);

        if ($clientsUpdated) {
            $_SESSION['profile_completed'] = true;
            die(json_encode(['success' => true]));
        } else {
            throw new Exception("Update failed.");
        }
    } catch (Exception $e) {
        error_log("Update error: " . $e->getMessage());
        $_SESSION['profile_update_error'] = "Failed to update profile. Please try again.";
        header('Location: Profile.php');
        exit();
    }
}

header('Location: Profile.php');
exit();
