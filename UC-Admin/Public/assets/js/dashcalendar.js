document.addEventListener("DOMContentLoaded", () => {
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
}