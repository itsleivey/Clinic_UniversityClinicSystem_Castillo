const gender = document.getElementById('genderSelect');

function showForm(formType) {
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
        document.querySelector('.left-btn').disabled = true;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'medical') {
        medicalForm.style.display = 'block';
        formHeader.textContent = 'Medical & Dental History';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'family') {
        familymedicalhistoryForm.style.display = 'block';
        formHeader.textContent = 'Family Medical History';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'social') {
        personalsocialhistoryForm.style.display = 'block';
        formHeader.textContent = 'Personal & Social History';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'forfemales') {
        forfemalesForm.style.display = 'block';
        formHeader.textContent = 'For Females Only';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'physicalexamination') {
        physicalexaminationForm.style.display = 'block';
        formHeader.textContent = 'Physical Examination';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    } else if (formType === 'diagnostic') {
        diagnosticForm.style.display = 'block';
        formHeader.textContent = 'Diagnostic Results';
        document.querySelector('.left-btn').disabled = false;
        document.querySelector('.right-btn').disabled = false;
    }

    // Step Progress Update (Apply to All Forms)
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active', 'completed');
    });

    const stepMap = {
        personal: 'step1',
        medical: 'step2',
        family: 'step3',
        social: 'step4',
        forfemales: 'step5',
        physicalexamination: 'step6',
        diagnostic: 'step7'
    };

    document.getElementById(stepMap[formType]).classList.add('active');

    // Mark previous steps as completed
    for (let i = 1; i < parseInt(stepMap[formType].replace('step', '')); i++) {
        document.getElementById(`step${i}`).classList.add('completed');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let currentForm = 'personal';  // Track the current form
    const gender = document.getElementById('genderSelect'); // Get gender element

    function updateButtons() {
        document.querySelector('.left-btn').disabled = (currentForm === 'personal');
        document.querySelector('.right-btn').disabled = (currentForm === 'diagnostic');
    }

    document.querySelector('.left-btn').addEventListener('click', function() {
        if (currentForm === 'medical') {
            currentForm = 'personal';
        } else if (currentForm === 'family') {
            currentForm = 'medical';
        } else if (currentForm === 'social') {
            currentForm = 'family';
        } else if (currentForm === 'forfemales') {
            currentForm = 'social';
        } else if (currentForm === 'physicalexamination') {
            currentForm = (gender && gender.value === 'female') ? 'forfemales' : 'social';
        } else if (currentForm === 'diagnostic') {
            currentForm = 'physicalexamination';
        }
        showForm(currentForm);
        updateButtons();
    });

    document.querySelector('.right-btn').addEventListener('click', function() {
        if (currentForm === 'personal') {
            currentForm = 'medical';
        } else if (currentForm === 'medical') {
            currentForm = 'family';
        } else if (currentForm === 'family') {
            currentForm = 'social';
        } else if (currentForm === 'social') {
            currentForm = (gender && gender.value === 'female') ? 'forfemales' : 'physicalexamination';
            if (step5) {
                if (gender && gender.value === 'female') {
                    step5.classList.remove('disabled');
                } else {
                    step5.classList.add('disabled');
                    step5.classList.remove('active', 'completed');
                    const checkmarks = step5.querySelectorAll('.check-icon, .step-check');
                    checkmarks.forEach(check => check.remove());
                }
            }
        } else if (currentForm === 'forfemales') {
            currentForm = 'physicalexamination';
        } else if (currentForm === 'physicalexamination') {
            currentForm = 'diagnostic';
        }
        showForm(currentForm);
        updateButtons();
    });

    // Initialize
    showForm('personal'); 
    updateButtons();
});


