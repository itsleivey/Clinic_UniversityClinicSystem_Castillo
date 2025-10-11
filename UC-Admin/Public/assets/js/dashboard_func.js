document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("js-loaded");
  const elements = cacheDOMElements();
  initializeNavbar(elements);
  initializeTabs();
});

function cacheDOMElements() {
  return {
    navbar: document.querySelector(".navbar"),
    toggleButton: document.getElementById("toggle-btn"),
    content: document.querySelector(".content"),
    profilePic: document.getElementById("profile-pic"),
    imageUpload: document.getElementById("image-upload"),
    selectedDate: document.getElementById("selected-date"),
    dateSelect: document.getElementById("date-select"),
    weekdaysContainer: document.querySelector(".weekdays"),
    daysContainer: document.querySelector(".days"),
    timeDisplay: document.getElementById("time"),
  };
}

function initializeNavbar(elements) {
  // Load saved sidebar state
  const isCollapsed = localStorage.getItem("navbar-collapsed") === "true";

  if (isCollapsed) {
    elements.navbar?.classList.add("collapsed");
    elements.navbar?.classList.remove("expanded");
  } else {
    elements.navbar?.classList.add("expanded");
    elements.navbar?.classList.remove("collapsed");
  }

  // Toggle sidebar and save new state
  elements.toggleButton?.addEventListener("click", () => {
    elements.navbar?.classList.toggle("collapsed");
    elements.navbar?.classList.toggle("expanded");

    const collapsed = elements.navbar?.classList.contains("collapsed");
    localStorage.setItem("navbar-collapsed", collapsed);
  });
}

function initializeTabs() {
  const pageMap = {
    "Dashboard.php": "dashboardBtn",
    "Manage_Clients.php": "manageclientsBtn",
    "ClientProfile.php": "manageclientsBtn",
    "History_Page.php": "manageclientsBtn",
    "Data_Management.php": "datamanagementBtn",
    "Calendar.html": "calendarBtn",
  };
  const activePage = pageMap[window.location.pathname.split("/").pop()];
  document.getElementById(activePage)?.classList.add("active");

  window.switchTab = (event, sectionId) => {
    document.querySelector(".tab-content.active")?.classList.remove("active");
    document.querySelector(".tab.active")?.classList.remove("active");

    document.getElementById(sectionId)?.classList.add("active");
    event.target.classList.add("active");
  };
}

document.addEventListener("DOMContentLoaded", () => {
  const departmentElement = document.getElementById("department");
  if (departmentElement) {
    departmentElement.addEventListener("change", function () {
      const selectedDepartment = this.value;
      const rows = document.querySelectorAll(".client-row");

      rows.forEach((row) => {
        const departmentCell = row.cells[4];
        const departmentValue = departmentCell
          ? departmentCell.textContent.trim()
          : "";

        row.style.display =
          selectedDepartment === "" || departmentValue === selectedDepartment
            ? ""
            : "none";
      });
    });
  }
});

//===========================================
function showProfileContent(clientID) {
  document.getElementById("mainContent").style.display = "none";

  document.getElementById("profileContent").style.display = "block";

  document.getElementById(
    "profileDetails"
  ).innerHTML = `Displaying profile for client ID: ${clientID}`;
}

function showMainContent() {
  document.getElementById("mainContent").style.display = "block";
  document.getElementById("profileContent").style.display = "none";
}
