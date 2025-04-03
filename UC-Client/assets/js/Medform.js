const gender = document.getElementById('genderSelect');
let currentForm = 'personal';
const forms = {
    personal: document.getElementById('personal-info-input'),
    medical: document.getElementById('medical-dental-history-input'),
    family: document.getElementById('family-medical-history-input'),
    social: document.getElementById('personal-social-history-input'),
    forfemales: document.getElementById('for-females-input'),
    physicalexamination: document.getElementById('physical-examination-input'),
    diagnostic: document.getElementById('Diagnostic-Results')
};
const stepMap = {
    personal: 'step1',
    medical: 'step2',
    family: 'step3',
    social: 'step4',
    forfemales: 'step5',
    physicalexamination: 'step6',
    diagnostic: 'step7'
};

function showForm(formType) {
    Object.values(forms).forEach(form => {
        if (form) form.style.display = 'none';
    });

    const formToShow = forms[formType];
    if (formToShow) {
        formToShow.style.display = 'block';
    }

    const formHeader = document.querySelector('.form-header h2');
    if (formHeader) {
        formHeader.textContent = {
            personal: 'Personal Information',
            medical: 'Medical & Dental History',
            family: 'Family Medical History',
            social: 'Personal & Social History',
            forfemales: 'For Females Only',
            physicalexamination: 'Physical Examination',
            diagnostic: 'Diagnostic Results'
        }[formType];
    }

    document.querySelector('.left-btn').disabled = (formType === 'personal');
    document.querySelector('.right-btn').disabled = (formType === 'diagnostic');

    updateStepNavigation(formType);
}

function updateStepNavigation(currentFormType) {
    const isFemale = gender && gender.value === 'female';
    const step5 = document.getElementById('step5');

    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active', 'completed');
    });

    if (step5) {
        step5.style.display = isFemale ? '' : 'none';
    }

    const activeStep = document.getElementById(stepMap[currentFormType]);
    if (activeStep) activeStep.classList.add('active');

    const currentStepNumber = parseInt(stepMap[currentFormType].replace('step', ''));
    for (let i = 1; i < currentStepNumber; i++) {
        const step = document.getElementById(`step${i}`);
        if (step) step.classList.add('completed');
    }
}

function handleNavigation(direction) {
    const isFemale = gender && gender.value === 'female';
    const navigationMap = {
        left: {
            personal: 'personal',
            medical: 'personal',
            family: 'medical',
            social: 'family',
            forfemales: 'social',
            physicalexamination: isFemale ? 'forfemales' : 'social',
            diagnostic: 'physicalexamination'
        },
        right: {
            personal: 'medical',
            medical: 'family',
            family: 'social',
            social: isFemale ? 'forfemales' : 'physicalexamination',
            forfemales: 'physicalexamination',
            physicalexamination: 'diagnostic',
            diagnostic: 'diagnostic'
        }
    };

    currentForm = navigationMap[direction][currentForm];
    showForm(currentForm);
}

document.addEventListener('DOMContentLoaded', function() {
    Object.values(forms).forEach(form => {
        if (form) form.style.display = 'none';
    });

    document.querySelector('.left-btn').addEventListener('click', () => handleNavigation('left'));
    document.querySelector('.right-btn').addEventListener('click', () => handleNavigation('right'));

    gender?.addEventListener('change', () => {
        if (currentForm === 'forfemales' && gender.value !== 'female') {
            currentForm = 'physicalexamination';
        }
        showForm(currentForm);
    });

    showForm('personal');
});

function scrollToActiveStep() {
    const activeStep = document.querySelector('.step.active');
    if (activeStep) {
        const stepNav = document.querySelector('.step-nav'); // Make sure you have this container
        if (stepNav) {
            // Calculate positions
            const stepNavRect = stepNav.getBoundingClientRect();
            const stepRect = activeStep.getBoundingClientRect();
            
            // Calculate scroll position
            const scrollPosition = stepRect.left - stepNavRect.left + stepNav.scrollLeft - (stepNavRect.width / 2) + (stepRect.width / 2);
            
            // Smooth scroll to the active step
            stepNav.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        }
    }
}

function scrollToActiveStep() {
    const activeStep = document.querySelector('.step.active');
    if (activeStep) {
        const stepNav = document.querySelector('.step-nav'); // Make sure you have this container
        if (stepNav) {
            // Calculate positions
            const stepNavRect = stepNav.getBoundingClientRect();
            const stepRect = activeStep.getBoundingClientRect();
            
            // Calculate scroll position
            const scrollPosition = stepRect.left - stepNavRect.left + stepNav.scrollLeft - (stepNavRect.width / 2) + (stepRect.width / 2);
            
            // Smooth scroll to the active step
            stepNav.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        }
    }
}

function updateStepNavigation(currentFormType) {
    const isFemale = gender && gender.value === 'female';
    const step5 = document.getElementById('step5');

    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active', 'completed');
    });

    if (step5) {
        step5.style.display = isFemale ? '' : 'none';
    }

    const activeStep = document.getElementById(stepMap[currentFormType]);
    if (activeStep) activeStep.classList.add('active');

    const currentStepNumber = parseInt(stepMap[currentFormType].replace('step', ''));
    for (let i = 1; i < currentStepNumber; i++) {
        const step = document.getElementById(`step${i}`);
        if (step) step.classList.add('completed');
    }
    
    // Add this line to scroll to the active step
    scrollToActiveStep();
}