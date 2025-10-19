<?php
require_once __DIR__ . '/../config/database.php';
$pdo = pdo_connect_mysql();

if (isset($_GET['historyID'])) {
    $historyID = $_GET['historyID'];

    $stmt1 = $pdo->prepare("SELECT * FROM consultationrecords WHERE historyid = ?");
    $stmt1->execute([$historyID]);
    $consultations = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT * FROM prescriptions WHERE historyID = ?");
    $stmt2->execute([$historyID]);
    $prescriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    .record-block {
        margin-bottom: 25px;
        padding: 20px;
        border-radius: 8px;
        background: #f8f9fa;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .form-group label {
        width: 180px;
        font-weight: 600;
        color: #004aad;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
        resize: none;
    }

    .form-group input[readonly],
    .form-group textarea[readonly] {
        background-color: #f5f5f5;
        color: #333;
        cursor: not-allowed;
    }

    h4 {
        color: #0056b3;
        margin-bottom: 15px;
    }
</style>

<?php if ($consultations || $prescriptions): ?>
    <?php foreach ($consultations as $consul): ?>
        <div class="record-block">
            <h4>Consultation Summary</h4>
            <div class="form-group">
                <label>BP</label>
                <input type="text" value="<?= htmlspecialchars($consul['BP']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>HR/PR</label>
                <input type="text" value="<?= htmlspecialchars($consul['HR_PR']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Temperature</label>
                <input type="text" value="<?= htmlspecialchars($consul['Temp']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>O₂ Saturation</label>
                <input type="text" value="<?= htmlspecialchars($consul['O2sat']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Subjective</label>
                <textarea rows="3" readonly><?= htmlspecialchars($consul['Subjective']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Objective</label>
                <textarea rows="3" readonly><?= htmlspecialchars($consul['Objective']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Assessment</label>
                <textarea rows="3" readonly><?= htmlspecialchars($consul['Assesment']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Plan</label>
                <textarea rows="3" readonly><?= htmlspecialchars($consul['Plan']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Date Created</label>
                <input type="text" value="<?= htmlspecialchars($consul['datecreated']) ?>" readonly>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($prescriptions): ?>
        <div class="record-block">
            <h4>Prescription Summary</h4>
            <?php foreach ($prescriptions as $pres): ?>
                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" value="<?= htmlspecialchars($pres['patient_name']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="text" value="<?= htmlspecialchars($pres['age']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Impression</label>
                    <input type="text" value="<?= htmlspecialchars($pres['impression']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Physician</label>
                    <input type="text" value="<?= htmlspecialchars($pres['physician']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>License No</label>
                    <input type="text" value="<?= htmlspecialchars($pres['license_no']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea rows="3" readonly><?= htmlspecialchars($pres['notes']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Date Created</label>
                    <input type="text" value="<?= htmlspecialchars($pres['date_created']) ?>" readonly>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p>No consultation or prescription records found for this visit.</p>
<?php endif; ?>