document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('profileModal');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('closeModal');
    const form = document.getElementById('profileForm');
    
    modal.style.display = 'block';
    overlay.style.display = 'block';
    
    closeBtn.addEventListener('click', function() {
        if (validateForm()) {
            modal.style.display = 'none';
            overlay.style.display = 'none';
        } else {
            alert('Please complete Client Type and Department fields before closing');
        }
    });
    
    overlay.addEventListener('click', function() {
        if (validateForm()) {
            modal.style.display = 'none';
            overlay.style.display = 'none';
        } else {
            alert('Please complete your profile before closing');
        }
    });
    
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Please fill out all required fields');
        }
    });
    
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (modal.style.display === 'block' && !validateForm()) {
                e.preventDefault();
                alert('Please complete your profile before navigating');
            }
        });
    });
    
    function validateForm() {
        const clientType = document.getElementById('clientType').value;
        const department = document.getElementById('department').value.trim();
        
        if (!clientType || !department) {
            return false;
        }
        return true;
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            if (validateForm()) {
                modal.style.display = 'none';
                overlay.style.display = 'none';
            } else {
                alert('Please complete your profile before closing');
            }
        }
    });
});