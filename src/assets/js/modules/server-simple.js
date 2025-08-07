// Simplified server module for debugging
console.log('server-simple.js file loaded');
const serverModule = {
    modal: null,
    currentId: null,
    filters: {
        search: '',
        type: '',
        location: '',
        environment: ''
    },
    
    init: function() {
        console.log('serverModule.init() called');
        console.log('DOM elements found:', {
            serverModal: !!document.getElementById('serverModal'),
            serverDetailModal: !!document.getElementById('serverDetailModal'),
            serverTableBody: !!document.getElementById('serverTableBody'),
            serverSearch: !!document.getElementById('serverSearch'),
            selectAll: !!document.getElementById('selectAll'),
            bulkActions: !!document.getElementById('bulkActions'),
            serverCount: !!document.getElementById('serverCount')
        });
        
        this.modal = new bootstrap.Modal(document.getElementById('serverModal'));
        this.detailModal = new bootstrap.Modal(document.getElementById('serverDetailModal'));
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
        
        // Search input
        const serverSearch = document.getElementById('serverSearch');
        if (serverSearch) {
            serverSearch.addEventListener('input', (e) => {
                this.filters.search = e.target.value;
                this.loadServers();
            });
        }
    },
    
    loadServers: async function() {
        try {
            let params = {};
            if (this.filters.search) params.search = this.filters.search;
            if (this.filters.location) params.location_id = this.filters.location;
            if (this.filters.environment) params.environment_id = this.filters.environment;
            
            const response = await utils.api.getAll('servers', params);
            
            // Client-side filtering for server type (since it's not implemented server-side)
            let filteredServers = response.data;
            if (this.filters.type) {
                filteredServers = filteredServers.filter(server => 
                    server.server_type === this.filters.type
                );
            }
            
            this.renderServers(filteredServers);
        } catch (error) {
            utils.showError('Failed to load servers: ' + error);
        }
    },
    
    renderServers: function(servers) {
        console.log('renderServers called with servers:', servers);
        const tbody = document.getElementById('serverTableBody');
        if (!tbody) {
            console.error('serverTableBody element not found in renderServers');
            return;
        }
        
        // Clear existing content
        tbody.innerHTML = '';
        
        // Update server count
        const serverCount = document.getElementById('serverCount');
        if (serverCount) {
            serverCount.textContent = `(${servers.length})`;
            console.log('Updated server count to:', servers.length);
        }
        
        // If no servers, show empty state
        if (!servers || servers.length === 0) {
            console.log('No servers to render');
            return;
        }
        
        servers.forEach((server, index) => {
            console.log(`Rendering server ${index + 1}:`, server);
            const tr = document.createElement('tr');
            
            // Apply styling for decommissioned servers (if status field exists)
            if (server.status === 'Decommissioned') {
                tr.style.opacity = '0.6';
                tr.style.backgroundColor = '#f8f9fa';
            }
            
            // Create checkbox cell
            const checkboxCell = document.createElement('td');
            checkboxCell.style.width = '50px';
            checkboxCell.style.textAlign = 'center';
            checkboxCell.onclick = function(e) { e.stopPropagation(); };
            checkboxCell.innerHTML = `<input type="checkbox" class="server-checkbox" value="${server.id}" onchange="serverModule.updateBulkActions()">`;
            
            // Create number cell
            const numberCell = document.createElement('td');
            numberCell.style.width = '50px';
            numberCell.style.textAlign = 'center';
            numberCell.onclick = function() { serverModule.showDetailModal(server.id); };
            numberCell.textContent = index + 1;
            
            // Create hostname cell
            const hostnameCell = document.createElement('td');
            hostnameCell.onclick = function() { serverModule.showDetailModal(server.id); };
            hostnameCell.textContent = server.hostname || '-';
            
            // Create IP cell
            const ipCell = document.createElement('td');
            ipCell.onclick = function() { serverModule.showDetailModal(server.id); };
            ipCell.textContent = server.ip_address;
            
            // Create type cell
            const typeCell = document.createElement('td');
            typeCell.onclick = function() { serverModule.showDetailModal(server.id); };
            typeCell.innerHTML = `
                <span class="badge ${server.server_type === 'Physical' ? 'bg-warning' : 'bg-info'}">${server.server_type || 'VM'}</span>
                ${server.status === 'Decommissioned' ? '<span class="badge bg-secondary ms-1">Decommissioned</span>' : ''}
            `;
            
            // Create location cell
            const locationCell = document.createElement('td');
            locationCell.onclick = function() { serverModule.showDetailModal(server.id); };
            locationCell.textContent = server.location_name || '-';
            
            // Create environment cell
            const environmentCell = document.createElement('td');
            environmentCell.onclick = function() { serverModule.showDetailModal(server.id); };
            environmentCell.textContent = server.environment_name || '-';
            
            // Create description cell
            const descriptionCell = document.createElement('td');
            descriptionCell.onclick = function() { serverModule.showDetailModal(server.id); };
            descriptionCell.textContent = server.description || '-';
            
            // Create actions cell
            const actionsCell = document.createElement('td');
            actionsCell.style.whiteSpace = 'nowrap';
            actionsCell.innerHTML = `
                <button class="btn btn-sm btn-primary" onclick="serverModule.showEditModal(${server.id}); event.stopPropagation();">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="serverModule.deleteServer(${server.id}); event.stopPropagation();">Delete</button>
            `;
            
            // Add all cells to row
            tr.appendChild(checkboxCell);
            tr.appendChild(numberCell);
            tr.appendChild(hostnameCell);
            tr.appendChild(ipCell);
            tr.appendChild(typeCell);
            tr.appendChild(locationCell);
            tr.appendChild(environmentCell);
            tr.appendChild(descriptionCell);
            tr.appendChild(actionsCell);
            
            tr.style.cursor = 'pointer';
            tbody.appendChild(tr);
            
            console.log(`Successfully added row ${index + 1} to table`);
        });
        
        console.log('Finished rendering all servers');
    },
    
    showAddModal: function() {
        this.currentId = null;
        document.getElementById('serverModalTitle').textContent = 'Add Server';
        document.getElementById('serverForm').reset();
        this.modal.show();
    },
    
    loadFilters: async function() {
        try {
            // Load locations for filter menu
            const locations = await utils.api.getAll('locations');
            const locationFilterMenu = document.getElementById('locationFilterMenu');
            if (locationFilterMenu && locations.data) {
                locations.data.forEach(location => {
                    const li = document.createElement('li');
                    li.innerHTML = `<a class="dropdown-item" href="#" onclick="serverModule.filterByLocation('${location.id}')">${location.name}</a>`;
                    locationFilterMenu.appendChild(li);
                });
            }
            
            // Load environments for filter menu
            const environments = await utils.api.getAll('environments');
            const environmentFilterMenu = document.getElementById('environmentFilterMenu');
            if (environmentFilterMenu && environments.data) {
                environments.data.forEach(env => {
                    const li = document.createElement('li');
                    li.innerHTML = `<a class="dropdown-item" href="#" onclick="serverModule.filterByEnvironment('${env.id}')">${env.name}</a>`;
                    environmentFilterMenu.appendChild(li);
                });
            }
            
            // Load options for modal form
            const locationSelect = document.getElementById('location_id');
            const environmentSelect = document.getElementById('environment_id');
            if (locationSelect && locations.data) {
                locations.data.forEach(location => {
                    locationSelect.add(new Option(location.name, location.id));
                });
            }
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
            
            // Populate hardware fields
            document.getElementById('cpu_type').value = server.cpu_type || '';
            document.getElementById('cpu_cores').value = server.cpu_cores || '';
            document.getElementById('memory_gb').value = server.memory_gb || '';
            document.getElementById('storage_details').value = server.storage_details || '';
            document.getElementById('serial_number').value = server.serial_number || '';
            
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
    
    showDetailModal: async function(id) {
        try {
            const response = await utils.api.getOne('servers', id);
            const server = response.data;
            
            document.getElementById('serverDetailTitle').textContent = `${server.hostname || server.ip_address} - Details`;
            
            const hardwareSection = `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Hardware Specifications 
                            ${server.server_type !== 'Physical' ? '<small class="text-muted">(For Physical servers)</small>' : ''}
                        </h6>
                    </div>
                    <div class="col-md-6">
                        <p><strong>CPU Type:</strong> ${server.cpu_type || (server.server_type === 'Physical' ? 'Not specified' : 'N/A (VM)')}</p>
                        <p><strong>CPU Cores:</strong> ${server.cpu_cores || (server.server_type === 'Physical' ? 'Not specified' : 'N/A (VM)')}</p>
                        <p><strong>Memory:</strong> ${server.memory_gb ? server.memory_gb + ' GB' : (server.server_type === 'Physical' ? 'Not specified' : 'N/A (VM)')}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Storage:</strong> ${server.storage_details || (server.server_type === 'Physical' ? 'Not specified' : 'N/A (VM)')}</p>
                        <p><strong>Serial Number:</strong> ${server.serial_number || (server.server_type === 'Physical' ? 'Not specified' : 'N/A (VM)')}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('serverDetailBody').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Server Information</h6>
                        <p><strong>Hostname:</strong> ${server.hostname || '-'}</p>
                        <p><strong>IP Address:</strong> ${server.ip_address}</p>
                        <p><strong>Server Type:</strong> <span class="badge ${server.server_type === 'Physical' ? 'bg-warning' : 'bg-info'}">${server.server_type || 'VM'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Location & Environment</h6>
                        <p><strong>Location:</strong> ${server.location_name || '-'}</p>
                        <p><strong>Environment:</strong> ${server.environment_name || '-'}</p>
                        <p><strong>Description:</strong> ${server.description || '-'}</p>
                    </div>
                </div>
                ${hardwareSection}
            `;
            
            // Set up edit button
            const editBtn = document.getElementById('editServerFromDetail');
            editBtn.onclick = () => {
                this.detailModal.hide();
                setTimeout(() => this.showEditModal(id), 300);
            };
            
            this.detailModal.show();
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
            this.loadServers();
        } catch (error) {
            utils.showError('Failed to save server: ' + error);
        }
    },
    
    // Excel-style filter methods
    filterByType: function(type) {
        this.filters.type = type;
        this.loadServers();
        event.preventDefault();
    },
    
    filterByLocation: function(locationId) {
        this.filters.location = locationId;
        this.loadServers();
        event.preventDefault();
    },
    
    filterByEnvironment: function(environmentId) {
        this.filters.environment = environmentId;
        this.loadServers();
        event.preventDefault();
    },
    
    clearAllFilters: function() {
        this.filters = {
            search: '',
            type: '',
            location: '',
            environment: ''
        };
        document.getElementById('serverSearch').value = '';
        this.loadServers();
    },

    // Bulk operation methods
    toggleSelectAll: function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.server-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        this.updateBulkActions();
    },

    updateBulkActions: function() {
        const checkboxes = document.querySelectorAll('.server-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.server-checkbox');
        
        console.log('updateBulkActions called:', {
            checkedCount: checkboxes.length,
            totalCheckboxes: allCheckboxes.length,
            bulkActionsElement: !!bulkActions
        });
        
        if (checkboxes.length > 0) {
            bulkActions.style.display = 'block';
            console.log('Showing bulk actions');
        } else {
            bulkActions.style.display = 'none';
            console.log('Hiding bulk actions');
        }
        
        // Update select all checkbox state
        if (checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else if (checkboxes.length > 0) {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    },

    getSelectedServerIds: function() {
        const checkboxes = document.querySelectorAll('.server-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    },

    bulkDecommission: async function() {
        const selectedIds = this.getSelectedServerIds();
        if (selectedIds.length === 0) return;

        if (!await utils.confirm(`Are you sure you want to decommission ${selectedIds.length} selected servers?`)) {
            return;
        }

        try {
            for (const id of selectedIds) {
                await utils.api.update('servers', id, { 
                    status: 'Decommissioned',
                    decommission_date: new Date().toISOString().split('T')[0]
                });
            }
            utils.showSuccess(`${selectedIds.length} servers decommissioned successfully`);
            this.loadServers();
            this.clearSelection();
        } catch (error) {
            utils.showError('Failed to decommission servers: ' + error);
        }
    },

    bulkActivate: async function() {
        const selectedIds = this.getSelectedServerIds();
        if (selectedIds.length === 0) return;

        if (!await utils.confirm(`Are you sure you want to activate ${selectedIds.length} selected servers?`)) {
            return;
        }

        try {
            for (const id of selectedIds) {
                await utils.api.update('servers', id, { 
                    status: 'Active',
                    decommission_date: null
                });
            }
            utils.showSuccess(`${selectedIds.length} servers activated successfully`);
            this.loadServers();
            this.clearSelection();
        } catch (error) {
            utils.showError('Failed to activate servers: ' + error);
        }
    },

    bulkDelete: async function() {
        const selectedIds = this.getSelectedServerIds();
        if (selectedIds.length === 0) return;

        if (!await utils.confirm(`Are you sure you want to permanently delete ${selectedIds.length} selected servers? This action cannot be undone.`)) {
            return;
        }

        try {
            for (const id of selectedIds) {
                await utils.api.delete('servers', id);
            }
            utils.showSuccess(`${selectedIds.length} servers deleted successfully`);
            this.loadServers();
            this.clearSelection();
        } catch (error) {
            utils.showError('Failed to delete servers: ' + error);
        }
    },

    clearSelection: function() {
        const checkboxes = document.querySelectorAll('.server-checkbox');
        const selectAll = document.getElementById('selectAll');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAll.checked = false;
        selectAll.indeterminate = false;
        this.updateBulkActions();
    }
};

// Initialize module when document is ready
console.log('Adding DOMContentLoaded listener for serverModule');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired, initializing serverModule');
    serverModule.init();
});