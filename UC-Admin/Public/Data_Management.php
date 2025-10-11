<?php
require_once 'config/database.php';
$pdo = pdo_connect_mysql();

try {
    $stmt = $pdo->prepare("SELECT * FROM backup_logs ORDER BY id ASC");
    $stmt->execute(); // ✅ You need to execute the query
    $historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching history data: " . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="assets/css/dashboardpagestyles.css">
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="assets/css/data_management.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard_func.js" defer></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <script src="assets/js/dashcalendar.js" defer></script>
    <script src="assets/js/dashgraph.js" defer></script>
    <title>Manage Profile</title>
</head>

<body>
    <div class="header">
        <img src="assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title"> University Clinic </span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="assets/images/menu-icon.svg">
        </button>
        <div class="page-title">
            <h4>Data Management</h4>
        </div>
    </div>

    <div class="main-container">
        <nav class="navbar">
            <a href="Dashboard.php">
                <button class="buttons" id="dashboardBtn">
                    <img src="assets/images/dashboard_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Dashboard</span>
                </button>
            </a>
            <a href="Manage_Clients.php">
                <button class="buttons" id="manageclientsBtn">
                    <img src="assets/images/manageclients_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Manage Patients</span>
                </button>
            </a>
            <a href="Data_Management.php">
                <button class="buttons" id="datamanagementBtn">
                    <img src="assets/images/data_manage_icon_active.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Data Management</span>
                </button>
            </a>
            <a href="index.php">
                <button class="buttons" id="logoutbtn">
                    <img src="assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </a>
        </nav>

        <main class="content">
            <div class="content-body">
                <div class="data-management-options">

                    <!-- Backup Card -->
                    <a href="javascript:void(0);" onclick="showBackupModal()" class="data-management-link">
                        <div class="data-management-card">
                            <span class="icon-span"><i class="fas fa-file-export"></i> Backup Data</span>
                            <p class="p-tag">Create a backup of the database to ensure data safety and recovery options.</p>
                        </div>
                    </a>

                    <!-- Restore Card -->
                    <a href="javascript:void(0);" onclick="showRestoreModal()" class="data-management-link">
                        <div class="data-management-card">
                            <span class="icon-span"><i class="fas fa-database"></i> Restore Data</span>
                            <p class="p-tag">Restore the database from a previously created backup file.</p>
                        </div>
                    </a>

                    <!-- Hidden Restore Form -->
                    <form id="restoreForm" action="restore.php" method="post" enctype="multipart/form-data" style="display:none;">
                        <input type="file" id="restoreInput" name="backup_file" accept=".sql">
                        <input type="hidden" name="restore" value="1">
                        <button type="submit">Restore</button>
                    </form>



                </div>

            </div>
            <?php if (!empty($historyData)): ?>
                <div class="backup-history">
                    <h2 style="margin-bottom: 10px; color:#005b99;"><i class="fas fa-file-export"></i> Backup History</h2>

                    <div class="table-wrapper">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyData as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row['file_name']) ?></td>
                                        <td><?= htmlspecialchars($row['backup_date']) ?></td>
                                        <td><?= htmlspecialchars($row['backup_time']) ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'success'): ?>
                                                <span class="status success">Success</span>
                                            <?php else: ?>
                                                <span class="status failed">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'success'): ?>
                                                <a href="backups/<?= htmlspecialchars($row['file_name']) ?>" class="btn-download">Download</a>
                                            <?php else: ?>
                                                <span class="muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p class="no-records">No backup records found.</p>
            <?php endif; ?>



        </main>

        <!-- Status Modal -->
        <script>
            document.getElementById("restoreInput").addEventListener("change", function() {
                const form = document.getElementById("restoreForm");
                const formData = new FormData(form);

                fetch("restore.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.msg); // ✅ only alert, no redirect
                    })
                    .catch(err => {
                        alert("❌ Restore error: " + err);
                    });
            });
        </script>




        <!-- ⚠️ Custom Modal for Restore Confirmation -->
        <div id="restoreWarningModal" class="modal-overlay">
            <div class="modal-box">
                <h2>⚠️ Restore Database</h2>
                <p>
                    Restoring a backup will <strong>overwrite all current data</strong>.<br>
                    Any new records created <em>after</em> this backup will be <strong>lost permanently</strong>.
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button class="btn-confirm" onclick="confirmRestore()">Yes, Restore</button>
                </div>
            </div>
        </div>

        <div id="backupWarningModal" class="modal-overlay">
            <div class="modal-box">
                <h2>💾 Backup Database</h2>
                <p>
                    This will create a backup of the <strong>current database</strong>.<br>
                    Make sure you save it in a secure location.
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeBackupModal()">Cancel</button>
                    <button class="btn-confirm" onclick="confirmBackup()">Yes, Backup</button>
                </div>
            </div>
        </div>


        <script>
            // ✅ Backup Function
            function runBackup() {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "back_up.php", true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        let response = xhr.responseText.trim();
                        console.log("back_up.php response:", response);

                        if (response.startsWith("success")) {
                            let parts = response.split("|");
                            let file = parts[1];
                            alert("✅ Backup created successfully!");
                            window.location.href = file; // download
                        } else {
                            alert("❌ Backup failed!\n\nResponse: " + response);
                        }
                    } else {
                        alert("❌ Request failed. Status: " + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    alert("❌ Network error calling backup.php");
                };
                xhr.send();
            }

            // ✅ Restore Confirmation Modal
            const restoreInput = document.getElementById("restoreInput");
            const restoreForm = document.getElementById("restoreForm");
            const modal = document.getElementById("restoreWarningModal");

            // Show modal first when clicking Restore
            function showRestoreModal() {
                modal.style.display = "flex";
            }

            function closeModal() {
                modal.style.display = "none";
            }

            function confirmRestore() {
                modal.style.display = "none";
                restoreInput.click(); // open file picker
            }

            // After file chosen → submit form


            const backupModal = document.getElementById("backupWarningModal");

            // Show modal first when clicking Backup
            function showBackupModal() {
                backupModal.style.display = "flex";
            }

            function closeBackupModal() {
                backupModal.style.display = "none";
            }

            function confirmBackup() {
                backupModal.style.display = "none";
                runBackup(); // now actually run the backup
            }
        </script>

        <style>
            /* General Page Style */
            body {
                overflow: hidden;
            }

            .content-body {
                display: flex;
                flex-direction: row;
                height: 195px;
                justify-content: center;
                width: 100%;
                border: solid #ccc 1px;
                border-radius: 5px;
                background-color: white;
                padding: 20px;
                box-sizing: border-box;
            }

            /* Options Grid */
            .data-management-options {
                display: flex;
                flex-direction: row;
                gap: 25px;
            }

            /* Card Style */
            .data-management-card {
                background: white;
                border-radius: 18px;
                padding: 45px 20px;
                text-align: center;
                box-shadow: 0 6px 20px rgba(0, 122, 204, 0.1);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
                cursor: pointer;
            }

            .data-management-card h3 {
                font-size: 20px;
                color: #005b99;
                margin-bottom: 10px;
            }

            .data-management-card p {
                font-size: 14px;
                color: #555;
                line-height: 1.5;
            }

            /* Hover Effects */
            .data-management-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 10px 25px rgba(0, 122, 204, 0.2);
                background: #e6f5ff;
            }

            .data-management-link {
                text-decoration: none;
                color: inherit;
            }

            .icon-span {
                font-size: clamp(18px, 2.5vw, 22px);
            }

            .p-tag {
                padding-top: 15px;
            }

            /* ⚠️ Modal Styles */
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .modal-box {
                background: #fff;
                padding: 25px 30px;
                border-radius: 12px;
                width: 400px;
                max-width: 90%;
                text-align: center;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
                animation: scaleUp 0.3s ease;
            }

            .modal-box h2 {
                margin-bottom: 15px;
                font-size: 20px;
                color: #d9534f;
            }

            .modal-box p {
                font-size: 14px;
                color: #444;
                margin-bottom: 20px;
                line-height: 1.4;
            }

            .modal-actions {
                display: flex;
                justify-content: space-between;
                gap: 15px;
            }

            .btn-cancel,
            .btn-confirm {
                flex: 1;
                padding: 10px 15px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                transition: background 0.25s;
            }

            .btn-cancel {
                background: #ccc;
                color: #333;
            }

            .btn-cancel:hover {
                background: #b3b3b3;
            }

            .btn-confirm {
                background: #d9534f;
                color: white;
                font-weight: bold;
            }

            .btn-confirm:hover {
                background: #c9302c;
            }

            @keyframes scaleUp {
                from {
                    transform: scale(0.9);
                    opacity: 0;
                }

                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        </style>

</body>

</html>