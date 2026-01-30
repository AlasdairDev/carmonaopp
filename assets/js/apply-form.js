// FILE: assets/js/apply-form.js
// Enhanced version with real-time AJAX updates similar to applications.php

// Global variables for tracking current selections
let currentDepartmentValue = '';
let currentServiceValue = '';
let departmentRefreshInterval;
let serviceRefreshInterval;

document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ Apply form script loaded');

    // CRITICAL FIX: Only run if we're on the application page
    const applicationForm = document.getElementById('applicationForm');

    if (!applicationForm) {
        console.log('ℹ️ Application form not found - skipping apply-form.js');
        return; // EXIT - Don't run any of this code on other pages!
    }

    console.log('✅ Application form found - initializing with AJAX');

    // Get form elements
    const departmentSelect = document.getElementById('department_select');
    const serviceSelect = document.getElementById('service_select');
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

    // Initial load of departments
    loadDepartments(false);

    // Start automatic refresh after initial load
    setTimeout(() => {
        startDepartmentRefresh();
        startServiceRefresh();
    }, 2000);

    // Department change handler - track selection and load services
    departmentSelect.addEventListener('change', function () {
        currentDepartmentValue = this.value;
        currentServiceValue = '';
        console.log('Department changed to:', currentDepartmentValue);

        if (currentDepartmentValue) {
            loadServices(currentDepartmentValue, false);
        } else {
            serviceSelect.innerHTML = '<option value="">-- First select a department --</option>';
            serviceSelect.disabled = true;
            hideRequirementsBox();
        }
    });

    // Track service selection changes
    serviceSelect.addEventListener('change', function () {
        currentServiceValue = this.value;
        console.log('Service changed to:', currentServiceValue);
    });

    // Form submit handler - ONLY for applicationForm
    applicationForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        console.log('Application form submitted');

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

            // Get the actual IDs from the select elements
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

    console.log('✅ Apply form script fully initialized with AJAX');
});

/**
 * Load departments from API with automatic refresh capability
 * @param {boolean} silent - If true, don't show loading states (for background refresh)
 */
async function loadDepartments(silent = false) {
    const departmentSelect = document.getElementById('department_select');

    if (!departmentSelect) {
        console.error('❌ Cannot find department select element');
        return;
    }

    // Store current selection to restore after refresh
    const previousValue = currentDepartmentValue || departmentSelect.value;

    try {
        if (!silent) {
            console.log('🔄 Loading departments from API...');
            departmentSelect.innerHTML = '<option value="">Loading departments...</option>';
            departmentSelect.disabled = true;
        }

        const response = await fetch('../api/get_departments.php');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!silent) {
            console.log('API Response:', data);
        }

        if (data.success && data.departments && data.departments.length > 0) {
            let options = '<option value="">-- Choose your department --</option>';

            data.departments.forEach(dept => {
                const selected = dept.id == previousValue ? 'selected' : '';
                const dataCode = dept.code ? `data-code="${dept.code}"` : '';
                options += `<option value="${dept.id}" ${selected} ${dataCode}>${dept.name}</option>`;
            });

            departmentSelect.innerHTML = options;
            departmentSelect.disabled = false;

            // Restore previous selection
            if (previousValue) {
                currentDepartmentValue = previousValue;
                departmentSelect.value = previousValue;

                // If we had a department selected, reload its services
                if (previousValue) {
                    loadServices(previousValue, silent);
                }
            }

            if (!silent) {
                console.log(`✅ Loaded ${data.departments.length} departments successfully`);
            }
        } else if (data.success && data.departments && data.departments.length === 0) {
            departmentSelect.innerHTML = '<option value="">No departments available</option>';
            if (!silent) {
                console.warn('⚠️ No departments found in database');
            }
        } else {
            throw new Error(data.message || 'Failed to load departments');
        }
    } catch (error) {
        console.error('❌ Error loading departments:', error);
        if (!silent) {
            departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
            showError('Failed to load departments. Please refresh the page.');
        }
    }
}

