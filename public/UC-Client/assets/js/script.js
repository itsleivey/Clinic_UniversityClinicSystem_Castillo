function initializeProfileUpload(elements) {
  elements.profilePic?.addEventListener("click", () =>
    elements.imageUpload?.click()
  );
  elements.imageUpload?.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => (elements.profilePic.src = e.target.result);
      reader.readAsDataURL(file);
    }
  });
}

/*Dropdown Form*/
function toggleDropdown() {
  const content = document.getElementById("dropdownContent");
  content.classList.toggle("open");
}

function toggleDropdown2() {
  const content = document.getElementById("dropdownContent2");
  content.classList.toggle("open");
}

function toggleDropdown3() {
  const content = document.getElementById("dropdownContent3");
  content.classList.toggle("open");
}

function toggleDropdown4() {
  const content = document.getElementById("dropdownContent4");
  content.classList.toggle("open");
}
//=================================================================================

document.addEventListener("DOMContentLoaded", function () {
  const overlay = document.getElementById("profileAlertOverlay");
  const form = document.getElementById("profileAlertForm");

  if (overlay) {
    overlay.style.zIndex = "9999";

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      fetch(form.action, {
        method: "POST",
        body: new FormData(form),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            overlay.style.display = "none";
            document.querySelector(".main-content").style.pointerEvents =
              "auto";
            document.querySelector(".main-content").style.opacity = "1";
          }
        })
        .catch((error) => {
          console.error("Error:", error);
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
document
  .getElementById("progressForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    try {
      const response = await fetch("createnewprogress.php", {
        method: "POST",
        body: new FormData(e.target),
      });

      const result = await response.json();

      if (result.success) {
        // Update UI without reload
        document.getElementById("statusDisplay").textContent = "Inprogress";
        document.getElementById("createProgressSection").style.display = "none";
        document.getElementById("cancelProgressSection").style.display =
          "block";
      } else {
        alert("Error: " + result.message);
      }
    } catch (error) {
      alert("Network error: " + error.message);
    }
  });

document
  .getElementById("cancelProgress")
  ?.addEventListener("click", async () => {
    if (!confirm("Are you sure you want to cancel this progress?")) return;

    try {
      const response = await fetch("cancelprogress.php", {
        method: "POST",
        body: new FormData(document.getElementById("progressForm")),
      });

      const result = await response.json();

      if (result.success) {
        document.getElementById("statusDisplay").textContent = "Undone";
        document.getElementById("createProgressSection").style.display =
          "block";
        document.getElementById("cancelProgressSection").style.display = "none";
      } else {
        alert("Error: " + result.message);
      }
    } catch (error) {
      alert("Network error: " + error.message);
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
