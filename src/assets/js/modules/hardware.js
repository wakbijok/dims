// src/assets/js/modules/hardware.js

const hardwareModule = {
    modal: null,
    currentId: null,
    
    init: async function() {
        this.modal = new bootstrap.Modal(document.getElementById('hardwareModal'));
        this.initializeEventListeners();
        await this.loadServers();
        await this.loadHardware();
    },
    
    initializeEventListeners: function() {
        document.getElementById('hardwareSearch').addEventListener('input', () => {
            this.loadHardware();
        });
        
        document.getElementById('serverFilter').addEventListener('change', () => {
            this.loadHardware();
        });
        
        document.getElementById('hardwareForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveHardware();
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
    
    loadHardware: async function() {
        try {
            const searchTerm = document.getElementById('hardwareSearch').value;
            const serverId = document.getElementById('serverFilter').value;
            
            let params = {};
            if (searchTerm) params.search = searchTerm;
            if (serverId) params.server_id = serverId;
            
            const response = await utils.api.getAll('hardware', params);
            this.renderHardware(response.data);
        } catch (error) {
            utils.showError('Failed to load hardware specifications: ' + error);
        }
    },
    
    renderHardware: function(hardware) {
        const tbody = document.getElementById('hardwareTableBody');
        tbody.innerHTML = '';
        
        hardware.forEach(spec => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${spec.server_hostname || '-'}</td>
                <td>${spec.cpu || '-'}</td>
                <td>${spec.memory || '-'}</td>
                <td>${spec.storage || '-'}</td>
                <td>${spec.serial_number || '-'}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="hardwareModule.showEditModal(${spec.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="hardwareModule.deleteHardware(${spec.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('hardwareModalTitle').textContent = 'Add Hardware Specification';
        document.getElementById('hardwareForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('hardware', id);
            const spec = response.data;
            
            this.currentId = id;
            document.getElementById('hardwareModalTitle').textContent = 'Edit Hardware Specification';
            
            document.getElementById('server_id').value = spec.server_id;
            document.getElementById('cpu').value = spec.cpu || '';
            document.getElementById('memory').value = spec.memory || '';
            document.getElementById('storage').value = spec.storage || '';
            document.getElementById('serial_number').value = spec.serial_number || '';
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load hardware specification: ' + error);
        }
    },
    
    saveHardware: async function() {
        try {
            const form = document.getElementById('hardwareForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            // Check serial number uniqueness
            const serialNumber = data.serial_number;
            if (serialNumber) {
                try {
                    const response = await utils.api.getAll('hardware', { serial_number: serialNumber });
                    const existing = response.data.find(spec => spec.id !== this.currentId);
                    if (existing) {
                        utils.showError('Serial number already exists');
                        return;
                    }
                } catch (error) {
                    console.error('Error checking serial number:', error);
                }
            }
            
            if (this.currentId) {
                await utils.api.update('hardware', this.currentId, data);
                utils.showSuccess('Hardware specification updated successfully');
            } else {
                await utils.api.create('hardware', data);
                utils.showSuccess('Hardware specification created successfully');
            }
            
            this.modal.hide();
            await this.loadHardware();
        } catch (error) {
            utils.showError('Failed to save hardware specification: ' + error);
        }
    },
    
    deleteHardware: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this hardware specification?')) {
            return;
        }
        
        try {
            await utils.api.delete('hardware', id);
            utils.showSuccess('Hardware specification deleted successfully');
            await this.loadHardware();
        } catch (error) {
            utils.showError('Failed to delete hardware specification: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => hardwareModule.init());