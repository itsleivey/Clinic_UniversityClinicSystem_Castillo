<?php
require_once('../config/database.php');
$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $client_type = $_POST['client_type'];
    $department = isset($_POST['department']) ? $_POST['department'] : null;

    // Split full name into first and last
    $parts = explode(" ", $fullname, 2);
    $firstname = $parts[0];
    $lastname = isset($parts[1]) ? $parts[1] : '';

    $stmt = $pdo->prepare("INSERT INTO clients (Firstname, Lastname, Email, Password, ClientType, Department) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $email, $password, $client_type, $department]);

    header("Location: ../Manage_Clients.php"); // redirect to your page
    exit();
}
?>
