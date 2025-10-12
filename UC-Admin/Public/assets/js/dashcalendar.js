/*document.addEventListener("DOMContentLoaded", () => {
  generateCalendar();
  updateClock();
  setInterval(updateClock, 1000);
});

function generateCalendar() {
  const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const weekdaysContainer = document.getElementById("weekdays");
  const daysContainer = document.getElementById("days");
  const monthYearDisplay = document.getElementById("month-year");

  const today = new Date();
  const monthNames = ["January", "February", "March", "April", "May", "June",
                      "July", "August", "September", "October", "November", "December"];
  
  monthYearDisplay.textContent = `${monthNames[today.getMonth()]} ${today.getFullYear()}`;

  weekdaysContainer.innerHTML = "";
  daysContainer.innerHTML = "";

  weekdays.forEach(day => {
      const div = document.createElement("div");
      div.textContent = day;
      weekdaysContainer.appendChild(div);
  });

  const weekStart = new Date(today);
  weekStart.setDate(today.getDate() - today.getDay());
  
  for (let i = 0; i < 7; i++) {
      const currentDay = new Date(weekStart);
      currentDay.setDate(weekStart.getDate() + i);
      
      const dayElement = document.createElement("div");
      dayElement.className = "day";
      dayElement.textContent = currentDay.getDate();
      
      // Highlight today
      if (currentDay.toDateString() === today.toDateString()) {
          dayElement.classList.add("current-day");
      }
      
      daysContainer.appendChild(dayElement);
  }
}

function updateClock() {
  const now = new Date();
  const hours = now.getHours() % 12 || 12;
  const ampm = now.getHours() >= 12 ? "PM" : "AM";
  const timeString = `${hours}:${now.getMinutes().toString().padStart(2, "0")}:${now.getSeconds().toString().padStart(2, "0")} ${ampm}`;
  
  document.getElementById("time").textContent = timeString;
}*/
// Global state for the calendar. We start with today's date.
let currentDate = new Date();

// Get references to DOM elements
const datesGrid = document.getElementById("dates-grid");
const currentMonthDisplay = document.getElementById("current-month");

// New references for the custom year dropdown
const customYearSelector = document.getElementById("custom-year-selector");
const selectedYearDisplay = document.getElementById("selected-year");
const yearOptionsList = document.getElementById("year-options-list");

// Month names for display
const monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];

/**
 * Handles selecting a year from the custom list.
 * @param {number} newYear - The year selected by the user.
 */
function handleYearSelection(newYear) {
  currentDate.setFullYear(newYear);
  yearOptionsList.classList.add("hidden");
  renderCalendar();
}

/**
 * Populates the custom year dropdown list with clickable divs instead of native options.
 */
function populateYearDropdown() {
  const currentYear = currentDate.getFullYear();
  const startYear = currentYear - 10;
  const endYear = currentYear + 10;
  yearOptionsList.innerHTML = "";

  for (let year = startYear; year <= endYear; year++) {
    const yearOption = document.createElement("div");
    yearOption.classList.add("year-option");
    yearOption.textContent = year;
    yearOption.dataset.year = year;

    yearOption.addEventListener("click", () => {
      handleYearSelection(year);
    });

    yearOptionsList.appendChild(yearOption);
  }

  selectedYearDisplay.textContent = currentYear;
}

/**
 * Renders the calendar grid for the current month and year.
 */
function renderCalendar() {
  datesGrid.innerHTML = "";

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  currentMonthDisplay.textContent = monthNames[month];
  selectedYearDisplay.textContent = year;

  // Highlight selected year
  document.querySelectorAll(".year-option").forEach((opt) => {
    opt.classList.remove("selected");
    if (parseInt(opt.dataset.year) === year) {
      opt.classList.add("selected");
    }
  });

  const firstDayOfMonth = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const today = new Date();
  const isCurrentMonth =
    today.getMonth() === month && today.getFullYear() === year;

  // Leading empty cells
  for (let i = 0; i < firstDayOfMonth; i++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell", "inactive");
    datesGrid.appendChild(cell);
  }

  // Actual days
  for (let day = 1; day <= daysInMonth; day++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell");
    cell.textContent = day;

    if (isCurrentMonth && day === today.getDate()) {
      cell.classList.add("today");
    }

    datesGrid.appendChild(cell);
  }

  // Trailing empty cells
  const totalCells = firstDayOfMonth + daysInMonth;
  const cellsNeeded = (42 - totalCells) % 7;

  for (let i = 0; i < cellsNeeded; i++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell", "inactive");
    datesGrid.appendChild(cell);
  }
}

/**
 * Attaches event listeners for navigation and year changes.
 */
function attachListeners() {
  document.getElementById("prev-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });

  document.getElementById("next-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });

  customYearSelector.addEventListener("click", (event) => {
    if (event.target.classList.contains("year-option")) return;

    yearOptionsList.classList.toggle("hidden");
    event.stopPropagation();
  });

  document.addEventListener("click", () => {
    yearOptionsList.classList.add("hidden");
  });
}

/**
 * Initializes the application.
 */
function initCalendarApp() {
  populateYearDropdown();
  renderCalendar();
  attachListeners();
}

// Run initialization when the window loads
window.onload = initCalendarApp;
