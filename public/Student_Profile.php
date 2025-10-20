<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//All_Personnel_Profile.php
require_once __DIR__ . '/../config/database.php';
$pdo = pdo_connect_mysql();

// Redirect if no session
if (!isset($_SESSION['ClientID'])) {
    header("Location: register.php");
    exit();
}

$clientId = $_SESSION['ClientID'];

// Get personnel info
$stmt = $pdo->prepare("SELECT * FROM clients WHERE ClientID = ?");
$stmt->execute([$clientId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Get consultation records
// Get history records
$stmtHistory = $pdo->prepare("SELECT * FROM history WHERE ClientID = ? ORDER BY actionDate DESC, actionTime DESC");
$stmtHistory->execute([$clientId]);
$histories = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | University Clinic</title>
    <link rel="stylesheet" href="UC-Client/assets/css/new_profile_style.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

</head>

<body>
    <div class="header">
        <img src="UC-Client/assets/images/Lspu logo.png" alt="Logo">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title">University Clinic</span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="UC-Client/assets/images/menu.png">
        </button>
        <div class="page-title">
            <h4>Student Profile</h4>
        </div>

        <div class="profile-container">
            <img id="profileBtn" src="../uploads/<?= htmlspecialchars($client['profilePicturePath'] ?? 'profilepic2.png') ?>" alt="Profile Picture">
            <div class="profile-dropdown" id="profileDropdown">
                <div class="fixed-profile-item">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($client['Email']) ?>
                </div>
                <a href="settings.php">
                    <div class="profile-item"><i class="fas fa-cog"></i> Settings</div>
                </a>
                <div class="profile-item" onclick="document.getElementById('logoutForm').submit()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </div>
                <form id="logoutForm" action="logout.php" method="post"></form>
            </div>
        </div>
    </div>

    <div class="main-container">
        <nav class="navbar">
            <a href="All_Personnel_Profile.php">
                <button class="active-buttons"><i class="fas fa-user"></i><span class="nav-text">Profile</span></button>
            </a>
            <a href="settings.php">
                <button class="buttons"><i class="fas fa-cog"></i><span class="nav-text">Settings</span></button>
            </a>
        </nav>

        <div class="content">
            <!-- PERSONAL INFO -->
            <div class="card">
                <h3>Personal Information</h3>
                <div class="info-grid">
                    <div class="info-item"><span class="info-label">Name:</span> <span class="info-value"><?= htmlspecialchars($client['Firstname'] . ' ' . $client['Lastname']) ?></span></div>
                    <div class="info-item"><span class="info-label">Email:</span> <span class="info-value"><?= htmlspecialchars($client['Email']) ?></span></div>
                    <div class="info-item"><span class="info-label">Sex:</span> <span class="info-value"><?= htmlspecialchars($client['Sex']) ?></span></div>
                    <div class="info-item"><span class="info-label">Birth Date:</span> <span class="info-value"><?= htmlspecialchars($client['BirthDate']) ?></span></div>
                    <div class="info-item"><span class="info-label">Client Type:</span> <span class="info-value"><?= htmlspecialchars($client['ClientType']) ?></span></div>
                    <div class="info-item"><span class="info-label">Department:</span> <span class="info-value"><?= htmlspecialchars($client['Department']) ?></span></div>
                    <div class="info-item"><span class="info-label">Course:</span> <span class="info-value"><?= htmlspecialchars($client['Course']) ?></span></div>
                </div>
            </div>

            <!-- CONSULTATION RECORDS -->
            <!-- HISTORY TABLE -->
            <div class="card">
                <h3>Consultation History</h3>
                <div class="table-container">
                    <table id="historyTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>View Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($histories): ?>
                                <?php foreach ($histories as $row): ?>
                                    <?php
                                    // Check if this historyID has consultation or prescription
                                    $hid = $row['historyID'];

                                    $hasConsultation = $pdo->prepare("SELECT COUNT(*) FROM consultationrecords WHERE historyid = ?");
                                    $hasConsultation->execute([$hid]);
                                    $consultExists = $hasConsultation->fetchColumn() > 0;

                                    $hasPrescription = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE historyID = ?");
                                    $hasPrescription->execute([$hid]);
                                    $rxExists = $hasPrescription->fetchColumn() > 0;

                                    if ($consultExists && $rxExists) {
                                        $type = "Consultation + Prescription";
                                    } elseif ($consultExists) {
                                        $type = "Consultation";
                                    } elseif ($rxExists) {
                                        $type = "Prescription";
                                    } else {
                                        $type = "Unknown";
                                    }
                                    ?>
                                    <tr data-historyid="<?= htmlspecialchars($row['historyID']) ?>">
                                        <td><?= htmlspecialchars($row['actionDate']) ?></td>
                                        <td><?= htmlspecialchars($row['actionTime']) ?></td>
                                        <td><?= htmlspecialchars($type) ?></td>
                                        <td><button class="view-btn">View</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">No history found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
            <!-- LARGE MODAL -->
            <div id="detailsModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-notes-medical"></i> Visit Details</h3>
                        <span class="close-btn">&times;</span>
                    </div>
                    <div class="modal-body" id="modalData">
                        <p>Loading details...</p>
                    </div>
                </div>
            </div>
            <script src="UC-Client/assets/js/new_profile_function.js" defer></script>
</body>
<script>
    const closeDetails = detailsModal.querySelector('.close-btn');


    // --- CONSULTATION HISTORY "VIEW" BUTTONS ---
    document.getElementById('historyTable').addEventListener('click', function(e) {
        if (e.target.classList.contains('view-btn')) {
            const tr = e.target.closest('tr');
            const historyID = tr.dataset.historyid;

            modalData.innerHTML = '<p>Loading details...</p>';
            detailsModal.style.display = 'block';

            fetch(`fetch_details.php?historyID=${historyID}`)
                .then(res => res.text())
                .then(data => modalData.innerHTML = data)
                .catch(err => {
                    modalData.innerHTML = '<p style="color:red;">Error loading details</p>';
                    console.error(err);
                });
        }
    });

    // --- MODAL CLOSE BUTTONS ---
    closeDetails.onclick = () => {
        detailsModal.style.display = 'none';
        modalData.innerHTML = '';
    };

    window.onclick = function(event) {
        if (event.target === historyModal) historyModal.style.display = 'none';
        if (event.target === filePreviewModal) {
            filePreviewModal.style.display = 'none';
            pdfViewer.src = '';
        }
        if (event.target === confirmModal) confirmModal.style.display = 'none';
        if (event.target === successModal) successModal.style.display = 'none';
        if (event.target === detailsModal) {
            detailsModal.style.display = 'none';
            modalData.innerHTML = '';
        }
    };
</script>
<style>
    body {
        background-color: #eef3fc;
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
    }

    .content {
        padding: 25px;
        transition: all 0.3s ease;
    }

    .card {
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-bottom: 5px;
    }

    p {
        font-family: "Inter", 'Segoe UI', sans-serif;
    }

    .card h3 {
        color: #397dda;
        border-bottom: 2px solid #e5e9f2;
        padding-bottom: 10px;
        margin-bottom: 20px;
        font-size: 20px;
    }

    /* PERSONAL INFO GRID */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px 40px;
    }

    .info-item {
        font-size: 15px;
    }

    .info-label {
        font-weight: 600;
        color: #333;
    }

    .info-value {
        color: #555;
    }

    /* TABLE STYLE */
    .table-container {
        overflow-x: auto;
        width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        min-width: 600px;
    }

    th,
    td {
        padding: 10px 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #397dda;
        color: #fff;
        font-weight: 600;
    }

    tr:hover {
        background-color: #f6f9ff;
    }

    /* UPLOAD SECTION */
    .upload-section {
        text-align: center;
        padding: 30px;
        border: 2px dashed #99b5e1;
        border-radius: 3px;
        background: #f9fbff;
    }

    .upload-section input[type=file] {
        display: none;
    }

    .upload-section label {
        background-color: #397dda;
        color: #fff;
        padding: 12px 25px;
        border-radius: 3px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: 500;
    }

    .upload-section label:hover {
        background-color: #003f8a;
    }

    .upload-section button {
        background-color: #397dda;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 3px;
        cursor: pointer;
        transition: 0.3s;
    }

    .upload-section button:hover {
        background-color: #003f8a;
    }

    /* RESPONSIVE DESIGN */
    @media (max-width: 992px) {
        .content {
            padding: 20px;
        }

        .card {
            padding: 20px;
        }

        th,
        td {
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .header .title {
            display: none;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .upload-section {
            padding: 20px;
        }

        .upload-section label,
        .upload-section button {
            width: 100%;
            display: block;
        }

        .page-title h4 {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .card h3 {
            font-size: 18px;
        }

        .info-item {
            font-size: 14px;
        }

        th,
        td {
            padding: 8px;
        }
    }

    /* MODAL */
    /* MODAL DESIGN */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow-y: hidden;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: #fff;
        margin: 30px auto;
        padding: 0;
        border-radius: 6px;
        width: 90%;
        max-width: 1000px;
        max-height: 700px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        animation: fadeIn 0.3s ease-in-out;
    }

    .modal-header {
        background: #397dda;
        color: #fff;
        padding: 16px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 6px 6px 0 0;
    }

    .modal-body {
        padding: 25px;
        background-color: #f9fbff;
        max-height: 80vh;
        overflow-y: auto;
    }

    .close-btn {
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        transition: 0.2s;
    }

    .close-btn:hover {
        color: #ffdddd;
    }

    /* TABLE STYLE INSIDE MODAL */
    .modal-body table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .modal-body th {
        background: #397dda;
        color: #fff;
        padding: 10px;
        text-align: left;
    }

    .modal-body td {
        padding: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
        vertical-align: top;
    }

    .modal-body tr:hover {
        background-color: #f3f7ff;
    }

    /* VIEW BUTTON */
    .view-btn {
        background-color: #397dda;
        color: #fff;
        border: none;
        padding: 7px 14px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: background-color 0.3s;
    }

    .view-btn:hover {
        background-color: #003f8a;
    }

    /* SMOOTH ANIMATION */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .modal-content.large {
        width: 95%;
        max-width: 1300px;
        height: 95%;
        overflow: hidden;
    }

    .modal-body {
        padding: 0;
    }


    body.modal-open {
        overflow: hidden;
        /* Prevent scrolling */
        padding-right: 15px;
        /* Optional: avoid layout shift if scrollbar disappears */
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

</html>