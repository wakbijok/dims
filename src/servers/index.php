<?php
// src/servers/index.php

// Include the header
$title = "Server Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Servers</h5>
        <button type="button" class="btn btn-primary" onclick="serverModule.showAddModal()">
            Add Server
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="serverSearch" placeholder="Search servers...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="locationFilter">
                    <option value="">All Locations</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="environmentFilter">
                    <option value="">All Environments</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hostname</th>
                        <th>IP Address</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Environment</th>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include server module JavaScript -->
<script src="/assets/js/modules/server-simple.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>