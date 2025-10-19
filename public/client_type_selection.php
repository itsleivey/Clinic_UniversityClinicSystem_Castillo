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
            header("Location: Faculty_Profile.php");
            break;
        case 'Personnel':
            header("Location: Non-Teaching_Profile.php");
            break;
        case 'NewPersonnel':
            header("Location: Newly_Hired_Profile.php");
            break;
        default:
            header("Location: Profile.php");
            break;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Client Type</title>
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f6fc;
            color: #1f2a38;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        h1 {
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 8px;
            text-align: center;
        }

        p.subtitle {
            font-size: 16px;
            color: #555;
            margin-bottom: 40px;
            text-align: center;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            width: 100%;
            max-width: 900px;
        }

        .user-card {
            background: #fff;
            border-radius: 16px;
            width: 160px;
            height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .user-card.selected {
            background-color: #e3f0ff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.12);
        }

        .user-card i {
            font-size: 36px;
            color: #397dda;
            margin-bottom: 10px;
        }

        .user-card span {
            font-size: 15px;
            font-weight: 500;
        }

        .user-card button {
            all: unset;
            width: 100%;
            height: 100%;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 30px 20px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            position: relative;
        }

        .modal h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 6px;
            display: block;
            color: #333;
        }

        .form-group label::after {
            content: "*";
            color: #d9534f;
            margin-left: 2px;
        }

        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            transition: border 0.3s;
        }

        .form-group select:focus {
            outline: none;
            border-color: #397dda;
            box-shadow: 0 0 5px rgba(57, 125, 218, 0.3);
        }

        .modal-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            background: #397dda;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .modal-buttons button:hover {
            background: #265aa6;
        }

        @media(max-width:768px) {
            .user-card {
                width: 140px;
                height: 140px;
            }

            h1 {
                font-size: 26px;
            }

            p.subtitle {
                font-size: 14px;
            }
        }

        @media(max-width:480px) {
            .card-container {
                flex-direction: column;
                gap: 12px;
            }

            .user-card {
                width: 100%;
                max-width: 180px;
                height: 140px;
            }

            .user-card i {
                font-size: 30px;
            }

            .user-card span {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

    <h1>Choose Your Patient Type</h1>
    <p class="subtitle">Select the category that matches your status at the clinic.</p>

    <div class="card-container">
        <div class="user-card"><button data-type="Student"><i class="fas fa-user-graduate"></i><span>Student</span></button></div>
        <div class="user-card"><button data-type="Freshman"><i class="fas fa-user-graduate"></i><span>Incoming Freshman Students</span></button></div>
        <div class="user-card"><button data-type="Faculty"><i class="fas fa-chalkboard-teacher"></i><span>Faculty</span></button></div>
        <div class="user-card"><button data-type="Personnel"><i class="fas fa-user-tie"></i><span>Personnel</span></button></div>
        <div class="user-card"><button data-type="NewPersonnel"><i class="fas fa-user-plus"></i><span>New Personnel</span></button></div>
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
                        <option value="College of Industrial Technology">College of Industrial Technology</option>
                        <option value="College of Teacher Education">College of Teacher Education</option>
                        <option value="College of Agriculture">College of Agriculture</option>
                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                        <option value="College of Business Administration and Accountancy">College of Business Administration and Accountancy</option>
                        <option value="College of Engineering">College of Engineering</option>
                        <option value="College of Criminal Justice Education">College of Criminal Justice Education</option>
                        <option value="College of Fisheries">College of Fisheries</option>
                        <option value="College of Hospitality Management and Tourism">College of Hospitality Management and Tourism</option>
                        <option value="College of Nursing and Allied Health">College of Nursing and Allied Health</option>
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
            "College of Industrial Technology": ["Bachelor of Industrial Technology major in Electrical Technology", "Bachelor of Industrial Technology major in Automotive Technology", "Bachelor of Industrial Technology major in Food Processing Technology"],
            "College of Teacher Education": ["Bachelor of Secondary Education", "Bachelor of Elementary Education"],
            "College of Agriculture": ["Bachelor of Science in Agriculture", "Bachelor of Science in Agricultural Technology"],
            "College of Arts and Sciences": ["Bachelor of Arts in English", "Bachelor of Science in Mathematics"],
            "College of Business Administration and Accountancy": ["Bachelor of Science in Business Administration", "Bachelor of Science in Accountancy"],
            "College of Engineering": ["Bachelor of Science in Electronics Engineering", "Bachelor of Science in Mechanical Engineering", "Bachelor of Science in Civil Engineering"],
            "College of Criminal Justice Education": ["Bachelor of Science in Criminology"],
            "College of Fisheries": ["Bachelor of Science in Fisheries"],
            "College of Hospitality Management and Tourism": ["Bachelor of Science in Hospitality Management", "Bachelor of Science in Tourism Management"],
            "College of Nursing and Allied Health": ["Bachelor of Science in Nursing", "Bachelor of Science in Medical Technology"]
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