// src/assets/js/modules/server.js

const serverModule = {
    modal: null,
    currentId: null,
    
    init: function() {
        // Check if required elements exist
        const serverModal = document.getElementById('serverModal');
        const serverTableBody = document.getElementById('serverTableBody');
        
        if (!serverModal || !serverTableBody) {
            console.error('Required DOM elements not found for server module');
            return;
        }
        
        // Initialize Bootstrap modal
        this.modal = new bootstrap.Modal(serverModal);
        
        // Initialize event listeners
        this.initializeEventListeners();
        
        // Load initial data (don't await, let them run independently)
        this.loadFilters();
        this.loadServers();
    },
    
    initializeEventListeners: function() {
        // Search input
        const serverSearch = document.getElementById('serverSearch');
        if (serverSearch) {
            serverSearch.addEventListener('input', (e) => {
                this.loadServers();
            });
        }
        
        // Filters
        const locationFilter = document.getElementById('locationFilter');
        if (locationFilter) {
            locationFilter.addEventListener('change', () => {
                this.loadServers();
            });
        }
        
        const environmentFilter = document.getElementById('environmentFilter');
        if (environmentFilter) {
            environmentFilter.addEventListener('change', () => {
                this.loadServers();
            });
        }
        
        // Form submission
        const serverForm = document.getElementById('serverForm');
        if (serverForm) {
            serverForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveServer();
            });
        }
    },
    
    loadFilters: async function() {
        try {
            // Load locations
            const locations = await utils.api.getAll('locations');
            const locationSelect = document.getElementById('location_id');
            const locationFilter = document.getElementById('locationFilter');
            
            if (locationSelect && locations.data) {
                locations.data.forEach(location => {
                    locationSelect.add(new Option(location.name, location.id));
                });
            }
            
            if (locationFilter && locations.data) {
                locations.data.forEach(location => {
                    locationFilter.add(new Option(location.name, location.id));
                });
            }
            
            // Load environments
            const environments = await utils.api.getAll('environments');
            const environmentSelect = document.getElementById('environment_id');
            const environmentFilter = document.getElementById('environmentFilter');
            
            if (environmentSelect && environments.data) {
                environments.data.forEach(env => {
                    environmentSelect.add(new Option(env.name, env.id));
                });
            }
            
            if (environmentFilter && environments.data) {
                environments.data.forEach(env => {
                    environmentFilter.add(new Option(env.name, env.id));
                });
            }
        } catch (error) {
            utils.showError('Failed to load filters: ' + error);
        }
    },
    
    loadServers: async function() {
        try {
            const searchInput = document.getElementById('serverSearch');
            const locationFilter = document.getElementById('locationFilter');
            const environmentFilter = document.getElementById('environmentFilter');
            
            const searchTerm = searchInput ? searchInput.value : '';
            const locationId = locationFilter ? locationFilter.value : '';
            const environmentId = environmentFilter ? environmentFilter.value : '';
            
            let params = {};
            if (searchTerm) params.search = searchTerm;
            if (locationId) params.location_id = locationId;
            if (environmentId) params.environment_id = environmentId;
            
            const response = await utils.api.getAll('servers', params);
            this.renderServers(response.data);
        } catch (error) {
            console.error('Load servers error:', error);
            utils.showError('Failed to load servers: ' + error);
        }
    },
    
    renderServers: function(servers) {
        const tbody = document.getElementById('serverTableBody');
        if (!tbody) {
            console.error('serverTableBody element not found');
            return;
        }
        tbody.innerHTML = '';
        
        if (!servers || !Array.isArray(servers)) {
            return;
        }
        
        servers.forEach(server => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${server.hostname}</td>
                <td>${server.ip_address || '-'}</td>
                <td>${server.location_name}</td>
                <td>${server.environment_name}</td>
                <td>${server.description || '-'}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="serverModule.viewDetails(${server.id})">
                        Details
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="serverModule.showEditModal(${server.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="serverModule.deleteServer(${server.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('serverModalTitle').textContent = 'Add Server';
        document.getElementById('serverForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('servers', id);
            const server = response.data;
            
            this.currentId = id;
            document.getElementById('serverModalTitle').textContent = 'Edit Server';
            
            // Populate form
            document.getElementById('hostname').value = server.hostname;
            document.getElementById('ip_address').value = server.ip_address || '';
            document.getElementById('location_id').value = server.location_id;
            document.getElementById('environment_id').value = server.environment_id;
            document.getElementById('description').value = server.description || '';
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load server details: ' + error);
        }
    },
    
    saveServer: async function() {
        try {
            const form = document.getElementById('serverForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            if (this.currentId) {
                await utils.api.update('servers', this.currentId, data);
                utils.showSuccess('Server updated successfully');
            } else {
                await utils.api.create('servers', data);
                utils.showSuccess('Server created successfully');
            }
            
            this.modal.hide();
            await this.loadServers();
        } catch (error) {
            utils.showError('Failed to save server: ' + error);
        }
    },
    
    deleteServer: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this server? This will also delete all associated services, hardware specs, and configurations.')) {
            return;
        }
        
        try {
            await utils.api.delete('servers', id);
            utils.showSuccess('Server deleted successfully');
            await this.loadServers();
        } catch (error) {
            utils.showError('Failed to delete server: ' + error);
        }
    },
    
    viewDetails: async function(id) {
        try {
            const [
                serverResponse,
                servicesResponse,
                hardwareResponse,
                backupsResponse,
                licensesResponse
            ] = await Promise.all([
                utils.api.getOne('servers', id),
                utils.api.getAll('services', { server_id: id }),
                utils.api.getAll('hardware', { server_id: id }),
                utils.api.getAll('backups', { server_id: id }),
                utils.api.getAll('licenses', { server_id: id })
            ]);

            const server = serverResponse.data;
            const details = `
                <h5>Server Details</h5>
                <p><strong>Hostname:</strong> ${server.hostname}</p>
                <p><strong>IP Address:</strong> ${server.ip_address || '-'}</p>
                <p><strong>Location:</strong> ${server.location_name}</p>
                <p><strong>Environment:</strong> ${server.environment_name}</p>
                <p><strong>Description:</strong> ${server.description || '-'}</p>
                
                <h5 class="mt-4">Services</h5>
                ${this.formatServices(servicesResponse.data)}
                
                <h5 class="mt-4">Hardware Specifications</h5>
                ${this.formatHardware(hardwareResponse.data)}
                
                <h5 class="mt-4">Backup Configurations</h5>
                ${this.formatBackups(backupsResponse.data)}
                
                <h5 class="mt-4">Licenses</h5>
                ${this.formatLicenses(licensesResponse.data)}
            `;
            
            Swal.fire({
                title: server.hostname,
                html: details,
                width: '800px',
                confirmButtonText: 'Close'
            });
        } catch (error) {
            utils.showError('Failed to load server details: ' + error);
        }
    },
    
    formatServices: function(services) {
        if (!services.length) return '<p>No services configured</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Protocol</th>
                            <th>Port</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${services.map(service => `
                            <tr>
                                <td>${service.url || '-'}</td>
                                <td>${service.protocol || '-'}</td>
                                <td>${service.port || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    },
    
    formatHardware: function(specs) {
        if (!specs.length) return '<p>No hardware specifications recorded</p>';
        
        return specs.map(spec => `
            <p><strong>CPU:</strong> ${spec.cpu || '-'}</p>
            <p><strong>Memory:</strong> ${spec.memory || '-'}</p>
            <p><strong>Storage:</strong> ${spec.storage || '-'}</p>
            <p><strong>Serial Number:</strong> ${spec.serial_number || '-'}</p>
        `).join('');
    },
    
    formatBackups: function(backups) {
        if (!backups.length) return '<p>No backup configurations</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Retention</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${backups.map(backup => `
                            <tr>
                                <td>${backup.backup_type || '-'}</td>
                                <td>${backup.schedule || '-'}</td>
                                <td>${backup.retention_period || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    },
    
    formatLicenses: function(licenses) {
        if (!licenses.length) return '<p>No licenses recorded</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Expiry Date</th>
                            <th>Support Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${licenses.map(license => `
                            <tr>
                                <td>${license.license_type || '-'}</td>
                                <td>${utils.formatDate(license.expiry_date) || '-'}</td>
                                <td>${license.support_level || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => serverModule.init());