// src/assets/js/modules/dashboard.js

const dashboardModule = {
    charts: {},
    
    init: async function() {
        try {
            await this.loadStats();
            await this.loadCharts();
            await this.loadAlerts();
            await this.loadExpiringLicenses();
            
            // Refresh data every 5 minutes
            setInterval(() => this.refreshData(), 300000);
        } catch (error) {
            utils.showError('Failed to initialize dashboard: ' + error);
        }
    },
    
    refreshData: async function() {
        await this.loadStats();
        await this.loadAlerts();
        await this.loadExpiringLicenses();
    },
    
    loadStats: async function() {
        try {
            // Load servers count
            const serversResponse = await utils.api.getAll('servers');
            document.getElementById('totalServers').textContent = serversResponse.data.length;
            
            // Load expiring licenses count
            const licensesResponse = await utils.api.getAll('licenses', { expiring: true });
            document.getElementById('expiringLicenses').textContent = licensesResponse.data.length;
            
            // Load overdue backups count
            const backupsResponse = await utils.api.getAll('backups', { overdue: true });
            document.getElementById('overdueBackups').textContent = backupsResponse.data.length;
            
            // Load active services count
            const servicesResponse = await utils.api.getAll('services');
            document.getElementById('activeServices').textContent = servicesResponse.data.length;
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    },
    
    loadCharts: async function() {
        try {
            // Load servers data
            const serversResponse = await utils.api.getAll('servers');
            const servers = serversResponse.data;
            
            // Process data for location chart
            const locationData = this.processChartData(servers, 'location_name');
            this.createChart('serversByLocation', 'Servers by Location', locationData);
            
            // Process data for environment chart
            const environmentData = this.processChartData(servers, 'environment_name');
            this.createChart('serversByEnvironment', 'Servers by Environment', environmentData);
        } catch (error) {
            console.error('Failed to load charts:', error);
        }
    },
    
    processChartData: function(data, field) {
        const counts = {};
        data.forEach(item => {
            const value = item[field] || 'Unknown';
            counts[value] = (counts[value] || 0) + 1;
        });
        
        return {
            labels: Object.keys(counts),
            datasets: [{
                data: Object.values(counts),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                ]
            }]
        };
    },
    
    createChart: function(canvasId, title, data) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        // Destroy existing chart if it exists
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false,
                        text: title
                    }
                }
            }
        });
    },
    
    loadAlerts: async function() {
        try {
            const alerts = [];
            
            // Get expiring licenses
            const licensesResponse = await utils.api.getAll('licenses', { expiring: true });
            licensesResponse.data.forEach(license => {
                alerts.push({
                    type: 'warning',
                    message: `License for ${license.server_hostname} expires in ${this.getDaysUntil(license.expiry_date)} days`,
                    date: new Date(license.expiry_date)
                });
            });
            
            // Get overdue backups
            const backupsResponse = await utils.api.getAll('backups', { overdue: true });
            backupsResponse.data.forEach(backup => {
                alerts.push({
                    type: 'danger',
                    message: `Backup overdue for ${backup.server_hostname}`,
                    date: new Date(backup.updated_at)
                });
            });
            
            // Sort alerts by date
            alerts.sort((a, b) => b.date - a.date);
            
            // Render alerts
            const alertsList = document.getElementById('alertsList');
            alertsList.innerHTML = alerts.length ? '' : '<div class="list-group-item">No active alerts</div>';
            
            alerts.forEach(alert => {
                const alertElement = document.createElement('div');
                alertElement.className = `list-group-item list-group-item-${alert.type}`;
                alertElement.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${alert.message}</h6>
                        <small>${utils.formatDate(alert.date)}</small>
                    </div>
                `;
                alertsList.appendChild(alertElement);
            });
        } catch (error) {
            console.error('Failed to load alerts:', error);
        }
    },
    
    loadExpiringLicenses: async function() {
        try {
            const response = await utils.api.getAll('licenses', { expiring: true });
            const licenses = response.data;
            
            // Sort by days until expiry
            licenses.sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date));
            
            const tbody = document.getElementById('expiringLicensesList');
            tbody.innerHTML = licenses.length ? '' : '<tr><td colspan="4">No licenses expiring soon</td></tr>';
            
            licenses.forEach(license => {
                const daysLeft = this.getDaysUntil(license.expiry_date);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${license.server_hostname}</td>
                    <td>${license.license_type}</td>
                    <td>${utils.formatDate(license.expiry_date)}</td>
                    <td><span class="badge bg-${this.getDaysLeftClass(daysLeft)}">${daysLeft} days</span></td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Failed to load expiring licenses:', error);
        }
    },
    
    getDaysUntil: function(date) {
        const diff = new Date(date) - new Date();
        return Math.ceil(diff / (1000 * 60 * 60 * 24));
    },
    
    getDaysLeftClass: function(days) {
        if (days <= 7) return 'danger';
        if (days <= 15) return 'warning';
        return 'info';
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => dashboardModule.init());