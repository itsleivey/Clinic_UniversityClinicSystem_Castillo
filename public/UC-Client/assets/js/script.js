document.addEventListener("DOMContentLoaded", () => {
    const elements = cacheDOMElements();

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
        "Profile.php": "profileBtn",
        "Medical_Form.php": "medicalBtn"
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

        const isToday = day === currentDate.getDate() &&
            month === currentDate.getMonth() &&
            year === currentDate.getFullYear();

        const isIssuanceDate = day === issuanceDate.getDate() &&
            month === issuanceDate.getMonth() &&
            year === issuanceDate.getFullYear();

        if (isToday) {
            dayElement.classList.add("current-day");
        }
        if (isIssuanceDate) {
            dayElement.classList.add("issuance-day");
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
            html += `<option value="${m}-${y}" ${isSelected ? "selected" : ""}>${new Date(0, m).toLocaleString("en", { month: "long" })
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
    elements.imageUpload?.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => elements.profilePic.src = e.target.result;
            reader.readAsDataURL(file);
        }
    });
}

/*Dropdown Form*/
function toggleDropdown() {
    const content = document.getElementById('dropdownContent');
    content.classList.toggle('open');
}

function toggleDropdown2() {
    const content = document.getElementById('dropdownContent2');
    content.classList.toggle('open');
}

function toggleDropdown3() {
    const content = document.getElementById('dropdownContent3');
    content.classList.toggle('open');
}

function toggleDropdown4() {
    const content = document.getElementById('dropdownContent4');
    content.classList.toggle('open');
}
//=================================================================================

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('profileAlertOverlay');
    const form = document.getElementById('profileAlertForm');

    if (overlay) {
        overlay.style.zIndex = '9999';

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        overlay.style.display = 'none';
                        document.querySelector('.main-content').style.pointerEvents = 'auto';
                        document.querySelector('.main-content').style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    }
});
//==========================Profile Alert========================
/*
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('hidden');
  }
  
  document.addEventListener('click', function (e) {
    const profileContainer = document.querySelector('.profile-container');
    if (!profileContainer.contains(e.target)) {
      document.getElementById('profileMenu').classList.add('hidden');
    }
  });

*/
document.getElementById('progressForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await fetch('createnewprogress.php', {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update UI without reload
            document.getElementById('statusDisplay').textContent = 'Inprogress';
            document.getElementById('createProgressSection').style.display = 'none';
            document.getElementById('cancelProgressSection').style.display = 'block';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error: ' + error.message);
    }
});

document.getElementById('cancelProgress')?.addEventListener('click', async () => {
    if (!confirm('Are you sure you want to cancel this progress?')) return;
    
    try {
        const response = await fetch('cancelprogress.php', {
            method: 'POST',
            body: new FormData(document.getElementById('progressForm'))
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('statusDisplay').textContent = 'Undone';
            document.getElementById('createProgressSection').style.display = 'block';
            document.getElementById('cancelProgressSection').style.display = 'none';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error: ' + error.message);
    }
});
//=======================================================================
/*
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.main-mobile-tab').forEach(tab => {
        tab.addEventListener('click', () => {

            document.querySelectorAll('.main-mobile-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.main-tab-content').forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            const tabId = tab.getAttribute('data-main-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    document.querySelectorAll('.mobile-sub-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            if (!document.getElementById('info-tab').classList.contains('active')) return;
            
            document.querySelectorAll('.mobile-sub-tab').forEach(t => t.classList.remove('active'));
            
            tab.classList.add('active');
            
            const tabName = tab.getAttribute('data-sub-tab');
            switchTab({ currentTarget: tab }, tabName);
        });
    });
    
    function initLayout() {
        if (window.innerWidth <= 768) {
            document.querySelector('.main-mobile-tab[data-main-tab="profile-tab"]').click();
        } else {
            document.querySelectorAll('.main-tab-content').forEach(c => c.classList.add('active'));
        }
    }
    
    initLayout();
    window.addEventListener('resize', initLayout);
});*/