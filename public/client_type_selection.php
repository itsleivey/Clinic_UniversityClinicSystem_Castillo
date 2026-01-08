<?php
session_start();
require '../config/database.php'; // Make sure this returns $pdo via pdo_connect_mysql()

if (!isset($_SESSION['ClientID'])) {
    header("Location: register.php");
    exit();
}

$pdo = pdo_connect_mysql();

// Handle modal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_type_modal'])) {
    $clientType = $_POST['user_type_modal'];
    $_SESSION['ClientType'] = $clientType;
    $clientID = $_SESSION['ClientID'];

    $department = isset($_POST['department_modal']) ? $_POST['department_modal'] : null;
    $course = isset($_POST['course_modal']) ? $_POST['course_modal'] : null;

    // Save ClientType, Department, Course
    $stmt = $pdo->prepare("UPDATE clients 
                           SET ClientType = :clientType,
                               Department = :department,
                               Course = :course
                           WHERE ClientID = :clientID");
    $stmt->execute([
        'clientType' => $clientType,
        'department' => $department,
        'course' => $course,
        'clientID' => $clientID
    ]);

    // Redirect based on ClientType
    switch ($clientType) {
        case 'Student':
            header("Location: Student_Profile.php");
            break;
        case 'Freshman':
            header("Location: Freshman_Profile.php");
            break;
        case 'Faculty':
        case 'Personnel':
            header("Location: All_Personnel_Profile.php");
            break;
        case 'NewPersonnel':
            header("Location: Newly_Hired_Profile.php");
            break;
        default:
            header("Location: Profile.php");
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Client Type</title>
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="UC-Client/assets/css/user_selection.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

</head>

<body>
    <div class="header">
        <h1>Choose Your Patient Type</h1>
        <p class="subtitle">Select the category that matches your status at the clinic.</p>
    </div>

    <div class="card-container">
        <div class="user-card">
            <button data-type="Student">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </button>
        </div>
        <div class="user-card">
            <button data-type="Freshman">
                <i class="fas fa-user-graduate"></i>
                <span>Incoming Freshman Students</span>
            </button>
        </div>
        <div class="user-card">
            <button data-type="Faculty">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Teaching-Personnel</span>
            </button>
        </div>
        <div class="user-card">
            <button data-type="Personnel">
                <i class="fas fa-user-tie"></i>
                <span>Non Teaching-Personnel</span>
            </button>
        </div>
        <div class="user-card">
            <button data-type="NewPersonnel">
                <i class="fas fa-user-plus"></i>
                <span>Newly Hired Personnel</span>
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="selectionModal">
        <div class="modal-content">
            <h2 id="modalTitle">Select Details</h2>
            <form method="POST" id="modalForm">
                <input type="hidden" name="user_type_modal" id="userTypeModal">
                <div class="form-group" id="modalDepartmentGroup">
                    <label for="department_modal">Department</label>
                    <select name="department_modal" id="department_modal" class="form-control" required>
                        <option value="">Select a Department</option>
                        <option value="College of Computer Studies">College of Computer Studies</option>
                        <option value="College of Food Nutrition and Dietetics">College of Food Nutrition and Dietetics</option>
                        <option value="College of Teacher Education">College of Teacher Education</option>
                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                        <option value="College of Business Administration and Accountancy">College of Business Administration and Accountancy</option>
                        <option value="College of Criminal Justice Education">College of Criminal Justice Education</option>
                        <option value="College of Fisheries">College of Fisheries</option>
                        <option value="College of Hospitality Management and Tourism">College of International Hospitality Management and Tourism</option>
                    </select>
                </div>
                <div class="form-group" id="modalCourseGroup">
                    <label for="course_modal">Course</label>
                    <select name="course_modal" id="course_modal" class="form-control">
                        <option value="">Select a Course</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit">Proceed</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const userCards = document.querySelectorAll('.user-card button');
        const modal = document.getElementById('selectionModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('modalForm');
        const userTypeModalInput = document.getElementById('userTypeModal');
        const modalDepartmentGroup = document.getElementById('modalDepartmentGroup');
        const modalCourseGroup = document.getElementById('modalCourseGroup');
        const departmentModalSelect = document.getElementById('department_modal');
        const courseModalSelect = document.getElementById('course_modal');

        const coursesByDepartment = {
            "College of Computer Studies": ["Bachelor of Science in Information Technology", "Bachelor of Science in Computer Science"],
            "College of Food Nutrition and Dietetics": ["Bachelor of Science in Food Nutrition", "Bachelor of Science in Dietetics"],
            "College of Teacher Education": ["Bachelor of Secondary Education", "SPECIAL PROGRAM - Intensive Course in English Proficiency", "SPECIAL PROGRAM - Bachelor of Elementary Education","AREA OF SPECIALIZATION - General Elementary Education", "AREA OF SPECIALIZATION - Pre-Elementary Education", "AREA OF SPECIALIZATION - Certificate in Teaching Proficiency (CTP)", "Bachelor of Elementary Education"],
            "College of Arts and Sciences": ["Bachelor of Science in Psychology"],
            "College of Business Administration and Accountancy": ["Bachelor of Science in Business Administration", "Bachelor of Science in Accountancy"],
            "College of Criminal Justice Education": ["Bachelor of Science in Criminology"],
            "College of Fisheries": ["Bachelor of Science in Fisheries","Bachelor of Science in Agri- Fisheries Business Management","Bachelor of Science in Fishery Education"],
            "College of Hospitality Management and Tourism": ["Bachelor of Science in Hotel and Restaurant Management", "Bachelor of Science in Tourism"],
        };

        // Show modal when a card is clicked
        userCards.forEach(card => {
            card.addEventListener('click', () => {
                userCards.forEach(c => c.closest('.user-card').classList.remove('selected'));
                card.closest('.user-card').classList.add('selected');
                const type = card.dataset.type;
                userTypeModalInput.value = type;

                if (type === 'Student' || type === 'Freshman') {
                    modalTitle.textContent = `Select Department & Course for ${type}`;
                    modalDepartmentGroup.style.display = 'block';
                    modalCourseGroup.style.display = 'block';
                    courseModalSelect.required = true;
                } else {
                    modalTitle.textContent = `Select Department for ${type}`;
                    modalDepartmentGroup.style.display = 'block';
                    modalCourseGroup.style.display = 'none';
                    courseModalSelect.required = false;
                }

                // Reset selects
                departmentModalSelect.value = '';
                courseModalSelect.innerHTML = '<option value="">Select a Course</option>';

                modal.style.display = 'flex';
            });
        });

        // Populate courses based on department
        departmentModalSelect.addEventListener('change', () => {
            const selectedDept = departmentModalSelect.value;
            const courses = coursesByDepartment[selectedDept] || [];
            courseModalSelect.innerHTML = '<option value="">Select a Course</option>';
            courses.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.textContent = c;
                courseModalSelect.appendChild(opt);
            });
        });

        // Close modal on click outside content
        modal.addEventListener('click', e => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>

</body>

</html>