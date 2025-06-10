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
