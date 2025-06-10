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

// Fetch data using historyID
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

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);

    $logoPath = 'UC-Client/assets/images/LSPULOGO-PDF.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 90, 10, 30, 30);
        $pdf->Ln(20);
    } else {
        die('Logo file not found.');
    }

    $content = '
    <div style="text-align: center; line-height: 1.2;">
        <p>Republic of the Philippines</p>
        <p>Laguna State Polytechnic University</p>
        <p>Province of Laguna</p>
    </div>

    <div style="text-align: center; font-weight: bold; margin: 10px 0; font-size: 12pt;">
        MEDICAL CERTIFICATE
    </div>

    <div style="line-height: 1.4;">
        <p>This is to certify that <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['PatientName'] ?? '') . '</span>,
        a <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['PatientAge'] ?? '') . '</span> year old
        <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['PatientGender'] ?? 'F/M') . '</span>,
        has been seen and examined on <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['ExamDate'] ?? '') . '</span>
        at the Medical Clinic.</p>

        <p><strong>Pertinent findings:</strong> <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['Findings'] ?? '') . '</span></p>

        <p><strong>Impression on examination:</strong> <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['Impression'] ?? '') . '</span></p>

        <p><strong>NOTE:</strong> <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['NoteContent'] ?? '') . '</span></p>

        <div style="margin-top: 20px;">
            Visiting Physician/University Nurse<br>
            License No: <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['LicenseNo'] ?? '') . '</span><br>
            Date Issued: <span style="border-bottom: 0.5px solid #000;">' . htmlspecialchars($medicalCertData['DateIssued'] ?? '') . '</span>
        </div>

        <div style="font-size: 8pt; text-align: center; margin-top: 10px;">
            LSPU-OSAS-SF-M08 | Rev. 0 | 10 Aug. 2016
        </div>
    </div>
    ';

    $pdf->writeHTML($content, true, false, true, false, '');
    $pdf->Output('medical_certificate_history_' . $historyID . '.pdf', 'D');
} catch (Exception $e) {
    die('PDF generation error: ' . $e->getMessage());
}