/**
 * Load services for selected department with automatic refresh capability
 * @param {string|number} departmentId - The department ID
 * @param {boolean} silent - If true, don't show loading states (for background refresh)
 */
async function loadServices(departmentId, silent = false) {
    const serviceSelect = document.getElementById('service_select');
    const requirementsBox = document.getElementById('requirementsBox');

    if (!serviceSelect) {
        console.error('❌ Cannot find service select element');
        return;
    }

    // Store current selection to restore after refresh
    const previousValue = currentServiceValue || serviceSelect.value;

    if (!silent) {
        // Reset service select with loading state
        serviceSelect.innerHTML = '<option value="">Loading services...</option>';
        serviceSelect.disabled = true;

        // Hide requirements box
        if (requirementsBox) {
            requirementsBox.style.display = 'none';
        }
    }

    try {
        if (!silent) {
            console.log('🔄 Loading services for department:', departmentId);
        }

        const response = await fetch(`../api/get_services.php?department_id=${departmentId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!silent) {
            console.log('Services API Response:', data);
        }

        if (data.success && data.services && data.services.length > 0) {
            let options = '<option value="">-- Select a service --</option>';

            data.services.forEach(service => {
                const selected = service.id == previousValue ? 'selected' : '';
                const dataFee = service.base_fee ? `data-fee="${service.base_fee}"` : 'data-fee="0"';
                const dataReq = service.requirements ? `data-requirements="${service.requirements.replace(/"/g, '&quot;')}"` : '';
                const dataProc = service.processing_days ? `data-processing="${service.processing_days}"` : '';

                options += `<option value="${service.id}" ${selected} ${dataFee} ${dataReq} ${dataProc}>${service.service_name}</option>`;
            });

            serviceSelect.innerHTML = options;
            serviceSelect.disabled = false;

            // Restore previous selection
            if (previousValue) {
                currentServiceValue = previousValue;
                serviceSelect.value = previousValue;

                // Show requirements if service was selected
                if (previousValue) {
                    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        showRequirements(selectedOption);
                    }
                }
            }

            // Add change listener if not already added
            if (!serviceSelect.dataset.listenerAdded) {
                serviceSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        showRequirements(selectedOption);
                    } else {
                        hideRequirementsBox();
                    }
                });
                serviceSelect.dataset.listenerAdded = 'true';
            }

            if (!silent) {
                console.log(`✅ Loaded ${data.services.length} services successfully`);
            }
        } else if (data.success && data.services && data.services.length === 0) {
            serviceSelect.innerHTML = '<option value="">No services available for this department</option>';
            serviceSelect.disabled = true;
            if (!silent) {
                console.warn('⚠️ No services found for this department');
            }
        } else {
            throw new Error(data.message || 'Failed to load services');
        }
    } catch (error) {
        console.error('❌ Error loading services:', error);
        if (!silent) {
            serviceSelect.innerHTML = '<option value="">Error loading services</option>';
            serviceSelect.disabled = true;
            showError('Failed to load services. Please try again.');
        }
    }
}

/**
 * Show requirements for selected service
 */
