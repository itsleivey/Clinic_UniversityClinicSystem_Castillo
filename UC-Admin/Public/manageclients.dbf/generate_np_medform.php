<?php
require_once '../../../public/vendor/autoload.php';

if (isset($_POST['print_action']) && $_POST['print_action'] == '1') {
    // Retrieve form data
    $client_id = $_POST['client_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $agency = $_POST['agency'] ?? '';
    $address = $_POST['address'] ?? '';
    $age = $_POST['age'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $civil_status = $_POST['civil-status'] ?? '';
    $position = $_POST['position'] ?? '';
    $physician_signature = $_POST['physician_signature'] ?? '';
    $physician_agency = $_POST['physician_agency'] ?? '';
    $other_info = $_POST['otherinfo'] ?? '';
    $license_no = $_POST['license_no'] ?? '';
    $height = $_POST['height'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $blood_type = $_POST['blood-type'] ?? '';
    $official_designation = $_POST['official_designation'] ?? '';
    $date_created = $_POST['date_created'] ?? '';
    $fitness_status = $_POST['fitness_status'] ?? 'FIT'; // Default to FIT if not specified

    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Medical Certificate Generator');
    $pdf->SetAuthor('Government Physician');
    $pdf->SetTitle('Medical Certificate for Employment');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 5, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->SetFont('helvetica', '', 10);
    // Add a page
    $pdf->AddPage();

    // Set font

    $blood_test = isset($_POST['blood_test'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';

    $urinalysis = isset($_POST['urinalysis'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';

    $chest_xray = isset($_POST['chest_xray'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';

    $drug_test = isset($_POST['drug_test'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';

    $psych_test = isset($_POST['psych_test'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';

    $neuro_test = isset($_POST['neuro_test'])
        ? '<span style="font-family: dejavusans;">✔</span>'
        : '<span style="font-family: dejavusans;">✘</span>';
    // Build the HTML content to match the form
    $html = <<<EOD
<style>
    table { border-collapse: collapse; font-size: 10px; }
    td { vertical-align: top; padding: 2px; }
</style>

<!-- Header Section -->
<table border="0" width="100%">
    <tr>
        <td width="100%">
           <span style="font-style: italic; font-size:9px;">CS Form No. 211</span><br>
            <span style="font-style: italic; font-size:8px;">Revised 2018</span>
        </td>
       
    </tr>
    <tr>
     <td width="100%" align="center" style="font-weight: bold; height="100px"; font-size: 12px;">
            MEDICAL CERTIFICATE<br>
            <span style="font-weight: normal;">(For Employment)</span>
        </td>
    </tr>
</table>
<br>
 <div style=" padding-top: 15px; text-align: center; gap: 3px;">
      <table border="2">
      </table><br>
  <span style="font-family: 'Arial'; font-weight: normal; letter-spacing: 2px;">INSTRUCTIONS</span><br>

    </div>

<!-- Instructions Box -->
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
     <td width="20%" style="text-align: center; font-size: 9px;">
     </td>
    <td width="70%" style="text-align: center; font-size: 9px;">
        <div style="padding-left: 40px; text-align: left;">
          a. This medical certificate should be accomplished by a licensed government physician.<br>
          b. Attach this certificate to original appointment, transfer and reemployment.<br>
          c. The results of the following pre-employment medical/physica/psychological <br> must be attached to this form:<br>
        </div>
       <table align="center" cellpadding="0" cellspacing="0">
          <tr>
             <td width="10%">
             </td>
            <td width="90%"align="left" style="font-size: 9px; text-align: left;">
              $blood_test Blood Test<br>
              $urinalysis Urinalysis<br>
              $chest_xray Chest X-Ray<br>
              $drug_test Drug Test<br>
              $psych_test Psychological<br>
              $neuro_test Neuro-Psychiatric Examination (if applicable)
            </td>
          </tr>
       </table>
    </td>
  </tr>
</table>
<br>
 <div style="padding-top: 15px; text-align: center;">
      <table border="2">
      </table><br>
 <span style="font-family: 'Arial'; letter-spacing: 0.2px;">FOR  THE  PROPOSED  APPOINTEE</span>


    </div>
<br>
   
<!-- Proposed Appointee Section -->
<table border="1" width="100%">
   
   
    <tr>
  
       <td width="60%">
         <strong>NAME </strong>
         <strong style="font-size: 8px;">(Last Name, First Name, Name Extension (if any) and Middle Name)</strong><br>
         $name<br>
         <div style="border-top: 2px solid #000;">
              <strong style="font-size: 9px;">ADDRESS</strong><br>
               $address
        </div>
    
        
       </td>
   
       <td width="40%" valign="top">
       

         <strong style="font-size: 9px;">AGENCY / ADDRESS</strong><br><br>
         $agency
      </td>
   </tr>
    <tr>
     
        <td width="20%">
            <strong style="font-size: 9px;">AGE:</strong><br> $age &nbsp;&nbsp;  
        </td>
        <td width="20%">
            <strong style="font-size: 9px;">SEX:</strong><br> $sex &nbsp;&nbsp;
        </td >
         <td width="20%">
            <strong style="font-size: 9px;">CIVIL STATUS:</strong><br> $civil_status &nbsp;&nbsp;
        </td>
         <td width="40%">
            <strong style="font-size: 9px;">PROPOSED POSITION:</strong><br> $position
        </td>
       
    </tr>
</table>


<br>
 <div style=" padding-top: 15px; text-align: center; gap: 3px;">
      <table border="2">
      </table><br>
      <span style="font-family: 'Arial'; letter-spacing: 1.5px;">FOR THE LICENSED GOVERNMENT PHYSICIAN</span>
    </div>
<br>
<!-- Physician Certification and Data -->
<table border="1" width="100%">
  
    <tr>
        <td style="font-style: italic;" colspan="2">
            I hereby certify that I have reviewed and evaluated the attached examination results,
            personally examined the above named individual and found him/her to be physically and medically
            &#9633; <strong>$fitness_status</strong> / &#9633; UNFIT for employment.
        </td>
    </tr>
      
    <tr>
  
       <td width="60%">
            <strong style="font-size: 8px;">SIGNATURE over PRINTED NAME OF LICENSED GOVERNMENT PHYSICIAN:</strong><br>$physician_signature
         <div style="border-top: 2px solid #000;">
             <strong style="font-size: 9px;">AGENCY / Affiliation of Licensed Government Physician:</strong><br>$physician_agency
        </div>
    
        
       </td>
   
       <td width="40%" valign="top">
          <strong style="font-size: 9px;">OTHER INFORMATION ABOUT THE PROPOSED APPOINTEE</strong><br>
           $other_info
      </td>
   </tr>
   
    <tr>
       <td width="60%">
            <strong style="font-size: 9px;">LICENSE NO.:</strong><br>$license_no
        </td>
        <td width="13%">
            <strong  style="font-size: 8px;">HEIGHT (m) Bare Foot:</strong><br> $height
        </td>
        <td width="14%">
            <strong  style="font-size: 8px;">WEIGHT (kg) Stripped:</strong><br> $weight
        </td>
        <td width="13%">
            <strong  style="font-size: 8px;">BLOOD <br> TYPE:</strong><br> $blood_type
        </td>
    </tr>
    <tr>
       <td width="60%">
            <strong style="font-size: 9px;">OFFICIAL DESIGNATION:</strong><br>$official_designation
        </td>
        <td  width="40%">
            <strong style="font-size: 9px;">DATE EXAMINED:</strong><br>$date_created
        </td>
    </tr>
</table>

<br>
 <div style=" padding-top: 15px; text-align: center; gap: 3px;">
      <table border="2">
      </table><br>
     
    </div>

EOD;


    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('medical_certificate.pdf', 'I');
} else {
    echo "Invalid request.";
}
