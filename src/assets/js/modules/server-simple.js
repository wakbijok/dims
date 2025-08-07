// Simplified server module for debugging
const serverModule = {
    modal: null,
    currentId: null,
    
    init: function() {
        this.modal = new bootstrap.Modal(document.getElementById('serverModal'));
        this.initializeEventListeners();
        this.loadServers();
        this.loadFilters();
    },
    
    initializeEventListeners: function() {
        const serverForm = document.getElementById('serverForm');
        if (serverForm) {
            serverForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveServer();
            });
        }
    },
    
    loadServers: async function() {
        try {
            const response = await utils.api.getAll('servers');
            this.renderServers(response.data);
        } catch (error) {
            utils.showError('Failed to load servers: ' + error);
        }
    },
    
    renderServers: function(servers) {
        const tbody = document.getElementById('serverTableBody');
        if (!tbody) {
            console.error('serverTableBody element not found in renderServers');
            return;
        }
        
        tbody.innerHTML = '';
        
        servers.forEach(server => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${server.hostname || '-'}</td>
                <td>${server.ip_address}</td>
                <td><span class="badge ${server.server_type === 'Physical' ? 'bg-warning' : 'bg-info'}">${server.server_type || 'VM'}</span></td>
                <td>${server.location_name || '-'}</td>
                <td>${server.environment_name || '-'}</td>
                <td>${server.description || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="serverModule.showEditModal(${server.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="serverModule.deleteServer(${server.id})">Delete</button>
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
    
    loadFilters: async function() {
        try {
            // Load locations
            const locations = await utils.api.getAll('locations');
            const locationSelect = document.getElementById('location_id');
            if (locationSelect && locations.data) {
                locations.data.forEach(location => {
                    locationSelect.add(new Option(location.name, location.id));
                });
            }
            
            // Load environments
            const environments = await utils.api.getAll('environments');
            const environmentSelect = document.getElementById('environment_id');
            if (environmentSelect && environments.data) {
                environments.data.forEach(env => {
                    environmentSelect.add(new Option(env.name, env.id));
                });
            }
        } catch (error) {
            console.error('Failed to load filters:', error);
        }
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('servers', id);
            const server = response.data;
            
            this.currentId = id;
            document.getElementById('serverModalTitle').textContent = 'Edit Server';
            
            // Populate form
            document.getElementById('hostname').value = server.hostname || '';
            document.getElementById('ip_address').value = server.ip_address;
            document.getElementById('server_type').value = server.server_type || 'VM';
            document.getElementById('location_id').value = server.location_id;
            document.getElementById('environment_id').value = server.environment_id;
            document.getElementById('description').value = server.description || '';
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load server details: ' + error);
        }
    },
    
    deleteServer: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this server? This will also delete all associated services, hardware specs, and configurations.')) {
            return;
        }
        
        try {
            await utils.api.delete('servers', id);
            utils.showSuccess('Server deleted successfully');
            this.loadServers();
        } catch (error) {
            utils.showError('Failed to delete server: ' + error);
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
            this.loadServers();
        } catch (error) {
            utils.showError('Failed to save server: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => serverModule.init());