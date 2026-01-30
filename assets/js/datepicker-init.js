
// Default date picker configuration
const datePickerConfig = {
    dateFormat: "m/d/Y",
    maxDate: "today",
    allowInput: true,
    altInput: false
};

/**
 * Initialize all date pickers on the page
 */
function initDatePickers(selector = ".date-picker", customConfig = {}) {
    const config = { ...datePickerConfig, ...customConfig };
    return flatpickr(selector, config);
}

/**
 * Convert MM/DD/YYYY to YYYY-MM-DD
 */
function convertToBackendDate(dateString) {
    if (!dateString) return null;
    const [month, day, year] = dateString.split('/');
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}

/**
 * Convert YYYY-MM-DD to MM/DD/YYYY
 */
function convertToDisplayDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
}

/**
 * Get today's date in MM/DD/YYYY format
 */
function getTodayFormatted() {
    const today = new Date();
    const month = (today.getMonth() + 1).toString().padStart(2, '0');
    const day = today.getDate().toString().padStart(2, '0');
    const year = today.getFullYear();
    return `${month}/${day}/${year}`;
}

/**
 * Validate date range
 */
function validateDateRange(dateFrom, dateTo) {
    if (!dateFrom || !dateTo) return true;
    
    const from = convertToBackendDate(dateFrom);
    const to = convertToBackendDate(dateTo);
    
    return new Date(from) <= new Date(to);
}

// Auto-initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initDatePickers();
});