function showRequirements(selectedOption) {
    const requirementsBox = document.getElementById('requirementsBox');
    const requirementsList = document.getElementById('requirementsList');
    const serviceFee = document.getElementById('serviceFee');

    if (!requirementsBox || !requirementsList || !serviceFee) return;

    const requirements = selectedOption.getAttribute('data-requirements');
    const fee = selectedOption.getAttribute('data-fee') || '0';
    const processingDays = selectedOption.getAttribute('data-processing');

    // Parse and display requirements
    if (requirements && requirements.trim() !== '') {
        const reqArray = requirements.split('\n').filter(req => req.trim() !== '');
        requirementsList.innerHTML = '';

        reqArray.forEach((req, index) => {
            const li = document.createElement('li');
            li.style.cursor = 'pointer';
            li.style.transition = 'all 0.2s ease';
            li.style.padding = '0.5rem';
            li.style.borderRadius = '6px';

            // Split requirement and image (format: "Requirement Name|image_filename.jpg")
            const parts = req.trim().split('|');
            const reqText = parts[0];
            const reqImage = parts[1] || null;

            li.textContent = reqText;

            // Add hover effect
            li.addEventListener('mouseenter', function () {
                this.style.backgroundColor = '#e8f5e9';
                if (reqImage) {
                    this.style.fontWeight = '600';
                    this.style.color = '#558b2f';
                }
            });

            li.addEventListener('mouseleave', function () {
                this.style.backgroundColor = '';
                this.style.fontWeight = '';
                this.style.color = '';
            });

            // Add click event to show image
            li.style.textDecoration = 'underline';
            li.style.cursor = 'pointer';
            li.title = reqImage ? 'Click to view sample' : 'Click to view (sample not available yet)';

            li.addEventListener('click', function () {
                showRequirementImage(reqText, reqImage);
            });

            requirementsList.appendChild(li);
        });

        // Display fee
        serviceFee.textContent = parseFloat(fee) > 0 ? `₱${parseFloat(fee).toFixed(2)}` : 'FREE';

        // Show the box
        requirementsBox.style.display = 'block';

        console.log('✅ Requirements displayed:', reqArray.length, 'items');
    } else {
        requirementsList.innerHTML = '<li style="list-style: none;">No specific requirements listed</li>';
        serviceFee.textContent = parseFloat(fee) > 0 ? `₱${parseFloat(fee).toFixed(2)}` : 'FREE';
        requirementsBox.style.display = 'block';
    }
}
// Function to show requirement image
function showRequirementImage(requirementName, imagePath) {
    const modal = document.getElementById('requirementImageModal');
    const img = document.getElementById('requirementImage');
    const title = document.getElementById('requirementImageTitle');

    if (modal && img && title) {
        title.textContent = requirementName;

        // Use placeholder if no image exists, or use the actual image path
        if (imagePath && imagePath.trim() !== '') {
            img.src = `../assets/images/requirements/${imagePath}`;

            // Add error handler to show placeholder if image doesn't exist
            img.onerror = function () {
                this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600"%3E%3Crect fill="%23f0f0f0" width="800" height="600"/%3E%3Ctext x="400" y="280" font-family="Arial, sans-serif" font-size="24" fill="%23999" text-anchor="middle"%3EImage not available yet%3C/text%3E%3Ctext x="400" y="320" font-family="Arial, sans-serif" font-size="18" fill="%23bbb" text-anchor="middle"%3E' + requirementName + '%3C/text%3E%3Cg transform="translate(350, 100)"%3E%3Crect x="0" y="0" width="100" height="120" fill="none" stroke="%23ccc" stroke-width="3" rx="5"/%3E%3Cpolyline points="20,80 50,50 80,80" fill="none" stroke="%23ccc" stroke-width="3"/%3E%3Ccircle cx="30" cy="40" r="8" fill="%23ccc"/%3E%3C/g%3E%3C/svg%3E';
                this.onerror = null; // Prevent infinite loop
            };
        } else {
            // No image path provided, show placeholder
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600"%3E%3Crect fill="%23f0f0f0" width="800" height="600"/%3E%3Ctext x="400" y="280" font-family="Arial, sans-serif" font-size="24" fill="%23999" text-anchor="middle"%3ENo sample image available%3C/text%3E%3Ctext x="400" y="320" font-family="Arial, sans-serif" font-size="18" fill="%23bbb" text-anchor="middle"%3E' + requirementName + '%3C/text%3E%3Cg transform="translate(350, 100)"%3E%3Crect x="0" y="0" width="100" height="120" fill="none" stroke="%23ccc" stroke-width="3" rx="5"/%3E%3Cpolyline points="20,80 50,50 80,80" fill="none" stroke="%23ccc" stroke-width="3"/%3E%3Ccircle cx="30" cy="40" r="8" fill="%23ccc"/%3E%3C/g%3E%3C/svg%3E';
        }

        // Flash animation
        modal.style.display = 'block';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '1';
        }, 10);

        console.log('📸 Showing requirement image:', requirementName, 'Path:', imagePath || 'No image');
    }
}
// Function to close requirement image
function closeRequirementImage() {
    const modal = document.getElementById('requirementImageModal');
    if (modal) {
        modal.style.transition = 'opacity 0.2s ease';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    }
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
 * Start automatic department refresh (every 5 seconds)
 */
function startDepartmentRefresh() {
    // Clear existing interval if any
    if (departmentRefreshInterval) {
        clearInterval(departmentRefreshInterval);
    }

    departmentRefreshInterval = setInterval(() => {
        loadDepartments(true); // Silent refresh
        console.log('🔄 Auto-refreshed departments (silent)');
    }, 5000);

    console.log('✅ Department auto-refresh started (every 5 seconds)');
}

/**
 * Start automatic service refresh (every 5 seconds)
 */
function startServiceRefresh() {
    // Clear existing interval if any
    if (serviceRefreshInterval) {
        clearInterval(serviceRefreshInterval);
    }

    serviceRefreshInterval = setInterval(() => {
        const departmentSelect = document.getElementById('department_select');
        if (departmentSelect && departmentSelect.value) {
            loadServices(departmentSelect.value, true); // Silent refresh
            console.log('🔄 Auto-refreshed services (silent)');
        }
    }, 5000);

    console.log('✅ Service auto-refresh started (every 5 seconds)');
}

/**
 * Stop all refresh intervals
 */
function stopAllRefresh() {
    if (departmentRefreshInterval) {
        clearInterval(departmentRefreshInterval);
        console.log('⏸️ Department auto-refresh stopped');
    }
    if (serviceRefreshInterval) {
        clearInterval(serviceRefreshInterval);
        console.log('⏸️ Service auto-refresh stopped');
    }
}

/**
 * Validate form before submission
 */
function validateForm() {
    const departmentSelect = document.getElementById('department_select');
    const serviceSelect = document.getElementById('service_select');
    const purpose = document.getElementById('purpose');
    const fileInput = document.getElementById('compiled_document');
    const terms = document.getElementById('terms');

    // Check department
    if (!departmentSelect.value) {
        showError('Please select a department');
        departmentSelect.focus();
        return false;
    }

    // Check service
    if (!serviceSelect.value) {
        showError('Please select a service');
        serviceSelect.focus();
        return false;
    }

    // Check purpose
    if (!purpose.value.trim()) {
        showError('Please enter the purpose of your application');
        purpose.focus();
        return false;
    }

    if (purpose.value.trim().length < 10) {
        showError('Purpose must be at least 10 characters long');
        purpose.focus();
        return false;
    }

    // Check file (OPTIONAL - only validate if a file is selected)
    if (fileInput.files && fileInput.files.length > 0) {
        const file = fileInput.files[0];

        // Check file type
        if (file.type !== 'application/pdf') {
            showError('Only PDF files are allowed');
            fileInput.focus();
            return false;
        }

        // Check file size (10MB max)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            showError('File size must not exceed 10MB');
            fileInput.focus();
            return false;
        }
    }

    // Check terms
    if (!terms.checked) {
        showError('Please accept the terms and conditions');
        terms.focus();
        return false;
    }

    console.log('✅ Form validation passed');
    return true;
}

