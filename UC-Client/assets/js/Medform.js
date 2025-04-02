// Function to show only one form at a time and update header
function showForm(formType) {
    // Get all form sections and header element
    const personalForm = document.getElementById('personal-info-input');
    const medicalForm = document.getElementById('medical-dental-history-input');
    const familymedicalhistoryForm = document.getElementById('family-medical-history-input');
    const personalsocialhistoryForm = document.getElementById('personal-social-history-input');
    const forfemalesForm = document.getElementById('for-females-input');
    const physicalexaminationForm = document.getElementById('physical-examination-input');
    const diagnosticForm = document.getElementById('Diagnostic-Results');
    const formHeader = document.querySelector('.form-header h2');
    
    // Hide all forms first
    personalForm.style.display = 'none';
    medicalForm.style.display = 'none';
    familymedicalhistoryForm.style.display = 'none';
    personalsocialhistoryForm.style.display = 'none';
    forfemalesForm.style.display = 'none';
    physicalexaminationForm.style.display = 'none';
    diagnosticForm.style.display = 'none';
    
    // Show the requested form and update header
    if (formType === 'personal') {
        personalForm.style.display = 'block';
        formHeader.textContent = 'Personal Information';
        // Button states
        document.querySelector('.left-btn').disabled = true;
        document.querySelector('.right-btn').disabled = false;
    } 
    else if (formType === 'medical') {
        medicalForm.style.display = 'block';
        formHeader.textContent = 'Medical & Dental History';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
    else if (formType === 'family') {
        familymedicalhistoryForm.style.display = 'block';
        formHeader.textContent = 'Family Medical History';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
    else if (formType === 'social') {
        personalsocialhistoryForm.style.display = 'block';
        formHeader.textContent = 'Personal & Social History';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
    else if (formType === 'forfemales') {
        forfemalesForm.style.display = 'block';
        formHeader.textContent = 'For Females Only';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
    else if (formType === 'physicalexamination') {
        physicalexaminationForm.style.display = 'block';
        formHeader.textContent = 'Physical Examination';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
    else if (formType === 'diagnostic') {
        diagnosticForm.style.display = 'block';
        formHeader.textContent = 'Diagnostic Results';
        // Button states
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let currentForm = 'personal';  // Track the current form

    function updateButtons() {
        document.querySelector('.left-btn').disabled = (currentForm === 'personal');
        document.querySelector('.right-btn').disabled = (currentForm === 'diagnostic');
    }

    document.querySelector('.left-btn').addEventListener('click', function() {
        if (currentForm === 'medical') {
            showForm('personal');
            currentForm = 'personal';
        } else if (currentForm === 'family') {
            showForm('medical');
            currentForm = 'medical';
        } else if (currentForm === 'social') {
            showForm('family');
            currentForm = 'family';
        } else if (currentForm === 'forfemales') {
            showForm('social');
            currentForm = 'social';
        } else if (currentForm === 'physicalexamination') {
            showForm('forfemales');
            currentForm = 'forfemales';
        } else if (currentForm === 'diagnostic') {
            showForm('physicalexamination');
            currentForm = 'physicalexamination';
        }
        updateButtons();
    });

    document.querySelector('.right-btn').addEventListener('click', function() {
        if (currentForm === 'personal') {
            showForm('medical');
            currentForm = 'medical';
        } else if (currentForm === 'medical') {
            showForm('family');
            currentForm = 'family';
        } else if (currentForm === 'family') {
            showForm('social');
            currentForm = 'social';
        } else if (currentForm === 'social') {
            showForm('forfemales');
            currentForm = 'forfemales';
        } else if (currentForm === 'forfemales') {
            showForm('physicalexamination');
            currentForm = 'physicalexamination';
        } else if (currentForm === 'physicalexamination') {
            showForm('diagnostic');
            currentForm = 'diagnostic';
        }
        updateButtons();
    });

    showForm('personal');  // Show default form on load
    updateButtons();  // Ensure button states are correct
});

document.addEventListener('DOMContentLoaded', function() {
    // Dysmenorrhea severity toggle
    document.querySelectorAll('#for-female-form input[name="dysmenorrhea"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('fem-severityRow').style.display = 
                this.value === 'yes' ? 'flex' : 'none';
        });
    });
    
    // Pregnancy details toggle
    document.querySelectorAll('#for-female-form input[name="previousPregnancy"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('fem-pregnancyDetailsRow').style.display = 
                this.value === 'yes' ? 'flex' : 'none';
        });
    });
    
    // Children details toggle
    document.querySelectorAll('#for-female-form input[name="hasChildren"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('fem-childrenDetailsRow').style.display = 
                this.value === 'yes' ? 'flex' : 'none';
        });
    });
});

//========================================
function updateProgress() {
    let completedSteps = 0;
    let totalSteps = 2;

    // Check status
    if (document.getElementById("status1").textContent.includes("Completed")) {
        completedSteps++;
    }
    if (document.getElementById("status2").textContent.includes("Completed")) {
        completedSteps++;
    }

    // Calculate percentage
    let progressPercentage = (completedSteps / totalSteps) * 100;
    document.getElementById("progressText").innerText = `${progressPercentage}%`;

    // Change progress bar color based on progress
    let circle = document.getElementById("progressCircle");
    if (progressPercentage > 0) {
        circle.style.borderColor = "#4caf50";
    }

    // Hide reminder if all tasks are completed
    if (progressPercentage === 100) {
        document.getElementById("reminder").style.display = "none";
    }
}

// Simulate progress change (You can update these values dynamically based on user input)
setTimeout(() => {
    document.getElementById("status1").textContent = "Completed";
    updateProgress();
}, 2000);

setTimeout(() => {
    document.getElementById("status2").textContent = "Completed";
    updateProgress();
}, 4000);