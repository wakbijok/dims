// src/assets/js/modules/environment.js

const environmentModule = {
    modal: null,
    currentId: null,
    
    init: function() {
        this.modal = new bootstrap.Modal(document.getElementById('environmentModal'));
        this.initializeEventListeners();
        this.loadEnvironments();
    },
    
    initializeEventListeners: function() {
        document.getElementById('environmentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveEnvironment();
        });
    },
    
    loadEnvironments: async function() {
        try {
            const response = await utils.api.getAll('environments');
            this.renderEnvironments(response.data);
        } catch (error) {
            utils.showError('Failed to load environments: ' + error);
        }
    },
    
    renderEnvironments: function(environments) {
        const tbody = document.getElementById('environmentTableBody');
        tbody.innerHTML = '';
        
        environments.forEach(environment => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${environment.name}</td>
                <td>${utils.formatDate(environment.created_at)}</td>
                <td>${utils.formatDate(environment.updated_at)}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="environmentModule.showEditModal(${environment.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="environmentModule.deleteEnvironment(${environment.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('environmentModalTitle').textContent = 'Add Environment';
        document.getElementById('environmentForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('environments', id);
            const environment = response.data;
            
            this.currentId = id;
            document.getElementById('environmentModalTitle').textContent = 'Edit Environment';
            document.getElementById('name').value = environment.name;
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load environment details: ' + error);
        }
    },
    
    saveEnvironment: async function() {
        try {
            const form = document.getElementById('environmentForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            if (this.currentId) {
                await utils.api.update('environments', this.currentId, data);
                utils.showSuccess('Environment updated successfully');
            } else {
                await utils.api.create('environments', data);
                utils.showSuccess('Environment created successfully');
            }
            
            this.modal.hide();
            await this.loadEnvironments();
        } catch (error) {
            utils.showError('Failed to save environment: ' + error);
        }
    },
    
    deleteEnvironment: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this environment?')) {
            return;
        }
        
        try {
            await utils.api.delete('environments', id);
            utils.showSuccess('Environment deleted successfully');
            await this.loadEnvironments();
        } catch (error) {
            utils.showError('Failed to delete environment: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => environmentModule.init());