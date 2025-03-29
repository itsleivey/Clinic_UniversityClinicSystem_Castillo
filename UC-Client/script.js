document.addEventListener("DOMContentLoaded", () => {
    // Cache DOM elements
    const elements = cacheDOMElements();
    
    // Initialize components
    initializeNavbar(elements);
    initializeTabs();
    initializeDateSelect(elements);
    initializeProfileUpload(elements);
    generateCalendar(elements, selectedDate.month, selectedDate.year);
    startClock(elements);
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
        timeDisplay: document.getElementById("time")
    };
}

function initializeNavbar(elements) {
    elements.toggleButton?.addEventListener("click", () => {
        elements.navbar?.classList.toggle("collapsed");
        elements.navbar?.classList.toggle("expanded");
    });
    
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("buttons") && elements.navbar?.classList.contains("collapsed")) {
            elements.navbar.classList.replace("collapsed", "expanded");
        }
    });
}

function initializeTabs() {
    const pageMap = {
        "Profile.html": "profileBtn",
        "Medical_Form.html": "medicalBtn"
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

const currentDate = new Date();
let selectedDate = { month: currentDate.getMonth(), year: currentDate.getFullYear() };

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
            html += `<option value="${m}-${y}" ${isSelected ? "selected" : ""}>${
                new Date(0, m).toLocaleString("en", { month: "long" })
            } ${y}</option>`;
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

function initializeProfileUpload(elements) {
    elements.profilePic?.addEventListener("click", () => elements.imageUpload?.click());
    elements.imageUpload?.addEventListener("change", function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => elements.profilePic.src = e.target.result;
            reader.readAsDataURL(file);
        }
    });
}
