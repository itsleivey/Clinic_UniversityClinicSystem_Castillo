document.getElementById('upload-btn').addEventListener('click', function () {
    document.getElementById('image-upload').click();
});

function previewImage() {
    const file = document.getElementById('image-upload').files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        document.getElementById('profile-pic').src = reader.result;
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}
//============================================================
function showExamInstructions() {
    document.getElementById('exam-modal').style.display = 'block';
}

function closeExamModal() {
    document.getElementById('exam-modal').style.display = 'none';
}

window.onclick = function (event) {
    const modal = document.getElementById('exam-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};
//=============================================================
function closeModal() {
    document.getElementById("logbookModal").style.display = "none";
}
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('logbookModal');

    // Disable closing on outside click
    window.onclick = function (event) {
        if (event.target == modal) {
            event.stopPropagation(); // Do nothing
        }
    };

    // Disable ESC key closing
    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            event.preventDefault();
        }
    });
});
//=============================================================
document.getElementById('medicalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Form submitted successfully!');
            // Optionally redirect or clear form
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the form');
    });
});