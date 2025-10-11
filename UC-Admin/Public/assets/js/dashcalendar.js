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
const timeDisplay = document.getElementById("current-time");

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
  // 1. Update global date state
  currentDate.setFullYear(newYear);

  // 2. Hide the list
  yearOptionsList.classList.add("hidden");

  // 3. Re-render calendar and selection highlighting
  renderCalendar();
}

/**
 * Populates the custom year dropdown list with clickable divs instead of native options.
 */
function populateYearDropdown() {
  const currentYear = currentDate.getFullYear();
  const startYear = currentYear - 10;
  const endYear = currentYear + 10;
  yearOptionsList.innerHTML = ""; // Clear existing options

  for (let year = startYear; year <= endYear; year++) {
    const yearOption = document.createElement("div");
    yearOption.classList.add("year-option");
    yearOption.textContent = year;
    yearOption.dataset.year = year;

    // Add click listener
    yearOption.addEventListener("click", () => {
      handleYearSelection(year);
    });

    yearOptionsList.appendChild(yearOption);
  }
  // Set initial display text
  selectedYearDisplay.textContent = currentYear;
}

/**
 * Renders the calendar grid for the current month and year in the global state.
 */
function renderCalendar() {
  // Clear previous grid
  datesGrid.innerHTML = "";

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  // Update header displays
  currentMonthDisplay.textContent = monthNames[month];
  selectedYearDisplay.textContent = year; // Use new display element

  // Highlight the selected year in the custom list
  document.querySelectorAll(".year-option").forEach((opt) => {
    opt.classList.remove("selected");
    if (parseInt(opt.dataset.year) === year) {
      opt.classList.add("selected");
    }
  });

  // Date calculations
  const firstDayOfMonth = new Date(year, month, 1).getDay(); // Day of the week (0=Sun, 6=Sat) for the 1st
  const daysInMonth = new Date(year, month + 1, 0).getDate(); // Total days in current month

  // Reference date for highlighting 'today'
  const today = new Date();
  const isCurrentMonth =
    today.getMonth() === month && today.getFullYear() === year;

  // 1. Add leading empty cells (placeholders for days of previous month)
  for (let i = 0; i < firstDayOfMonth; i++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell", "inactive");
    datesGrid.appendChild(cell);
  }

  // 2. Add day cells for the current month
  for (let day = 1; day <= daysInMonth; day++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell");
    cell.textContent = day;

    // Highlight the current day
    if (isCurrentMonth && day === today.getDate()) {
      cell.classList.add("today");
    }

    datesGrid.appendChild(cell);
  }

  // 3. Add trailing empty cells to complete the grid (up to 6 rows total)
  const totalCells = firstDayOfMonth + daysInMonth;
  const cellsNeeded = (42 - totalCells) % 7; // Ensure we only fill up to the last visible row

  for (let i = 0; i < cellsNeeded; i++) {
    const cell = document.createElement("div");
    cell.classList.add("date-cell", "inactive");
    datesGrid.appendChild(cell);
  }
}

/**
 * Updates the floating time display every second.
 */
function updateClock() {
  const now = new Date();

  // Format hours for 12-hour display
  let hours = now.getHours();
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12;
  hours = hours ? hours : 12; // The hour '0' should be '12'

  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  timeDisplay.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
}

/**
 * Attaches event listeners for navigation and year changes.
 */
function attachListeners() {
  // Previous Month Button
  document.getElementById("prev-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });

  // Next Month Button
  document.getElementById("next-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });

  // Custom Year Selector Toggle
  customYearSelector.addEventListener("click", (event) => {
    // Prevent click on an option inside the list from bubbling up and re-toggling
    if (event.target.classList.contains("year-option")) return;

    yearOptionsList.classList.toggle("hidden");

    // Stop propagation so the outside click listener doesn't immediately close it
    event.stopPropagation();
  });

  // Global click listener to close the dropdown when clicking anywhere else
  document.addEventListener("click", () => {
    yearOptionsList.classList.add("hidden");
  });
}

/**
 * Initializes the application.
 */
function initCalendarApp() {
  // 1. Set up initial state and render the calendar
  populateYearDropdown();
  renderCalendar();

  // 2. Set up the clock interval (updates every 1000ms/1s)
  updateClock();
  setInterval(updateClock, 1000);

  // 3. Attach all interactive listeners
  attachListeners();
}

// Run initialization when the window loads
window.onload = initCalendarApp;
