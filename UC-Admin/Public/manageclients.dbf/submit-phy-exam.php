<?php
session_start();
include 'config/database.php';

$pdo = pdo_connect_mysql();

header('Content-Type: application/json');

if (!isset($_POST['client_id']) || empty($_POST['client_id'])) {
    echo json_encode(["status" => "error", "message" => "Error: No client selected"]);
    exit();
}

$fields = [
    'height',
    'weight',
    'bmi',
    'bp',
    'hr',
    'rr',
    'temp',
    'skin_normal',
    'skin_findings',
    'head_normal',
    'head_findings',
    'chest_normal',
    'chest_findings',
    'abdomen_normal',
    'abdomen_findings',
    'extremities_normal',
    'extremities_findings',
    'others_normal',
    'others_findings'
];

$data = ['client_id' => $_POST['client_id']];
foreach ($fields as $field) {
    $data[$field] = $_POST[$field] ?? '';
}

try {
    // Get or create history ID
    $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' Order By historyID Desc LIMIT 1");
    $getHistory->execute([$data['client_id']]);
    $historyID = $getHistory->fetchColumn();

    if (!$historyID) {
        $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
        $insertHistory->execute([$data['client_id']]);
        $historyID = $pdo->lastInsertId();
    }

    // Check if physical examination already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM physicalexamination WHERE historyID = ?");
    $checkStmt->execute([$historyID]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        $sql = "UPDATE physicalexamination SET 
            Height = ?, Weight = ?, BMI = ?, BP = ?, HR = ?, RR = ?, Temp = ?,
            GenAppearanceAndSkinNormal = ?, GenAppearanceAndSkinFindings = ?,
            HeadAndNeckNormal = ?, HeadAndNeckFindings = ?,
            ChestAndBackNormal = ?, ChestAndBackFindings = ?,
            AbdomenNormal = ?, AbdomenFindings = ?,
            ExtremitiesNormal = ?, ExtremitiesFindings = ?,
            OthersNormal = ?, OthersFindings = ? 
            WHERE historyID = ?";

        $params = [
            $data['height'],
            $data['weight'],
            $data['bmi'],
            $data['bp'],
            $data['hr'],
            $data['rr'],
            $data['temp'],
            $data['skin_normal'],
            $data['skin_findings'],
            $data['head_normal'],
            $data['head_findings'],
            $data['chest_normal'],
            $data['chest_findings'],
            $data['abdomen_normal'],
            $data['abdomen_findings'],
            $data['extremities_normal'],
            $data['extremities_findings'],
            $data['others_normal'],
            $data['others_findings'],
            $historyID
        ];
    } else {
        $sql = "INSERT INTO physicalexamination (
            ClientID, historyID, Height, Weight, BMI, BP, HR, RR, Temp,
            GenAppearanceAndSkinNormal, GenAppearanceAndSkinFindings,
            HeadAndNeckNormal, HeadAndNeckFindings,
            ChestAndBackNormal, ChestAndBackFindings,
            AbdomenNormal, AbdomenFindings,
            ExtremitiesNormal, ExtremitiesFindings,
            OthersNormal, OthersFindings
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['client_id'],
            $historyID,
            $data['height'],
            $data['weight'],
            $data['bmi'],
            $data['bp'],
            $data['hr'],
            $data['rr'],
            $data['temp'],
            $data['skin_normal'],
            $data['skin_findings'],
            $data['head_normal'],
            $data['head_findings'],
            $data['chest_normal'],
            $data['chest_findings'],
            $data['abdomen_normal'],
            $data['abdomen_findings'],
            $data['extremities_normal'],
            $data['extremities_findings'],
            $data['others_normal'],
            $data['others_findings']
        ];
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        echo json_encode(["status" => "success", "message" => "Physical examination saved successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error saving data."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
