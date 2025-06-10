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
/*
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

document.addEventListener('DOMContentLoaded', function () {
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
*/
function scrollToActiveStep() {
    const activeStep = document.querySelector('.step.active');
    if (activeStep) {
        const stepNav = document.querySelector('.step-nav');
        if (stepNav) {
            const stepNavRect = stepNav.getBoundingClientRect();
            const stepRect = activeStep.getBoundingClientRect();

            const scrollPosition = stepRect.left - stepNavRect.left + stepNav.scrollLeft - (stepNavRect.width / 2) + (stepRect.width / 2);

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
        const stepNav = document.querySelector('.step-nav');
        if (stepNav) {
            const stepNavRect = stepNav.getBoundingClientRect();
            const stepRect = activeStep.getBoundingClientRect();

            const scrollPosition = stepRect.left - stepNavRect.left + stepNav.scrollLeft - (stepNavRect.width / 2) + (stepRect.width / 2);

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

    scrollToActiveStep();
}

document.addEventListener('DOMContentLoaded', function () {
    const savedData = localStorage.getItem('personalFormData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        document.querySelectorAll('input, select').forEach(input => {
            if (input.name && formData[input.name] !== undefined) {
                input.value = formData[input.name];

                if (input.tagName === 'SELECT') {
                    const option = input.querySelector(`option[value="${formData[input.name]}"]`);
                    if (option) option.selected = true;
                }
            }
        });
    }

    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', saveFormData);
        input.addEventListener('change', saveFormData);
    });
});

function saveFormData() {
    const formData = {};
    document.querySelectorAll('input, select').forEach(input => {
        if (input.name) {
            formData[input.name] = input.value;
        }
    });
    localStorage.setItem('personalFormData', JSON.stringify(formData));
}

document.getElementById('personalInfoForm').addEventListener('submit', function () {
    localStorage.removeItem('personalFormData');
});

document.getElementById('logoutbtn').addEventListener('click', function () {
    fetch('logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
        .then(response => {
            if (response.ok) {
                window.location.href = 'index.php';
            }
        })
        .catch(error => console.error('Logout error:', error));
});



$(document).ready(function () {
    $('#personalInfoForm').on('submit', function (event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'Personal-Form.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                var updatedData = JSON.parse(response);
                updateForm(updatedData);
            },
            error: function () {
                alert('Error updating data.');
            }
        });
    });

    function updateForm(data) {
        $('#personalInfoForm input[name="surname"]').val(data.surname);
        $('#personalInfoForm input[name="given_name"]').val(data.given_name);
        $('#personalInfoForm input[name="middle_name"]').val(data.middle_name);
        $('#personalInfoForm input[name="age"]').val(data.age);
        $('#personalInfoForm select[name="gender"]').val(data.gender);
        $('#personalInfoForm input[name="dob"]').val(data.dob);
        $('#personalInfoForm select[name="status"]').val(data.status);
        $('#personalInfoForm input[name="course"]').val(data.course);
        $('#personalInfoForm input[name="school_year_entered"]').val(data.school_year_entered);
        $('#personalInfoForm input[name="current_address"]').val(data.current_address);
        $('#personalInfoForm input[name="contact_number"]').val(data.contact_number);
        $('#personalInfoForm input[name="mothers_name"]').val(data.mothers_name);
        $('#personalInfoForm input[name="fathers_name"]').val(data.fathers_name);
        $('#personalInfoForm input[name="guardians_name"]').val(data.guardians_name);
        $('#personalInfoForm input[name="emergency_contact_name"]').val(data.emergency_contact_name);
        $('#personalInfoForm input[name="emergency_contact_relationship"]').val(data.emergency_contact_relationship);

        // Optionally, you can change the button text
        if (data.surname) {
            $('.form-buttons').text('Update');
        } else {
            $('.form-buttons').text('Save');
        }
    }
});

//=======================female health history========================
document.addEventListener('DOMContentLoaded', function () {
    // Dysmenorrhea severity toggle
    document.querySelectorAll('input[name="dysmenorrhea"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('fem-severityRow').style.display =
                this.value === 'yes' ? 'block' : 'none';
        });
    });

    // Pregnancy details toggle
    document.querySelectorAll('input[name="previousPregnancy"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('fem-pregnancyDetailsRow').style.display =
                this.value === 'yes' ? 'block' : 'none';
        });
    });

    // Children details toggle
    document.querySelectorAll('input[name="hasChildren"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('fem-childrenDetailsRow').style.display =
                this.value === 'yes' ? 'block' : 'none';
        });
    });
});
//========================================================================
document.addEventListener('DOMContentLoaded', function () {
    // Dysmenorrhea severity
    const dysmenorrheaRadios = document.querySelectorAll('input[name="Dysmenorrhea"]');
    const severityRow = document.getElementById('fem-severityRow');

    dysmenorrheaRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            severityRow.style.display = this.value === 'yes' ? '' : 'none';
        });
    });

    // Pregnancy details
    const pregnancyRadios = document.querySelectorAll('input[name="PreviousPregnancy"]');
    const pregnancyDetailsRow = document.getElementById('fem-pregnancyDetailsRow');

    pregnancyRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            pregnancyDetailsRow.style.display = this.value === 'yes' ? '' : 'none';
        });
    });

    // Children details
    const childrenRadios = document.querySelectorAll('input[name="HasChildren"]');
    const childrenDetailsRow = document.getElementById('fem-childrenDetailsRow');

    childrenRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            childrenDetailsRow.style.display = this.value === 'yes' ? '' : 'none';
        });
    });
});
//========================================================================
function showFormMessage(message, type, containerId) {

    $('#' + containerId + ' #topAlert').remove();

    const cls = type === 'success' ? 'top-alert success' : 'top-alert error';
    const $alert = $(`
        <div id="topAlert" class="${cls}">
            ${message}
            <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
        </div>
    `);

    $('#' + containerId).prepend($alert);
    $alert.hide().fadeIn();

    setTimeout(() => {
        $alert.fadeOut(400, function () {
            $(this).remove();
        });
    }, 5000);
}

