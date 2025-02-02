<?php
// src/services/index.php

$title = "Service Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Services</h5>
        <button type="button" class="btn btn-primary" onclick="serviceModule.showAddModal()">
            Add Service
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="serviceSearch" placeholder="Search services...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="serverFilter">
                    <option value="">All Servers</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>URL</th>
                        <th>Protocol</th>
                        <th>Port</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="serviceTableBody">
                    <!-- Service data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalTitle">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="serviceForm">
                <div class="modal-body">
                    <input type="hidden" id="serviceId" name="id">
                    
                    <div class="mb-3">
                        <label for="server_id" class="form-label required">Server</label>
                        <select class="form-select" id="server_id" name="server_id" required>
                            <option value="">Select Server</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="text" class="form-control" id="url" name="url">
                    </div>
                    
                    <div class="mb-3">
                        <label for="protocol" class="form-label">Protocol</label>
                        <select class="form-select" id="protocol" name="protocol">
                            <option value="">Select Protocol</option>
                            <option value="HTTP">HTTP</option>
                            <option value="HTTPS">HTTPS</option>
                            <option value="FTP">FTP</option>
                            <option value="SSH">SSH</option>
                            <option value="RDP">RDP</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="port" class="form-label">Port</label>
                        <input type="number" class="form-control" id="port" name="port" 
                               min="1" max="65535">
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
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

<!-- Include service module JavaScript -->
<script src="/assets/js/modules/service.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>