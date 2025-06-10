<?php
require 'config/database.php';
$conn = pdo_connect_mysql();

$categories = [
    'Alcohol Intake' => 'AlcoholIntake',
    'Tobacco Use'    => 'TobaccoUse',
    'Drug Use'       => 'DrugUse'
];

$data = [];
foreach ($categories as $label => $column) {
    // initialize
    $data[$label] = ['yes' => 0, 'no' => 0, 'former' => 0];
    // get counts
    $stmt = $conn->prepare("
        SELECT `$column` AS val, COUNT(*) AS cnt
        FROM personalsocialhistory
        GROUP BY `$column`
    ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$label][$row['val']] = (int)$row['cnt'];
    }
}

// output JSON
header('Content-Type: application/json');
echo json_encode($data);
