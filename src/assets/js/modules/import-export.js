// src/assets/js/modules/import-export.js

const importExportModule = {
    // Module properties
    validationModal: null,
    progressModal: null,
    validatedData: null,
    currentImportType: null,

    // Required fields definitions for each data type
    requiredFields: {
        servers: ['hostname', 'location_id', 'environment_id'],
        hardware: ['server_id', 'cpu', 'memory', 'storage'],
        services: ['server_id', 'protocol', 'port'],
        backups: ['server_id', 'backup_type', 'schedule'],
        licenses: ['server_id', 'license_type', 'expiry_date']
    },

    // Template structures for each data type
    templates: {
        servers: [
            { hostname: 'server1', location_id: '1', environment_id: '1', ip_address: '192.168.1.1', description: 'Example server' }
        ],
        hardware: [
            { server_id: '1', cpu: 'Intel Xeon', memory: '16GB', storage: '500GB', serial_number: 'SN123456' }
        ],
        services: [
            { server_id: '1', protocol: 'HTTP', port: '80', url: 'http://example.com', username: '', password: '' }
        ],
        backups: [
            { server_id: '1', backup_type: 'Full', schedule: 'Daily', retention_period: '30 days' }
        ],
        licenses: [
            { server_id: '1', license_type: 'Windows Server', expiry_date: '2024-12-31', support_level: 'Premium' }
        ]
    },

    // Initialize module
    init: function() {
        this.validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
        this.progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
        this.initializeEventListeners();
    },

    // Set up event listeners
    initializeEventListeners: function() {
        document.getElementById('exportForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleExport();
        });

        document.getElementById('importForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleImport();
        });

        document.getElementById('proceedImport').addEventListener('click', () => {
            this.proceedWithImport();
        });
    },

    // Export functionality
    handleExport: async function() {
        try {
            const selectedTypes = [
                'servers', 'hardware', 'services', 'backups', 'licenses'
            ].filter(type => document.getElementById(`export${type.charAt(0).toUpperCase() + type.slice(1)}`).checked);

            if (selectedTypes.length === 0) {
                utils.showError('Please select at least one data type to export');
                return;
            }

            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            this.showProgress('Preparing export...');
            const data = await this.gatherExportData(selectedTypes);
            await this.downloadExportFile(data, format);
            this.hideProgress();
            utils.showSuccess('Export completed successfully');
        } catch (error) {
            this.hideProgress();
            utils.showError('Export failed: ' + error);
        }
    },

    gatherExportData: async function(types) {
        const data = {};
        let progress = 0;
        const increment = 100 / types.length;

        for (const type of types) {
            const response = await utils.api.getAll(type);
            data[type] = response.data;
            progress += increment;
            this.updateProgress(Math.round(progress), `Exporting ${type}...`);
        }

        return data;
    },

    downloadExportFile: async function(data, format) {
        const timestamp = new Date().toISOString().split('T')[0];

        if (format === 'excel') {
            const workbook = XLSX.utils.book_new();
            
            Object.entries(data).forEach(([type, items]) => {
                const worksheet = XLSX.utils.json_to_sheet(items);
                XLSX.utils.book_append_sheet(workbook, worksheet, type);
            });
            
            XLSX.writeFile(workbook, `inventory_export_${timestamp}.xlsx`);
        } else {
            Object.entries(data).forEach(([type, items]) => {
                const csv = Papa.unparse(items);
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `inventory_${type}_${timestamp}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            });
        }
    },

    // Import functionality
    handleImport: async function() {
        const file = document.getElementById('importFile').files[0];
        const type = document.getElementById('importType').value;
        const validateOnly = document.getElementById('validateOnly').checked;

        if (!file || !type) {
            utils.showError('Please select both a file and data type');
            return;
        }

        try {
            this.showProgress('Reading file...');
            const data = await this.readFile(file);
            
            this.showProgress('Validating data...');
            const validationResults = await this.validateData(data, type);
            
            this.hideProgress();
            this.showValidationResults(validationResults);

            if (validationResults.valid && !validateOnly) {
                document.getElementById('proceedImport').style.display = 'block';
                this.validatedData = data;
                this.currentImportType = type;
            }
        } catch (error) {
            this.hideProgress();
            utils.showError('Import failed: ' + error);
        }
    },

    readFile: function(file) {
        return new Promise((resolve, reject) => {
            if (file.name.endsWith('.xlsx')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const workbook = XLSX.read(e.target.result, { type: 'array' });
                        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                        const data = XLSX.utils.sheet_to_json(firstSheet);
                        resolve(data);
                    } catch (error) {
                        reject('Failed to parse Excel file: ' + error);
                    }
                };
                reader.readAsArrayBuffer(file);
            } else if (file.name.endsWith('.csv')) {
                Papa.parse(file, {
                    complete: (results) => resolve(results.data),
                    header: true,
                    skipEmptyLines: true,
                    error: (error) => reject('Failed to parse CSV file: ' + error)
                });
            } else {
                reject('Unsupported file format');
            }
        });
    },

    validateData: async function(data, type) {
        const results = {
            valid: true,
            errors: [],
            warnings: []
        };

        if (!Array.isArray(data) || data.length === 0) {
            results.errors.push('File contains no data');
            results.valid = false;
            return results;
        }

        const requiredFields = this.requiredFields[type];
        const firstRow = data[0];
        
        // Check for required fields
        const missingFields = requiredFields.filter(field => !(field in firstRow));
        if (missingFields.length > 0) {
            results.errors.push(`Missing required fields: ${missingFields.join(', ')}`);
            results.valid = false;
        }

        // Validate each row
        for (let i = 0; i < data.length; i++) {
            const row = data[i];
            const rowNum = i + 2;

            // Check required fields have values
            for (const field of requiredFields) {
                if (!row[field]) {
                    results.errors.push(`Row ${rowNum}: Missing value for required field "${field}"`);
                }
            }

            // Type-specific validations
            if (type === 'servers') {
                if (row.ip_address && !this.isValidIP(row.ip_address)) {
                    results.errors.push(`Row ${rowNum}: Invalid IP address format`);
                }
            } else if (type === 'services') {
                if (row.port && (isNaN(row.port) || row.port < 1 || row.port > 65535)) {
                    results.errors.push(`Row ${rowNum}: Invalid port number`);
                }
            } else if (type === 'licenses') {
                if (row.expiry_date && !this.isValidDate(row.expiry_date)) {
                    results.errors.push(`Row ${rowNum}: Invalid date format (use YYYY-MM-DD)`);
                }
            }
        }

        results.valid = results.errors.length === 0;
        return results;
    },

    proceedWithImport: async function() {
        try {
            this.validationModal.hide();
            this.showProgress('Importing data...');

            let imported = 0;
            const total = this.validatedData.length;

            for (const item of this.validatedData) {
                await utils.api.create(this.currentImportType, item);
                imported++;
                this.updateProgress(
                    Math.round((imported / total) * 100),
                    `Imported ${imported} of ${total} records...`
                );
            }

            this.hideProgress();
            utils.showSuccess(`Successfully imported ${imported} records`);
        } catch (error) {
            this.hideProgress();
            utils.showError('Import failed: ' + error);
        }
    },

    // Template handling
    downloadTemplate: function(type) {
        const template = this.templates[type];
        const timestamp = new Date().toISOString().split('T')[0];
        
        // Create workbook with template data
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.json_to_sheet(template);
        XLSX.utils.book_append_sheet(workbook, worksheet, type);
        
        // Download the template
        XLSX.writeFile(workbook, `${type}_template_${timestamp}.xlsx`);
    },

    // UI helpers
    showProgress: function(message) {
        this.progressModal.show();
        this.updateProgress(0, message);
    },

    hideProgress: function() {
        this.progressModal.hide();
    },

    updateProgress: function(percent, message) {
        const progressBar = document.getElementById('importProgress');
        const messageElement = document.getElementById('progressMessage');
        
        progressBar.style.width = `${percent}%`;
        progressBar.textContent = `${percent}%`;
        messageElement.textContent = message;
    },

    showValidationResults: function(results) {
        const container = document.getElementById('validationResults');
        container.innerHTML = '';

        if (results.errors.length > 0) {
            const errorList = document.createElement('div');
            errorList.className = 'alert alert-danger';
            errorList.innerHTML = '<strong>Errors:</strong><ul>' +
                results.errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
            container.appendChild(errorList);
        }

        if (results.warnings.length > 0) {
            const warningList = document.createElement('div');
            warningList.className = 'alert alert-warning';
            warningList.innerHTML = '<strong>Warnings:</strong><ul>' +
                results.warnings.map(warning => `<li>${warning}</li>`).join('') + '</ul>';
            container.appendChild(warningList);
        }

        if (results.valid) {
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success';
            successMessage.textContent = 'Validation successful. Data is ready to import.';
            container.appendChild(successMessage);
        }

        this.validationModal.show();
    },

    // Utility functions
    isValidIP: function(ip) {
        const pattern = /^(\d{1,3}\.){3}\d{1,3}$/;
        if (!pattern.test(ip)) return false;
        return ip.split('.').every(num => parseInt(num) >= 0 && parseInt(num) <= 255);
    },

    isValidDate: function(date) {
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(date)) return false;
        const d = new Date(date);
        return d instanceof Date && !isNaN(d);
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => importExportModule.init());