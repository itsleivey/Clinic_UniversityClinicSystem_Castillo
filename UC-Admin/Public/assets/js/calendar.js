document.addEventListener("DOMContentLoaded", () => {
  const elements = cacheDOMElements();
  initializeDateSelect(elements);
  generateCalendar(elements, selectedDate.month, selectedDate.year);
  startClock(elements);
  loadToDoList(); 
});

let allEvents = [];
const currentDate = new Date();
let selectedDate = { month: currentDate.getMonth(), year: currentDate.getFullYear() };

function cacheDOMElements() {
  return {
    selectedDate: document.getElementById("selected-date"),
    dateSelect: document.getElementById("date-select"),
    weekdaysContainer: document.querySelector(".weekdays"),
    daysContainer: document.querySelector(".days"),
    timeDisplay: document.getElementById("time")
  };
}

function generateCalendar(elements, month, year) {
  const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const weekdayFragment = document.createDocumentFragment();
  const daysFragment = document.createDocumentFragment();

  elements.weekdaysContainer.innerHTML = "";
  elements.daysContainer.innerHTML = "";

  weekdays.forEach(day => {
    const div = document.createElement("div");
    div.className = "weekday";
    div.textContent = day;
    weekdayFragment.appendChild(div);
  });
  elements.weekdaysContainer.appendChild(weekdayFragment);

  const firstDay = new Date(year, month, 1).getDay();
  for (let i = 0; i < firstDay; i++) {
    const emptyDay = document.createElement("div");
    emptyDay.className = "day empty";
    daysFragment.appendChild(emptyDay);
  }

  const daysInMonth = new Date(year, month + 1, 0).getDate();
  for (let day = 1; day <= daysInMonth; day++) {
    const dayElement = document.createElement("div");
    dayElement.className = "day";

    if (day === currentDate.getDate() && month === currentDate.getMonth() && year === currentDate.getFullYear()) {
      dayElement.classList.add("current-day");
    }

    dayElement.textContent = day;
    dayElement.addEventListener("click", (event) => selectDate(event, elements, day, month, year));
    daysFragment.appendChild(dayElement);
  }

  elements.daysContainer.appendChild(daysFragment);
  
  if (allEvents.length > 0) {
    highlightEventDates(allEvents);
  }
}

function highlightEventDates(events) {
  document.querySelectorAll('.day.has-event').forEach(day => {
    day.classList.remove('has-event');
    const tooltip = day.querySelector('.event-tooltip');
    if (tooltip) tooltip.remove();
  });

  const eventsByDate = {};
  events.forEach(event => {
    const dateStr = event.date;
    if (!eventsByDate[dateStr]) {
      eventsByDate[dateStr] = [];
    }
    eventsByDate[dateStr].push(event);
  });

  Object.keys(eventsByDate).forEach(dateStr => {
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    
    if (date.getMonth() === selectedDate.month && date.getFullYear() === selectedDate.year) {
      const dayElements = document.querySelectorAll('.day:not(.empty)');
      dayElements.forEach(dayElement => {
        if (parseInt(dayElement.textContent) === day) {
          dayElement.classList.add('has-event');
          const eventsForDay = eventsByDate[dateStr];
          
          const tooltip = document.createElement('div');
          tooltip.className = 'event-tooltip';
          
          tooltip.innerHTML = eventsForDay.map(event => `
            <div class="event-tooltip-item">
              <strong>${formatTime(event.time)}</strong> - ${event.event}
              ${event.location ? `<div class="event-location">📍 ${event.location}</div>` : ''}
              ${event.noted ? `<div class="event-notes">📝 ${event.noted}</div>` : ''}
            </div>
          `).join('');
          
          dayElement.appendChild(tooltip);
          
          dayElement.addEventListener('mouseenter', () => {
            tooltip.style.display = 'block';
          });
          
          dayElement.addEventListener('mouseleave', () => {
            tooltip.style.display = 'none';
          });
        }
      });
    }
  });
}

function formatTime(timeString) {
  const [hours, minutes] = timeString.split(':');
  const hour = parseInt(hours) % 12 || 12;
  const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
  return `${hour}:${minutes} ${ampm}`;
}

function selectDate(event, elements, day, month, year) {
  document.querySelectorAll(".day").forEach(el => el.classList.remove("selected"));
  event.target.classList.add("selected");
  elements.selectedDate.textContent = `${String(day).padStart(2, '0')}.${String(month + 1).padStart(2, '0')}.${year}`;
}

function initializeDateSelect(elements) {
  let html = "";
  for (let y = currentDate.getFullYear(); y <= currentDate.getFullYear() + 3; y++) {
    html += `<optgroup label="${y}">`;
    for (let m = 0; m < 12; m++) {
      const isSelected = y === selectedDate.year && m === selectedDate.month;
      html += `<option value="${m}-${y}" ${isSelected ? "selected" : ""}>${new Date(0, m).toLocaleString("en", { month: "long" })} ${y}</option>`;
    }
    html += "</optgroup>";
  }
  elements.dateSelect.innerHTML = html;
  elements.dateSelect.addEventListener("change", () => {
    const [month, year] = elements.dateSelect.value.split("-").map(Number);
    selectedDate = { month, year };
    generateCalendar(elements, month, year);
  });
}

function startClock(elements) {
  function updateClock() {
    const now = new Date();
    const hours = now.getHours() % 12 || 12;
    elements.timeDisplay.textContent =
      `${hours}:${now.getMinutes().toString().padStart(2, "0")}:${now.getSeconds().toString().padStart(2, "0")} ${now.getHours() >= 12 ? "PM" : "AM"}`;
  }
  updateClock();
  setInterval(updateClock, 1000);
}

/*================ To-Do List Section =========================== */
