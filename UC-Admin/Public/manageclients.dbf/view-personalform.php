<?php
require_once 'config/database.php';

$pdo = pdo_connect_mysql();

$clientid = null;
$personalInfo = null;

if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    // Fetch client info
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);
    $clientid = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch personal info
    $stmt2 = $pdo->prepare("SELECT * FROM personalinfo WHERE ClientID = ?");
    $stmt2->execute([$clientID]);
    $personalInfo = $stmt2->fetch(PDO::FETCH_ASSOC);
}
