// FILE: assets/js/apply-form.js
// FIXED VERSION - Handles null elements safely

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Apply form script loaded');
    
    // FIXED: Use the correct IDs from your HTML
    const departmentSelect = document.getElementById('department_select');
    const serviceSelect = document.getElementById('service_select');
    const applicationForm = document.getElementById('applicationForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Log what we found
    console.log('Found elements:', {
        department: !!departmentSelect,
        service: !!serviceSelect,
        form: !!applicationForm,
        button: !!submitBtn
    });
    
    // Check if elements exist
    if (!departmentSelect) {
        console.error('❌ Department select not found');
        return;
    }
    
    if (!serviceSelect) {
        console.error('❌ Service select not found');
        return;
    }
    
    if (!applicationForm) {
        console.error('❌ Form not found');
        return;
    }
    
    // Load departments on page load
    loadDepartments();
    
    // Department change handler
    departmentSelect.addEventListener('change', function() {
        const departmentId = this.value;
        console.log('Department selected:', departmentId);
        
        if (departmentId) {
            loadServices(departmentId);
        } else {
            serviceSelect.innerHTML = '<option value="">-- First select a department --</option>';
            serviceSelect.disabled = true;
            hideRequirementsBox();
        }
    });
    
    // Form submit handler
    applicationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('📝 Form submitted');
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Disable submit button
        if (submitBtn) {
            submitBtn.disabled = true;
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            if (btnText) btnText.style.display = 'none';
            if (btnLoader) btnLoader.style.display = 'inline-block';
        }
        
        try {
            // Create FormData
            const formData = new FormData(this);
            
            // IMPORTANT: Get the actual IDs from the select elements
            const deptId = document.getElementById('department_select').value;
            const serviceId = document.getElementById('service_select').value;
            
            // Add IDs to form data with correct names
            formData.set('department_id', deptId);
            formData.set('service_id', serviceId);
            
            // Log form data for debugging
            console.log('Form data:', {
                department_id: deptId,
                service_id: serviceId,
                purpose: formData.get('purpose')?.substring(0, 50),
                file: formData.get('compiled_document')?.name
            });
            
            // Submit to API
            const response = await fetch('../api/submit_department_application.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            
            // Parse response
            const result = await response.json();
            console.log('API response:', result);
            
            if (result.success) {
                // Success!
                showSuccessModal(result);
            } else {
                // Error from API
                showError(result.message || 'An error occurred. Please try again.');
            }
            
        } catch (error) {
            console.error('❌ Submission error:', error);
            showError('Failed to submit application. Please check your connection and try again.');
        } finally {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoader = submitBtn.querySelector('.btn-loader');
                if (btnText) btnText.style.display = 'inline-block';
                if (btnLoader) btnLoader.style.display = 'none';
            }
        }
    });
});

/**
 * Load departments from API
 */
async function loadDepartments() {
    const departmentSelect = document.getElementById('department_select');
    
    if (!departmentSelect) {
        console.error('❌ Cannot find department select element');
        return;
    }
    
    try {
        console.log('📥 Loading departments from API...');
        
        // Show loading state
        departmentSelect.innerHTML = '<option value="">Loading departments...</option>';
        departmentSelect.disabled = true;
        
        const response = await fetch('../api/get_departments.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success && data.departments && data.departments.length > 0) {
            departmentSelect.innerHTML = '<option value="">-- Choose your department --</option>';
            
            data.departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.name;
                if (dept.code) option.setAttribute('data-code', dept.code);
                departmentSelect.appendChild(option);
            });
            
            departmentSelect.disabled = false;
            console.log(`✅ Loaded ${data.departments.length} departments successfully`);
        } else if (data.success && data.departments && data.departments.length === 0) {
            departmentSelect.innerHTML = '<option value="">No departments available</option>';
            console.warn('⚠️ No departments found in database');
        } else {
            throw new Error(data.message || 'Failed to load departments');
        }
    } catch (error) {
        console.error('❌ Error loading departments:', error);
        departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
        showError('Failed to load departments. Please refresh the page. Error: ' + error.message);
    }
}

