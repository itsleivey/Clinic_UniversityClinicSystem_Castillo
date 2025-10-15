<?php
require_once('../config/database.php');
$pdo = pdo_connect_mysql();

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    $stmt = $pdo->prepare("SELECT ClientID FROM clients WHERE Email = ?");
    $stmt->execute([$email]);
    
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
}
?>