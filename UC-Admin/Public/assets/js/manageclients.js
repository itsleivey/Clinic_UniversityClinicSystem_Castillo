document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function () {

        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');

        this.classList.add('active');
        const contentId = this.getAttribute('data-target');
        const targetContent = document.getElementById(contentId);
        if (targetContent) {
            targetContent.style.display = 'block';
        }

        const dropdownCon = document.querySelector('.drop-down-con');
        if (dropdownCon) {
            if (contentId === 'personnel-content') {
                dropdownCon.style.display = 'none';
            } else {
                dropdownCon.style.display = 'flex';
            }
        }
    });
});

document.getElementById('clientTypeDropdown').addEventListener('change', function() {
    const selectedContent = this.value;
    
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
     const targetContent = document.getElementById(selectedContent);
    if (targetContent) {
        targetContent.style.display = 'block';
        
        // Ensure the table container maintains scroll functionality
        const tableContainer = targetContent.querySelector('.table-div');
        if (tableContainer) {
            tableContainer.style.maxHeight = '500px';
            tableContainer.style.overflowY = 'auto';
        }
    }
});

function addDoubleClickToRows() {
    document.querySelectorAll('.client-row').forEach(row => {
        row.addEventListener('dblclick', function() {
            // Find the eye icon link in this row
            const eyeIconLink = this.querySelector('a[title="View Profile"]');
            if (eyeIconLink) {
                window.location.href = eyeIconLink.href;
            }
        });
        
        // Optional: Add hover effect to indicate clickability
        row.style.cursor = 'pointer';
    });
}

// Initialize double-click on page load
document.addEventListener('DOMContentLoaded', function() {
    addDoubleClickToRows();
    
    // Re-initialize after search results are loaded
    const originalLoadFilteredData = loadFilteredData;
    loadFilteredData = function(tabId, clientType, searchId) {
        return originalLoadFilteredData(tabId, clientType, searchId).then(() => {
            addDoubleClickToRows();
        });
    };
});

// Also update the existing loadFilteredData function to maintain the promise chain
const originalLoadFilteredData = loadFilteredData;
loadFilteredData = function(tabId, clientType, searchId) {
    return originalLoadFilteredData(tabId, clientType, searchId).then(() => {
        addDoubleClickToRows();
    });
};

function loadFilteredData(tabId, clientType, searchId) {
    return fetch(`manageclients.dbf/get_user.php?client_type=${clientType}&id_filter=${encodeURIComponent(searchId)}`)
        .then(response => response.text())
        .then(html => {
            // Replace "View" buttons with eye icons in the returned HTML
            const updatedHtml = html.replace(
                /<a href="ClientProfile\.php\?id=([^"]+)" class="btn btn-primary btn-sm">View<\/a>/g,
                '<a href="ClientProfile.php?id=$1" title="View Profile"><i class="fas fa-eye eye-icon" style="color: #000; font-size: 18px;"></i></a>'
            );
            
            const tbody = document.querySelector(`#${tabId} tbody`);
            if (tbody) {
                tbody.innerHTML = updatedHtml;
                
                // Ensure scroll is maintained after loading new data
                const tableContainer = document.querySelector(`#${tabId} .table-div`);
                if (tableContainer) {
                    tableContainer.style.maxHeight = '500px';
                    tableContainer.style.overflowY = 'auto';
                }
            }
        });
}

// Initialize scroll on page load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all table containers have scroll enabled
    document.querySelectorAll('.table-div').forEach(container => {
        container.style.maxHeight = '500px';
        container.style.overflowY = 'auto';
    });
    
    const urlParams = new URLSearchParams(window.location.search);
    const idFilter = urlParams.get('id_filter');

    if (idFilter) {
        searchInput.value = idFilter;
    }
    
    // Set default tab to students
    document.getElementById('students-content').style.display = 'block';
});

