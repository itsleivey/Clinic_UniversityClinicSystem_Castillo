document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".navbar");
  const toggleButton = document.getElementById("toggle-btn");

  // Load saved sidebar state
  const isCollapsed = localStorage.getItem("navbar-collapsed") === "true";
  if (isCollapsed) {
    navbar.classList.add("collapsed");
    navbar.classList.remove("expanded");
  } else {
    navbar.classList.add("expanded");
    navbar.classList.remove("collapsed");
  }

  // Toggle sidebar and save new state
  toggleButton.addEventListener("click", () => {
    navbar.classList.toggle("collapsed");
    navbar.classList.toggle("expanded");

    const collapsed = navbar.classList.contains("collapsed");
    localStorage.setItem("navbar-collapsed", collapsed);
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const profileBtn = document.getElementById("profileBtn");
  const profileDropdown = document.getElementById("profileDropdown");

  // Toggle dropdown on click
  profileBtn.addEventListener("click", () => {
    profileDropdown.style.display =
      profileDropdown.style.display === "block" ? "none" : "block";
  });

  // Hide dropdown if click outside
  document.addEventListener("click", (e) => {
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.style.display = "none";
    }
  });
});
