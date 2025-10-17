document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registerForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm-password");
  const sexSelect = document.getElementById("sex");
  const dobInput = document.getElementById("dob");

  function showError(input, message) {
    let container = input.closest(".input-container") || input.parentElement;
    let error = container.querySelector(".error-message");

    if (!error) {
      error = document.createElement("div");
      error.className = "error-message";
      error.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${message}</span>`;
      container.appendChild(error);
    } else {
      error.querySelector("span").textContent = message;
      error.classList.remove("error-hidden");
    }
  }

  function clearError(input) {
    let container = input.closest(".input-container") || input.parentElement;
    const error = container.querySelector(".error-message");
    if (error) error.classList.add("error-hidden");
  }

  emailInput.addEventListener("input", () => {
    const emailVal = emailInput.value.trim();
    const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

    if (!emailRegex.test(emailVal)) {
      showError(emailInput, "Enter a valid email address.");
      return;
    } else {
      clearError(emailInput);
    }

    clearTimeout(emailInput.timer);
    emailInput.timer = setTimeout(() => {
      fetch("validate_email.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "email=" + encodeURIComponent(emailVal),
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.valid) {
            showError(emailInput, data.message);
          } else {
            clearError(emailInput);
          }
        })
        .catch(() =>
          showError(emailInput, "Unable to validate email right now.")
        );
    }, 500);
  });

  const passwordErrorDiv = document.getElementById("passwordError");

  passwordInput.addEventListener("input", () => {
    const passVal = passwordInput.value;
    const strongRegex = /^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,})/;

    if (passVal.length < 8) {
      passwordErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Password must be at least 8 characters long.`;
      passwordErrorDiv.classList.remove("error-hidden");
    } else if (!strongRegex.test(passVal)) {
      passwordErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Password must include a number and special character.`;
      passwordErrorDiv.classList.remove("error-hidden");
    } else {
      passwordErrorDiv.classList.add("error-hidden");
    }
  });

  const confirmPasswordErrorDiv = document.getElementById(
    "confirmPasswordError"
  );

  confirmPasswordInput.addEventListener("input", () => {
    if (confirmPasswordInput.value !== passwordInput.value) {
      confirmPasswordErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Passwords do not match.`;
      confirmPasswordErrorDiv.classList.remove("error-hidden");
    } else {
      confirmPasswordErrorDiv.classList.add("error-hidden");
    }
  });

  sexSelect.addEventListener("change", () => {
    if (!sexSelect.value) {
      showError(sexSelect, "Please select your sex.");
    } else {
      clearError(sexSelect);
    }
  });

  dobInput.addEventListener("input", () => {
    const dobVal = dobInput.value;
    const dobErrorDiv = document.getElementById("dobError");

    if (!dobVal) {
      dobErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>Please select your date of birth.</span>`;
      dobErrorDiv.classList.remove("error-hidden");
      return;
    }

    const dob = new Date(dobVal);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
      age--;
    }

    if (age < 17) {
      dobErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>You must be at least 17 years old to register.</span>`;
      dobErrorDiv.classList.remove("error-hidden");
    } else {
      dobErrorDiv.classList.add("error-hidden");
    }
  });

  form.addEventListener("submit", (e) => {
    const visibleErrors = form.querySelectorAll(
      ".error-message:not(.error-hidden)"
    );
    if (visibleErrors.length > 0) {
      e.preventDefault();
      alert("Please fix all errors before submitting.");
    }
  });
});
