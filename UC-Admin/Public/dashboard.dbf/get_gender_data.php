<?php
require 'config/database.php'; // includes pdo_connect_mysql()

$conn = pdo_connect_mysql();

// Prepare and execute queries for each gender
$stmt_male = $conn->query("SELECT COUNT(*) AS count FROM personalinfo WHERE Gender = 'male'");
$stmt_female = $conn->query("SELECT COUNT(*) AS count FROM personalinfo WHERE Gender = 'female'");

// Fetch the results
$data = [
    'male' => $stmt_male->fetch(PDO::FETCH_ASSOC)['count'],
    'female' => $stmt_female->fetch(PDO::FETCH_ASSOC)['count']
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
