<?php
session_start();
require_once '../../../public/vendor/autoload.php';
require_once 'config/database.php';

$pdo = pdo_connect_mysql();

if (!isset($_GET['ClientID']) || !is_numeric($_GET['ClientID'])) {
    die('Invalid certificate ID provided.');
}

$clientID = (int)$_GET['ClientID'];

if (!$clientID || !is_numeric($clientID)) {
    die("Invalid Client ID.");
}

// List of all required tables
$tables = [
    'personalinfo',
    'medicaldentalhistory',
    'familymedicalhistory',
    'personalsocialhistory',
    'femalehealthhistory',
    'physicalexamination',
    'diagnosticresults'
];

$allTablesHaveData = true;
$missingTables = [];

foreach ($tables as $table) {
    $query = "SELECT * FROM $table WHERE ClientID = ? " . ($table == 'diagnosticresults' ? 'ORDER BY DiagnosticID DESC' : '');
    $stmt = $pdo->prepare($query);
    $stmt->execute([$clientID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $allTablesHaveData = false;
        $missingTables[] = $table;
    }
}

// =================== FETCH DATA WITH NULL DEFAULT ===================

// Helper to fetch safely
function fetchOrNull($pdo, $query, $params = [])
{
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}

function safeVal($array, $key, $default = '')
{
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}



$info               = fetchOrNull($pdo, "SELECT * FROM personalinfo WHERE ClientID = ?", [$clientID]);
$medDental          = fetchOrNull($pdo, "SELECT * FROM medicaldentalhistory WHERE ClientID = ?", [$clientID]);
$familyHistory      = fetchOrNull($pdo, "SELECT * FROM familymedicalhistory WHERE ClientID = ?", [$clientID]);
$socialHistory      = fetchOrNull($pdo, "SELECT * FROM personalsocialhistory WHERE ClientID = ?", [$clientID]);
$femaleHealthHistory = fetchOrNull($pdo, "SELECT * FROM femalehealthhistory WHERE ClientID = ?", [$clientID]);
$physicalExamination = fetchOrNull($pdo, "SELECT * FROM physicalexamination WHERE ClientID = ?", [$clientID]);
$diagnosticResults  = fetchOrNull($pdo, "SELECT * FROM diagnosticresults WHERE ClientID = ? ORDER BY DiagnosticID DESC", [$clientID]);


$info = $info ?? [];

// Safe values (default to empty string if missing)
$surname       = $info['Surname'] ?? '';
$givenName     = $info['GivenName'] ?? '';
$middleName    = $info['MiddleName'] ?? '';
$age           = $info['Age'] ?? '';
$gender        = $info['Gender'] ?? '';
$status        = $info['Status'] ?? '';
$birthDate     = $formattedBirthDate ?? '';
$course        = $info['Course'] ?? '';
$schoolYear    = $info['SchoolYearEntered'] ?? '';
$currentAddr   = $info['CurrentAddress'] ?? '';
$contactNumber = $info['ContactNumber'] ?? '';
$mothersName   = $info['MothersName'] ?? '';
$fathersName   = $info['FathersName'] ?? '';
$guardiansName = $info['GuardiansName'] ?? '';
$emergencyPerson   = $info['EmergencyContactPerson'] ?? '';
$emergencyRel      = $info['EmergencyContactRelationship'] ?? '';
$emergencyContact  = $info['EmergencyContactName'] ?? '';

$knownIllnessDetails       = $medDental['KnownIllnessDetails'] ?? '';
$presentImmunizations      = $medDental['PresentImmunizationsDetails'] ?? '';
$hospitalizationDetails    = $medDental['HospitalizationDetails'] ?? '';
$currentMedicinesDetails   = $medDental['CurrentMedicinesDetails'] ?? '';
$allergiesDetails          = $medDental['AllergiesDetails'] ?? '';
$dentalProblemsDetails     = $medDental['DentalProblemsDetails'] ?? '';
$childImmunizationDetails  = $medDental['ChildImmunizationDetails'] ?? '';
$primaryPhysicianDetails   = $medDental['PrimaryPhysicianDetails'] ?? '';

$allergyDetails         = $familyHistory['AllergyDetails'] ?? '';
$cancerDetails          = $familyHistory['CancerDetails'] ?? '';
$asthmaDetails          = $familyHistory['AsthmaDetails'] ?? '';
$liverDiseaseDetails    = $familyHistory['LiverDiseaseDetails'] ?? '';
$tuberculosisDetails    = $familyHistory['TuberculosisDetails'] ?? '';
$kidneyBladderDetails   = $familyHistory['KidneyBladderDetails'] ?? '';
$hypertensionDetails    = $familyHistory['HypertensionDetails'] ?? '';
$bloodDiseaseDetails    = $familyHistory['BloodDiseaseDetails'] ?? '';
$strokeDetails          = $familyHistory['StrokeDetails'] ?? '';
$mentalDisorderDetails  = $familyHistory['MentalDisorderDetails'] ?? '';
$diabetesDetails        = $familyHistory['DiabetesDetails'] ?? '';
$otherIllnessDetails    = $familyHistory['OtherIllnessDetails'] ?? '';
$bloodDisorderDetails   = $familyHistory['BloodDisorderDetails'] ?? '';
$epilepsyDetails        = $familyHistory['EpilepsyDetails'] ?? '';

// =================== CHECKMARK FUNCTIONS ===================
function checkMark($value)
{
    return ($value == 1) ? '<span style="font-family: dejavusans; font-weight: bolder;">✓</span>' : '';
}
function FamMedCheckMark($value)
{
    return ($value == 1) ? '<span style="font-family: dejavusans; font-weight: bolder;">✓</span>' : '';
}
function SocialCheckMark($value, $target)
{
    return ($value === $target) ? '<span style="font-family: dejavusans; font-weight: bold;">✓</span>' : '';
}
function diagnosticCheckMark($value)
{
    return ($value == 1) ? '<span style="font-family: dejavusans; font-weight: bolder;">✓</span>' : '';
}
function diagnosticRecommendation($value, $target)
{
    return ($value === $target) ? '<span style="font-family: dejavusans; font-weight: bold;">✓</span>' : '';
}

// =================== SAFE VARIABLE EXTRACTION ===================
// Example: If $medDental is null, all become null safely
$check_KnownIllness        = checkMark($medDental['KnownIllness']        ?? null);
$check_Hospitalization     = checkMark($medDental['Hospitalization']     ?? null);
$check_Allergies           = checkMark($medDental['Allergies']           ?? null);
$check_ChildImmunization   = checkMark($medDental['ChildImmunization']   ?? null);
$check_PresentImmunizations = checkMark($medDental['PresentImmunizations'] ?? null);
$check_CurrentMedicines    = checkMark($medDental['CurrentMedicines']    ?? null);
$check_DentalProblems      = checkMark($medDental['DentalProblems']      ?? null);
$check_PrimaryPhysician    = checkMark($medDental['PrimaryPhysician']    ?? null);

$check_allergy         = FamMedCheckMark($familyHistory['Allergy']        ?? null);
$check_cancer          = FamMedCheckMark($familyHistory['Cancer']         ?? null);
$check_asthma          = FamMedCheckMark($familyHistory['Asthma']         ?? null);
$check_tuberculosis    = FamMedCheckMark($familyHistory['Tuberculosis']   ?? null);
$check_hypertension    = FamMedCheckMark($familyHistory['Hypertension']   ?? null);
$check_bloodDisease    = FamMedCheckMark($familyHistory['BloodDisease']   ?? null);
$check_stroke          = FamMedCheckMark($familyHistory['Stroke']         ?? null);
$check_diabetes        = FamMedCheckMark($familyHistory['Diabetes']       ?? null);
$check_liverDisease    = FamMedCheckMark($familyHistory['LiverDisease']   ?? null);
$check_kidneyBladder   = FamMedCheckMark($familyHistory['KidneyBladder']  ?? null);
$check_bloodDisorder   = FamMedCheckMark($familyHistory['BloodDisorder']  ?? null);
$check_epilepsy        = FamMedCheckMark($familyHistory['Epilepsy']       ?? null);
$check_mentalDisorder  = FamMedCheckMark($familyHistory['MentalDisorder'] ?? null);
$check_others          = FamMedCheckMark($familyHistory['OtherIllness']   ?? null);

// Social history
$alcohol = $socialHistory['AlcoholIntake'] ?? null;
$tobacco = $socialHistory['TobaccoUse']    ?? null;
$drug    = $socialHistory['DrugUse']       ?? null;

$alcohol_yes    = SocialCheckMark($alcohol, 'yes');
$alcohol_no     = SocialCheckMark($alcohol, 'no');
$alcohol_former = SocialCheckMark($alcohol, 'former');

$tobacco_yes    = SocialCheckMark($tobacco, 'yes');
$tobacco_no     = SocialCheckMark($tobacco, 'no');
$tobacco_former = SocialCheckMark($tobacco, 'former');

$drug_yes       = SocialCheckMark($drug, 'yes');
$drug_no        = SocialCheckMark($drug, 'no');
$drug_former    = SocialCheckMark($drug, 'former');

$alcoholDetails = $socialHistory['AlcoholDetails'] ?? '';
$tobaccoDetails = $socialHistory['TobaccoDetails'] ?? '';
$drugDetails    = $socialHistory['DrugDetails'] ?? '';


// Checkboxes (tinyint(1) in schema)
$xray_check             = diagnosticCheckMark($diagnosticResults['ChestXrayPerformed'] ?? null);
$diagnostic_check       = diagnosticCheckMark($diagnosticResults['Discussions']        ?? null);
$homeMed_Check          = diagnosticCheckMark($diagnosticResults['HomeMedication']     ?? null);
$homeInstructions_Check = diagnosticCheckMark($diagnosticResults['HomeInstructions']   ?? null);
$medCert_Isseud_check   = diagnosticCheckMark($diagnosticResults['MedicalCertIssued']  ?? null);

// Recommendations (enum)
$fit_enroll_work  = diagnosticRecommendation($diagnosticResults['Recommendation'] ?? null, 'fit');
$fit_sports       = diagnosticRecommendation($diagnosticResults['Recommendation'] ?? null, 'fit_sports');
$fit_enroll_eval  = diagnosticRecommendation($diagnosticResults['Recommendation'] ?? null, 'fit_enroll');
$fit_work_eval    = diagnosticRecommendation($diagnosticResults['Recommendation'] ?? null, 'fit_work_eval');
$fit_sports_eval  = diagnosticRecommendation($diagnosticResults['Recommendation'] ?? null, 'fit_sports_eval');

// Extract all values for display from diagnostic results
$chestXrayResult = $diagnosticResults['XrayFindings'] ?? '';
$impression = $diagnosticResults['Impression'] ?? '';
$discussionDetails = $diagnosticResults['DiscussionDetails'] ?? '';
$homeMedication = $diagnosticResults['MedicationDetails'] ?? '';
$homeInstructions = $diagnosticResults['InstructionDetails'] ?? '';
$abbreviationsUsed = $diagnosticResults['AbbreviationsUsed'] ?? '';
$followUpDate = $diagnosticResults['F1Date'] ?? '';
$referred = $diagnosticResults['ReferredTo'] ?? '';
$physicianName = $diagnosticResults['PhysicianName'] ?? '';
$licenseNo = $diagnosticResults['LicenseNo'] ?? '';
$signatureDate = $diagnosticResults['SignatureDate'] ?? '';
$institution = $diagnosticResults['Institution'] ?? 'LAGUNA STATE POLYTECHNIC UNIVERSITY - UNIVERSITY CLINIC';
$examDate = $diagnosticResults['ExamDate'] ?? '';

// Format dates if needed
function formatDateForDisplay($dateString)
{
    if (!empty($dateString)) {
        $dateObject = DateTime::createFromFormat('Y-m-d', $dateString);
        return $dateObject ? $dateObject->format('m/d/Y') : $dateString;
    }
    return '';
}

$followUpDate = formatDateForDisplay($followUpDate);
$signatureDate = formatDateForDisplay($signatureDate);
$examDate = formatDateForDisplay($examDate);

// Birth date formatting (safe null check)
$BirthDate = $info['DateOfBirth'] ?? null;
$formattedBirthDate = '';
if ($BirthDate) {
    $birthdateObject = DateTime::createFromFormat('Y-m-d', $BirthDate);
    if ($birthdateObject) {
        $formattedBirthDate = $birthdateObject->format('m/d/Y');
    }
}



class MYPDF extends TCPDF
{
    public function Footer()
    {
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

try {
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('LSPU Medical Clinic');
    $pdf->SetAuthor('LSPU Medical Clinic');
    $pdf->SetTitle("Patient's Record");

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    // Load image
    $imagePath = realpath(__DIR__ . '/../assets/images/Lspu-Header.jpg');
    $imageHtml = '';
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $imageHtml = '<img src="data:image/jpeg;base64,' . $imageData . '" height="70">';
    }

    // Start HTML
    $html = <<<EOD
<style>
     .header{
     margin-top: 10px;
        display: flex;
        height: 100px;
        align-items: center;
        border-bottom: 1px solid black;
      }
 
      .value{
         font-weight: bold;
         font-size: 9pt;
       }

       .line{
          font-weight: bolder;
          border-bottom: 5px solid black;
          }
</style>

<table width="90%" style="font-size: 10pt; margin-bottom: 10px; padding-left: 30px;">
    <tr>
        <td colspan="0" style="width: 20px; text-align: center;"></td>
        <td  colspan="5" style="width: 380px; text-align: center;">
            $imageHtml
        </td>
        <td rowspan="5" style="width: 80px; border: 1px solid black; text-align: center;">
        <b>1x1<br>picture</b>
        </td>
    </tr>
</table>
<table width="93%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold; font-size: 11pt;">
            MEDICAL RECORDS<br>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td style="width: 30%; border: 1px solid black; border-bottom: none;">
            <span style="font-style: italic; font-weight: normal;">Surname</span><br>
            <span class="value">{$surname}</span>
        </td>
        <td style="width: 35%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Given Name</span><br>
            <span class="value">{$givenName}</span>
        </td>
        <td style="width: 35%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Middle Name</span><br>
            <span class="value">{$middleName}</span>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td style="width: 10%; border: 1px solid black; border-bottom: none;">
            <span style="font-style: italic; font-weight: normal;">Age</span><br>
            <span class="value">{$age}</span>
        </td>
        <td style="width: 10%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Sex</span><br>
            <span class="value">{$gender}</span>
        </td>
        <td style="width: 10%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Status</span><br>
            <span class="value">{$status}</span>
        </td>
        <td style="width: 20%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Date of Birth</span><br>
            <span class="value">{$birthDate}</span>
        </td>
        <td style="width: 15%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Course</span><br>
            <span class="value">{$course}</span>
        </td>
        <td style="width: 35%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">School year entered (if applicable)</span><br>
            <span class="value">{$schoolYear}</span>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td style="width: 75%; border: 1px solid black; border-bottom: none;">
            <span style="font-style: italic; font-weight: normal;">Current Address</span><br>
            <span class="value">{$currentAddr}</span>
        </td>
        <td style="width: 25%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Tell/ Cell NO.</span><br>
            <span class="value">{$contactNumber}</span>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td style="width: 30%; border: 1px solid black; border-bottom: none;">
            <span style="font-style: italic; font-weight: normal;">Mother's Name</span><br>
            <span class="value">{$mothersName}</span>
        </td>
        <td style="width: 20%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Father's Name</span><br>
            <span class="value">{$fathersName}</span>
        </td>
        <td style="width: 50%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Guardian's Name (if Applicable)</span><br>
            <span class="value">{$guardiansName}</span>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
    <tr>
        <td style="width: 50%; border: 1px solid black; border-bottom: none;">
            <span style="font-style: italic; font-weight: normal;">Name of Contact Person in CASE OF EMERGENCY  (REQUIRED)</span><br>
            <span class="value">{$emergencyPerson}</span>
        </td>
        <td style="width: 35%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Relationship</span><br>
            <span class="value">{$emergencyRel}</span>
        </td>
        <td style="width: 15%; border: 1px solid black;">
            <span style="font-style: italic; font-weight: normal;">Contact No. (REQUIRED)</span><br>
            <span class="value">{$emergencyContact}</span>
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
   <tr>
       <td></td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
  <tr>
    <td colspan="2" style="border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black;">
      <span style="font-size: 7.5px;"><br>
        I, <span class="line">_________________________________ </span>hereby ascertain that I have willingly shared/disclosed all information contained
        within this Medical Report and that this information is True and CORRECT to the best of my knowledge.
      </span>
    </td>
  </tr>
  <tr style="height: 40px;"> <!-- Add height to push text down -->
    <td style="border-left: 1px solid black; border-bottom: 1px solid black; text-align: center; vertical-align: bottom;">
      <span style="font-size: 7.5px;"><br><br><span class="line">______________________________________</span><br>Signature over Printed Name<br></span>
    </td>
    <td style="border-right: 1px solid black; border-bottom: 1px solid black; text-align: center; vertical-align: bottom;">
      <span style="font-size: 7.5px;"><br><br><span class="line">_____________________</span><br>Date<br></span>
    </td>
  </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            INSTRUCTIONS: Pls, check all that apply and provide details.
        </td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            I. PAST MEDICAL AND DENTAL HISTORY
        </td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px;">
   <tr>
       <td><br></td><br>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
<br>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;">$check_KnownIllness</td>
        <td style="width: 40%;">Previosly/ present KNOWN illness</td>
        <td style="width: 15%;"></td>
        <td style="width: 2.6%; border: 1px solid black;">$check_PresentImmunizations</td>
        <td style="width: 40%;">Present immunizations (ex. Flu, Hepa B. etc)</td>
    </tr>
    <tr>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span class="value" style="text-align: center">{$knownIllnessDetails}</span></td>
        <td style="width: 15%;"></td>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span  class="value" style="text-align: center">{$presentImmunizations}</span></td>
    </tr>
    <tr>
         <td style="font-size: 2.5px;"></td>
    </tr>
     <tr>
        <td style="width: 2.6%; border: 1px solid black;">$check_Hospitalization</td>
        <td style="width: 40%;">Past hospitalization/ confinement</td>
        <td style="width: 15%;"></td>
        <td style="width: 2.6%; border: 1px solid black;">$check_CurrentMedicines</td>
        <td style="width: 40%;">currently taking medicine/ vitamins</td>
    </tr>
    <tr>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span  class="value" style="text-align: center">{$hospitalizationDetails}</span></td>
        <td style="width: 15%;"></td>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span  class="value" style="text-align: center">{$currentMedicinesDetails}</span></td>
    </tr>
     <tr>
         <td style="font-size: 2.5px;"></td>
    </tr>
     <tr>
        <td style="width: 2.6%; border: 1px solid black;">$check_Allergies</td>
        <td style="width: 40%;">KNOWN allergies to food and medicine</td>
        <td style="width: 15%;"></td>
        <td style="width: 2.6%; border: 1px solid black;">$check_DentalProblems</td>
        <td style="width: 40%;">Dental problems (ex. Gingivits, etc )</td>
    </tr>
    <tr>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span class="value"  style="text-align: center">{$allergiesDetails}</span></td>
        <td style="width: 15%;"></td>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span class="value"  style="text-align: center">{$dentalProblemsDetails}</span></td>
    </tr>
      <tr>
         <td style="font-size: 2.5px;"></td>
    </tr>
     <tr>
        <td style="width: 2.6%; border: 1px solid black;">$check_ChildImmunization</td>
        <td style="width: 40%;">Childhood immunization</td>
        <td style="width: 15%;"></td>
        <td style="width: 2.6%; border: 1px solid black;">$check_PrimaryPhysician</td>
        <td style="width: 40%;">Primary care Physician</td>
    </tr>
    <tr>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span class="value"  style="text-align: center">{$childImmunizationDetails}</span></td>
        <td style="width: 15%;"></td>
        <td style="width: 42.5%; border-bottom: 1px solid black;"><span  class="value" style="text-align: center">{$primaryPhysicianDetails}</span></td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            II. FAMILY MEDICAL HISTORY
        </td>
    </tr>
</table>

<table width="100%" style="font-size: 8.5px; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
        <br>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_allergy ?></td>
        <td style="width: 7%;">Allergy</td>
        <td style="width: 34%; border-bottom: 1px solid black;"><span class="value"><?= $allergyDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_cancer ?></td>
        <td style="width: 7%;">Cancer</td>
        <td style="width: 33%; border-bottom: 1px solid black;"><span class="value"><?= $cancerDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_asthma ?></td>
        <td style="width: 12%;">Asthma/"hika"</td>
        <td style="width: 29%; border-bottom: 1px solid black;"><span class="value"><?= $asthmaDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_liverDisease ?></td>
        <td style="width: 11%;">Liver disease</td>
        <td style="width: 29%; border-bottom: 1px solid black;"><span class="value"><?= $liverDiseaseDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_tuberculosis ?></td>
        <td style="width: 14%;">Tuberculosis/ TB</td>
        <td style="width: 27%; border-bottom: 1px solid black;"><span class="value"><?= $tuberculosisDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_kidneyBladder ?></td>
        <td style="width: 21%;">Kidney or bladder disease</td>
        <td style="width: 19%; border-bottom: 1px solid black;"><span class="value"><?= $kidneyBladderDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_hypertension ?></td>
        <td style="width: 21%;">Hypertension/ "high blood"</td>
        <td style="width: 20%; border-bottom: 1px solid black;"><span class="value"><?= $hypertensionDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_bloodDisease ?></td>
        <td style="width: 12%;">Blood disease</td>
        <td style="width: 28%; border-bottom: 1px solid black;"><span class="value"><?= $bloodDiseaseDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_stroke ?></td>
        <td style="width: 6%;">Stroke</td>
        <td style="width: 35%; border-bottom: 1px solid black;"><span class="value"><?= $strokeDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_mentalDisorder ?></td>
        <td style="width: 13%;">Mental Disorder</td>
        <td style="width: 27%; border-bottom: 1px solid black;"><span class="value"><?= $mentalDisorderDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_diabetes ?></td>
        <td style="width: 8%;">Diabetes</td>
        <td style="width: 33%; border-bottom: 1px solid black;"><span class="value"><?= $diabetesDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_others ?></td>
        <td style="width: 6%;">Others</td>
        <td style="width: 34%; border-bottom: 1px solid black;"><span class="value"><?= $otherIllnessDetails ?></span></td>
    </tr>
    <tr><td colspan="7" style="font-size: 2.5px;"></td></tr>
    <tr>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_bloodDisorder ?></td>
        <td style="width: 14%;">Blood disorder</td>
        <td style="width: 27%; border-bottom: 1px solid black;"><span class="value"><?= $bloodDisorderDetails ?></span></td>
        <td style="width: 14%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $check_epilepsy ?></td>
        <td style="width: 11%;">Epilepsy</td>
        <td style="width: 29%; border-bottom: 1px solid black;"><span class="value"><?= $epilepsyDetails ?></span></td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            III. PERSONAL AND SOCIAL HISTORY
        </td>
        
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">

    <tr>
        <br>
        <td style="width: 15%;">1. Alcohol Intake:</td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $alcohol_yes ?></td>
        <td style="width: 5%;">Yes</td>
        <td style="width: 35%; border-bottom: 1px solid black;"><span class="value"><?= $alcoholDetails ?></span></td>
        <td style="width: 1%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $alcohol_no ?></td>
        <td style="width: 10%;">No</td>
    </tr>

    <tr>
        <br>
        <td style="width: 15%;">2. Tobacco Use:</td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $tobacco_yes ?></td>
        <td style="width: 5%;">Yes</td>
        <td style="width: 35%; border-bottom: 1px solid black;"><span class="value"><?= $tobaccoDetails ?></span></td>
        <td style="width: 1%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $tobacco_no ?></td>
        <td style="width: 10%;">No</td>
    </tr>

    <tr>
        <br>
        <td style="width: 15%;">3. Drug Use:</td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $drug_yes ?></td>
        <td style="width: 5%;">Yes</td>
        <td style="width: 35%; border-bottom: 1px solid black;"><span class="value"><?= $drugDetails ?></span></td>
        <td style="width: 1%;"></td>
        <td style="width: 2.6%; border: 1px solid black;"><?= $drug_no ?></td>
        <td style="width: 10%;">No</td>
    </tr>

</table>

<br><br><br><br>
EOD;
    //===================================================================
    $gender = strtolower(trim($info['Gender'] ?? ''));


    if ($gender === 'female' || $gender === '') {
        $pregnancyDetails = $femaleHealthHistory['PregnancyDetails'] ?? null;

        if (is_array($pregnancyDetails)) {
            $pregnancyDetails = implode(", ", $pregnancyDetails); // join into string
        }
        // ✅ Use null coalescing operator for safety
        // ✅ Always set to null if missing
        $Regularity              = $femaleHealthHistory['Regularity'] ?? null;
        $Historydysmenorrhea     = $femaleHealthHistory['Dysmenorrhea'] ?? null;
        $severity                = $femaleHealthHistory['DysmenorrheaSeverity'] ?? null;
        $historyExcessiveBleeding = $femaleHealthHistory['AbnormalBleeding'] ?? null;
        $PreviousPregnancy       = $femaleHealthHistory['PreviousPregnancy'] ?? null;
        $hadchildren             = $femaleHealthHistory['HasChildren'] ?? null;

        // ✅ Wrap in SocialCheckMark safely
        $regular       = SocialCheckMark($Regularity, 'regular');
        $irregular     = SocialCheckMark($Regularity, 'irregular');
        $has_history   = SocialCheckMark($Historydysmenorrhea, 'yes');
        $no_history    = SocialCheckMark($Historydysmenorrhea, 'no');
        $mild          = SocialCheckMark($severity, 'mild');
        $moderate      = SocialCheckMark($severity, 'moderate');
        $severe        = SocialCheckMark($severity, 'severe');
        $abnormal_yes  = SocialCheckMark($historyExcessiveBleeding, 'yes');
        $abnormal_no   = SocialCheckMark($historyExcessiveBleeding, 'no');
        $has_pregnancy = SocialCheckMark($PreviousPregnancy, 'yes');
        $no_pregnancy  = SocialCheckMark($PreviousPregnancy, 'no');
        $has_children  = SocialCheckMark($hadchildren, 'yes');
        $no_children   = SocialCheckMark($hadchildren, 'no');

        // ✅ Handle dates safely (null if missing or invalid)
        $rawDate = $femaleHealthHistory['LastPeriod'] ?? null;
        $formattedDate = null;
        if ($rawDate) {
            $dateObject = DateTime::createFromFormat('Y-m-d', $rawDate);
            $formattedDate = $dateObject ? $dateObject->format('m/d/Y') : null;
        }

        $LOBVrawDate = $femaleHealthHistory['LastOBVisit'] ?? null;
        $LOBVformattedDate = null;
        if ($LOBVrawDate) {
            $LOBVdateObject = DateTime::createFromFormat('Y-m-d', $LOBVrawDate);
            $LOBVformattedDate = $LOBVdateObject ? $LOBVdateObject->format('m/d/Y') : null;
        }

        // ✅ Use null fallback for optional text fields
        $duration         = $femaleHealthHistory['Duration'] ?? null;
        $padsPerDay       = $femaleHealthHistory['PadsPerDay'] ?? null;
        $pregnancyDetails = $femaleHealthHistory['PregnancyDetails'] ?? null;
        $childrenCount    = $femaleHealthHistory['ChildrenCount'] ?? null;

        $duration         = $femaleHealthHistory['Duration'] ?? null;
        $padsPerDay       = $femaleHealthHistory['PadsPerDay'] ?? null;
        $pregnancyDetails = $femaleHealthHistory['PregnancyDetails'] ?? null;
        $childrenCount    = $femaleHealthHistory['ChildrenCount'] ?? null;


        $html .= <<<EOD
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            4. For <span style="font-style: italic">FEMALES</span>
        </td>
    </tr>
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="text-align:left; font-size: 10pt;">
            a. Menstrual period 
        </td>
    </tr>
    <tr>
        <td style="width: 14%;"></td>
        <td colspan="1" style="width: 43%; text-align:left; font-size: 10pt;">
            Date of <span style="font-style: italic">first day</span> of LAST period (MM/DD/YYYY): 
        </td>
        <td colspan="2" style="text-align:left; font-size: 10pt; border-bottom: 1px solid black;"><span class="value">{$formattedDate}</span></td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 16%;"></td>
        <td style="width: 2.7%; border: 1px solid black;">{$regular}</td>
        <td>Regular</td>
        <td style="width: 2.7%; border: 1px solid black;">{$irregular}</td>
        <td>Irregular</td>
    </tr>
     <tr>
        <td style="width: 15.5%;"></td>
        <td style="width: 9%;">Duration:</td>
        <td style="width: 14%; font-weight: bold; border-bottom: 1px solid black;"><?= $duration ?? '' ?></td>
        <td style="width: 12%;">days/ weeks</td>
    </tr>
    <tr>
        <td style="width: 15.5%;"></td>
        <td style="width: 15%;">No. of pads/day:</td>
        <td style="width: 19%; font-weight: bold; border-bottom: 1px solid black;"><?= $padsPerDay ?? '' ?></td>
    </tr>
   
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 26%; text-align:left; font-size: 10pt;">
            b. History of dysmenorrhea:  
        </td>
        <td style="width: 2.7%; border: 1px solid black;">{$has_history}</td>
        <td>Yes</td>
        <td style="width: 2.7%; border: 1px solid black;">{$no_history}</td>
        <td>No</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 43%; text-align:left; font-size: 10pt;">
            c. if YES, how severe is your dysmenorrheal?   
        </td>
        <td style="width: 2.7%; border: 1px solid black;">$mild</td>
        <td>Mild</td>
        <td style="width: 2.7%; border: 1px solid black;">$moderate</td>
        <td>Moderate</td>
         <td style="width: 2.7%; border: 1px solid black;">$severe</td>
        <td>Severe</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 58%; text-align:left; font-size: 10pt;">
            d. Date of last check-up with an OB-gynecologist (MM-DD-YYYY): 
        </td>
        <td style="width: 24%; font-weight: bold; border-bottom: 1px solid black;">{$LOBVformattedDate}</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 40%; text-align:left; font-size: 10pt;">
            e. History of excessive/ abnormal bleeding?
        </td>
         <td style="width: 2.7%; border: 1px solid black;">{$abnormal_yes}</td>
        <td style="width: 18.5%;">Yes (pls. give details)</td>
        <td style="width: 30%; border-bottom: 1px solid black"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 40%; text-align:left; font-size: 10pt;"></td>
        <td style="width: 2.7%; border: 1px solid black;">{$abnormal_no}</td>
        <td>No</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 23%; text-align:left; font-size: 10pt;">
            f. Previous pregnancy?
        </td>
         <td style="width: 2.7%; border: 1px solid black;">$has_pregnancy</td>
        <td style="width: 41%;">Yes (number, normal/ C-section, home/hospital, etc)</td>
        <td style="width: 27%; border-bottom: 1px solid black"><?= $pregnancyDetails ?? '' ?></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 23%; text-align:left; font-size: 10pt;"></td>
        <td style="width: 2.7%; border: 1px solid black;">$no_pregnancy</td>
        <td>No</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 15%; text-align:left; font-size: 10pt;">
            g. Children?
        </td>
         <td style="width: 2.6%; border: 1px solid black;">$has_children</td>
        <td style="width: 15%;">Yes (how many?)</td>
        <td style="width: 27%; border-bottom: 1px solid black"><?= $childrenCount ?? '' ?></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="width: 7%;"></td>
        <td colspan="1" style="width: 15%; text-align:left; font-size: 10pt;"></td>
        <td style="width: 2.6%; border: 1px solid black;">$no_children</td>
        <td>No</td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;">----------------------------------------------STUDENTS FILL UP FORM UNTIL HERE ONLY---------------------------------------------</td>
    </tr>
</table>
<br>
EOD;
    }
    // Make sure arrays aren't null
    $physicalExamination = $physicalExamination ?? [];

    // vitals (safe)
    $height = $physicalExamination['Height'] ?? '';
    $weight = $physicalExamination['Weight'] ?? '';
    $bmi    = $physicalExamination['BMI'] ?? '';
    $bp     = $physicalExamination['BP'] ?? '';
    $hr     = $physicalExamination['HR'] ?? '';
    $rr     = $physicalExamination['RR'] ?? '';
    $temp   = $physicalExamination['Temp'] ?? '';

    // Normal? (Yes/No/blank if missing)
    $genAppearanceText = array_key_exists('GenAppearanceAndSkinNormal', $physicalExamination)
        ? (($physicalExamination['GenAppearanceAndSkinNormal'] == 1) ? 'Yes' : 'No') : '';
    $headNeckText = array_key_exists('HeadAndNeckNormal', $physicalExamination)
        ? (($physicalExamination['HeadAndNeckNormal'] == 1) ? 'Yes' : 'No') : '';
    $checkBackText = array_key_exists('ChestAndBackNormal', $physicalExamination)
        ? (($physicalExamination['ChestAndBackNormal'] == 1) ? 'Yes' : 'No') : '';
    $abdomenText = array_key_exists('AbdomenNormal', $physicalExamination)
        ? (($physicalExamination['AbdomenNormal'] == 1) ? 'Yes' : 'No') : '';
    $extremitiesText = array_key_exists('ExtremitiesNormal', $physicalExamination)
        ? (($physicalExamination['ExtremitiesNormal'] == 1) ? 'Yes' : 'No') : '';
    $othersText = array_key_exists('OthersNormal', $physicalExamination)
        ? (($physicalExamination['OthersNormal'] == 1) ? 'Yes' : 'No') : '';

    // Findings (safe)
    $genAppearanceFindings = $physicalExamination['GenAppearanceAndSkinFindings'] ?? '';
    $headNeckFindings      = $physicalExamination['HeadAndNeckFindings'] ?? '';
    $chestBackFindings     = $physicalExamination['ChestAndBackFindings'] ?? '';
    $abdomenFindings       = $physicalExamination['AbdomenFindings'] ?? '';
    $extremitiesFindings   = $physicalExamination['ExtremitiesFindings'] ?? '';
    $othersFindings        = $physicalExamination['OthersFindings'] ?? '';

    $html .= <<<EOD
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            IV. PHYSICAL EXAMINATION
        </td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 2pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;" border="1">
    <tr>
       <td style="font-style: italic">Height (m)<br><span class="value">{$height}</span></td>
       <td style="font-style: italic">Weight (kg)<br><span class="value">{$weight}</span></td>
       <td style="font-style: italic">BMI (kg/m2)<br><span class="value">{$bmi}</span></td>
       <td style="font-style: italic">BP (mmHg)<br><span class="value">{$bp}</span></td>
       <td style="font-style: italic">HR (bpm)<br><span class="value">{$hr}</span></td>
       <td style="font-style: italic">RR (cpm)<br><span class="value">{$rr}</span></td>
       <td style="font-style: italic">Temp (C)<br><span class="value">{$temp}</span></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;" border="1">
     <tr>
       <td style="width: 30%"></td>
       <td style="width: 20%; text-align: center">Normal</td>
       <td style="width: 50%"></td>
    </tr>
    <tr>
       <td style="width: 30%">Gen. Appearance and Skin</td>
       <td style="width: 20%; text-align: center"><span class="value">{$genAppearanceText}</span></td>
       <td style="width: 50%"><span class="value">{$genAppearanceFindings}</span></td>
    </tr>
     <tr>
       <td style="width: 30%">Head and Neck</td>
       <td style="width: 20%; text-align: center"><span class="value">{$headNeckText}</span></td>
       <td style="width: 50%"><span class="value">{$headNeckFindings}</span></td>
    </tr>
     <tr>
       <td style="width: 30%">Chest and Back</td>
       <td style="width: 20%; text-align: center"><span class="value">{$checkBackText}</span></td>
       <td style="width: 50%"><span class="value">{$chestBackFindings}</span></td>
    </tr>
     <tr>
       <td style="width: 30%">Abdomen</td>
       <td style="width: 20%; text-align: center"><span class="value">{$abdomenText}</span></td>
       <td style="width: 50%"><span class="value">{$abdomenFindings}</span></td>
    </tr>
     <tr>
       <td style="width: 30%">Extremities</td>
       <td style="width: 20%; text-align: center"><span class="value">{$extremitiesText}</span></td>
       <td style="width: 50%"><span class="value">{$extremitiesFindings}</span></td>
    </tr>
    <tr>
       <td style="width: 30%">Others</td>
       <td style="width: 20%; text-align: center"><span class="value">{$othersText}</span></td>
       <td style="width: 50%"><span class="value">{$othersFindings}</span></td>
    </tr>
</table>

<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            V. DIAGNOSTIC RESULTS: (Pls. include date of examination)
        </td>
    </tr>
</table>
<!-- Chest X-ray -->
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <br>
        <td colspan="2" style="text-align:left; font-weight: bold; font-size: 10pt;">
            V. DIAGNOSTIC RESULTS: (Pls. include date of examination)
        </td>
    </tr>
</table>
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <td style="width: 4%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$xray_check}</td>
       <td style="width: 1%"></td>
       <td style="width: 12%">Chest X-ray:</td>
       <td style="width: 45%; border-bottom: 1px solid black">
           <span class="value">{$chestXrayResult}</span>
       </td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>

<!-- Impression -->
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
        <td colspan="2" style="width: 18%; text-align:left; font-weight: bold; font-size: 10pt;">VI. IMPRESSION:</td>
        <td style="width: 82%; border-bottom: 1px solid black">
            <span class="value">{$impression}</span>
        </td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>

<!-- Plan -->
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
        <td colspan="2" style="width: 12%; text-align:left; font-weight: bold; font-size: 10pt;">VII. PLAN:</td>
    </tr>
</table>

<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <td style="width: 4%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$diagnostic_check}</td>
       <td style="width: 1%"></td>
       <td style="width: 11%">Diagnostic:</td>
       <td style="width: 35%; border-bottom: 1px solid black">
           <span class="value">{$discussionDetails}</span>
       </td>

       <td style="width: 10%"></td>
       <td style="width: 2.7%; border: 1px solid black;"></td>
       <td style="width: 1%"></td>
       <td style="width: 8%">Advice:</td>
       <td style="width: 25%; border-bottom: 1px solid black">
           <span class="value">{$abbreviationsUsed}</span>
       </td>
    </tr>

    <tr><td style="font-size: 2.5px;"></td></tr>

    <tr>
       <td style="width: 4%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$homeMed_Check}</td>
       <td style="width: 1%"></td>
       <td style="width: 17%">Home Medication:</td>
       <td style="width: 29%; border-bottom: 1px solid black">
           <span class="value">{$homeMedication}</span>
       </td>

       <td style="width: 10%"></td>
       <td style="width: 2.7%; border: 1px solid black;"></td>
       <td style="width: 1%"></td>
       <td style="width: 10%">F-f (Date):</td>
       <td style="width: 23%; border-bottom: 1px solid black">
           <span class="value">{$followUpDate}</span>
       </td>
    </tr>

    <tr><td style="font-size: 2.5px;"></td></tr>

    <tr>
       <td style="width: 4%"></td>
       <td style="width: 49.6%; border-bottom: 1px solid black"></td>

       <td style="width: 10%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$medCert_Isseud_check}</td>
       <td style="width: 1%"></td>
       <td style="width: 30%">Medical Certificate issued:</td>
    </tr>

    <tr><td style="font-size: 2.5px;"></td></tr>

    <tr>
       <td style="width: 4%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$homeInstructions_Check}</td>
       <td style="width: 1%"></td>
       <td style="width: 17%">Home Instructions:</td>
       <td style="width: 29%; border-bottom: 1px solid black">
           <span class="value">{$homeInstructions}</span>
       </td>

       <td style="width: 10%"></td>
       <td style="width: 2.7%; border: 1px solid black;"></td>
       <td style="width: 1%"></td>
       <td style="width: 9%">Referred:</td>
       <td style="width: 24%; border-bottom: 1px solid black">
           <span class="value">{$referred}</span>
       </td>
    </tr>
</table>
<table width="100%" style="font-size: 9pt; margin-bottom: 10px; border-collapse: collapse;" border="0">
    <tr>
        <td style="font-size: 10pt;"></td>
    </tr>
</table>

<!-- Recommendation -->
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <td style="width: 20%">Recommendation:</td>
       <td style="width: 2.7%; border: 1px solid black;">{$fit_enroll_work}</td>
       <td style="width: 25%">Fit to Enroll/Work</td>
       <td style="width: 2.7%; border: 1px solid black;">{$fit_enroll_eval}</td>
       <td style="width: 50%">Fit to Enroll but requires further evaluation</td>
    </tr>

    <tr><td style="font-size: 2.5px;"></td></tr>

    <tr>
       <td style="width: 20%"></td>
       <td style="width: 2.7%;"></td>
       <td style="width: 25%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$fit_work_eval}</td>
       <td style="width: 50%">Fit to Work but requires further evaluation</td>
    </tr>

    <tr><td style="font-size: 2.5px;"></td></tr>

    <tr>
       <td style="width: 20%"></td>
       <td style="width: 2.7%; border: 1px solid black;">{$fit_sports}</td>
       <td style="width: 25%">Fit to Participate in Sports</td>
       <td style="width: 2.7%; border: 1px solid black;">{$fit_sports_eval}</td>
       <td style="width: 50%">Fit to Participate in Sports but requires further evaluation</td>
    </tr>
</table>

<br><br><br>

<!-- Physician -->
<table width="100%" style="font-size: 10pt; margin-bottom: 10px; border-collapse: collapse;">
    <tr>
       <td style="width: 33%; border-bottom: 1px solid black; text-align: center;">
           <span class="value">{$physicianName}</span>
       </td>
    </tr>
    <tr><td style="font-size: 2.5px;"></td></tr>
    <tr>
       <td style="width: 35%; font-size: 10.5px; font-weight: bold">Physician's Name and Signature</td>
    </tr>
    <tr>
        <td style="width: 7%">Lic No:</td>
        <td style="width: 25%; border-bottom: 1px solid black">
            <span class="value">{$licenseNo}</span>
        </td>
    </tr>
    <tr>
        <td style="width: 6%">Date:</td>
        <td style="width: 26%; border-bottom: 1px solid black">
            <span class="value">{$signatureDate}</span>
        </td>
    </tr>
    <br>
    <tr>
        <td style="width: 100%; font-size: 9.5px;">{$institution}</td>
    </tr>
</table>
EOD;
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Patient_Record.pdf', 'I');
} catch (Exception $e) {
    echo 'PDF generation failed: ' . $e->getMessage();
}
