<?php
require '../config/database.php';
$pdo = pdo_connect_mysql();

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || !isset($data['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    date_default_timezone_set('Asia/Manila');

    $client_id = $data['client_id'];
    $actionDate = date('Y-m-d');
    $actionTime12hr = date('h:i:s A');
    $date_issued = date('Y-m-d');

    // Insert into history table
    $insertHistory = $pdo->prepare("
        INSERT INTO history (ClientID, actionDate, actionTime, progress) 
        VALUES (?, ?, ?, 'completed')
    ");
    $insertHistory->execute([$client_id, $actionDate, $actionTime12hr]);
    $historyID = $pdo->lastInsertId();

    // Insert into prescriptions table
    $stmt = $pdo->prepare("INSERT INTO prescriptions 
        (ClientID, historyID, patient_name, age, impression, physician, license_no, notes, date_created)
        VALUES (:client_id, :history_id, :patient_name, :age, :impression, :physician, :license_no, :notes, :date_created)");

    $stmt->execute([
        ':client_id' => $client_id,
        ':history_id' => $historyID,
        ':patient_name' => $data['patient_name'],
        ':age' => $data['age'],
        ':impression' => $data['impression'],
        ':physician' => $data['physician'],
        ':license_no' => $data['license_no'],
        ':notes' => $data['notes'],
        ':date_created' => $data['date_created']
    ]);

    $remarks = "Medical certificate issued on $date_issued";
    $stmt2 = $pdo->prepare("INSERT INTO consultations (client_id, historyID, consultation_date, certificate_issued, remarks) 
                            VALUES (?, ?, CURDATE(), TRUE, ?)");
    $stmt2->execute([$client_id, $historyID, $remarks]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