/**
 * Show success modal with application details
 */
function showSuccessModal(result) {
    const modal = document.getElementById('successModal');
    const trackingNumber = document.getElementById('modalTrackingNumber');
    const serviceName = document.getElementById('modalService');
    const departmentName = document.getElementById('modalDepartment');

    if (modal) {
        // Set tracking number
        if (trackingNumber && result.tracking_number) {
            trackingNumber.textContent = result.tracking_number;
        }

        // Set service name
        if (serviceName && result.service_name) {
            serviceName.textContent = result.service_name;
        }

        // Set department name
        if (departmentName && result.department_name) {
            departmentName.textContent = result.department_name;
        }

        // Show modal
        modal.style.display = 'flex';

        console.log('✅ Success modal displayed');
    }
}

/**
 * Copy tracking number to clipboard
 */
function copyTrackingNumber() {
    const trackingNumber = document.getElementById('modalTrackingNumber');
    if (trackingNumber) {
        const text = trackingNumber.textContent;

        // Modern clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                // Show temporary feedback
                const originalText = trackingNumber.textContent;
                trackingNumber.textContent = '✓ Copied!';
                trackingNumber.style.color = '#22c55e';

                setTimeout(() => {
                    trackingNumber.textContent = originalText;
                    trackingNumber.style.color = '';
                }, 2000);

                console.log('✅ Tracking number copied to clipboard');
            }).catch(err => {
                console.error('❌ Failed to copy:', err);
                fallbackCopy(text);
            });
        } else {
            // Fallback for older browsers
            fallbackCopy(text);
        }
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    document.body.appendChild(textArea);
    textArea.select();

    try {
        document.execCommand('copy');
        console.log('✅ Tracking number copied (fallback method)');
        alert('Tracking number copied to clipboard!');
    } catch (err) {
        console.error('❌ Fallback copy failed:', err);
        alert('Failed to copy. Please copy manually: ' + text);
    }

    document.body.removeChild(textArea);
}

