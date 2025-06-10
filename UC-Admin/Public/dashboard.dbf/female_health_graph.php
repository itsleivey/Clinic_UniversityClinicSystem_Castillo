<?php
// female_health_graph.php
require_once '../config/database.php';
$conn = pdo_connect_mysql();

$categories = [
    'Regularity'         => 'Regularity',
    'Dysmenorrhea'       => 'Dysmenorrhea',
    'Abnormal Bleeding'  => 'AbnormalBleeding',
    'Previous Pregnancy' => 'PreviousPregnancy'
];

$data = [];
foreach ($categories as $label => $column) {
    // initialize
    $data[$label] = ['yes' => 0, 'no' => 0, 'regular' => 0, 'irregular' => 0, 'mild' => 0, 'moderate' => 0, 'severe' => 0];
    // get counts for each column
    $stmt = $conn->prepare("
        SELECT `$column` AS val, COUNT(*) AS cnt
        FROM femalehealthhistory
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