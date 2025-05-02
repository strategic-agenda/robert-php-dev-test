/**
 * Format date for display
 * @param {string} dateString - ISO date string
 * @param {object} options - Date formatting options
 * @returns {string} Formatted date string
 */
export const formatDate = (dateString, options = {}) => {
    if (!dateString) return '';

    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };

    const mergedOptions = { ...defaultOptions, ...options };

    try {
        return new Date(dateString).toLocaleDateString(undefined, mergedOptions);
    } catch (error) {
        console.error('Date formatting error:', error);
        return dateString;
    }
};

/**
 * Format time for display
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted time string
 */
export const formatTime = (dateString) => {
    if (!dateString) return '';

    try {
        return new Date(dateString).toLocaleTimeString(undefined, {
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.error('Time formatting error:', error);
        return '';
    }
};

/**
 * Format date and time together
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date and time string
 */
export const formatDateTime = (dateString) => {
    if (!dateString) return '';
    return `${formatDate(dateString)} ${formatTime(dateString)}`;
};

/**
 * Truncate text with ellipsis
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length before truncation
 * @returns {string} Truncated text with ellipsis if needed
 */
export const truncateText = (text, maxLength = 100) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return `${text.substring(0, maxLength)}...`;
};

/**
 * Debounce function to limit how often a function can be called
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function} Debounced function
 */
export const debounce = (func, wait = 300) => {
    let timeout;

    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Parse query parameters from URL
 * @returns {object} Object containing query parameters
 */
export const parseQueryParams = () => {
    const params = new URLSearchParams(window.location.search);
    const result = {};

    for (const [key, value] of params.entries()) {
        result[key] = value;
    }

    return result;
};

/**
 * Build query string from params object
 * @param {object} params - Query parameters
 * @returns {string} Query string
 */
export const buildQueryString = (params) => {
    if (!params || typeof params !== 'object') return '';

    const searchParams = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            searchParams.append(key, value);
        }
    });

    const queryString = searchParams.toString();
    return queryString ? `?${queryString}` : '';
};

/**
 * Capitalize first letter of string
 * @param {string} string - String to capitalize
 * @returns {string} Capitalized string
 */
export const capitalizeFirst = (string) => {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
};

/**
 * Check if a value is empty (null, undefined, empty string or empty array/object)
 * @param {*} value - Value to check
 * @returns {boolean} True if value is empty
 */
export const isEmpty = (value) => {
    if (value === null || value === undefined) return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    if (Array.isArray(value) && value.length === 0) return true;
    if (typeof value === 'object' && Object.keys(value).length === 0) return true;
    return false;
};
