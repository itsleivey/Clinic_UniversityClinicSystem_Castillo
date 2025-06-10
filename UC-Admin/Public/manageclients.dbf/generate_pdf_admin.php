<?php
require_once '../../../public/vendor/autoload.php';

// Get all parameters from the request
$patient_name = $_GET['patient_name'] ?? '';
$patient_age = $_GET['patient_age'] ?? '';
$patient_gender = $_GET['patient_gender'] ?? '';
$exam_date = $_GET['exam_date'] ?? '';
$findings = $_GET['findings'] ?? '';
$impression = $_GET['impression'] ?? '';
$note = $_GET['note'] ?? '';
$license_no = $_GET['license_no'] ?? '';
$date_issued = $_GET['date_issued'] ?? '';

// Helper function to create underlined spans
function underline($text, $width_mm) {
    $font_weight = 'Bold';
    $content = trim($text) === '' ? '&nbsp;' : htmlspecialchars($text);
    return "<span class=\"underline\" style=\"width: {$width_mm}mm; font-weight: $font_weight;\">$content</span>";
}

// Format all fields
$patient_name = underline($patient_name, 70);
$patient_age = underline($patient_age, 15);
$patient_gender = underline($patient_gender, 20);
$exam_date = underline($exam_date, 30);
$findings = underline($findings, 120);
$impression = underline($impression, 110);
$note = underline($note, 125);
$license_no = underline($license_no, 30);
$date_issued = underline($date_issued, 30);

try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('LSPU Medical Clinic');
    $pdf->SetAuthor('LSPU Medical Clinic');
    $pdf->SetTitle('Medical Certificate');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);

    $image_path = realpath(__DIR__ . '/../assets/images/Lspu-Header.jpg');

    $html = <<<EOD
    <style>
        .header {
            margin-top: 40px;
            width: 70%;
            padding-left: 55px;
        }
        .title {
            text-align: center;
            font-weight: medium;
            font-size: 12pt;
            margin: 15px 0;
            padding: 5px 0;
        }
        .underline {
            border-bottom: 1px solid #000;
            padding: 0 5px;
            display: inline-block;
        }
        .section {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .signature {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .footer {
            font-size: 9pt;
            text-align: center;
            margin-top: 15px;
            color: #555;
        }
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
        This is to certify that $patient_name,
        a $patient_age year old $patient_gender,
        has been seen and examined on $exam_date
        at the Medical Clinic.
    </div>

    <div class="section">
        <span>Pertinent findings:</span> $findings
    </div>

    <div class="section">
        <span>Impression on examination:</span> $impression
    </div>

    <div class="section">
        <span>NOTE:</span> $note
    </div>

    <div class="signature">
        Visiting Physician/University Nurse<br>
        License No.: $license_no<br>
        Date Issued: $date_issued
    </div>

    <table class="footer-table">
        <tr>
            <td align="left" style="padding-top: 20px;">LSPU-OSAS-SF-M08</td>
            <td align="center" style="padding-top: 20px;">Rev. 0</td>
            <td align="right" style="padding-top: 20px;">10 Aug. 2016</td>
        </tr>
    </table>
    EOD;

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('medical_certificate.pdf', 'I');

} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}