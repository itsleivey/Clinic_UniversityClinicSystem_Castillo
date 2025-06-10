<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Debug: Log received data
file_put_contents('form_debug.log', print_r($_POST, true), FILE_APPEND);

// Verify client_id exists
if (empty($_POST['client_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Client ID is required',
        'missing_fields' => ['client_id'],
        'received_data' => $_POST // For debugging
    ]);
    exit;
}

function insertMedicalForm($formData) {
    $pdo = pdo_connect_mysql();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO newpersonnel_form (
                client_id, blood_test, urinalysis, chest_xray, drug_test, psych_test, neuro_test,
                full_name, agency_address, address, age, sex, civil_status, proposed_position,
                height, weight, blood_type, created_at
            ) VALUES (
                :client_id, :blood_test, :urinalysis, :chest_xray, :drug_test, :psych_test, :neuro_test,
                :full_name, :agency_address, :address, :age, :sex, :civil_status, :proposed_position,
                :height, :weight, :blood_type, NOW()
            )
        ");

        // Checkbox conversion helper
        $cb = function($key) use ($formData) {
            return isset($formData[$key]) && ($formData[$key] === '1' || $formData[$key] === 'on') ? 1 : 0;
        };

        $stmt->execute([
            ':client_id' => $formData['client_id'] ?? 0,
            ':blood_test' => $cb('blood_test'),
            ':urinalysis' => $cb('urinalysis'),
            ':chest_xray' => $cb('chest_xray'),
            ':drug_test' => $cb('drug_test'),
            ':psych_test' => $cb('psych_test'),
            ':neuro_test' => $cb('neuro_test'),
            ':full_name' => $formData['name'] ?? '',
            ':agency_address' => $formData['agency'] ?? '',
            ':address' => $formData['address'] ?? '',
            ':age' => $formData['age'] ?? 0,
            ':sex' => $formData['sex'] ?? '',
            ':civil_status' => $formData['civil-status'] ?? '',
            ':proposed_position' => $formData['position'] ?? '',
            ':height' => $formData['height'] ?? '',
            ':weight' => $formData['weight'] ?? '',
            ':blood_type' => $formData['blood-type'] ?? ''
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Output error message in JSON so the frontend can see it
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit; // Stop execution immediately
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Required fields to validate
    $required = ['name', 'agency', 'address', 'age', 'sex', 'civil-status', 'position', 'height', 'weight', 'blood-type', 'client_id'];
    $missing = [];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        echo json_encode([
            'success' => false, 
            'message' => "Missing required fields: " . implode(', ', $missing),
            'missing_fields' => $missing
        ]);
        exit;
    }

    $insertId = insertMedicalForm($_POST);

    if ($insertId !== false) {
        echo json_encode([
            'success' => true, 
            'message' => 'Medical form submitted successfully',
            'insert_id' => $insertId
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to submit medical form. Please try again.'
        ]);
    }
    exit;
}
?>
