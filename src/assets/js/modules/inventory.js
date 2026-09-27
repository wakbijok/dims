// src/assets/js/modules/inventory.js

const inventoryModule = {
    vmTemplate: null,
    hardwareTemplate: null,
    inventoryData: {
        vm: [],
        hardware: []
    },
    currentView: 'vm',

    init() {
        this.vmTemplate = document.getElementById('vmInstanceTemplate');
        this.hardwareTemplate = document.getElementById('hardwareTemplate');
        this.initializeEventListeners();
        this.loadFilters()
            .then(() => this.loadInventory())
            .catch(error => utils.showError('Failed to initialize: ' + error));
    },

    initializeEventListeners() {
        // View switcher
        document.querySelectorAll('input[name="viewType"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.currentView = e.target.value;
                document.querySelector('.hardware-filter').style.display = 
                    this.currentView === 'hardware' ? 'block' : 'none';
                this.filterInventory();
            });
        });

        // Search and filters
        const filterInputs = ['inventorySearch', 'locationFilter', 'environmentFilter', 'statusFilter', 'hardwareTypeFilter'];
        filterInputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', () => this.filterInventory());
                if (element.tagName === 'INPUT') {
                    element.addEventListener('input', () => this.filterInventory());
                }
            }
        });
    },

    async loadFilters() {
        try {
            const [locationsResponse, environmentsResponse] = await Promise.all([
                utils.api.getAll('locations'),
                utils.api.getAll('environments')
            ]);

            // Load locations
            const locationFilter = document.getElementById('locationFilter');
            locationsResponse.data.forEach(location => {
                locationFilter.add(new Option(location.name, location.id));
            });

            // Load environments
            const environmentFilter = document.getElementById('environmentFilter');
            environmentsResponse.data.forEach(env => {
                environmentFilter.add(new Option(env.name, env.id));
            });
        } catch (error) {
            utils.showError('Failed to load filters: ' + error);
            throw error;
        }
    },

    async loadInventory() {
        try {
            const serversResponse = await utils.api.getAll('servers');
            const servers = serversResponse.data;

            // Load VM data
            const vmPromises = servers.map(async server => {
                const [hardware, services, backups, licenses] = await Promise.all([
                    utils.api.getAll('hardware', { server_id: server.id }),
                    utils.api.getAll('services', { server_id: server.id }),
                    utils.api.getAll('backups', { server_id: server.id }),
                    utils.api.getAll('licenses', { server_id: server.id })
                ]);

                return {
                    ...server,
                    hardware: hardware.data[0] || {},
                    services: services.data || [],
                    backups: backups.data || [],
                    licenses: licenses.data || []
                };
            });

            this.inventoryData.vm = await Promise.all(vmPromises);

            // Load hardware data
            const hardwareResponse = await utils.api.getAll('hardware');
            const hardwarePromises = hardwareResponse.data.map(async hardware => {
                const server = servers.find(s => s.id === hardware.server_id);
                const licenses = await utils.api.getAll('licenses', { server_id: hardware.server_id });

                return {
                    ...hardware,
                    server: server || {},
                    licenses: licenses.data || []
                };
            });

            this.inventoryData.hardware = await Promise.all(hardwarePromises);
            this.filterInventory();
        } catch (error) {
            utils.showError('Failed to load inventory: ' + error);
            throw error;
        }
    },

    filterInventory() {
        const searchTerm = document.getElementById('inventorySearch').value.toLowerCase();
        const locationId = document.getElementById('locationFilter').value;
        const environmentId = document.getElementById('environmentFilter').value;
        const status = document.getElementById('statusFilter').value;
        const hardwareType = document.getElementById('hardwareTypeFilter').value;

        const filteredData = this.currentView === 'vm' 
            ? this.filterVMData(searchTerm, locationId, environmentId, status)
            : this.filterHardwareData(searchTerm, locationId, hardwareType, status);

        if (this.currentView === 'vm') {
            this.renderVMInventory(filteredData);
        } else {
            this.renderHardwareInventory(filteredData);
        }
    },

    filterVMData(searchTerm, locationId, environmentId, status) {
        return this.inventoryData.vm.filter(vm => {
            const matchesSearch = 
                vm.hostname.toLowerCase().includes(searchTerm) ||
                (vm.ip_address?.toLowerCase() || '').includes(searchTerm) ||
                (vm.description?.toLowerCase() || '').includes(searchTerm);

            const matchesLocation = !locationId || vm.location_id === locationId;
            const matchesEnvironment = !environmentId || vm.environment_id === environmentId;
            const matchesStatus = !status || this.getVMStatus(vm) === status;

            return matchesSearch && matchesLocation && matchesEnvironment && matchesStatus;
        });
    },

    filterHardwareData(searchTerm, locationId, hardwareType, status) {
        return this.inventoryData.hardware.filter(hw => {
            const matchesSearch = 
                (hw.server.hostname?.toLowerCase() || '').includes(searchTerm) ||
                (hw.serial_number?.toLowerCase() || '').includes(searchTerm) ||
                (hw.model?.toLowerCase() || '').includes(searchTerm);

            const matchesLocation = !locationId || hw.server.location_id === locationId;
            const matchesType = !hardwareType || hw.type === hardwareType;
            const matchesStatus = !status || this.getHardwareStatus(hw) === status;

            return matchesSearch && matchesLocation && matchesType && matchesStatus;
        });
    },

    // Status Management
    getVMStatus: function(vm) {
        const hasExpiringLicense = vm.licenses.some(license => {
            const daysUntilExpiry = Math.ceil((new Date(license.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
            return daysUntilExpiry <= 30;
        });

        const hasOverdueBackup = vm.backups.some(backup => {
            const hoursSinceLastBackup = (new Date() - new Date(backup.updated_at)) / (1000 * 60 * 60);
            return hoursSinceLastBackup > 24;
        });

        if (hasExpiringLicense && hasOverdueBackup) return 'critical';
        if (hasExpiringLicense || hasOverdueBackup) return 'warning';
        return 'active';
    },

    getHardwareStatus: function(hw) {
        const hasExpiringLicense = hw.licenses.some(license => {
            const daysUntilExpiry = Math.ceil((new Date(license.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
            return daysUntilExpiry <= 30;
        });

        const hasWarrantyExpiring = hw.warranty_end ? 
            (new Date(hw.warranty_end) - new Date()) / (1000 * 60 * 60 * 24) <= 30 : false;

        if (hasExpiringLicense && hasWarrantyExpiring) return 'critical';
        if (hasExpiringLicense || hasWarrantyExpiring) return 'warning';
        return 'active';
    },

    getStatusColor: function(status) {
        const colors = {
            critical: 'danger',
            warning: 'warning',
            active: 'success',
            default: 'secondary'
        };
        return colors[status] || colors.default;
    },

    // Rendering Functions
    renderVMInventory: function(data) {
        const container = document.querySelector('.inventory-list');
        container.innerHTML = '';

        data.forEach(vm => {
            const vmCard = this.vmTemplate.content.cloneNode(true);
            const status = this.getVMStatus(vm);

            // Basic info
            vmCard.querySelector('.hostname').textContent = vm.hostname;
            vmCard.querySelector('.ip-address').textContent = vm.ip_address || 'N/A';
            vmCard.querySelector('.description').textContent = vm.description || 'No description';

            // Status indicator and badges
            vmCard.querySelector('.status-indicator').className = `status-indicator bg-${this.getStatusColor(status)}`;
            vmCard.querySelector('.location-badge').textContent = vm.location_name;
            vmCard.querySelector('.environment-badge').textContent = vm.environment_name;

            // Hardware specs
            const hardware = vm.hardware;
            if (hardware) {
                vmCard.querySelector('.cpu').textContent = hardware.cpu || 'N/A';
                vmCard.querySelector('.memory').textContent = hardware.memory || 'N/A';
                vmCard.querySelector('.storage').textContent = hardware.storage || 'N/A';
                vmCard.querySelector('.serial-number').textContent = hardware.serial_number || 'N/A';
            }

            // Services
            this.renderServices(vmCard.querySelector('.services-list'), vm.services);

            // Licenses
            this.renderLicenses(vmCard.querySelector('.licenses-list'), vm.licenses);

            // Backup status
            this.renderBackupStatus(vmCard.querySelector('.backup-status'), vm.backups);

            container.appendChild(vmCard);
        });
    },

    renderHardwareInventory: function(data) {
        const container = document.querySelector('.inventory-list');
        container.innerHTML = '';

        data.forEach(hw => {
            const hwCard = this.hardwareTemplate.content.cloneNode(true);
            const status = this.getHardwareStatus(hw);

            // Basic info
            hwCard.querySelector('.hardware-name').textContent = hw.server.hostname || hw.serial_number;
            hwCard.querySelector('.serial-number').textContent = hw.serial_number || 'N/A';
            hwCard.querySelector('.model').textContent = hw.model || 'N/A';
            hwCard.querySelector('.manufacturer').textContent = hw.manufacturer || 'N/A';

            // Status and badges
            hwCard.querySelector('.status-indicator').className = `status-indicator bg-${this.getStatusColor(status)}`;
            hwCard.querySelector('.type-badge').textContent = hw.type || 'Unknown Type';
            hwCard.querySelector('.location-badge').textContent = hw.server.location_name || 'Unknown Location';

            // Specifications
            hwCard.querySelector('.cpu').textContent = hw.cpu || 'N/A';
            hwCard.querySelector('.memory').textContent = hw.memory || 'N/A';
            hwCard.querySelector('.storage').textContent = hw.storage || 'N/A';
            hwCard.querySelector('.network').textContent = this.formatNetworkInfo(hw) || 'N/A';

            // Support and maintenance
            this.renderLicenses(hwCard.querySelector('.support-info'), hw.licenses);
            this.renderMaintenanceHistory(hwCard.querySelector('.maintenance-history'), hw.maintenance_history);

            container.appendChild(hwCard);
        });
    },

    // Helper render functions
    renderServices: function(container, services) {
        if (!services?.length) {
            container.innerHTML = '<div>No services configured</div>';
            return;
        }

        services.forEach(service => {
            const serviceItem = document.createElement('div');
            serviceItem.textContent = `${service.protocol || ''} ${service.port || ''} - ${service.url || 'N/A'}`;
            container.appendChild(serviceItem);
        });
    },

    renderLicenses: function(container, licenses) {
        if (!licenses?.length) {
            container.innerHTML = '<div>No licenses registered</div>';
            return;
        }

        licenses.forEach(license => {
            const daysUntilExpiry = Math.ceil(
                (new Date(license.expiry_date) - new Date()) / (1000 * 60 * 60 * 24)
            );
            
            const item = document.createElement('div');
            item.innerHTML = `
                ${license.license_type} - Expires in ${daysUntilExpiry} days
                <span class="badge bg-${this.getExpiryColor(daysUntilExpiry)}">
                    ${this.getExpiryStatus(daysUntilExpiry)}
                </span>
            `;
            container.appendChild(item);
        });
    },

    renderBackupStatus: function(container, backups) {
        if (!backups?.length) {
            container.innerHTML = '<span class="badge bg-warning">No backups configured</span>';
            return;
        }

        const lastBackup = new Date(Math.max(...backups.map(b => new Date(b.updated_at))));
        const hoursSinceLastBackup = (new Date() - lastBackup) / (1000 * 60 * 60);
        
        container.innerHTML = `
            <span class="badge bg-${hoursSinceLastBackup > 24 ? 'danger' : 'success'}">
                Last Backup: ${utils.formatDate(lastBackup)}
            </span>
        `;
    },

    renderMaintenanceHistory: function(container, history) {
        if (!history?.length) {
            container.innerHTML = '<div>No maintenance history</div>';
            return;
        }

        history.forEach(maintenance => {
            const item = document.createElement('div');
            item.innerHTML = `
                <small>${utils.formatDate(maintenance.date)} - ${maintenance.type}</small>
            `;
            container.appendChild(item);
        });
    },

    // Utility functions
    formatNetworkInfo: function(hw) {
        if (!hw.network_interfaces?.length) return null;
        return hw.network_interfaces
            .map(intf => `${intf.name}: ${intf.ip_address}`)
            .join(', ');
    },

    getExpiryColor: function(days) {
        if (days <= 7) return 'danger';
        if (days <= 30) return 'warning';
        return 'success';
    },

    getExpiryStatus: function(days) {
        if (days <= 7) return 'Critical';
        if (days <= 30) return 'Warning';
        return 'OK';
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => inventoryModule.init());