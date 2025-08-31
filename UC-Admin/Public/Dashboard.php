<?php
session_start();

require 'config/database.php';

if (!isset($_SESSION['AdminID'])) {
    header('Location: index.php');
    exit;
}

$pdo = pdo_connect_mysql();
$counts = ['Student' => 0, 'Freshman' => 0, 'Faculty' => 0, 'Personnel' => 0, 'NewPersonnel' => 0];

// Fetch counts by ClientType
$stmt = $pdo->query("SELECT ClientType, COUNT(*) AS total FROM clients GROUP BY ClientType");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $type = $row['ClientType'];
    $counts[$type] = $row['total'];
}

// Calculate total count
$counts['Total'] = array_sum($counts);

$stmt = $pdo->query("SELECT Gender, COUNT(*) AS total FROM personalinfo GROUP BY Gender");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $gender = $row['Gender'];
    $genderCounts[$gender] = $row['total'];
}
//===============================================================================================
$yearStmt = $pdo->query("SELECT COUNT(*) FROM consultations WHERE YEAR(consultation_date) = YEAR(CURDATE())");
$perYear = $yearStmt->fetchColumn();

$month = date('n');
$semesterStart = ($month >= 1 && $month <= 6) ? 1 : 7;
$semesterEnd = ($month >= 1 && $month <= 6) ? 6 : 12;
$semesterStmt = $pdo->prepare("
    SELECT COUNT(*) FROM consultations 
    WHERE YEAR(consultation_date) = YEAR(CURDATE()) 
    AND MONTH(consultation_date) BETWEEN ? AND ?
");
$semesterStmt->execute([$semesterStart, $semesterEnd]);
$perSemester = $semesterStmt->fetchColumn();

$monthStmt = $pdo->query("SELECT COUNT(*) FROM consultations WHERE MONTH(consultation_date) = MONTH(CURDATE()) AND YEAR(consultation_date) = YEAR(CURDATE())");
$perMonth = $monthStmt->fetchColumn();
//============================================================================
$monthlyCounts = [];

for ($month = 1; $month <= 12; $month++) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM consultations
        WHERE MONTH(consultation_date) = ? AND YEAR(consultation_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$month]);
    $monthlyCounts[] = (int)$stmt->fetchColumn();
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
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/dashcalendar.js" defer></script>
    <script src="assets/js/dashgraph.js" defer></script>
    <title>Manage Profile</title>
</head>

