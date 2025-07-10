document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', function () {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('#personal-info-div, #medical-history, #medical-cert, #visit-history, #medrec, #rx, #np_medical-history').forEach(content => {
      content.style.display = 'none';
    });

    this.classList.add('active');
    const contentId = this.getAttribute('data-target');
    const targetContent = document.getElementById(contentId);
    if (targetContent) {
      targetContent.style.display = 'block';
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const activeTab = document.querySelector('.tab.active');
  if (activeTab) {
    const contentId = activeTab.getAttribute('data-target');
    const targetContent = document.getElementById(contentId);
    if (targetContent) {
      targetContent.style.display = 'block'; // or 'flex' if you want
    }

    const img = activeTab.querySelector('img');
    if (img) {
      img.src = img.getAttribute('data-active');
    }
  }
});


window.addEventListener('DOMContentLoaded', () => {
  const activeTab = document.querySelector('.tab.active');
  if (activeTab) {
    const contentId = activeTab.getAttribute('data-target');
    const targetContent = document.getElementById(contentId);
    if (targetContent) {
      targetContent.style.display = 'block';
    }
  }
});


document.querySelectorAll('.medtab').forEach(tab => {
  tab.addEventListener('click', function () {
    document.querySelectorAll('.medtab').forEach(t => t.classList.remove('active'));

    document.querySelectorAll('#medicaldentalhistory, #familymedicalhistory, #personalsocialhistory, #menstrualHistory, #physicalExamination, #diagnosticResults').forEach(content => {
      content.style.display = 'none';
    });

    this.classList.add('active');

    const contentId = this.getAttribute('data-target');
    const targetContent = document.getElementById(contentId);
    if (targetContent) {
      targetContent.style.display = 'block';
    }
  });
});

function submitForm() {
  const form = document.getElementById('phy-exam-form');
  const url = form.action;
  const body = new URLSearchParams(new FormData(form));

  fetch(url, {
    method: 'POST',
    body: body,
  })
    .then(response => {
      console.log('HTTP status:', response.status, response.statusText);
      return response.text().then(text => {
        try {
          const json = JSON.parse(text);
          return json;
        } catch (err) {
          console.error('Response was not JSON:', text);
          throw new Error('Invalid JSON response');
        }
      });
    })
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      alert('An error occurred when submitting:\n' + err.message);
    });
}
//======================================================
/*
function generateMedicalCertificatePDF() {
  // Fill hidden fields first
  document.getElementById('hidden-patient-name').value = document.getElementById('patient-name').innerText.trim();
  document.getElementById('hidden-patient-age').value = document.getElementById('patient-age').innerText.trim();
  document.getElementById('hidden-exam-date').value = document.getElementById('exam-date').value;
  document.getElementById('hidden-findings').value = document.getElementById('findings').innerText.trim();
  document.getElementById('hidden-impression').value = document.getElementById('impression').innerText.trim();
  document.getElementById('hidden-note').value = document.getElementById('note').innerText.trim();
  document.getElementById('hidden-license-no').value = document.getElementById('license-no').innerText.trim();
  document.getElementById('hidden-date-issued').value = document.getElementById('date-issued').value.trim();

  // Select the form
  const element = document.getElementById('med-cert-form');

  // PDF options for A4
  const opt = {
      margin:     [0.5, 0.5, 0.5, 0.5],  // top, left, bottom, right (inches)
      filename:   'medical_certificate.pdf',
      image:      { type: 'jpeg', quality: 1 },
      html2canvas:{ scale: 3, useCORS: true },
      jsPDF:      { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  // Hide the buttons temporarily while exporting
  const buttons = document.querySelector('.cert-controls');
  buttons.style.display = 'none';

  // Generate PDF
  html2pdf().from(element).set(opt).save().then(() => {
      // Show buttons again after saving
      buttons.style.display = 'block';
  });
}
/*============================================================*/
function clearMedicalCert() {
  // Clear contenteditable spans
  document.getElementById('patient-name').innerText = '';
  document.getElementById('patient-age').innerText = '';
  document.getElementById('findings').innerText = '';
  document.getElementById('impression').innerText = '';
  document.getElementById('note').innerText = '';
  document.getElementById('license-no').innerText = '';

  // Clear date inputs
  document.getElementById('exam-date').value = '';
  document.getElementById('date-issued').value = '';

  // Clear hidden fields (optional)
  document.getElementById('hidden-patient-name').value = '';
  document.getElementById('hidden-patient-age').value = '';
  document.getElementById('hidden-exam-date').value = '';
  document.getElementById('hidden-findings').value = '';
  document.getElementById('hidden-impression').value = '';
  document.getElementById('hidden-note').value = '';
  document.getElementById('hidden-license-no').value = '';
  document.getElementById('hidden-date-issued').value = '';
}

//============================================================
document.getElementById('progressForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  try {
    const response = await fetch('createnewprogress.php', {
      method: 'POST',
      body: new FormData(e.target)
    });

    const result = await response.json();

    if (result.success) {
      // Update UI without reload
      document.getElementById('statusDisplay').textContent = 'Inprogress';
      document.getElementById('createProgressSection').style.display = 'none';
      document.getElementById('cancelProgressSection').style.display = 'block';
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    alert('Network error: ' + error.message);
  }
});

document.getElementById('cancelProgress')?.addEventListener('click', async () => {
  if (!confirm('Are you sure you want to cancel this progress?')) return;

  try {
    const response = await fetch('cancelprogress.php', {
      method: 'POST',
      body: new FormData(document.getElementById('progressForm'))
    });

    const result = await response.json();

    if (result.success) {
      document.getElementById('statusDisplay').textContent = 'Undone';
      document.getElementById('createProgressSection').style.display = 'block';
      document.getElementById('cancelProgressSection').style.display = 'none';
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    alert('Network error: ' + error.message);
  }
});
//============================================================
function preparePdfData() {
  document.getElementById('input_patient_name').value = document.getElementById('name').innerText.trim();
  document.getElementById('input_patient_age').value = document.getElementById('age').innerText.trim();
  document.getElementById('input_patient_address').value = document.getElementById('address').innerText.trim();
  document.getElementById('input_patient_course').value = document.getElementById('course').innerText.trim();

  if (!document.getElementById('input_patient_name').value) {
    alert('Please enter the name.');
    return false;
  }
  return true;
}

function printPdf() {
  const clientId = document.getElementById('client-id').value;
  if (!clientId) {
    alert('Client ID is missing.');
    return;
  }

  const patient_name = document.getElementById('name').textContent.trim();
  const patient_age = document.getElementById('age').textContent.trim();
  const patient_address = document.getElementById('address').textContent.trim();
  const patient_course = document.getElementById('course').textContent.trim();

  const form = document.querySelector('form');
  const formData = new FormData(form);

  const params = new URLSearchParams();

  params.append('patient_name', patient_name);
  params.append('patient_age', patient_age);
  params.append('patient_address', patient_address);
  params.append('patient_course', patient_course);

  for (const [key, value] of formData.entries()) {
    params.append(key, value);
  }

  const url = `manageclients.dbf/patients_rec_genpdf.php?${params.toString()}`;
  window.open(url, '_blank');
}
