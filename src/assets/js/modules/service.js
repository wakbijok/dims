// src/assets/js/modules/service.js

const serviceModule = {
    modal: null,
    currentId: null,
    
    init: async function() {
        this.modal = new bootstrap.Modal(document.getElementById('serviceModal'));
        this.initializeEventListeners();
        await this.loadServers();
        await this.loadServices();
    },
    
    initializeEventListeners: function() {
        document.getElementById('serviceSearch').addEventListener('input', () => {
            this.loadServices();
        });
        
        document.getElementById('serverFilter').addEventListener('change', () => {
            this.loadServices();
        });
        
        document.getElementById('serviceForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveService();
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
    
    loadServices: async function() {
        try {
            const searchTerm = document.getElementById('serviceSearch').value;
            const serverId = document.getElementById('serverFilter').value;
            
            let params = {};
            if (searchTerm) params.search = searchTerm;
            if (serverId) params.server_id = serverId;
            
            const response = await utils.api.getAll('services', params);
            this.renderServices(response.data);
        } catch (error) {
            utils.showError('Failed to load services: ' + error);
        }
    },
    
    renderServices: function(services) {
        const tbody = document.getElementById('serviceTableBody');
        tbody.innerHTML = '';
        
        services.forEach(service => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${service.server_hostname || '-'}</td>
                <td>${service.url || '-'}</td>
                <td>${service.protocol || '-'}</td>
                <td>${service.port || '-'}</td>
                <td>${service.username || '-'}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="serviceModule.showEditModal(${service.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="serviceModule.deleteService(${service.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('serviceModalTitle').textContent = 'Add Service';
        document.getElementById('serviceForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('services', id);
            const service = response.data;
            
            this.currentId = id;
            document.getElementById('serviceModalTitle').textContent = 'Edit Service';
            
            document.getElementById('server_id').value = service.server_id;
            document.getElementById('url').value = service.url;
            document.getElementById('protocol').value = service.protocol;
            document.getElementById('port').value = service.port;
            document.getElementById('username').value = service.username;
            document.getElementById('password').value = ''; // Don't populate password
            document.getElementById('remarks').value = service.remarks;
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load service details: ' + error);
        }
    },
    
    saveService: async function() {
        try {
            const form = document.getElementById('serviceForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            // Only include password if it's been changed
            if (!data.password) {
                delete data.password;
            }
            
            if (this.currentId) {
                await utils.api.update('services', this.currentId, data);
                utils.showSuccess('Service updated successfully');
            } else {
                await utils.api.create('services', data);
                utils.showSuccess('Service created successfully');
            }
            
            this.modal.hide();
            await this.loadServices();
        } catch (error) {
            utils.showError('Failed to save service: ' + error);
        }
    },
    
    deleteService: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this service?')) {
            return;
        }
        
        try {
            await utils.api.delete('services', id);
            utils.showSuccess('Service deleted successfully');
            await this.loadServices();
        } catch (error) {
            utils.showError('Failed to delete service: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => serviceModule.init());