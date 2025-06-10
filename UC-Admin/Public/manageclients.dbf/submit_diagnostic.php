<?php
require '../config/database.php';
header('Content-Type: application/json');

$pdo = pdo_connect_mysql();

function getCheckboxValue($name)
{
    return isset($_POST[$name]) ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;

    $getHistory = $pdo->prepare("SELECT historyID FROM history WHERE ClientID = ? AND progress = 'inprogress' Order By historyID Desc LIMIT 1");
    $getHistory->execute([$client_id]);
    $historyID = $getHistory->fetchColumn();

    if (!$historyID) {
        $insertHistory = $pdo->prepare("INSERT INTO history (ClientID, actionDate, progress) VALUES (?, NOW(), 'inprogress')");
        $insertHistory->execute([$client_id]);
        $historyID = $pdo->lastInsertId();
    }

    $history_id = $historyID;

    $exam_date           = $_POST['exam_date'] ?? null;
    $chest_xray          = getCheckboxValue('chest_xray');
    $xray_findings       = htmlspecialchars($_POST['xray_findings'] ?? '');
    $impression          = htmlspecialchars($_POST['impression'] ?? '');
    $discussions         = getCheckboxValue('discussions');
    $discussion_details  = htmlspecialchars($_POST['discussion_details'] ?? '');
    $home_medication     = getCheckboxValue('home_medication');
    $medication_details  = htmlspecialchars($_POST['medication_details'] ?? '');
    $home_instructions   = getCheckboxValue('home_instructions');
    $instruction_details = htmlspecialchars($_POST['instruction_details'] ?? '');
    $abbreviations       = htmlspecialchars($_POST['abbreviations'] ?? '');
    $f1_date             = $_POST['f1_date'] ?? null;
    $med_cert_issued     = getCheckboxValue('med_cert_issued');
    $referred_to         = htmlspecialchars($_POST['referred_to'] ?? '');
    $recommendation      = $_POST['recommendation'] ?? '';
    $physician_name      = htmlspecialchars($_POST['physician_name'] ?? '');
    $license_no          = htmlspecialchars($_POST['license_no'] ?? '');
    $signature_date      = $_POST['signature_date'] ?? null;
    $institution         = htmlspecialchars($_POST['institution'] ?? '');

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM diagnosticresults WHERE historyID = ?");
    $stmt_check->execute([$history_id]);
    $recordExists = $stmt_check->fetchColumn() > 0;

    $updateHistoryCompleted = $pdo->prepare("UPDATE history SET progress = 'completed' WHERE historyID = ?");
    $updateHistoryCompleted->execute([$history_id]);

    try {
        if ($recordExists) {
            $stmt = $pdo->prepare("UPDATE diagnosticresults SET 
                ExamDate = :exam_date,
                ChestXrayPerformed = :chest_xray,
                XrayFindings = :xray_findings,
                Impression = :impression,
                Discussions = :discussions,
                DiscussionDetails = :discussion_details,
                HomeMedication = :home_medication,
                MedicationDetails = :medication_details,
                HomeInstructions = :home_instructions,
                InstructionDetails = :instruction_details,
                AbbreviationsUsed = :abbreviations,
                F1Date = :f1_date,
                MedicalCertIssued = :med_cert_issued,
                ReferredTo = :referred_to,
                Recommendation = :recommendation,
                PhysicianName = :physician_name,
                LicenseNo = :license_no,
                SignatureDate = :signature_date,
                Institution = :institution,
                ClientID = :client_id
                WHERE historyID = :history_id
            ");
        } else {
            $stmt = $pdo->prepare("INSERT INTO diagnosticresults (
                ClientID, historyID, ExamDate, ChestXrayPerformed, XrayFindings, Impression,
                Discussions, DiscussionDetails, HomeMedication, MedicationDetails,
                HomeInstructions, InstructionDetails, AbbreviationsUsed, F1Date,
                MedicalCertIssued, ReferredTo, Recommendation, PhysicianName,
                LicenseNo, SignatureDate, Institution
            ) VALUES (
                :client_id, :history_id, :exam_date, :chest_xray, :xray_findings, :impression,
                :discussions, :discussion_details, :home_medication, :medication_details,
                :home_instructions, :instruction_details, :abbreviations, :f1_date,
                :med_cert_issued, :referred_to, :recommendation, :physician_name,
                :license_no, :signature_date, :institution
            )");
        }

        $stmt->execute([
            ':history_id'          => $history_id,
            ':client_id'           => $client_id,
            ':exam_date'           => $exam_date,
            ':chest_xray'          => $chest_xray,
            ':xray_findings'       => $xray_findings,
            ':impression'          => $impression,
            ':discussions'         => $discussions,
            ':discussion_details'  => $discussion_details,
            ':home_medication'     => $home_medication,
            ':medication_details'  => $medication_details,
            ':home_instructions'   => $home_instructions,
            ':instruction_details' => $instruction_details,
            ':abbreviations'       => $abbreviations,
            ':f1_date'             => $f1_date,
            ':med_cert_issued'     => $med_cert_issued,
            ':referred_to'         => $referred_to,
            ':recommendation'      => $recommendation,
            ':physician_name'      => $physician_name,
            ':license_no'          => $license_no,
            ':signature_date'      => $signature_date,
            ':institution'         => $institution
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Diagnostic record saved successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