$(document).ready(function () {
    $('#personalInfoForm').off('submit').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);

        $('#personal-info-input #topAlert').remove();

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function () {

            },
            success: function (response) {
                if (response.success) {
                    showFormMessage('Personal information updated successfully', 'success', 'personal-info-input');
                } else {
                    showFormMessage(response.message || 'Error updating data', 'error', 'personal-info-input');
                }
            },
            error: function (xhr, status, error) {
                showFormMessage('Server error: ' + error, 'error', 'personal-info-input');
            }
        });
    });
    $('#medicalDentalForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFormMessage('Medical & dental history updated successfully', 'success', 'medical-dental-history-input');
                } else {
                    showFormMessage(response.message, 'error', 'medical-dental-history-input');
                }
            },
            error: function (xhr, status, error) {
                showFormMessage('Error saving data: ' + error, 'error', 'medical-dental-history-input');
            }
        });
    });

    $('#family-med-historyForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFormMessage('Updated family medical history', 'success', 'family-medical-history-input');
                } else {
                    showFormMessage(response.message, 'error', 'family-medical-history-input');
                }
            },
            error: function (xhr, status, error) {
                showFormMessage('Error saving data: ' + error, 'error', 'family-medical-history-input');
            }
        });
    });

    $('#personal-social-historyForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: 'SocialHis_POST.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFormMessage('Personal & social history updated successfully', 'success', 'personal-social-history-input');
                    updateForm(response.data);
                } else {
                    showFormMessage(response.message, 'error', 'personal-social-history-input');
                }
            },
            error: function (xhr, status, error) {
                showFormMessage('Error saving data: ' + error, 'error', 'personal-social-history-input');
            }
        });
    });

    function updateForm(data) {
        $('#alcoholIntake').val(data.alcoholIntake || 'no').trigger('change');
        $('input[name="alcoholDetails"]').val(data.alcoholDetails || '');
        $('#tobaccoUse').val(data.tobaccoUse || 'no').trigger('change');
        $('input[name="tobaccoDetails"]').val(data.tobaccoDetails || '');
        $('#drugUse').val(data.drugUse || 'no').trigger('change');
        $('input[name="drugDetails"]').val(data.drugDetails || '');
    }

    $('#for-female-form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: 'FemMed_Logic.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showFormMessage('Female medical information updated successfully', 'success', 'for-females-input');
                } else {
                    showFormMessage(response.message, 'error', 'for-females-input');
                }
            },
            error: function (xhr, status, error) {
                showFormMessage('Error saving data: ' + error, 'error', 'for-females-input');
            }
        });
    });

    $('input[name="Dysmenorrhea"]').change(function () {
        $('#fem-severityRow').toggle($(this).val() === 'yes');
    });
    $('input[name="PreviousPregnancy"]').change(function () {
        $('#fem-pregnancyDetailsRow').toggle($(this).val() === 'yes');
    });
    $('input[name="HasChildren"]').change(function () {
        $('#fem-childrenDetailsRow').toggle($(this).val() === 'yes');
    });
});

//=========================================================================
document.getElementById('saveAllButton').addEventListener('click', function () {
    const forms = [
        document.getElementById('personalInfoForm'),
        document.getElementById('medicalDentalForm'),
        document.getElementById('familyMedHistoryForm'),
        document.getElementById('personalSocialHistoryForm'),
        document.getElementById('femaleForm')
    ];

    const formData = new FormData();
    const messageDiv = document.getElementById('master-message');
    messageDiv.style.display = 'none';

    // Collect data from all forms
    forms.forEach(form => {
        if (form) {
            const formElements = form.elements;
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                if (element.name && element.type !== 'file') {
                    if (element.type === 'checkbox') {
                        formData.append(element.name, element.checked ? '1' : '0');
                    } else if (element.type === 'radio') {
                        if (element.checked) {
                            formData.append(element.name, element.value);
                        }
                    } else {
                        formData.append(element.name, element.value);
                    }
                }
            }
        }
    });

    formData.append('saveAll', 'true');

    const saveButton = document.getElementById('saveAllButton');
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';

    fetch('saveAllData.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('All information saved successfully!', 'success');
            } else {
                showMessage(data.message || 'Error saving some information', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while saving', 'error');
        })
        .finally(() => {
            saveButton.disabled = false;
            saveButton.textContent = 'Save All Information';
        });

    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.style.display = 'block';
        messageDiv.className = type;

        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
});
//=========================================================================
document.addEventListener('DOMContentLoaded', function () {
    const genderSelect = document.getElementById('genderSelect');
    const femaleSection = document.getElementById('for-females-input');

    function toggleFemaleSection() {
        if (genderSelect.value === 'female') {
            femaleSection.style.display = 'flex';
        } else {
            femaleSection.style.display = 'none';
        }
    }

    toggleFemaleSection();

    genderSelect.addEventListener('change', toggleFemaleSection);
});

