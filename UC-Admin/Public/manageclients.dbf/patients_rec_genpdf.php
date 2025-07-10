<?php
ob_start();
require_once '../../../public/vendor/autoload.php'; // TCPDF autoload path

function clean($text)
{
    return htmlspecialchars(trim($text));
}

$name = clean($_POST['name']);
$age = clean($_POST['age']);
$address = clean($_POST['address']);
$course = clean($_POST['course']);
$date = clean($_POST['date']);

$bp = clean($_POST['bp']);
$hr = clean($_POST['hr_pr']);  // consistent with $hr
$temp = clean($_POST['temp']);
$o2sat = clean($_POST['o2sat']);

$subjective = clean($_POST['subjective']);
$objective = clean($_POST['objective']);
$assessment = clean($_POST['assessment']);
$plan = clean($_POST['plan']);


try {

    class MYPDF extends TCPDF
    {
        public function Footer()
        {
            // Position at 20 mm from bottom
            $this->SetY(-20);
            $this->SetFont('helvetica', '', 8);

            $html = '
        <table width="100%" style="font-size: 8pt;">
            <tr>
                <td align="left">LSPU-OSAS-SF-M08</td>
                <td align="center">Rev. 0</td>
                <td align="right">10 Aug. 2016</td>
            </tr>
        </table>';

            $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, 0, true, 'L', true);
        }
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('LSPU Medical Clinic');
    $pdf->SetAuthor('LSPU Medical Clinic');
    $pdf->SetTitle("Patient's Record");

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    $pdf->SetMargins(12, 12, 12);

    // Improved AutoPageBreak settings
    $pdf->SetAutoPageBreak(true, 20); // 20mm margin at bottom before breaking

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);

    $imagePath = realpath(__DIR__ . '/../assets/images/Lspu-Header.jpg');
    $imageHtml = '';
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $imageHtml = '<img src="data:image/jpeg;base64,' . $imageData . '" height="70">';
    }

    $html = <<<EOD
<style>
    .header {
        text-align: center;
        font-weight: bold;
        font-size: 12pt;
        font-family: italic;
    }
    .subheader {
        text-align: center;
        font-size: 12pt;
        font-family: italic;
        margin-bottom: 20px;
    }
    .section-title {
        font-weight: bold;
        font-size: 12pt;
        font-family: helvetica;
        margin-top: 15px;
        margin-bottom: 8px;
        text-decoration: underline;
    }
    .section {
        font-size: 11pt;
        font-family: helvetica;
        margin-bottom: 2px;
    }
    .field-label {
        font-weight: bold;
        display: inline-block;
        width: 90px;
    }
    .underline {
        display: inline-block;
        padding: 2px 5px;
        min-width: 60px;
        border-bottom: 1px solid #000;
    }
    .soap-container {
        font-family: helvetica;
        font-size: 10pt;
        line-height: 1.5;
    }
    .soap-section {
        margin-bottom: 15px;
    }
    .soap-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    .soap-content {
        text-align: justify;
        padding-left: 10px;
    }
    .border-box {
        padding-left: 8px;
    }
    .vital-signs-box {
        padding-right: 10px;
    }
    .footer-table {
        width: 100%;
        position: fixed;
        font-size: 8pt;
        padding-top: 50px;
        padding: 6px;
    }
</style>

<table width="90%">
    <tr>
        <td align="center">$imageHtml</td>
    </tr>
</table>

<div class="header">MEDICAL CLINIC</div>
<div class="subheader">Consultation Record</div>

<div class="section">
    <div><span class="field-label">Name:</span> $name</div>
    <div><span class="field-label">Age:</span> $age</div>
    <div><span class="field-label">Address:</span> $address</div>
    <div><span class="field-label">Course:</span> $course</div>
    <div><span class="field-label">Date:</span> $date</div>
</div>

<!-- Two-column table layout -->
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <!-- Left column for Vital Signs -->
        <td width="28%" valign="top" class="border-box vital-signs-box">
            <div class="section-title">Vital Signs</div>
            <div class="section">
                <div><span class="field-label">BP:</span> <span class="underline">$bp</span></div>
                <div><span class="field-label">HR/PR:</span> <span class="underline">$hr</span></div>
                <div><span class="field-label">Temp:</span> <span class="underline">$temp</span></div>
                <div><span class="field-label">O<sub>2</sub> Sat:</span> <span class="underline">$o2sat</span></div>

            </div>
        </td>
        
        <!-- Right column for SOAP Notes -->
        <td width="65%" valign="top" class="border-box">
        
            <div class="soap-container">
                <div class="soap-section">
                    <div class="soap-title">Subjective:</div>
                    <div class="soap-content">$subjective</div>
                </div>
                <div class="soap-section">
                    <div class="soap-title">Objective:</div>
                    <div class="soap-content">$objective</div>
                </div>
                <div class="soap-section">
                    <div class="soap-title">Assessment:</div>
                    <div class="soap-content">$assessment</div>
                </div>
                <div class="soap-section">
                    <div class="soap-title">Plan:</div>
                    <div class="soap-content">$plan</div>
                </div>
            </div>
        </td>
    </tr>

    
</table>

EOD;
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Patient_Record.pdf', 'I');
    ob_end_flush();
} catch (Exception $e) {
    echo 'Error generating PDF: ' . $e->getMessage();
}