<body>
    <div class="header">
        <img src="assets/images/Lspu logo.png" alt="Logo" type="image/webp" loading="lazy">
        <div class="title">
            <span class="university_title">University Clinic </span>
            <p>Patient's Profile </p>
            <p>Management System</p>
        </div>
        <button id="toggle-btn">
            <img id="btnicon" src="assets/images/menu-icon.svg">
        </button>
        <div class="page-title">
            <h4>Dashboard</h4>
        </div>
    </div>

    <div class="main-container">
        <nav class="navbar">
            <a href="Dashboard.php">
                <button class="buttons" id="dashboardBtn">
                    <img src="assets/images/dashboard_icon2.svg" class="button-icon-nav" loading="lazy">
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
            <a href="index.php">
                <button class="buttons" id="logoutbtn">
                    <img src="assets/images/logout-icon.svg" class="button-icon-nav" loading="lazy">
                    <span class="nav-text">Logout</span>
                </button>
            </a>
        </nav>

        <main class="content">
            <h3>Welcome Admin!</h3>
            <div class="report-cards-container">
                <div id="clienttype-card" class="report-cards">
                    <h3 class="report-card-text">Registered Patients</h3>
                    <div class="cards-icon-div">
                        <div class="card-data">
                            <div id="freshman-item" class="legend-item">
                                <i class="fas fa-user-graduate icon freshman"></i>
                                <span class="label">Freshman Students</span>
                                <span class="value"><?= $counts['Freshman'] ?></span>
                            </div>
                            <div class="legend-item">
                                <i class="fas fa-users icon student"></i>
                                <span class="label">Regular Students</span>
                                <span class="value"><?= $counts['Student'] ?></span>
                            </div>
                            <div class="legend-item">
                                <i class="fas fa-chalkboard-teacher icon faculty"></i>
                                <span class="label">Teaching Personnel</span>
                                <span class="value"><?= $counts['Faculty'] ?></span>
                            </div>
                            <div class="legend-item">
                                <i class="fas fa-user-tie icon personnel"></i>
                                <span class="label">Non-Teaching Personnel</span>
                                <span class="value"><?= $counts['Personnel'] ?></span>
                            </div>
                            <div id="newly-hired-item" class="legend-item">
                                <i class="fas fa-user-plus icon new-personnel"></i>
                                <span class="label">Newly-Hired Personnel</span>
                                <span class="value"><?= $counts['NewPersonnel'] ?></span>
                            </div>
                            <div id="total-count" class="legend-item">
                                <i class="fas fa-calculator"></i>
                                <span class="label">Total</span>
                                <span class="value"><?= $counts['Total'] ?></span>
                            </div>
                        </div>

                    </div>
                    <!-- <button class="cards-buttons">More details</button>-->
                </div>
                <div class="report-cards">
                    <h3 class="report-card-text">Patients Sex Distribution</h3>
                    <div class="cards-icon-div">

                        <div class="card-data">
                            <div class="legend-item">
                                <i class="fas fa-mars icon" style="color: #6BD9E7;"></i>
                                <span class="label">Male</span>
                                <span class="value"><?= $genderCounts['male'] ?? "0" ?></span>
                            </div>
                            <div class="legend-item">
                                <i class="fas fa-venus icon" style="color: #FF8FC9;"></i>
                                <span class="label">Female</span>
                                <span class="value"><?= $genderCounts['female'] ?? "0" ?></span>
                            </div>
                        </div>


                    </div>
                    <!-- <button class="cards-buttons">More details</button>-->
                </div>
                <div class="report-cards">
                    <h3 class="report-card-text">Number of Consultations</h3>
                    <div class="cards-icon-div-condata">
                        <div class="legend-item">
                            <span class="label">Per Year</span>
                            <span class="value"><?php echo $perYear; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="label">Per Semester</span>
                            <span class="value"><?php echo $perSemester; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="label">Per Month</span>
                            <span class="value"><?php echo $perMonth; ?></span>
                        </div>
                    </div>
                </div>

                <div class="calendar-card">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <h2>My Calendar</h2>
                            <div class="month-year" id="month-year"></div>
                        </div>

                        <div class="weekdays" id="weekdays"></div>
                        <div class="days" id="days"></div>

                        <div class="calendar-footer">
                            <a href="Calendar.html">
                                <button class="see-details" href="Calendar.html">See Details</button>
                            </a>
                            <div class="time-display" id="time"></div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="graph_charts_container">
                <div class="bar-graph-container">
                    <h2>Health Data Overview</h2>
                    <div class="dropdown-container">
                        <select id="graph-selector">
                            <option value="consultationgraph" selected>Consultation Graph</option>
                            <option value="familydentalgraph">Family Dental Graph</option>
                            <option value="familymedhisgraph">Family Medical History Graph</option>
                            <option value="personalsocialgraph">Personal Social Graph</option>
                            <option value="femalementrualgraph">Female Menstrual Graph</option>
                        </select>

                        <select id="yearSelector"></select>
                        <script>
                            const select = document.getElementById("yearSelector");
                            const currentYear = new Date().getFullYear();

                            // Define how many years to show, it will show consultation record 2 years ago to present year.
                            for (let i = 0; i < 3; i++) {
                                const year = currentYear - i;
                                const option = document.createElement("option");
                                option.value = year;
                                option.text = year;
                                if (year === currentYear) {
                                    option.selected = true;
                                }
                                select.appendChild(option);
                            }
                        </script>

                    </div>

                    <div class="graph-container" id="familydentalgraph">
                        <canvas id="familyDentalChart" width="600" height="300" loading="lazy"></canvas>
                    </div>

                    <div class="graph-container" width="600" height="300" id="familymedhisgraph" loading="lazy">
                        <canvas id="familyMedicalChart"></canvas>
                    </div>

                    <div class="graph-container" width="600" height="300" id="personalsocialgraph" loading="lazy">
                        <canvas id="personalSocialChart"></canvas>
                    </div>

                    <div class="graph-container" width="600" height="300" id="femalementrualgraph" loading="lazy">
                        <canvas id="femaleHealthChart"></canvas>
                    </div>

                    <div class="graph-container" width="600" height="300" id="consultationgraph" loading="lazy">
                        <canvas id="consultationChart"></canvas>
                    </div>

                </div>

                <div class="chart-container">
                    <div class="legend-data">
                        <div class="legend-header">
                            <h2>Department List</h2>
                            <div class="tabs">
                                <div class="tab active" data-target="students-content">Students</div>
                                <div class="tab" data-target="employees-content">Teaching Personnels</div>
                            </div>
                        </div>

                        <div id="students-content" class="tab-content" style="display: block;">
                            <div>
                                <h3>Registered Students</h3>
                            </div>
                            <div class="department-table-container">
                                <?php
                                require_once 'dashboard.dbf/students_department_stats.php';
                                $stats = getStudentDepartmentStats();

                                if (isset($stats['error'])) {
                                    echo '<div class="error">' . htmlspecialchars($stats['error']) . '</div>';
                                } else {
                                ?>
                                    <table class="department-table">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Department</th>
                                                <th>Number of Students</th>
                                                <!-- <th>Percentage</th>
                                                <th>Visual</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $rank = 1;
                                            foreach ($stats['departments'] as $dept) {
                                                $percentage = ($stats['total'] > 0) ? round(($dept['count'] / $stats['total']) * 100, 1) : 0;
                                                $percentageWidth = min(100, $percentage * 2);
                                                echo '
                    <tr>
                        <td>' . $rank++ . '</td>
                        <td class="dep-tr">' . htmlspecialchars($dept['Department']) . '</td>
                        <td>' . $dept['count'] . '</td>
                    
                    </tr>';
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2">Total Students</td>
                                                <td><?= $stats['total'] ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php
                                    if ($stats['total'] === 0) {
                                        echo '<p class="no-data">No student records found in the database.</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <!--   <td>
                                <td>' . $percentage . '%</td>
                            div class="percentage-bar-container">
                                <div class="percentage-bar" style="width: ' . $percentageWidth . '%;"></div>
                                <span class="percentage-text">' . $percentage . '%</span>
                            </div>
                        </td>-->
                        <div id="employees-content" class="tab-content" style="display: none;">
                            <div>
                                <h3>Registered Teaching Personnels</h3>
                            </div>
                            <div class="department-table-container">
                                <?php
                                require_once 'dashboard.dbf/faculty_department_stats.php';
                                $stats = getFacultyDepartmentStats();

                                if (isset($stats['error'])) {
                                    echo '<div class="error">' . htmlspecialchars($stats['error']) . '</div>';
                                } else {
                                ?>
                                    <table class="department-table">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Department</th>
                                                <th>Number of Teaching Personnels</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $rank = 1;
                                            foreach ($stats['departments'] as $dept) {
                                                $percentage = ($stats['total'] > 0) ? round(($dept['count'] / $stats['total']) * 100, 1) : 0;
                                                $percentageWidth = min(100, $percentage * 2); // Scale for better visualization
                                                echo '
                    <tr>
                        <td>' . $rank++ . '</td>
                        <td>' . htmlspecialchars($dept['Department']) . '</td>
                        <td>' . $dept['count'] . '</td>
                        
                    </tr>';
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2">Total Faculties</td>
                                                <td><?= $stats['total'] ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php
                                    if ($stats['total'] === 0) {
                                        echo '<p class="no-data">No student records found in the database.</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </main>
    </div>

</body>

</html