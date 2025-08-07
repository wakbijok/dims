<?php
// src/hardware/index.php

$title = "Hardware Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Hardware Specifications</h5>
        <div>
            <small class="text-muted me-3">Physical servers only</small>
            <button type="button" class="btn btn-primary" onclick="hardwareModule.showAddModal()">
                Add Hardware Spec
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="hardwareSearch" placeholder="Search hardware...">
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
                        <th>CPU</th>
                        <th>Memory</th>
                        <th>Storage</th>
                        <th>Serial Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="hardwareTableBody">
                    <!-- Hardware data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Hardware Modal -->
<div class="modal fade" id="hardwareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hardwareModalTitle">Add Hardware Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="hardwareForm">
                <div class="modal-body">
                    <input type="hidden" id="hardwareId" name="id">
                    
                    <div class="mb-3">
                        <label for="server_id" class="form-label required">Physical Server</label>
                        <select class="form-select" id="server_id" name="server_id" required>
                            <option value="">Select Physical Server</option>
                        </select>
                        <div class="form-text">Only Physical servers are available for hardware specs</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cpu" class="form-label">CPU</label>
                        <input type="text" class="form-control" id="cpu" name="cpu" 
                               placeholder="e.g., Intel Xeon E5-2680 v4 2.40GHz">
                    </div>
                    
                    <div class="mb-3">
                        <label for="memory" class="form-label">Memory</label>
                        <input type="text" class="form-control" id="memory" name="memory" 
                               placeholder="e.g., 64GB DDR4">
                    </div>
                    
                    <div class="mb-3">
                        <label for="storage" class="form-label">Storage</label>
                        <input type="text" class="form-control" id="storage" name="storage" 
                               placeholder="e.g., 2x 500GB SSD RAID1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="serial_number" name="serial_number">
                        <small class="form-text text-muted">Must be unique if provided</small>
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

<!-- Include hardware module JavaScript -->
<script src="/assets/js/modules/hardware.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>