/**
 * Show error message
 */
function showError(message) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
            <div style="flex: 1;">
                <strong>Error</strong><br>
                ${message}
            </div>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #991b1b; padding: 0; width: 24px; height: 24px;">
                &times;
            </button>
        </div>
    `;

    document.body.appendChild(alert);

    // Auto-remove after 5 seconds
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
    fileInput.addEventListener('change', function () {
        const filePreview = document.getElementById('filePreview');

        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);

            if (filePreview) {
                filePreview.innerHTML = `
                    <div style="margin-top: 1rem; padding: 1rem; background: #e8f5e9; border: 2px solid #81c784; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%232e7d32%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z%27/%3E%3Cpolyline points=%2714 2 14 8 20 8%27/%3E%3Cline x1=%2716%27 y1=%2713%27 x2=%278%27 y2=%2713%27/%3E%3Cline x1=%2716%27 y1=%2717%27 x2=%278%27 y2=%2717%27/%3E%3Cpolyline points=%2710 9 9 9 8 9%27/%3E%3C/svg%3E') center/contain no-repeat;"></div>
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

            console.log('📎 File selected:', fileName, fileSize + 'MB');
        } else {
            if (filePreview) {
                filePreview.style.display = 'none';
                filePreview.innerHTML = '';
            }
        }
    });
}

// Stop refresh when leaving page
window.addEventListener('beforeunload', function () {
    stopAllRefresh();
});

// Pause/resume based on tab visibility
document.addEventListener('visibilitychange', function () {
    const applicationForm = document.getElementById('applicationForm');
    if (!applicationForm) return;

    if (document.hidden) {
        console.log('👁️ Tab hidden - pausing auto-refresh');
        stopAllRefresh();
    } else {
        console.log('👁️ Tab visible - resuming auto-refresh');
        loadDepartments(true);

        const departmentSelect = document.getElementById('department_select');
        if (departmentSelect && departmentSelect.value) {
            loadServices(departmentSelect.value, true);
        }

        startDepartmentRefresh();
        startServiceRefresh();
    }
});

console.log('✅ Apply form script with AJAX auto-refresh fully loaded');