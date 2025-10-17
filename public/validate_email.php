<?php
require '../config/database.php';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Validate format first
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["valid" => false, "message" => "Invalid email format."]);
        exit;
    }

    // Check database
    $pdo = pdo_connect_mysql();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Clients WHERE Email = ?");
    $stmt->execute([$email]);
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        echo json_encode(["valid" => false, "message" => "This email is already registered."]);
    } else {
        echo json_encode(["valid" => true, "message" => "Email is available."]);
    }
}
