<?php
require_once '../../../public/vendor/autoload.php';

$patientName = $_POST['patient_name'] ?? '';
$patientAge = $_POST['patient_age'] ?? '';
$patientSex = $_POST['patient_sex'] ?? '';
$patientImpression = $_POST['p-impression'] ?? '';
$physician = $_POST['physician'] ?? '';
$licNo = $_POST['LicNo'] ?? '';
$notes = $_POST['notes'] ?? '';
$dateToday = $_POST['input_date'] ?? date('F d, Y');

try {
    // Custom paper size: 4" x 5.5" in mm (101.6mm x 139.7mm)
    $pdf = new TCPDF('P', 'mm', [101.6, 139.7], true, 'UTF-8', false);

    $pdf->SetCreator('LSPU Medical Clinic');
    $pdf->SetAuthor('LSPU Medical Clinic');
    $pdf->SetTitle('Rx Form');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(5, 8, 10);  // Left, Top, Right margins
    $pdf->SetAutoPageBreak(true, 15);  // Bottom margin of 15mm for footer

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    // HEADER IMAGE (scaled to fit)
    $headerImagePath = __DIR__ . '/../assets/images/Lspu-Header.jpg';
    if (file_exists($headerImagePath)) {
        $pdf->Image($headerImagePath, '', '', 80, 18);  // Width: 80mm, Height: 18mm
        $pdf->Ln(26);  // Space after image
    }

    // PATIENT INFO SECTION
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(1, 5, 'Name:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(38, 5, $patientName, 0, 0, 'R');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetX(60);
    $pdf->Cell(15, 5, 'Age/Sex:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(0, 5, $patientAge. "/". $patientSex, 0, 1);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(20, 5, 'Impression:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(60, 5, $patientImpression, 0, 0);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(60, $pdf->GetY());
    $pdf->Cell(10, 5, 'Date:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(30, 5, $dateToday, 0, 1, 'L');

    // Rx SYMBOL + NOTES SECTION
    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(10, 7, '℞', 0, 1);

    // Calculate available space for notes before footer
    $footerStartY = 139.7 - 15; // Page height - footer height
    $currentY = $pdf->GetY();
    $availableSpace = $footerStartY - $currentY - 25; // Extra 25mm for signature area
    
    $pdf->SetFont('helvetica', '', 9);
    // MultiCell with maximum height constraint
    $pdf->MultiCell(0, 5, $notes, 0, 'L', false, 1, '', '', true, 0, false, true, $availableSpace);

    // SIGNATURE AREA (positioned above footer)
    $pdf->SetY($footerStartY - 25); // Position 25mm above footer
    $pdf->SetX(50);
    $pdf->Cell(0, 5, $physician, 0, 1, 'L');

    $pdf->SetX(70);
    $pdf->Cell(0, 5, '________________________', 0, 1, 'R');
    $pdf->Cell(68, 5, 'Visiting Physician', 0, 1, 'R');

    // License number (aligned with signature)
    $pdf->SetX(8);
    $pdf->Cell(60, 5, 'Lic. No: ' . $licNo, 0, 1, 'R');

    // FOOTER (fixed position at bottom)
    $pdf->SetAutoPageBreak(false); // Disable auto page break for footer
    $pdf->SetY(-15); // Always 15mm from bottom
    $pdf->SetFont('helvetica', '', 6);
    $pdf->Cell(33.8, 4, 'LSPU-OSAS-SF-M13', 0, 0, 'L');
    $pdf->Cell(22.0, 4, 'Rev. 0', 0, 0, 'C');
    $pdf->Cell(29.8, 4, '10 Aug. 2016', 0, 0, 'R');

    $pdf->Output('rx_form.pdf', 'I');
} catch (Exception $e) {
    die('PDF Generation Error: ' . $e->getMessage());
}