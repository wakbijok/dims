// src/assets/js/modules/license.js

const licenseModule = {
    modal: null,
    currentId: null,
    
    init: async function() {
        this.modal = new bootstrap.Modal(document.getElementById('licenseModal'));
        this.initializeEventListeners();
        await this.loadServers();
        await this.loadLicenses();
        this.checkExpiringLicenses();
    },
    
    initializeEventListeners: function() {
        // Search and filters
        document.getElementById('licenseSearch').addEventListener('input', () => this.loadLicenses());
        document.getElementById('serverFilter').addEventListener('change', () => this.loadLicenses());
        document.getElementById('statusFilter').addEventListener('change', () => this.loadLicenses());
        document.getElementById('supportFilter').addEventListener('change', () => this.loadLicenses());
        
        // Form submission
        document.getElementById('licenseForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveLicense();
        });
        
        // Set minimum date for expiry date
        const expiryDateInput = document.getElementById('expiry_date');
        const today = new Date().toISOString().split('T')[0];
        expiryDateInput.setAttribute('min', today);
    },
    
    loadServers: async function() {
        try {
            const response = await utils.api.getAll('servers');
            const serverSelect = document.getElementById('server_id');
            const serverFilter = document.getElementById('serverFilter');
            
            response.data.forEach(server => {
                const option = new Option(`${server.hostname} (${server.ip_address || 'No IP'})`, server.id);
                serverSelect.add(option.cloneNode(true));
                serverFilter.add(option.cloneNode(true));
            });
        } catch (error) {
            utils.showError('Failed to load servers: ' + error);
        }
    },
    
    loadLicenses: async function() {
        try {
            const searchTerm = document.getElementById('licenseSearch').value;
            const serverId = document.getElementById('serverFilter').value;
            const status = document.getElementById('statusFilter').value;
            const supportLevel = document.getElementById('supportFilter').value;
            
            let params = {};
            if (searchTerm) params.search = searchTerm;
            if (serverId) params.server_id = serverId;
            if (status === 'expiring') params.expiring = true;
            if (status === 'expired') params.expired = true;
            if (supportLevel) params.support_level = supportLevel;
            
            const response = await utils.api.getAll('licenses', params);
            this.renderLicenses(response.data);
        } catch (error) {
            utils.showError('Failed to load licenses: ' + error);
        }
    },
    
    checkExpiringLicenses: async function() {
        try {
            const response = await utils.api.getAll('licenses', { expiring: true });
            const expiringCount = response.data.length;
            
            const alert = document.getElementById('expiringLicensesAlert');
            const count = document.getElementById('expiringLicensesCount');
            
            if (expiringCount > 0) {
                count.textContent = expiringCount;
                alert.style.display = 'block';
            } else {
                alert.style.display = 'none';
            }
        } catch (error) {
            console.error('Failed to check expiring licenses:', error);
        }
    },
    
    getLicenseStatus: function(expiryDate) {
        const today = new Date();
        const expiry = new Date(expiryDate);
        const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
        
        if (daysUntilExpiry < 0) {
            return { text: 'Expired', class: 'danger' };
        } else if (daysUntilExpiry <= 30) {
            return { text: 'Expiring Soon', class: 'warning' };
        }
        return { text: 'Active', class: 'success' };
    },
    
    renderLicenses: function(licenses) {
        const tbody = document.getElementById('licenseTableBody');
        tbody.innerHTML = '';
        
        licenses.forEach(license => {
            const status = this.getLicenseStatus(license.expiry_date);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${license.server_hostname || '-'}</td>
                <td>${license.license_type || '-'}</td>
                <td>${utils.formatDate(license.expiry_date)}</td>
                <td>${license.support_level || '-'}</td>
                <td>
                    <span class="badge bg-${status.class}">
                        ${status.text}
                    </span>
                </td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="licenseModule.showEditModal(${license.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="licenseModule.deleteLicense(${license.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('licenseModalTitle').textContent = 'Add License';
        document.getElementById('licenseForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('licenses', id);
            const license = response.data;
            
            this.currentId = id;
            document.getElementById('licenseModalTitle').textContent = 'Edit License';
            
            document.getElementById('server_id').value = license.server_id;
            document.getElementById('license_type').value = license.license_type;
            document.getElementById('expiry_date').value = license.expiry_date;
            document.getElementById('support_level').value = license.support_level || '';
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load license: ' + error);
        }
    },
    
    saveLicense: async function() {
        try {
            const form = document.getElementById('licenseForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            if (this.currentId) {
                await utils.api.update('licenses', this.currentId, data);
                utils.showSuccess('License updated successfully');
            } else {
                await utils.api.create('licenses', data);
                utils.showSuccess('License created successfully');
            }
            
            this.modal.hide();
            await this.loadLicenses();
            await this.checkExpiringLicenses();
        } catch (error) {
            utils.showError('Failed to save license: ' + error);
        }
    },
    
    deleteLicense: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this license?')) {
            return;
        }
        
        try {
            await utils.api.delete('licenses', id);
            utils.showSuccess('License deleted successfully');
            await this.loadLicenses();
            await this.checkExpiringLicenses();
        } catch (error) {
            utils.showError('Failed to delete license: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => licenseModule.init());