function initializeEmailValidation() {
    const emailInput = document.getElementById('emailInput');
    const emailError = document.getElementById('emailError');
    const saveButton = document.getElementById('saveButton');
    const form = document.getElementById('addPatientForm');

    if (emailInput) {
        // Real-time email validation on input change
        emailInput.addEventListener('blur', function() {
            validateEmail(this.value);
        });

        // Clear error when user starts typing again
        emailInput.addEventListener('input', function() {
            if (emailError.style.display !== 'none') {
                emailError.style.display = 'none';
                emailInput.style.borderColor = '#ddd';
                saveButton.disabled = false;
                saveButton.style.backgroundColor = '#28a745';
            }
        });
    }

    // Form submission validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = emailInput.value.trim();
            if (!validateEmailOnSubmit(email)) {
                e.preventDefault(); // Prevent form submission
            }
        });
    }
}

// Real-time email validation
function validateEmail(email) {
    if (email === '') return;

    fetch('manageclients.dbf/check_email.php?email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            const emailError = document.getElementById('emailError');
            const emailInput = document.getElementById('emailInput');
            const saveButton = document.getElementById('saveButton');

            if (data.exists) {
                emailError.style.display = 'block';
                emailInput.style.borderColor = '#e74c3c';
                saveButton.disabled = true;
                saveButton.style.backgroundColor = '#95a5a6';
            } else {
                emailError.style.display = 'none';
                emailInput.style.borderColor = '#ddd';
                saveButton.disabled = false;
                saveButton.style.backgroundColor = '#28a745';
            }
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
}

function validateEmailOnSubmit(email) {
    if (email === '') return true;

    // Simple synchronous validation for final check
    const emailError = document.getElementById('emailError');
    if (emailError.style.display === 'block') {
        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        emailInput.focus();
        return false;
    }
    return true;
}

function openAddPatientModal() {
    document.getElementById('addPatientModal').style.display = 'block';
    setTimeout(initializeEmailValidation, 100);
    
    const emailError = document.getElementById('emailError');
    const emailInput = document.getElementById('emailInput');
    const saveButton = document.getElementById('saveButton');
    
    if (emailError) emailError.style.display = 'none';
    if (emailInput) emailInput.style.borderColor = '#ddd';
    if (saveButton) {
        saveButton.disabled = false;
        saveButton.style.backgroundColor = '#28a745';
    }
}




/*
function filterTabledep() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const department = document.getElementById("department").value.toLowerCase();

    const visibleTab = document.querySelector('.tab-content[style*="display: block"]');
    const rows = visibleTab.querySelectorAll(".client-row");

    rows.forEach(row => {
        const nameCell = row.querySelector(".searchable-name").textContent.toLowerCase();
        const departmentCell = row.querySelector("td:nth-child(5)")?.textContent.toLowerCase(); // 5th column = department

        const matchesName = nameCell.includes(input);
        const matchesDepartment = department === "" || (departmentCell && departmentCell.includes(department));

        if (matchesName && matchesDepartment) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

async function filterTableById() {
    const searchValue = document.getElementById('searchInput').value.trim();
    
    if (searchValue === '') {
        document.querySelectorAll('.clientTableBody tr.client-row').forEach(row => {
            row.style.display = '';
        });
        return;
    }
    
    try {
        const response = await fetch(`../../manageclients.dbf/get_user.php?searchById=${encodeURIComponent(searchValue)}`);
        const results = await response.json();
        
        const tableBody = document.querySelector('.clientTableBody');
        tableBody.innerHTML = '';
        
        if (results.length > 0) {
            const client = results[0];
            const row = document.createElement('tr');
            row.className = 'client-row';
            row.innerHTML = `
                <td class="searchable-id">${client.ClientID}</td>
                <td><img src="${client.profilePicturePath || 'default.jpg'}" class="profile-pic"></td>
                <td>${client.FullName}</td>
                <td>${client.Email}</td>
                <td>${client.Course}</td>
                <td>${client.Department}</td>
                <td>${client.ClientType}</td>
                <td class="action-buttons">
                    <button class="btn btn-primary btn-sm view-btn" data-id="${client.ClientID}">View</button>
                    <button class="btn btn-info btn-sm edit-btn" data-id="${client.ClientID}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="${client.ClientID}">Delete</button>
                </td>
            `;
            tableBody.appendChild(row);
        } else {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No matching ID found</td></tr>';
        }
    } catch (error) {
        console.error('Error searching:', error);
    }
}
*/
//==============================================================================
