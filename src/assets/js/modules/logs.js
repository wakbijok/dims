// src/assets/js/modules/logs.js

const logsModule = {
    currentPage: 1,
    itemsPerPage: 20,
    
    init: function() {
        this.initializeEventListeners();
        this.loadLogs();
    },
    
    initializeEventListeners: function() {
        document.getElementById('resourceFilter').addEventListener('change', () => {
            this.currentPage = 1;
            this.loadLogs();
        });
        
        document.getElementById('actionFilter').addEventListener('change', () => {
            this.currentPage = 1;
            this.loadLogs();
        });
    },
    
    loadLogs: async function() {
        try {
            const resourceType = document.getElementById('resourceFilter').value;
            const actionType = document.getElementById('actionFilter').value;
            
            let params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            
            if (resourceType) params.resource_type = resourceType;
            if (actionType) params.action_type = actionType;
            
            const response = await utils.api.getAll('logs', params);
            this.renderLogs(response.data);
            this.renderPagination(response.total, response.total_pages);
        } catch (error) {
            utils.showError('Failed to load logs: ' + error);
        }
    },
    
    renderLogs: function(logs) {
        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = '';
        
        logs.forEach(log => {
            const tr = document.createElement('tr');
            const changes = JSON.parse(log.changes || '{}');
            
            tr.innerHTML = `
                <td>${utils.formatDate(log.created_at)}</td>
                <td>
                    <span class="badge bg-${this.getActionColor(log.action_type)}">
                        ${log.action_type}
                    </span>
                </td>
                <td>${this.formatResourceType(log.resource_type)}</td>
                <td>${this.formatChanges(changes, log.action_type)}</td>
                <td>${log.ip_address || 'N/A'}</td>
            `;
            
            tbody.appendChild(tr);
        });
    },
    
    renderPagination: function(total, totalPages) {
        const pagination = document.getElementById('logsPagination');
        pagination.innerHTML = '';
        
        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${this.currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" onclick="logsModule.changePage(${this.currentPage - 1})">
                Previous
            </a>
        `;
        pagination.appendChild(prevLi);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === this.currentPage ? 'active' : ''}`;
            li.innerHTML = `
                <a class="page-link" href="#" onclick="logsModule.changePage(${i})">
                    ${i}
                </a>
            `;
            pagination.appendChild(li);
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${this.currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" onclick="logsModule.changePage(${this.currentPage + 1})">
                Next
            </a>
        `;
        pagination.appendChild(nextLi);
    },
    
    changePage: function(page) {
        this.currentPage = page;
        this.loadLogs();
    },
    
    getActionColor: function(action) {
        const colors = {
            'CREATE': 'success',
            'UPDATE': 'info',
            'DELETE': 'danger'
        };
        return colors[action] || 'secondary';
    },
    
    formatResourceType: function(type) {
        return type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();
    },
    
    formatChanges: function(changes, actionType) {
        if (actionType === 'DELETE') {
            return 'Resource deleted';
        }
        
        if (actionType === 'CREATE') {
            return 'New resource created';
        }
        
        if (!changes.before || !changes.after) {
            return 'No changes recorded';
        }
        
        const changedFields = Object.keys(changes.after);
        if (changedFields.length === 0) {
            return 'No changes';
        }
        
        return changedFields.map(field => {
            const oldValue = changes.before[field] || 'null';
            const newValue = changes.after[field] || 'null';
            return `${field}: ${oldValue} → ${newValue}`;
        }).join(', ');
    }
};

// Initialize module when document is ready
document.addEventListener('DOMContentLoaded', () => logsModule.init());