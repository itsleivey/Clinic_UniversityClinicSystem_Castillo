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

// Map ClientType to user-friendly label
switch ($client['ClientType']) {
    case 'Faculty':
        $displayType = 'Teaching Personnel';
        break;
    case 'Personnel':
    case 'NewPersonnel':
        $displayType = 'Non-Teaching Personnel';
        break;
    default:
        $displayType = $client['ClientType'];
        break;
}

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
    <title>Personnel Profile | University Clinic</title>
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
                    <div class="info-item"><span class="info-label">Client Type:</span> <span class="info-value"><?= htmlspecialchars($displayType) ?></span></div>
                    <div class="info-item"><span class="info-label">Department:</span> <span class="info-value"><?= htmlspecialchars($client['Department']) ?></span></div>
                </div>
            </div>

            <!-- CONSULTATION RECORDS -->
            <!-- HISTORY TABLE
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

            </div> -->
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


            <!-- FILE UPLOAD -->
            <!-- FILE UPLOAD -->
            <div class="card">
                <h3>Upload Annual Examination File</h3>
                <div class="upload-section">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="file" name="exam_files[]" id="exam_file" accept=".pdf,.doc,.docx,.jpg,.png" multiple required>

                        <label for="exam_file"><i class="fas fa-upload"></i> Choose File</label>
                        <br><br>
                        <button type="button" id="uploadBtn">Upload</button>
                    </form>
                </div>

                <div id="fileOverview" style="margin-top:20px;"></div>

                <button id="viewHistoryBtn" class="btn-secondary" style="margin-top:15px;">📁 View Uploaded Files History</button>
            </div>

            <!-- Confirm Modal -->
            <div id="confirmModal" class="messagemodal">
                <div class="messagemodal-content">
                    <h2>Confirm Submission</h2>
                    <p>Are you sure the selected file is correct?</p>
                    <div class="messagemodal-buttons">
                        <button id="confirmYes" class="btn-primary">Yes, Submit</button>
                        <button id="confirmNo" class="btn-secondary">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div id="successModal" class="messagemodal">
                <div class="messagemodal-content">
                    <h2>✅ Uploaded Successfully!</h2>
                    <p>Your file has been saved.</p>
                    <div class="messagemodal-buttons">
                        <button id="successClose" class="btn-primary">OK</button>
                    </div>
                </div>
            </div>

            <!-- File History Modal -->
            <div id="historyModal" class="modal">
                <div class="modal-content" style="max-width:800px;">
                    <div class="modal-header">
                        <h3>📄 Uploaded Files History</h3>
                        <span class="close-btn" id="closeHistory">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div id="historyContent" style="max-height:400px; overflow-y:auto;">
                            <table style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="padding:10px; color: #206bbb; background:#f1f7ff; text-align:left;">File Name</th>
                                        <th style="padding:10px; color: #206bbb;background:#f1f7ff; text-align:left;">Type</th>
                                        <th style="padding:10px; color: #206bbb;background:#f1f7ff; text-align:left;">Size</th>
                                        <th style="padding:10px; color: #206bbb;background:#f1f7ff; text-align:left;">Date</th>
                                        <th style="padding:10px; color: #206bbb;background:#f1f7ff; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <!-- Files will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Preview Modal -->
            <div id="filePreviewModal" class="modal">
                <div class="modal-content large">
                    <div class="modal-header">
                        <h2>📄 File Preview</h2>
                        <span class="close-btn" data-close="filePreviewModal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <iframe id="pdfViewer" width="100%" height="650px" style="border: none; display: none;"></iframe>
                        <!-- Preview container for non-PDF files will be dynamically inserted here -->
                    </div>
                </div>
            </div>

        </div>
        <script>
            // --- ELEMENT REFERENCES ---
            const uploadBtn = document.getElementById('uploadBtn');
            const confirmModal = document.getElementById('confirmModal');
            const successModal = document.getElementById('successModal');
            const confirmYes = document.getElementById('confirmYes');
            const confirmNo = document.getElementById('confirmNo');
            const successClose = document.getElementById('successClose');
            const uploadForm = document.getElementById('uploadForm');
            const fileOverview = document.getElementById('fileOverview');
            const viewHistoryBtn = document.getElementById('viewHistoryBtn');
            const historyModal = document.getElementById('historyModal');
            const closeHistory = document.getElementById('closeHistory');
            const filePreviewModal = document.getElementById('filePreviewModal');
            const pdfViewer = document.getElementById('pdfViewer');
            const detailsModal = document.getElementById('detailsModal');
            const modalData = document.getElementById('modalData');
            const closeDetails = detailsModal.querySelector('.close-btn');

            // --- SHOW SELECTED FILE INFO ---
            document.getElementById('exam_file').addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    fileOverview.innerHTML = `
            <div style="font-family:Segoe UI,sans-serif; border:1px solid #ddd; border-radius:8px; padding:15px; background: #d0ffde;">
                <h4 style="color:black; margin-bottom:10px;">Selected File</h4>
                <p><strong>File Name:</strong> ${file.name}</p>
                <p><strong>File Size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                <p><strong>File Type:</strong> ${file.type || 'Unknown'}</p>
            </div>
        `;
                }
            });

            // --- FILE UPLOAD FLOW ---
            uploadBtn.onclick = () => {
                if (!document.getElementById('exam_file').value) {
                    alert('Please select a file first.');
                    return;
                }
                confirmModal.style.display = 'flex';
            };

            confirmNo.onclick = () => confirmModal.style.display = 'none';

            confirmYes.onclick = () => {
                confirmModal.style.display = 'none';
                const formData = new FormData(uploadForm);

                fetch('upload_annual_exam.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            successModal.style.display = 'flex';
                            fileOverview.innerHTML = `
                    <div style="font-family:Segoe UI,sans-serif; border:1px solid #4CAF50; border-radius:8px; padding:15px; background:#d0ffde;">
                        <h4 style="color:#4CAF50; margin-bottom:10px;">✅ Upload Successful</h4>
                        <p><strong>File Name:</strong> ${data.file_name}</p>
                        <p><strong>File Size:</strong> ${data.file_size} KB</p>
                        <p><strong>File Type:</strong> ${data.file_type}</p>
                        <p><strong>Upload Date:</strong> ${data.upload_date}</p>
                    </div>
                `;
                            uploadForm.reset();
                        } else {
                            alert(data.message || 'Upload failed.');
                        }
                    })
                    .catch(() => alert('Error uploading file.'));
            };

            successClose.onclick = () => successModal.style.display = 'none';

            // --- VIEW FILE HISTORY ---
            viewHistoryBtn.onclick = () => {
                historyModal.style.display = 'block';
                loadFileHistory();
            };

            closeHistory.onclick = () => historyModal.style.display = 'none';

            // --- LOAD FILE HISTORY ---
            function loadFileHistory() {
                fetch('fetch_exam_history.php')
                    .then(response => response.json())
                    .then(files => {
                        const tbody = document.getElementById('historyTableBody');

                        if (!files.length) {
                            tbody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#666;">
                            No files uploaded yet.
                        </td>
                    </tr>
                `;
                            return;
                        }

                        tbody.innerHTML = files.map(file => `
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:12px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:18px;">${file.icon}</span>
                            <div>
                                <div style="font-weight:500;">${file.file_name}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px;">${file.file_type.split('/')[1]?.toUpperCase() || 'FILE'}</td>
                    <td style="padding:12px;">${file.file_size_formatted}</td>
                    <td style="padding:12px;">${file.upload_date_formatted}</td>
                    <td style="padding:12px; text-align:center;">
                        <button class="view-btn" data-file-path="${file.file_path}" data-file-name="${file.file_name}">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </td>
                </tr>
            `).join('');
                    })
                    .catch(err => {
                        console.error(err);
                        document.getElementById('historyTableBody').innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; padding:20px; color:#d32f2f;">
                        Error loading file history.
                    </td>
                </tr>
            `;
                    });
            }

            // --- FILE PREVIEW HANDLER ---
            // --- FILE PREVIEW HANDLER ---
            document.getElementById('historyContent').addEventListener('click', function(e) {
                const btn = e.target.closest('.view-btn');
                if (!btn) return;

                // Prevent triggering if it's the consultation table (safety)
                if (btn.closest('#historyTable')) return;

                const filePath = btn.getAttribute('data-file-path');
                const fileName = btn.getAttribute('data-file-name');

                // Determine how to handle different file types
                handleFilePreview(filePath, fileName);
            });

            // --- FILE PREVIEW LOGIC ---
            function handleFilePreview(filePath, fileName) {
                const fileExtension = fileName.split('.').pop().toLowerCase();

                // Clear previous content
                pdfViewer.style.display = 'none';

                // Create preview container if it doesn't exist
                let previewContainer = document.getElementById('previewContainer');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.id = 'previewContainer';
                    previewContainer.style.width = '100%';
                    previewContainer.style.height = '650px';
                    previewContainer.style.display = 'flex';
                    previewContainer.style.flexDirection = 'column';
                    previewContainer.style.alignItems = 'center';
                    previewContainer.style.justifyContent = 'center';
                    previewContainer.style.background = '#f5f5f5';
                    previewContainer.style.border = '1px solid #ddd';

                    const modalBody = filePreviewModal.querySelector('.modal-body');
                    modalBody.appendChild(previewContainer);
                }

                previewContainer.innerHTML = ''; // Clear previous content

                switch (fileExtension) {
                    case 'pdf':
                        // Show PDF in iframe
                        pdfViewer.style.display = 'block';
                        pdfViewer.src = filePath;

                        break;

                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'bmp':
                    case 'webp':
                        // Show images
                        const img = document.createElement('img');
                        img.src = filePath;
                        img.style.maxWidth = '100%';
                        img.style.maxHeight = '100%';
                        img.style.objectFit = 'contain';
                        img.alt = fileName;
                        previewContainer.appendChild(img);
                        break;

                    case 'txt':
                        // Show text files
                        fetch(filePath)
                            .then(response => response.text())
                            .then(text => {
                                const pre = document.createElement('pre');
                                pre.style.width = '100%';
                                pre.style.height = '100%';
                                pre.style.padding = '20px';
                                pre.style.overflow = 'auto';
                                pre.style.background = 'white';
                                pre.style.margin = '0';
                                pre.textContent = text;
                                previewContainer.appendChild(pre);
                            })
                            .catch(error => {
                                previewContainer.innerHTML = `
                        <div style="text-align: center; color: #666;">
                            <p>❌ Unable to load text file</p>
                            <p style="font-size: 14px;">${error.message}</p>
                        </div>
                    `;
                            });
                        break;

                    case 'doc':
                    case 'docx':
                        // For Word documents - provide download option
                        previewContainer.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📄</div>
                    <h3>Word Document</h3>
                    <p style="color: #666; margin-bottom: 20px;">"${fileName}"</p>
                    <p style="color: #888; margin-bottom: 30px;">Word documents can be downloaded and opened in Microsoft Word or compatible software.</p>
                    <button onclick="downloadFile('${filePath}', '${fileName}')" 
                            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        📥 Download File
                    </button>
                </div>
            `;
                        break;

                    case 'xls':
                    case 'xlsx':
                        // For Excel files
                        previewContainer.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                    <h3>Excel Spreadsheet</h3>
                    <p style="color: #666; margin-bottom: 20px;">"${fileName}"</p>
                    <p style="color: #888; margin-bottom: 30px;">Excel files can be downloaded and opened in Microsoft Excel or compatible software.</p>
                    <button onclick="downloadFile('${filePath}', '${fileName}')" 
                            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        📥 Download File
                    </button>
                </div>
            `;
                        break;

                    default:
                        // For unsupported file types
                        previewContainer.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📁</div>
                    <h3>File Preview Not Available</h3>
                    <p style="color: #666; margin-bottom: 20px;">"${fileName}"</p>
                    <p style="color: #888; margin-bottom: 30px;">Preview is not available for .${fileExtension} files. You can download the file to view it.</p>
                    <button onclick="downloadFile('${filePath}', '${fileName}')" 
                            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        📥 Download File
                    </button>
                </div>
            `;
                }

                // Show the modal
                filePreviewModal.style.display = 'block';
            }

            // --- DOWNLOAD FILE FUNCTION ---
            function downloadFile(filePath, fileName) {
                const link = document.createElement('a');
                link.href = filePath;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // --- UPDATE MODAL CLOSE BUTTON ---
            filePreviewModal.querySelector('.close-btn').onclick = () => {
                filePreviewModal.style.display = 'none';
                pdfViewer.src = '';
                pdfViewer.style.display = 'none';

                // Clear preview container
                const previewContainer = document.getElementById('previewContainer');
                if (previewContainer) {
                    previewContainer.innerHTML = '';
                }
            };

            // --- UPDATE WINDOW CLICK HANDLER ---
            window.onclick = function(event) {
                if (event.target === historyModal) historyModal.style.display = 'none';
                if (event.target === filePreviewModal) {
                    filePreviewModal.style.display = 'none';
                    pdfViewer.src = '';
                    pdfViewer.style.display = 'none';

                    // Clear preview container
                    const previewContainer = document.getElementById('previewContainer');
                    if (previewContainer) {
                        previewContainer.innerHTML = '';
                    }
                }
                if (event.target === confirmModal) confirmModal.style.display = 'none';
                if (event.target === successModal) successModal.style.display = 'none';
                if (event.target === detailsModal) {
                    detailsModal.style.display = 'none';
                    modalData.innerHTML = '';
                }
            };

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

            filePreviewModal.querySelector('.close-btn').onclick = () => {
                filePreviewModal.style.display = 'none';
                pdfViewer.src = '';
            };

            // --- CLOSE MODALS BY CLICKING OUTSIDE ---
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





            .messagemodal {
                display: none;
                position: fixed;
                z-index: 999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                justify-content: center;
                align-items: center;
            }

            .messagemodal-content {
                background: #fff;
                padding: 25px 35px;
                border-radius: 10px;
                text-align: center;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                animation: fadeIn 0.3s ease;
            }

            .messagemodal-buttons {
                margin-top: 20px;
                display: flex;
                justify-content: center;
                gap: 10px;
            }

            .btn-primary {
                background: #2767c0;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
            }

            .btn-secondary {
                background: #ddd;
                color: #333;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
            }

            /* FILE PREVIEW MODAL STYLES */
            .modal-content.large {
                width: 95%;
                max-width: 1200px;
                height: 90vh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .modal-body {
                flex: 1;
                padding: 0;
                overflow: hidden;
                background: #f9fbff;
            }

            /* Make preview fill modal body properly */
            #pdfViewer {
                width: 100%;
                height: 100%;
                display: block;
                border: none;
            }

            /* Preview container for images and other files */
            #previewContainer {
                width: 100%;
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                background: #f5f5f5;
                padding: 0;
                margin: 0;
            }

            /* Keep images perfectly scaled */
            #previewContainer img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            /* Text file preview */
            #previewContainer pre {
                width: 100%;
                height: 100%;
                padding: 20px;
                overflow: auto;
                background: white;
                margin: 0;
                box-sizing: border-box;
            }

            /* Download button container */
            #previewContainer>div {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;
                box-sizing: border-box;
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
        <script src="UC-Client/assets/js/new_profile_function.js" defer></script>
</body>

</html>