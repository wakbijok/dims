// src/assets/js/utils.js

const API_BASE_URL = '/api';

const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json'
    }
});

const utils = {
    // Show success message
    showSuccess: function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    },

    // Show error message
    showError: function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    },

    // Confirm action
    confirm: async function(message) {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        });
        return result.isConfirmed;
    },

    // Format date
    formatDate: function(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString();
    },

    // API wrapper functions
    api: {
        // Get all records
        getAll: async function(resource, params = {}) {
            try {
                const response = await apiClient.get(`/${resource}`, { params });
                return response.data;
            } catch (error) {
                throw this.handleError(error);
            }
        },

        // Get single record
        getOne: async function(resource, id) {
            try {
                const response = await apiClient.get(`/${resource}/${id}`);
                return response.data;
            } catch (error) {
                throw this.handleError(error);
            }
        },

        // Create record
        create: async function(resource, data) {
            try {
                const response = await apiClient.post(`/${resource}`, data);
                return response.data;
            } catch (error) {
                throw this.handleError(error);
            }
        },

        // Update record
        update: async function(resource, id, data) {
            try {
                const response = await apiClient.put(`/${resource}/${id}`, data);
                return response.data;
            } catch (error) {
                throw this.handleError(error);
            }
        },

        // Delete record
        delete: async function(resource, id) {
            try {
                const response = await apiClient.delete(`/${resource}/${id}`);
                return response.data;
            } catch (error) {
                throw this.handleError(error);
            }
        },

        // Handle API errors
        handleError: function(error) {
            if (error.response) {
                return error.response.data.error || 'An error occurred';
            }
            return error.message || 'Network error';
        }
    },

    // Form handling
    form: {
        // Get form data as object
        getFormData: function(form) {
            const formData = new FormData(form);
            const data = {};
            for (const [key, value] of formData.entries()) {
                data[key] = value;
            }
            return data;
        },

        // Reset form
        resetForm: function(form) {
            form.reset();
        },

        // Validate form
        validateForm: function(form) {
            return form.checkValidity();
        }
    }
};

// Add global error handler for axios
apiClient.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Handle unauthorized access
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);