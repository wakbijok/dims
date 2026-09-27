// src/assets/js/modules/backup.js

const backupModule = {
    modal: null,
    currentId: null,
    
    init: async function() {
        this.modal = new bootstrap.Modal(document.getElementById('backupModal'));
        this.initializeEventListeners();
        await this.loadServers();
        await this.loadBackups();
    },
    
    initializeEventListeners: function() {
        // Search and filters
        document.getElementById('backupSearch').addEventListener('input', () => this.loadBackups());
        document.getElementById('serverFilter').addEventListener('change', () => this.loadBackups());
        document.getElementById('typeFilter').addEventListener('change', () => this.loadBackups());
        document.getElementById('overdueFilter').addEventListener('change', () => this.loadBackups());
        
        // Form submission
        document.getElementById('backupForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveBackup();
        });
        
        // Custom schedule handling
        document.getElementById('schedule').addEventListener('change', (e) => {
            const customScheduleDiv = document.getElementById('customScheduleDiv');
            customScheduleDiv.style.display = e.target.value === 'Custom' ? 'block' : 'none';
            
            const customScheduleInput = document.getElementById('custom_schedule');
            customScheduleInput.required = e.target.value === 'Custom';
        });
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
    
    loadBackups: async function() {
        try {
            const searchTerm = document.getElementById('backupSearch').value;
            const serverId = document.getElementById('serverFilter').value;
            const backupType = document.getElementById('typeFilter').value;
            const overdueOnly = document.getElementById('overdueFilter').checked;
            
            let params = {};
            if (searchTerm) params.search = searchTerm;
            if (serverId) params.server_id = serverId;
            if (backupType) params.backup_type = backupType;
            if (overdueOnly) params.overdue = true;
            
            const response = await utils.api.getAll('backups', params);
            this.renderBackups(response.data);
        } catch (error) {
            utils.showError('Failed to load backup configurations: ' + error);
        }
    },
    
    getStatusClass: function(backup) {
        const lastBackup = new Date(backup.updated_at);
        const now = new Date();
        const hoursSinceLastBackup = (now - lastBackup) / (1000 * 60 * 60);
        
        if (hoursSinceLastBackup > 48) {
            return 'danger';
        } else if (hoursSinceLastBackup > 24) {
            return 'warning';
        }
        return 'success';
    },
    
    renderBackups: function(backups) {
        const tbody = document.getElementById('backupTableBody');
        tbody.innerHTML = '';
        
        backups.forEach(backup => {
            const statusClass = this.getStatusClass(backup);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${backup.server_hostname || '-'}</td>
                <td>${backup.backup_type || '-'}</td>
                <td>${backup.schedule || '-'}</td>
                <td>${backup.retention_period || '-'}</td>
                <td>${utils.formatDate(backup.updated_at)}</td>
                <td>
                    <span class="badge bg-${statusClass}">
                        ${statusClass.charAt(0).toUpperCase() + statusClass.slice(1)}
                    </span>
                </td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="backupModule.showEditModal(${backup.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="backupModule.deleteBackup(${backup.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('backupModalTitle').textContent = 'Add Backup Configuration';
        document.getElementById('backupForm').reset();
        document.getElementById('customScheduleDiv').style.display = 'none';
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('backups', id);
            const backup = response.data;
            
            this.currentId = id;
            document.getElementById('backupModalTitle').textContent = 'Edit Backup Configuration';
            
            document.getElementById('server_id').value = backup.server_id;
            document.getElementById('backup_type').value = backup.backup_type;
            
            // Handle schedule
            const schedule = document.getElementById('schedule');
            if (['Daily', 'Weekly', 'Monthly'].includes(backup.schedule)) {
                schedule.value = backup.schedule;
                document.getElementById('customScheduleDiv').style.display = 'none';
            } else {
                schedule.value = 'Custom';
                document.getElementById('customScheduleDiv').style.display = 'block';
                document.getElementById('custom_schedule').value = backup.schedule;
            }
            
            // Handle retention period
            const retentionMatch = backup.retention_period?.match(/(\d+)\s+(\w+)/);
            if (retentionMatch) {
                document.getElementById('retention_number').value = retentionMatch[1];
                document.getElementById('retention_unit').value = retentionMatch[2].toLowerCase();
            }
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load backup configuration: ' + error);
        }
    },
    
    saveBackup: async function() {
        try {
            const form = document.getElementById('backupForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const formData = utils.form.getFormData(form);
            
            // Format schedule
            const schedule = formData.schedule === 'Custom' 
                ? formData.custom_schedule 
                : formData.schedule;
            
            // Format retention period
            const retentionPeriod = formData.retention_number && formData.retention_unit
                ? `${formData.retention_number} ${formData.retention_unit}`
                : null;
            
            const data = {
                server_id: formData.server_id,
                backup_type: formData.backup_type,
                schedule: schedule,
                retention_period: retentionPeriod
            };
            
            if (this.currentId) {
                await utils.api.update('backups', this.currentId, data);
                utils.showSuccess('Backup configuration updated successfully');
            } else {
                await utils.api.create('backups', data);
                utils.showSuccess('Backup configuration created successfully');
            }
            
            this.modal.hide();
            await this.loadBackups();
        } catch (error) {
            utils.showError('Failed to save backup configuration: ' + error);
        }
    },
    
    deleteBackup: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this backup configuration?')) {
            return;
        }
        
        try {
            await utils.api.delete('backups', id);
            utils.showSuccess('Backup configuration deleted successfully');
            await this.loadBackups();
        } catch (error) {
            utils.showError('Failed to delete backup configuration: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => backupModule.init());