/**
 * Load services for selected department
 */
async function loadServices(departmentId) {
    const serviceSelect = document.getElementById('service_select');
    const requirementsBox = document.getElementById('requirementsBox');
    
    if (!serviceSelect) {
        console.error('❌ Cannot find service select element');
        return;
    }
    
    // Reset service select
    serviceSelect.innerHTML = '<option value="">Loading services...</option>';
    serviceSelect.disabled = true;
    
    // Hide requirements box
    if (requirementsBox) {
        requirementsBox.style.display = 'none';
    }
    
    try {
        console.log('📥 Loading services for department:', departmentId);
        
        const response = await fetch(`../api/get_services.php?department_id=${departmentId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success && data.services) {
            serviceSelect.innerHTML = '<option value="">-- Choose your service --</option>';
            
            if (data.services.length === 0) {
                serviceSelect.innerHTML = '<option value="">No services available for this department</option>';
                console.warn('⚠️ No services found for department:', departmentId);
                return;
            }
            
            data.services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.service_name;
                if (service.base_fee) option.setAttribute('data-fee', service.base_fee);
                if (service.processing_days) option.setAttribute('data-days', service.processing_days);
                if (service.requirements) option.setAttribute('data-requirements', service.requirements);
                if (service.description) option.setAttribute('data-description', service.description);
                serviceSelect.appendChild(option);
            });
            
            serviceSelect.disabled = false;
            console.log(`✅ Loaded ${data.services.length} services successfully`);
            
            // Add service change listener
            serviceSelect.removeEventListener('change', handleServiceChange);
            serviceSelect.addEventListener('change', handleServiceChange);
        } else {
            throw new Error(data.message || 'Failed to load services');
        }
    } catch (error) {
        console.error('❌ Error loading services:', error);
        serviceSelect.innerHTML = '<option value="">Error loading services</option>';
        showError('Failed to load services. Please try again. Error: ' + error.message);
    }
}

/**
 * Handle service selection change
 */
function handleServiceChange() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (!selectedOption.value) {
        hideRequirementsBox();
        return;
    }
    
    displayServiceInfo(selectedOption);
}

/**
 * Display selected service information
 */
function displayServiceInfo(selectedOption) {
    const requirementsBox = document.getElementById('requirementsBox');
    const requirementsList = document.getElementById('requirementsList');
    const serviceFee = document.getElementById('serviceFee');
    
    if (!requirementsBox || !requirementsList) return;
    
    const fee = selectedOption.getAttribute('data-fee');
    const requirements = selectedOption.getAttribute('data-requirements');
    
    // Display requirements
    if (requirements && requirements.trim()) {
        const reqList = requirements.split('\n').filter(r => r.trim());
        if (reqList.length > 0) {
            let html = '<ul style="margin: 10px 0; padding-left: 20px;">';
            reqList.forEach(req => {
                html += `<li style="margin: 8px 0;">${req.trim()}</li>`;
            });
            html += '</ul>';
            requirementsList.innerHTML = html;
        }
    } else {
        requirementsList.innerHTML = '<p style="color: #999; font-style: italic;">No specific requirements listed</p>';
    }
    
    // Display fee
    if (serviceFee && fee) {
        serviceFee.textContent = `Fee: ₱${parseFloat(fee).toFixed(2)}`;
    }
    
    // Show requirements box
    requirementsBox.style.display = 'block';
}

/**
 * Hide requirements box
 */
function hideRequirementsBox() {
    const requirementsBox = document.getElementById('requirementsBox');
    if (requirementsBox) {
        requirementsBox.style.display = 'none';
    }
}

/**
 * Validate form before submission
 */
function validateForm() {
    const department = document.getElementById('department_select');
    const service = document.getElementById('service_select');
    const purpose = document.getElementById('purpose');
    const fileInput = document.getElementById('compiled_document');
    const termsCheckbox = document.getElementById('terms');
    
    // Check department
    if (!department || !department.value) {
        showError('Please select a department');
        department?.focus();
        return false;
    }
    
    // Check service
    if (!service || !service.value) {
        showError('Please select a service');
        service?.focus();
        return false;
    }
    
    // Check purpose
    if (!purpose || !purpose.value.trim()) {
        showError('Please enter the purpose of your application');
        purpose?.focus();
        return false;
    }
    
    if (purpose.value.trim().length < 10) {
        showError('Purpose must be at least 10 characters long');
        purpose?.focus();
        return false;
    }
    
    // Check file
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showError('Please upload the required document (PDF only)');
        fileInput?.focus();
        return false;
    }
    
    const file = fileInput.files[0];
    
    // Check file type
    if (file.type !== 'application/pdf') {
        showError('Only PDF files are allowed');
        fileInput.focus();
        return false;
    }
    
    // Check file size (10MB max)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        showError('File size must not exceed 10MB');
        fileInput.focus();
        return false;
    }
    
    // Check terms checkbox
    if (termsCheckbox && !termsCheckbox.checked) {
        showError('Please accept the terms and conditions');
        termsCheckbox.focus();
        return false;
    }
    
    console.log('✅ Form validation passed');
    return true;
}

/**
 * Show success modal with application details
 */
function showSuccessModal(result) {
    // Create modal HTML
    const modalHTML = `
        <div id="successModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 500px;">
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 4rem; color: #4CAF50; margin-bottom: 20px;">✅</div>
                    <h2 style="color: #4CAF50; margin-bottom: 15px;">Application Submitted Successfully!</h2>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">Your Tracking Number:</p>
                        <p style="font-size: 1.8rem; font-weight: 800; color: #333; margin: 0;">${result.tracking_number}</p>
                    </div>
                    
                    <div style="text-align: left; margin: 20px 0;">
                        <p><strong>Service:</strong> ${result.service_name}</p>
                        <p><strong>Department:</strong> ${result.department}</p>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 15px;">
                            Please save your tracking number for future reference. 
                            You can check your application status in "My Applications" page.
                        </p>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 30px;">
                        <button onclick="window.location.href='applications.php'" 
                                style="flex: 1; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            View My Applications
                        </button>
                        <button onclick="window.location.reload()" 
                                style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            Submit Another
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('successModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    console.log('✅ Success modal displayed');
}

/**
 * Show error message
 */
function showError(message) {
    // Try to find an alert container
    let alertContainer = document.getElementById('alertContainer');
    
    if (!alertContainer) {
        // Create alert container if it doesn't exist
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(alertContainer);
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.style.cssText = `
        background: #f8d7da;
        color: #721c24;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-left: 4px solid #dc3545;
        animation: slideIn 0.3s ease;
    `;
    alert.innerHTML = `
        <strong>❌ Error:</strong> ${message}
    `;
    
    // Add to container
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
    
    console.error('❌ Error shown:', message);
}

/**
 * File input preview
 */
const fileInput = document.getElementById('compiled_document');
if (fileInput) {
    fileInput.addEventListener('change', function() {
        const filePreview = document.getElementById('filePreview');
        
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            if (filePreview) {
                filePreview.innerHTML = `
                    <div style="margin-top: 1rem; padding: 1rem; background: #e8f5e9; border: 2px solid #81c784; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2rem;">📄</div>
                        <div style="flex: 1;">
                            <strong style="color: #2e7d32; display: block;">${fileName}</strong>
                            <small style="color: #558b2f;">Size: ${fileSize} MB</small>
                        </div>
                        <button type="button" onclick="document.getElementById('compiled_document').value=''; document.getElementById('filePreview').style.display='none';" 
                                style="padding: 0.5rem 1rem; background: #f44336; color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Remove
                        </button>
                    </div>
                `;
                filePreview.style.display = 'block';
            }
            
            console.log('File selected:', fileName, fileSize + 'MB');
        } else {
            if (filePreview) {
                filePreview.style.display = 'none';
                filePreview.innerHTML = '';
            }
        }
    });
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    
    .modal-content {
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: slideIn 0.3s ease;
    }
`;
document.head.appendChild(style);

console.log('✅ Apply form script fully initialized');