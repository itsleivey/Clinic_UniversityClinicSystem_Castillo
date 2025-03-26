const toggleButton = document.getElementById('toggle-btn');
const navbar = document.querySelector('.navbar');
const content = document.querySelector('.content');

toggleButton.addEventListener('click', () => {
    navbar.classList.toggle('collapsed');
    navbar.classList.toggle('expanded');
});

document.addEventListener("click", (event) => {
    if (event.target.classList.contains('buttons') && navbar.classList.contains('collapsed')) {
        navbar.classList.remove('collapsed');
        navbar.classList.add('expanded');
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const currentPage = window.location.pathname.split("/").pop();
    const activeButton = {
        "Profile.html": "profileBtn",
        "Medical_Form.html": "medicalBtn"
    }[currentPage];

    if (activeButton) {
        document.getElementById(activeButton).classList.add("active");
    }
});

function switchTab(event, sectionId) {
    document.querySelector(".tab-content.active")?.classList.remove("active");
    document.querySelector(".tab.active")?.classList.remove("active");

    document.getElementById(sectionId).classList.add("active");
    event.target.classList.add("active");
}

//Calendar functions ----------------------------------- >>>>
document.addEventListener("DOMContentLoaded", function () {
    const selectedDate = document.getElementById("selected-date");
    const dateSelect = document.getElementById("date-select"); // Single dropdown
    const weekdaysContainer = document.querySelector(".weekdays");
    const daysContainer = document.querySelector(".days");

    let currentDate = new Date();
    let currentDay = currentDate.getDate();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    let selectedMonth = currentMonth;
    let selectedYear = currentYear;

    function generateCalendar(month, year) {
        weekdaysContainer.innerHTML = "";
        daysContainer.innerHTML = "";

        const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        weekdays.forEach(day => {
            let weekdayElement = document.createElement("div");
            weekdayElement.classList.add("weekday");
            weekdayElement.textContent = day;
            weekdaysContainer.appendChild(weekdayElement);
        });

        let firstDay = new Date(year, month, 1).getDay();
        let daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            let emptySlot = document.createElement("div");
            emptySlot.classList.add("day", "empty");
            daysContainer.appendChild(emptySlot);
        }

        for (let i = 1; i <= daysInMonth; i++) {
            let dayElement = document.createElement("div");
            dayElement.classList.add("day");
            dayElement.textContent = i;

            if (i === currentDay && month === currentMonth && year === currentYear) {
                dayElement.classList.add("current-day"); 
            }

            dayElement.addEventListener("click", function () {
                document.querySelectorAll(".day").forEach(el => el.classList.remove("selected"));
                this.classList.add("selected");
                selectedDate.textContent = `${i.toString().padStart(2, '0')}.${(month + 1).toString().padStart(2, '0')}.${year}`;
            });

            daysContainer.appendChild(dayElement);
        }
    }

    // Populate dropdown with <optgroup> divisions for the current year + next 3 years
    for (let y = currentYear; y <= currentYear + 3; y++) {
        let yearGroup = document.createElement("optgroup");
        yearGroup.label = y; // Set year as group label

        for (let m = 0; m < 12; m++) {
            let option = document.createElement("option");
            option.value = `${m}-${y}`; // Store as "month-year"
            option.textContent = `${new Date(0, m).toLocaleString("en", { month: "long" })} ${y}`;
            
            // Set the current date as selected
            if (y === selectedYear && m === selectedMonth) option.selected = true;

            yearGroup.appendChild(option);
        }

        dateSelect.appendChild(yearGroup);
    }

    dateSelect.addEventListener("change", function () {
        let [month, year] = this.value.split("-").map(Number);
        selectedMonth = month;
        selectedYear = year;
        generateCalendar(selectedMonth, selectedYear);
    });

    generateCalendar(selectedMonth, selectedYear);

    function updateTime() {
        const timeDisplay = document.getElementById("time");
        const now = new Date();

        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, "0");
        const seconds = now.getSeconds().toString().padStart(2, "0");

        const amPm = hours >= 12 ? "PM" : "AM";

        hours = hours % 12 || 12;

        timeDisplay.textContent = `${hours}:${minutes}:${seconds} ${amPm}`;
    }

    setInterval(updateTime, 1000);
    updateTime();
});


// Inserting Profile Picture
document.addEventListener("DOMContentLoaded", function () {
    const profilePic = document.getElementById("profile-pic");
    const imageUpload = document.getElementById("image-upload");

    profilePic.addEventListener("click", function () {
        imageUpload.click();
    });

    imageUpload.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profilePic.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
