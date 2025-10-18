document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".navbar");
  const toggleButton = document.getElementById("toggle-btn");

  // Mobile overlay
  let mobileOverlay = document.querySelector(".mobile-menu-overlay");
  if (!mobileOverlay) {
    mobileOverlay = document.createElement("div");
    mobileOverlay.className = "mobile-menu-overlay";
    document.body.appendChild(mobileOverlay);
  }

  function isMobile() {
    return window.innerWidth <= 1024; // breakpoint for mobile
  }

  // Desktop sidebar: collapsed/expanded
  function desktopSidebarHandler() {
    // Load saved state
    const isCollapsed = localStorage.getItem("navbar-collapsed") === "true";
    if (isCollapsed) {
      navbar.classList.add("collapsed");
      navbar.classList.remove("expanded");
    } else {
      navbar.classList.add("expanded");
      navbar.classList.remove("collapsed");
    }

    toggleButton.addEventListener("click", () => {
      if (!isMobile()) {
        navbar.classList.toggle("collapsed");
        navbar.classList.toggle("expanded");
        localStorage.setItem(
          "navbar-collapsed",
          navbar.classList.contains("collapsed")
        );
      }
    });
  }

  // Mobile sidebar: show/hide with overlay
  function mobileSidebarHandler() {
    toggleButton.addEventListener("click", () => {
      if (isMobile()) {
        navbar.classList.toggle("show");
        mobileOverlay.classList.toggle("show");
      }
    });

    mobileOverlay.addEventListener("click", () => {
      navbar.classList.remove("show");
      mobileOverlay.classList.remove("show");
    });

    const navLinks = document.querySelectorAll(".navbar a");
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        if (isMobile()) {
          navbar.classList.remove("show");
          mobileOverlay.classList.remove("show");
        }
      });
    });
  }

  // Profile dropdown
  const profileBtn = document.getElementById("profileBtn");
  const profileDropdown = document.getElementById("profileDropdown");

  profileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    profileDropdown.style.display =
      profileDropdown.style.display === "block" ? "none" : "block";
  });

  document.addEventListener("click", (e) => {
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.style.display = "none";
    }
  });

  // Initialize
  desktopSidebarHandler();
  mobileSidebarHandler();

  // Optional: reset mobile/desktop on window resize
  window.addEventListener("resize", () => {
    if (!isMobile()) {
      navbar.classList.remove("show");
      mobileOverlay.classList.remove("show");
    }
  });
});
