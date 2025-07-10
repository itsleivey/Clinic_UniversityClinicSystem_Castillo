<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'config/database.php'; 
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = pdo_connect_mysql();
if (!isset($_GET['ClientID']) || !is_numeric($_GET['ClientID'])) {
    die('Invalid certificate ID provided.');
}

$certificateId = (int)$_GET['ClientID'];

// Database connection


// Fetch medical certificate data
try {
    $stmt = $pdo->prepare("SELECT * FROM medicalcertificate WHERE ClientID = ? ORDER BY MedicalCertID DESC LIMIT 1");
    $stmt->execute([$certificateId]);
    $medicalCertData = $stmt->fetch();

    if (!$medicalCertData) {
        die('No data found for the given certificate ID.');
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Load HTML template from separate file
$templatePath = __DIR__ . '/certificate_template.html';
if (!file_exists($templatePath)) {
    die('Template file not found.');
}
$template = file_get_contents($templatePath);

// Handle logo image
$imagePath = __DIR__ . '/assets/images/LSPULOGO-PDF.jpg';
$logoPlaceholder = '<div class="logo-placeholder" style="width:60px;height:60px;border:1px dashed #ccc;"></div>';

if (file_exists($imagePath) && is_readable($imagePath)) {
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
    $base64Logo = 'data:image/' . $imageType . ';base64,' . $imageData;

    $template = str_replace(
        'src="assets/images/LSPULOGO-PDF.jpg"',
        'src="' . $base64Logo . '"',
        $template
    );
} else {
    $template = str_replace(
        'src="assets/images/LSPULOGO-PDF.jpg"',
        $logoPlaceholder,
        $template
    );
}

$headerPath = __DIR__ . '/assets/images/Lspu-Header.png';
$headerImage = '';

if (file_exists($headerPath) && is_readable($headerPath)) {
    $headerData = base64_encode(file_get_contents($headerPath));
    $headerType = pathinfo($headerPath, PATHINFO_EXTENSION);
    $headerImage = 'data:image/' . $headerType . ';base64,' . $headerData;
} else {
    die('Header image not found or not readable.');
}


// Replace placeholders with data
$replacements = [
    '{{PATIENT_NAME}}'   => htmlspecialchars($medicalCertData['PatientName'] ?? ''),
    '{{PATIENT_AGE}}'    => htmlspecialchars($medicalCertData['PatientAge'] ?? ''),
    '{{PATIENT_GENDER}}' => htmlspecialchars($medicalCertData['PatientGender'] ?? 'F/M'),
    '{{EXAM_DATE}}'      => htmlspecialchars($medicalCertData['ExamDate'] ?? ''),
    '{{FINDINGS}}'       => nl2br(htmlspecialchars($medicalCertData['Findings'] ?? '')),
    '{{IMPRESSION}}'     => nl2br(htmlspecialchars($medicalCertData['Impression'] ?? '')),
    '{{NOTE}}'           => nl2br(htmlspecialchars($medicalCertData['NoteContent'] ?? '')),
    '{{LICENSE_NO}}'     => htmlspecialchars($medicalCertData['LicenseNo'] ?? ''),
    '{{DATE_ISSUED}}'    => htmlspecialchars($medicalCertData['DateIssued'] ?? ''),
    '{{HEADER_IMAGE}}'   => $headerImage,
];


$template = str_replace(array_keys($replacements), array_values($replacements), $template);

// Configure Dompdf
$options = new Options();
$options->set([
    'isRemoteEnabled' => true,
    'isHtml5ParserEnabled' => true,
    'isPhpEnabled' => true,
    'defaultPaperSize' => 'A4',
    'defaultPaperOrientation' => 'portrait',
    'isFontSubsettingEnabled' => true,
]);

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($template);

try {
    $dompdf->render();
} catch (Exception $e) {
    die('PDF generation failed: ' . $e->getMessage());
}

// Save for debug (optional)
file_put_contents(__DIR__ . '/pdf_debug.html', $template);

// Output PDF
$dompdf->stream('medical_certificate_' . $certificateId . '.pdf', [
    'Attachment' => true,
    'compress' => true
]);

exit;
