<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
$stmt2 = $pdo->prepare("SELECT * FROM consultationrecords WHERE ClientID = ? ORDER BY datecreated DESC");
$stmt2->execute([$clientId]);
$consultations = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Profile | University Clinic</title>
    <link rel="stylesheet" href="UC-Client/assets/css/new_profile_style.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #eef3fc;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 40px;
            transition: all 0.3s ease;
        }

        .card {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 25px;
        }

        .card h3 {
            color: #0056b3;
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
            background-color: #0056b3;
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
            background-color: #0056b3;
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
            background-color: #0056b3;
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
    </style>
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
            <h4>Personnel Profile</h4>
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
            <div class="card">
                <h3>Consultation Records</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>BP</th>
                                <th>HR/PR</th>
                                <th>Temp</th>
                                <th>O₂ Sat</th>
                                <th>Assessment</th>
                                <th>Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($consultations): ?>
                                <?php foreach ($consultations as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['datecreated']) ?></td>
                                        <td><?= htmlspecialchars($row['BP']) ?></td>
                                        <td><?= htmlspecialchars($row['HR_PR']) ?></td>
                                        <td><?= htmlspecialchars($row['Temp']) ?></td>
                                        <td><?= htmlspecialchars($row['O2sat']) ?></td>
                                        <td><?= htmlspecialchars($row['Assesment']) ?></td>
                                        <td><?= htmlspecialchars($row['Plan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FILE UPLOAD -->
            <div class="card">
                <h3>Upload Annual Examination File</h3>
                <div class="upload-section">
                    <form action="upload_annual_exam.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="exam_file" id="exam_file" accept=".pdf,.doc,.docx,.jpg,.png" required>
                        <label for="exam_file"><i class="fas fa-upload"></i> Choose File</label>
                        <br><br>
                        <button type="submit">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="UC-Client/assets/js/new_profile_function.js" defer></script>
</body>

</html>