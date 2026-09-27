// src/assets/js/modules/location.js

const locationModule = {
    modal: null,
    currentId: null,
    
    init: function() {
        this.modal = new bootstrap.Modal(document.getElementById('locationModal'));
        this.initializeEventListeners();
        this.loadLocations();
    },
    
    initializeEventListeners: function() {
        document.getElementById('locationForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveLocation();
        });
    },
    
    loadLocations: async function() {
        try {
            const response = await utils.api.getAll('locations');
            this.renderLocations(response.data);
        } catch (error) {
            utils.showError('Failed to load locations: ' + error);
        }
    },
    
    renderLocations: function(locations) {
        const tbody = document.getElementById('locationTableBody');
        tbody.innerHTML = '';
        
        locations.forEach(location => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${location.name}</td>
                <td>${utils.formatDate(location.created_at)}</td>
                <td>${utils.formatDate(location.updated_at)}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="locationModule.showEditModal(${location.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="locationModule.deleteLocation(${location.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('locationModalTitle').textContent = 'Add Location';
        document.getElementById('locationForm').reset();
        this.modal.show();
    },
    
    showEditModal: async function(id) {
        try {
            const response = await utils.api.getOne('locations', id);
            const location = response.data;
            
            this.currentId = id;
            document.getElementById('locationModalTitle').textContent = 'Edit Location';
            document.getElementById('name').value = location.name;
            
            this.modal.show();
        } catch (error) {
            utils.showError('Failed to load location details: ' + error);
        }
    },
    
    saveLocation: async function() {
        try {
            const form = document.getElementById('locationForm');
            if (!utils.form.validateForm(form)) {
                return;
            }
            
            const data = utils.form.getFormData(form);
            
            if (this.currentId) {
                await utils.api.update('locations', this.currentId, data);
                utils.showSuccess('Location updated successfully');
            } else {
                await utils.api.create('locations', data);
                utils.showSuccess('Location created successfully');
            }
            
            this.modal.hide();
            await this.loadLocations();
        } catch (error) {
            utils.showError('Failed to save location: ' + error);
        }
    },
    
    deleteLocation: async function(id) {
        if (!await utils.confirm('Are you sure you want to delete this location?')) {
            return;
        }
        
        try {
            await utils.api.delete('locations', id);
            utils.showSuccess('Location deleted successfully');
            await this.loadLocations();
        } catch (error) {
            utils.showError('Failed to delete location: ' + error);
        }
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => locationModule.init());