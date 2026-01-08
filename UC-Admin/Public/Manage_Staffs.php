<?php
require_once 'config/database.php';
require_once 'manageclients.dbf/get_user.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}


$pdo = pdo_connect_mysql();
if (isset($_GET['error'])) {
    echo '<div class="alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
}

try {
    $stmt = $pdo->prepare("SELECT * FROM admin");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error");
}



$pdo = pdo_connect_mysql();
// Get admin ID from session
$admin_id = $_SESSION['user_id'] ?? null;

// Query admin table safely
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = :adminID");
$stmt->execute(['adminID' => $admin_id]);

$current_user = $stmt->fetch(PDO::FETCH_ASSOC); // false if no row found

$role = $current_user['user_type'] ?? 'Admin';
$name = $current_user['username'] ?? '';
$email = $current_user['email'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="assets/css/manageusers.css">
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="assets/js/dashboard_func.js" defer></script>
    

    <style>
        @font-face {
            font-family: "Montserrat";
            src: url("assets/fonts/Montserrat/Montserrat-VariableFont_wght.ttf") format("woff2");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: "Poppins";
            src: url("assets/fonts/Poppins/Poppins-Medium.ttf") format("woff2");
            font-weight: 400;
            font-style: normal;
        }
    </style>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <title>Manage Staffs</title>
</head>
</head>

<body style="overflow:auto;">
    <div class="header">
        <img src="assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">LSPU-LBC</span>
            <span class="university_title"> University Clinic </span>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="assets/images/menu.png">
        </button>
        </button>
        <div class="page-title">
            <h4>Manage Staffs</h4>
        </div>
        <div class="profile-container">
            <img id="profileBtn" src="<?= htmlspecialchars(!empty($rec['profilePicturePath']) ? '../../uploads/' . $rec['profilePicturePath'] : '../../uploads/profilepic2.png') ?>" class="profile-pic" alt="Profile">
            <div class="profile-dropdown" id="profileDropdown">
                <div class="fixed-profile-item">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($current_user['username'] ?? 'Admin') ?>
                </div>
                <a href="Clinic_settings.php">
                    <div class="fixed-profile-item"><i class="fas fa-cog"></i> Settings</div>
                </a>
                <div class="profile-item" onclick="document.getElementById('logoutForm').submit()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </div>
                <form id="logoutForm" action="admin_logout.php" method="post"></form>
            </div>
        </div>
    </div>
    <script>
        const profileBtn = document.getElementById("profileBtn");
        const profileDropdown = document.getElementById("profileDropdown");

        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            profileDropdown.style.display =
                profileDropdown.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.style.display = "none";
            }
        });
    </script>

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
            <a href="Activity_logs.php">
                <button class="buttons" id="activitylogsBtn">
                    <img src="assets/images/activity_logs_icon.png" class="button-icon-nav" style="width: 23px; height: 23px" loading="lazy">
                    <span class="nav-text">Activity Logs</span>
                </button>
            </a>
            <a href="Manage_Staffs.php">
                <button class="buttons" id="managestaffsBtn">
                    <img src="assets/images/manageclients_icon2.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Manage Staffs</span>
                </button>
            </a>
            <a href="Data_Management.php">
                <button class="buttons" id="datamanagementBtn">
                    <img src="assets/images/data_manage_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Data Management</span>
                </button>
            </a>
            <!--
            <a href="Calendar.html">
                <button class="buttons" id="calendarBtn">
                    <img src="assets/images/calendar_icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Caledar</span>
                </button>
            </a>
    -->
            <a href="admin_logout.php">
                <button class="buttons" id="logoutbtn">
                    <img src="assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </a>
        </nav>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <main class="content" id="managestaff-page">
            <div id="staffViewContainer">
                <div class="clients-table-container">
                    <div class="table-header-controls">
                        <div class="table-left-controls">

                            <div class="search-input-container rectangular-search">
                                <div class="input-wrapper">
                                    <i class="fas fa-search search-icon-inset"></i>
                                    <input type="text"
                                        id="searchInput"
                                        name="id_filter"
                                        placeholder="Search ID, Name, Email..."
                                        value="<?= htmlspecialchars($_GET['id_filter'] ?? '') ?>"
                                        maxlength="400">
                                </div>
                            </div>
                            <div class="select-wrapper">
                                <i class="fas fa-filter"></i>
                                <select id="clientTypeDropdown" class="client-type-dropdown">
                                    <option value="All">All</option>
                                    <option value="Doctor">Doctor</option>
                                    <option value="Nurse">Nurse</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="add-btn" onclick="openAddStaffModal()">
                            <i class="fas fa-user-plus"></i> Add Staff
                        </button>

                    </div>
                    <div id="addStaffModal" class="modal">
                        <div class="modal-content">
                            <span onclick="closeAddStaffModal()" class="close-btn">&times;</span>
                            <h3 class="modal-title">
                                <i class="fas fa-user-plus title-icon"></i> Add Staff
                            </h3>

                            <div class="loading-sec">
                                <div id="staffFormProgressBar" class="form-progress-bar"></div>
                            </div>

                            <form method="POST" action="manageclients.dbf/add-staff.php" id="addStaffForm">

                                <!-- Username -->
                                <div class="form-group">
                                    <label><i class="fas fa-user icon-blue"></i> Username</label>
                                    <input type="text" name="username" id="staffUsername" class="form-control" required placeholder="Enter username">
                                    <div id="usernameError" class="error-message" style="display:none;">
                                        <i class="fas fa-exclamation-triangle"></i> Username already exists
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label><i class="fas fa-envelope icon-blue"></i> Email</label>
                                    <input type="email" name="email" id="staffEmail" class="form-control" required placeholder="Enter email">
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <label><i class="fas fa-lock icon-blue"></i> Password</label>
                                    <div class="pass-input-wrapper">
                                        <input type="password" name="password" id="staffPassword" class="form-control" required placeholder="Enter password">
                                        <i id="toggleStaffPassword" class="fas fa-eye toggle-password"></i>
                                    </div>
                                </div>

                                <!-- User Type -->
                                <div class="form-group">
                                    <label><i class="fas fa-users icon-blue"></i> Staff Type</label>
                                    <select name="user_type" id="staffType" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="Doctor">Doctor</option>
                                        <option value="Nurse">Nurse</option>
                                    </select>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" id="saveStaffBtn" class="btn-save">
                                    <i class="fas fa-save"></i> Save Staff
                                </button>

                            </form>
                        </div>

                        <script>
                            // Toggle password visibility
                            const toggleStaffPassword = document.getElementById('toggleStaffPassword');
                            const staffPassword = document.getElementById('staffPassword');
                            toggleStaffPassword.addEventListener('click', () => {
                                if (staffPassword.type === 'password') staffPassword.type = 'text';
                                else staffPassword.type = 'password';
                            });

                            // Form submission with progress bar and AJAX
                            const staffForm = document.getElementById('addStaffForm');
                            const staffProgressBar = document.getElementById('staffFormProgressBar');
                            const saveStaffBtn = document.getElementById('saveStaffBtn');

                            function startProgress() {
                                staffProgressBar.style.width = '0%';
                                staffProgressBar.style.display = 'block';
                                let width = 0;
                                const interval = setInterval(() => {
                                    if (width < 90) {
                                        width += Math.random() * 10;
                                        staffProgressBar.style.width = width + '%';
                                    }
                                }, 200);
                                return interval;
                            }

                            function finishProgress(interval) {
                                clearInterval(interval);
                                staffProgressBar.style.width = '100%';
                                setTimeout(() => {
                                    staffProgressBar.style.width = '0%';
                                }, 500);
                            }

                            staffForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                saveStaffBtn.disabled = true;

                                const interval = startProgress();
                                const formData = new FormData(staffForm);

                                fetch(staffForm.action, {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        finishProgress(interval);
                                        saveStaffBtn.disabled = false;

                                        if (data.success) {
                                            alert(data.message);
                                            staffForm.reset();
                                            window.location.reload();
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(err => {
                                        finishProgress(interval);
                                        saveStaffBtn.disabled = false;
                                        alert('Submission failed: ' + err);
                                    });
                            });
                        </script>
                    </div>

                </div>
                <table class="table table-bordered table-hover align-middle" id="freshmanstudentsTable"
                    style="height: 90%; margin-top: 10px; overflow: auto;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="clientTableBody">
                        <?php foreach ($admins as $row): ?>
                            <tr class="client-row">
                                <td class="searchable-id"><?= htmlspecialchars($row['id']) ?></td>

                                <td>
                                    <?php
                                    // Admin table has no profilePicturePath, using placeholder
                                    $profilePath = '../../uploads/profilepic2.png';
                                    ?>
                                    <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
                                </td>

                                <td class="searchable-name">
                                    <?= htmlspecialchars($row['username']) ?>
                                </td>

                                <td class="email-td">
                                    <?= htmlspecialchars($row['email'] ?: '—') ?>
                                </td>

                                <td class="course-td">
                                    <?= htmlspecialchars($row['user_type'] ?: '—') ?> <!-- Using user_type for Course -->
                                </td>

                                <td class="actions-column">
                                    <div class="action-buttons">

                                        <a class="row-view-btn"
                                            data-id="<?= $row['id'] ?>"
                                            data-username="<?= htmlspecialchars($row['username']) ?>"
                                            data-email="<?= htmlspecialchars($row['email'] ?: '—') ?>"
                                            data-role="<?= htmlspecialchars($row['user_type'] ?: '—') ?>"
                                            title="View User">
                                            <i class="fa-solid fa-eye view-icon"></i>
                                        </a>

                                        <a class="row-delete-btn"
                                            data-id="<?= $row['id'] ?>"
                                            data-url="manageclients.dbf/delete_staff.php"
                                            title="Delete User">
                                            <i class="fa-solid fa-trash delete-icon"></i>
                                        </a>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Staff Info Modal -->
            <div id="viewStaffModal" class="staff-info-overlay">
                <div class="staff-info-content">
                    <span onclick="closeViewStaffModal()" class="close-btn">&times;</span>
                    <h3 class="modal-title">
                        <i class="fas fa-user title-icon"></i> Staff Info
                    </h3>
                    <div id="staffDetails">
                        <p><strong>Username:</strong> <span id="staffUsernameText"></span></p>
                        <p><strong>Email:</strong> <span id="staffEmailText"></span></p>
                        <p><strong>Role:</strong> <span id="staffRoleText"></span></p>
                    </div>
                </div>
            </div>

            <!-- Confirm Delete Modal -->
            <!-- Confirm Delete Modal -->
            <div id="confirmationModal" class="delete-confirm-overlay">
                <div class="delete-confirm-content">
                    <span onclick="closeConfirmationModal()" class="close-btn">&times;</span>
                    <h3 class="modal-title">
                        <i class="fas fa-exclamation-triangle title-icon" style="color:#f44336;"></i> Confirm Delete
                    </h3>
                    <p>Are you sure you want to delete <strong id="confirmationName"></strong>?</p>
                    <div class="modal-buttons">
                        <button class="btn-cancel" onclick="closeConfirmationModal()">Cancel</button>
                        <button class="btn-delete" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>

        </main>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const searchInput = document.getElementById('searchInput');
            const clientTypeDropdown = document.getElementById('clientTypeDropdown');
            const tableBody = document.querySelector('#freshmanstudentsTable tbody');

            function filterTable() {
                const searchValue = searchInput.value.toLowerCase().trim();
                const typeValue = clientTypeDropdown.value.toLowerCase().trim();

                tableBody.querySelectorAll('tr').forEach(row => {
                    const id = row.querySelector('.searchable-id').textContent.toLowerCase().trim();
                    const name = row.querySelector('.searchable-name').textContent.toLowerCase().trim();
                    const email = row.querySelector('.email-td').textContent.toLowerCase().trim();
                    const userType = row.querySelector('.course-td').textContent.toLowerCase().trim(); // user_type

                    // Search matches
                    const matchesSearch = !searchValue || id.includes(searchValue) || name.includes(searchValue) || email.includes(searchValue);

                    // Type filter matches
                    const matchesType = typeValue === 'all' || userType === typeValue;

                    // Show/hide row
                    row.style.display = (matchesSearch && matchesType) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            clientTypeDropdown.addEventListener('change', filterTable);

            // Show all rows initially
            filterTable();
        });
    </script>

    <script>
        const addStaffModal = document.getElementById('addStaffModal');

        function openAddStaffModal() {
            addStaffModal.style.display = 'block';
        }

        function closeAddStaffModal() {
            addStaffModal.style.display = 'none';
        }

        // Close modal if user clicks outside the modal content
        window.addEventListener('click', function(e) {
            if (e.target === addStaffModal) {
                addStaffModal.style.display = 'none';
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Modals ---
            const viewStaffModal = document.getElementById('viewStaffModal');
            const confirmationModal = document.getElementById('confirmationModal');
            const confirmationName = document.getElementById('confirmationName');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            // Staff info spans
            const staffUsernameText = document.getElementById('staffUsernameText');
            const staffEmailText = document.getElementById('staffEmailText');
            const staffRoleText = document.getElementById('staffRoleText');

            // --- Open view modal on row click (except delete button) ---
            document.querySelectorAll('#freshmanstudentsTable .client-row').forEach(row => {
                row.addEventListener('click', function(e) {
                    // Ignore clicks on the delete button
                    if (e.target.closest('.row-delete-btn')) return;

                    const username = row.querySelector('.searchable-name').textContent.trim();
                    const email = row.querySelector('.email-td').textContent.trim();
                    const role = row.querySelector('.course-td').textContent.trim();

                    staffUsernameText.textContent = username;
                    staffEmailText.textContent = email;
                    staffRoleText.textContent = role;

                    viewStaffModal.style.display = 'block';
                });
            });

            function closeViewStaffModal() {
                viewStaffModal.style.display = 'none';
            }

            // --- Open confirmation modal on delete button click ---
            document.querySelectorAll('.row-delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent row click
                    const row = btn.closest('tr');
                    const username = row.querySelector('.searchable-name').textContent.trim();
                    const staffId = btn.getAttribute('data-id');

                    confirmationName.textContent = username;
                    confirmationModal.style.display = 'block';

                    // Assign delete action
                    confirmDeleteBtn.onclick = function() {
                        fetch(btn.dataset.url, {
                                method: 'POST',
                                body: new URLSearchParams({
                                    id: staffId
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    row.remove();
                                } else {
                                    alert(data.message);
                                }
                                closeConfirmationModal();
                            })
                            .catch(err => {
                                alert('Delete failed: ' + err);
                                closeConfirmationModal();
                            });
                    }
                });
            });

            function closeConfirmationModal() {
                confirmationModal.style.display = 'none';
            }

            // --- Close modals if click outside ---
            window.addEventListener('click', function(e) {
                if (e.target === viewStaffModal) closeViewStaffModal();
                if (e.target === confirmationModal) closeConfirmationModal();
            });

            // Expose close functions for HTML buttons
            window.closeViewStaffModal = closeViewStaffModal;
            window.closeConfirmationModal = closeConfirmationModal;
        });
    </script>

</body>




</html>