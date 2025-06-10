<?php
session_start();
require_once 'vendor/autoload.php';

if (!isset($_GET['historyID']) || !is_numeric($_GET['historyID'])) {
    die('Invalid history ID provided.');
}

$historyID = (int)$_GET['historyID'];

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=University_Clinic_System;port=4307',
        'root',
        '181414',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Fetch latest medical certificate for the given historyID
try {
    $stmt = $pdo->prepare("SELECT * FROM medicalcertificate WHERE historyID = ? ORDER BY MedicalCertID DESC LIMIT 1");
    $stmt->execute([$historyID]);
    $medicalCertData = $stmt->fetch();

    if (!$medicalCertData) {
        die('No data found for the given history ID.');
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

if (!$medicalCertData) {
    die('No data found for the given history ID.');
}

// Mark certificate as downloaded
try {
    $updateStmt = $pdo->prepare("UPDATE medicalcertificate SET IsDownload = 1 WHERE MedicalCertID = ?");
    $updateStmt->execute([$medicalCertData['MedicalCertID']]);
} catch (PDOException $e) {
    die('Failed to update download status: ' . $e->getMessage());
}


// Assign fetched values
$patient_name = htmlspecialchars($medicalCertData['PatientName'] ?? '');
$patient_age = htmlspecialchars($medicalCertData['PatientAge'] ?? '');
$patient_gender = htmlspecialchars($medicalCertData['PatientGender'] ?? 'F/M');
$exam_date = htmlspecialchars($medicalCertData['ExamDate'] ?? '');
$findings = htmlspecialchars($medicalCertData['Findings'] ?? '');
$impression = htmlspecialchars($medicalCertData['Impression'] ?? '');
$note = htmlspecialchars($medicalCertData['Note'] ?? '');
$license_no = htmlspecialchars($medicalCertData['LicenseNo'] ?? '');
$date_issued = htmlspecialchars($medicalCertData['DateIssued'] ?? '');

try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('LSPU Medical Clinic');
    $pdf->SetAuthor('LSPU Medical Clinic');
    $pdf->SetTitle('Medical Certificate');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 40, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);

    $image_path = 'UC-Client/assets/images/Lspu-Header.jpg';

    $html = <<<EOD
<style>
    .header { text-align: center; margin-bottom: 10px; }
    .title { text-align: center; font-weight: bold; font-size: 14pt; margin: 15px 0; }
    .underline {
        border-bottom: 1px solid #000;
        padding: 0 5px;
        display: inline-block;
    }
    .section { margin-bottom: 10px; line-height: 1.5; }
    .signature { margin-top: 30px; margin-bottom: 30px;}
    .footer-table {
        width: 100%;
        font-size: 8pt;
        padding-top: 15px;
        padding: 6px;
    }
</style>

<table width="100%" cellpadding="30" cellspacing="0">
    <tr>
        <td align="left" style="padding-left: 0px;">
            <img src="$image_path" height="85">
        </td>
    </tr>
</table>

<div class="title">MEDICAL CERTIFICATE</div>

<div class="section">
    This is to certify that <span class="underline" style="width: 70mm;">$patient_name</span>,
    a <span class="underline" style="width: 15mm;">$patient_age</span> year old 
    <span class="underline" style="width: 20mm;">$patient_gender</span>,
    has been seen and examined on <span class="underline" style="width: 50mm;">$exam_date</span>
    at the Medical Clinic.
</div>

<div class="section">
    <strong>Pertinent findings:</strong> <span class="underline" style="width: 120mm;">$findings</span>
</div>

<div class="section">
    <strong>Impression on examination:</strong> <span class="underline" style="width: 110mm;">$impression</span>
</div>

<div class="section">
    <strong>NOTE:</strong> <span class="underline" style="width: 125mm;">$note</span>
</div>

<div class="signature">
    Visiting Physician/University Nurse<br>
    License No.: <span class="underline" style="width: 30mm;">$license_no</span><br>
    Date Issued: <span class="underline" style="width: 30mm;">$date_issued</span>
</div>

<table class="footer-table">
    <tr>
        <td align="left">LSPU-OSAS-SF-M08</td>
        <td align="center">Rev. 0</td>
        <td align="right">10 Aug. 2016</td>
    </tr>
</table>
EOD;

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('medical_certificate.pdf', 'D');
} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}
