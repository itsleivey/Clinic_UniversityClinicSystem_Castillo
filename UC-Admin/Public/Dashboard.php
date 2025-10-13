<?php
include 'dashboard.dbf/fetch_dashboard_data.php';
require 'dashboard.dbf/recent_consultations.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Example</title>
    <link rel="stylesheet" href="assets/css/dashboardpagestyles.css">
    <link rel="stylesheet" href="assets/css/adminstyles.css">
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
    <link rel="stylesheet" href="webicons/fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard_func.js" defer></script>
    <script src="assets/js/dashcalendar.js" defer></script>
    <script src="assets/js/dashgraph.js" defer></script>
    <script src="assets/js/clientTypeChart.js" defer></script>
    <script src="assets/css/calendarstyles.css" defer></script>
    <script src="node_modules/chart.js/dist/chart.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <title>Manage Profile</title>
</head>

<body>
    <!-- Dont remove this divs below -->
    <div class="client-stats">
        <div id="student-count" class="stat-box"></div>
        <div id="freshman-count" class="stat-box"></div>
        <div id="faculty-count" class="stat-box"></div>
        <div id="personnel-count" class="stat-box"></div>
        <div id="newpersonnel-count" class="stat-box"></div>
        <div id="total-count" class="stat-box"></div>
    </div>

    <!-- Dont remove this divs above -->

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

        <main id="dashboard-main-content" class="content">
            <div class="report-cards-container">
                <div class="quantity-statistics-container">
                    <div class="admin-tex-div">
                        <h3>Welcome Admin!</h3>
                    </div>
                    <div class="patients-statisticis">
                        <div class="patients-type-counts-div">
                            <div id="header-count-div" class="counts-div">
                                <h4>Registered Patients Overview</h4>
                                <button class="header-modal-button">
                                    <i class="fas fa-plus"></i>
                                    View more
                                </button>

                                <!-- Modal Structure -->
                                <div id="myModal" class="modal">
                                    <div class="modal-content">
                                        <div id="client-type-chart" class="chart-container">
                                            <h2>Client Type Distribution</h2>
                                            <canvas id="clientTypeChart"></canvas>
                                        </div>
                                        <div class="text-labels-client-type">
                                            <div class="modal-header">
                                                <h2>List of Registered Patients</h2>
                                                <span class="close">&times;</span>
                                            </div>
                                            <div class="patients-table">
                                                <div id="students-card" class="patients-cards-count">
                                                    <div id="students-icon-card" class="icon-cards">
                                                        <i class="fas fa-user-graduate icon freshman"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Students</p>
                                                        <h4><?= $counts['Student'] ?></h4>
                                                    </div>
                                                </div>
                                                <div id="teaching-card" class="patients-cards-count">
                                                    <div id="teaching-icon-card" class="icon-cards">
                                                        <i class="fas fa-chalkboard-teacher icon faculty"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Teaching Personnels</p>
                                                        <h4><?= $counts['Faculty'] ?></h4>
                                                    </div>
                                                </div>
                                                <div id="non-teaching-card" class="patients-cards-count">
                                                    <div id="non-teaching-icon-card" class="icon-cards">
                                                        <i class="fas fa-user-tie icon personnel"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Non-Teaching Personnels</p>
                                                        <h4><?= $counts['Personnel'] ?></h4>
                                                    </div>
                                                </div>
                                                <div id="freshman-card" class="patients-cards-count">
                                                    <div id="freshman-card-icon-card" class="icon-cards">
                                                        <i class="fas fa-users icon student"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Freshman/Applicants</p>
                                                        <h4><?= $counts['Freshman'] ?></h4>
                                                    </div>
                                                </div>
                                                <div id="new-personnel-card" class="patients-cards-count">
                                                    <div id="new-personnel-icon-card" class="icon-cards">
                                                        <i class="fas fa-user-plus icon new-personnel"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Newly Hired</p>
                                                        <h4><?= $counts['NewPersonnel'] ?></h4>
                                                    </div>
                                                </div>
                                                <div id="totals-card" class="patients-cards-count">
                                                    <div id="total-icon-card" class="icon-cards">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </div>
                                                    <div class="cards-labels">
                                                        <p>Total Counts</p>
                                                        <h4><?= $counts['Total'] ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="counts-div">
                                <div id="students-card" class="patients-cards-count">
                                    <div id="students-icon-card" class="icon-cards">
                                        <i class="fas fa-user-graduate icon freshman"></i>
                                    </div>
                                    <div class="cards-labels">
                                        <p>Students</p>
                                        <h4><?= $counts['Student'] ?></h4>
                                    </div>
                                </div>
                                <div id="teaching-card" class="patients-cards-count">
                                    <div id="teaching-icon-card" class="icon-cards">
                                        <i class="fas fa-chalkboard-teacher icon faculty"></i>
                                    </div>
                                    <div class="cards-labels">
                                        <p>Teaching Personnels</p>
                                        <h4><?= $counts['Faculty'] ?></h4>
                                    </div>
                                </div>
                                <div id="non-teaching-card" class="patients-cards-count">
                                    <div id="non-teaching-icon-card" class="icon-cards">
                                        <i class="fas fa-user-tie icon personnel"></i>
                                    </div>
                                    <div class="cards-labels">
                                        <p>Non-Teaching Personnels</p>
                                        <h4><?= $counts['Personnel'] ?></h4>
                                    </div>
                                </div>
                                <div id="totals-card" class="patients-cards-count">
                                    <div id="total-icon-card" class="icon-cards">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="cards-labels">
                                        <p>Total Counts</p>
                                        <h4><?= $counts['Total'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="patients-sex-distribution-count-div">
                            <div class="gender-div-header">
                                <h4>Patients Sex Distribution</h4>
                            </div>
                            <div class="gender-chart-container">
                                <canvas id="genderChart" width="400" height="400"></canvas>
                                <div class="gender-count">
                                    <div id="male-card-count" class="gender-count-sub-sec">
                                        <div id="male-card-icon" class="gender-icon-cards">
                                            <i class="fas fa-mars"></i>
                                        </div>
                                        <div id="male-card" class="gender-cards-labels">
                                            <h4><?= $Male ?? 0 ?></h4>
                                        </div>
                                    </div>
                                    <div id="female-card-count" class="gender-count-sub-sec">
                                        <div id="female-card-icon" class="gender-icon-cards">
                                            <i class="fas fa-venus"></i>
                                        </div>
                                        <div id="female-card" class="gender-cards-labels">
                                            <h4><?= $Female ?? 0 ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="calendar-div">
                    <div id="calendar-app">

                        <div id="calendar-header">
                            <h1 class="app-title">Calendar</h1>

                            <div class="year-selector" id="custom-year-selector">
                                <span id="selected-year" class="year-select-display"></span>
                                <div class="calendar-icon-bg">

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="calendar-icon">
                                        <path d="M19 4h-1V3c0-.55-.45-1-1-1s-1 .45-1 1v1H8V3c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z" />
                                    </svg>
                                </div>

                                <div id="year-options-list" class="hidden">

                                </div>
                            </div>
                        </div>


                        <div id="month-navigation">
                            <button id="prev-month" class="nav-arrow left-arrow" aria-label="Previous Month">

                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                                </svg>
                            </button>
                            <h2 id="current-month"></h2>
                            <button id="next-month" class="nav-arrow right-arrow" aria-label="Next Month">

                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 6 8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
                                </svg>
                            </button>
                        </div>

                        <div id="day-names" class="calendar-grid">
                            <span>Sun</span>
                            <span>Mon</span>
                            <span>Tue</span>
                            <span>Wed</span>
                            <span>Thu</span>
                            <span>Fri</span>
                            <span>Sat</span>
                        </div>

                        <div id="dates-grid" class="calendar-grid">
                            <!-- Dates will be injected here -->
                        </div>

                        <!--  <div id="time-display-container">
                            <div id="current-time">00:00:00 AM</div>
                        </div> -->

                    </div>
                </div>
            </div>

            <div class="tables-bargraphs">
                <div class="second-section-graph-table">
                    <div class="bar-graph-container">
                        <h2>Consultations and Health Data Overview</h2>
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
                    <div class="patients-overview">
                        <div class="overview-header">
                            <h3>Recent Consultations</h3>
                        </div>

                        <div class="overview-body">
                            <div class="consult-table-container">
                                <div class="scrollable-table">
                                    <div class="recent-patients-table">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Patient Info</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($recentRecords): ?>
                                                    <?php foreach ($recentRecords as $rec): ?>
                                                        <tr>
                                                            <td class="patient-info-cell">
                                                                <div class="patient-info">
                                                                    <img src="<?= htmlspecialchars(!empty($rec['profilePicturePath']) ? '../../uploads/' . $rec['profilePicturePath'] : '../../uploads/profilepic2.png') ?>" class="profile-pic" alt="Profile">

                                                                    <div class="details">
                                                                        <span class="name"><?= htmlspecialchars($rec['Firstname'] . ' ' . $rec['Lastname']) ?></span>
                                                                        <span class="client-type"><?= htmlspecialchars($rec['ClientType']) ?></span>
                                                                        <span class="datetime"><?= date('M d, Y', strtotime($rec['last_record_datetime'])) ?> | <?= date('h:i A', strtotime($rec['last_record_datetime'])) ?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <button class="action-btn" onclick="viewPatient(<?= $rec['ClientID'] ?>)">View</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="2">No recent patient records found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <script>
                                        function viewPatient(clientId) {
                                            window.location.href = 'ClientProfile.php?id=' + clientId;
                                        }
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <div class="legend-data">
                    <div class="legend-header">
                        <h2>Patients Department List</h2>
                        <div class="tabs">
                            <div class="tab active" data-target="students-content">Students</div>
                            <div class="tab" data-target="employees-content">Teaching Personnels</div>
                        </div>
                    </div>

                    <div id="students-content" class="tab-content" style="display: block;">
                        <div class="table-name">
                            <h3>Registered Students per Department</h3>
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
                        <div class="table-name">
                            <h3>Registered Teaching Personnel by Department</h3>
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
                                            <td colspan="2">Total Teaching Personnels</td>
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
<script>
    const modal = document.getElementById("myModal");
    const btn = document.querySelector(".header-modal-button");
    const close = document.querySelector(".close");

    // Open modal
    btn.onclick = () => {
        modal.style.display = "flex";
    };

    // Close modal when X is clicked
    close.onclick = () => {
        modal.style.display = "none";
    };

    // Close modal when clicking outside of it
    window.onclick = (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    };
</script>
<script>
    fetch('dashboard.dbf/get_gender_data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('genderChart').getContext('2d');

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [{
                        data: [data.male, data.female],
                        backgroundColor: ['#1ABC9C', '#FF6B6B'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: '',
                            font: {
                                size: 18
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading data:', error));
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        fetch("dashboard.bdf/clientTypeData.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                const labels = data.map((item) => item.ClientType);
                const counts = data.map((item) => item.count);

                const ctx = document.getElementById("clientTypeChart").getContext("2d");
                new Chart(ctx, {
                    type: "pie",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Number of Clients by Type",
                            data: counts,
                            backgroundColor: [
                                "#2196f3", // Student
                                "#4caf50", // Faculty
                                "#ff9800", // Personnel
                                "#9c27b0", // Freshman
                                "#f44336", // New Personnel
                                "#607d8b", // Default
                            ],
                            borderColor: "#fff",
                            borderWidth: 2,
                        }, ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: {
                                    font: {
                                        size: 14
                                    },
                                    color: "#333",
                                },
                            },
                            title: {
                                display: true,
                                text: "Registered Clients by Type",
                                font: {
                                    size: 18,
                                    weight: "bold"
                                },
                                color: "#004a8f",
                            },
                        },
                    },
                });
            })
            .catch((error) => console.error("Error fetching data:", error));
    });
</script>

</html