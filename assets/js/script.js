function scrollToLogin() {
    document.getElementById("login-section").scrollIntoView({ behavior: "smooth", block: "start" });
}

function autoScrollToLogin() {
    setTimeout(() => {
        document.querySelector(".right-section").scrollIntoView({ behavior: "smooth" });
    }, 100);
}
