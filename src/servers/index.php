<?php
// src/servers/index.php

// Include the header
$title = "Server Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Servers <span id="serverCount" class="text-muted">(0)</span></h5>
        <div>
            <div class="btn-group" id="bulkActions" style="display: none;">
                <button type="button" class="btn btn-warning btn-sm" onclick="serverModule.bulkDecommission()">
                    <i class="fas fa-ban"></i> Decommission Selected
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="serverModule.bulkActivate()">
                    <i class="fas fa-check"></i> Activate Selected
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="serverModule.bulkDelete()">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            <button type="button" class="btn btn-primary ms-2" onclick="serverModule.showAddModal()">
                Add Server
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="serverSearch" placeholder="Search servers...">
            </div>
            <div class="col-md-8 text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="serverModule.clearAllFilters()">
                    <i class="fas fa-times"></i> Clear All Filters
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">
                            <input type="checkbox" id="selectAll" onchange="serverModule.toggleSelectAll()">
                        </th>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Hostname</th>
                        <th>IP Address</th>
                        <th>
                            Type 
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border: none; padding: 2px 5px;">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <ul class="dropdown-menu" id="typeFilterMenu">
                                    <li><a class="dropdown-item" href="#" onclick="serverModule.filterByType('')">All Types</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="serverModule.filterByType('VM')">VM</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="serverModule.filterByType('Physical')">Physical</a></li>
                                </ul>
                            </div>
                        </th>
                        <th>
                            Location
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border: none; padding: 2px 5px;">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <ul class="dropdown-menu" id="locationFilterMenu">
                                    <li><a class="dropdown-item" href="#" onclick="serverModule.filterByLocation('')">All Locations</a></li>
                                    <!-- Locations will be populated here -->
                                </ul>
                            </div>
                        </th>
                        <th>
                            Environment
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border: none; padding: 2px 5px;">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <ul class="dropdown-menu" id="environmentFilterMenu">
                                    <li><a class="dropdown-item" href="#" onclick="serverModule.filterByEnvironment('')">All Environments</a></li>
                                    <!-- Environments will be populated here -->
                                </ul>
                            </div>
                        </th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="serverTableBody">
                    <!-- Server data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Server Modal -->
<div class="modal fade" id="serverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serverModalTitle">Add Server</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="serverForm">
                <div class="modal-body">
                    <input type="hidden" id="serverId" name="id">
                    
                    <div class="mb-3">
                        <label for="hostname" class="form-label">Hostname</label>
                        <input type="text" class="form-control" id="hostname" name="hostname">
                        <div class="form-text">Optional - can be same for multiple servers</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ip_address" class="form-label required">IP Address</label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" required
                               pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                        <div class="form-text">Required and must be unique</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="server_type" class="form-label required">Server Type</label>
                        <select class="form-select" id="server_type" name="server_type" required>
                            <option value="VM" selected>VM</option>
                            <option value="Physical">Physical</option>
                        </select>
                        <div class="form-text">Physical servers will also appear in Hardware page</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location_id" class="form-label required">Location</label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <!-- Locations will be populated here -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="environment_id" class="form-label required">Environment</label>
                        <select class="form-select" id="environment_id" name="environment_id" required>
                            <!-- Environments will be populated here -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <!-- Hardware Specifications -->
                    <div id="hardwareSpecs">
                        <hr>
                        <h6>Hardware Specifications <small class="text-muted">(Optional - mainly for Physical servers)</small></h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cpu_type" class="form-label">CPU Type</label>
                                    <input type="text" class="form-control" id="cpu_type" name="cpu_type" 
                                           placeholder="e.g., Intel Xeon E5-2680, AMD EPYC 7742">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cpu_cores" class="form-label">CPU Cores</label>
                                    <input type="number" class="form-control" id="cpu_cores" name="cpu_cores" 
                                           placeholder="e.g., 16" min="1">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="memory_gb" class="form-label">Memory (GB)</label>
                                    <input type="number" class="form-control" id="memory_gb" name="memory_gb" 
                                           placeholder="e.g., 64" min="1">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="storage_details" class="form-label">Storage Details</label>
                                    <textarea class="form-control" id="storage_details" name="storage_details" rows="3"
                                              placeholder="e.g., 2x 500GB SSD RAID1, 4x 2TB HDD RAID10"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="serial_number" class="form-label">Serial Number</label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                           placeholder="e.g., ABC123DEF456">
                                    <div class="form-text">Must be unique if provided</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Server Detail Modal -->
<div class="modal fade" id="serverDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serverDetailTitle">Server Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="serverDetailBody">
                <!-- Server details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editServerFromDetail">Edit Server</button>
            </div>
        </div>
    </div>
</div>

<!-- Include server module JavaScript -->
<script src="/assets/js/modules/server-simple.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>