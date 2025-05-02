import axios from 'axios';

// API base URL - change this for different environments
const API_BASE_URL = 'http://localhost:8000/api';

// Create an axios instance with common configuration
const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Add a request interceptor for global handling
apiClient.interceptors.request.use(
    (config) => {
        // You can add auth tokens here if needed
        // const token = localStorage.getItem('auth_token');
        // if (token) {
        //   config.headers['Authorization'] = `Bearer ${token}`;
        // }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add a response interceptor for global error handling
apiClient.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Handle common errors here
        if (error.response) {
            // Server responded with error status
            console.error('API Error:', error.response.data);

            // Handle specific status codes
            switch (error.response.status) {
                case 401:
                    // Unauthorized - handle authentication issues
                    // You could redirect to login here
                    break;
                case 403:
                    // Forbidden - handle permission issues
                    break;
                case 500:
                    // Server error
                    break;
                default:
                    // Other status codes
                    break;
            }
        } else if (error.request) {
            // Request was made but no response received
            console.error('Network Error:', error.request);
        } else {
            // Something else happened
            console.error('Error:', error.message);
        }

        return Promise.reject(error);
    }
);

// Language API endpoints
export const languageAPI = {
    // Get all languages with optional filters
    getAll: (params = {}) => {
        return apiClient.get('/languages', { params });
    },

    // Get a single language by ID
    getById: (id) => {
        return apiClient.get(`/languages/${id}`);
    },

    // Create a new language
    create: (data) => {
        return apiClient.post('/languages', data);
    },

    // Update a language
    update: (id, data) => {
        return apiClient.put(`/languages/${id}`, data);
    },

    // Delete a language
    delete: (id) => {
        return apiClient.delete(`/languages/${id}`);
    }
};

// Translation API endpoints
export const translationAPI = {
    // Get all translation units with optional filters
    getAll: (params = {}) => {
        return apiClient.get('/translations', { params });
    },

    // Get a single translation unit by ID
    getById: (id) => {
        return apiClient.get(`/translations/${id}`);
    },

    // Create a new translation unit
    createUnit: (sourceContent, context = '') => {
        return apiClient.post('/translations', {
            source_content: sourceContent,
            context
        });
    },

    // Update a translation unit
    updateUnit: (id, sourceContent, context = '') => {
        return apiClient.put(`/translations/${id}`, {
            source_content: sourceContent,
            context
        });
    },

    // Add a translation to a unit
    addTranslation: (unitId, languageId, content) => {
        return apiClient.post('/translations', {
            translation_unit_id: unitId,
            language_id: languageId,
            content
        });
    },

    // Update a translation
    updateTranslation: (unitId, translationId, content) => {
        return apiClient.put(`/translations/${unitId}`, {
            translation_id: translationId,
            content
        });
    },

    // Archive a translation unit
    archiveUnit: (id) => {
        return apiClient.delete(`/translations/${id}`);
    }
};
