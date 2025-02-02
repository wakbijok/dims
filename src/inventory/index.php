<?php
// src/inventory/index.php

$title = "Infrastructure Inventory";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Infrastructure Inventory</h5>
            <div class="btn-group mt-2" role="group">
                <input type="radio" class="btn-check" name="viewType" id="vmView" value="vm" checked>
                <label class="btn btn-outline-primary" for="vmView">VM View</label>
                
                <input type="radio" class="btn-check" name="viewType" id="hardwareView" value="hardware">
                <label class="btn btn-outline-primary" for="hardwareView">Hardware View</label>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-secondary" onclick="inventoryModule.exportToExcel()">
                Export to Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="inventorySearch" placeholder="Search inventory...">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="locationFilter">
                    <option value="">All Locations</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="environmentFilter">
                    <option value="">All Environments</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="warning">Warning</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <!-- Hardware specific filters -->
            <div class="col-md-2 hardware-filter" style="display: none;">
                <select class="form-select" id="hardwareTypeFilter">
                    <option value="">All Types</option>
                    <option value="physical">Physical Server</option>
                    <option value="storage">Storage</option>
                    <option value="network">Network Device</option>
                </select>
            </div>
        </div>

        <div class="inventory-list">
            <!-- Inventory items will be populated here -->
        </div>
    </div>
</div>

<!-- Template for VM instance card -->
<template id="vmInstanceTemplate">
    <div class="card mb-3 vm-instance">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <span class="status-indicator"></span>
                <span class="hostname"></span>
            </h6>
            <div>
                <span class="badge location-badge"></span>
                <span class="badge environment-badge"></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Basic Info -->
                <div class="col-md-3">
                    <h6 class="text-muted">Basic Information</h6>
                    <p><strong>IP Address:</strong> <span class="ip-address"></span></p>
                    <p><strong>Description:</strong> <span class="description"></span></p>
                </div>
                
                <!-- Hardware -->
                <div class="col-md-3">
                    <h6 class="text-muted">Hardware Specifications</h6>
                    <p><strong>CPU:</strong> <span class="cpu"></span></p>
                    <p><strong>Memory:</strong> <span class="memory"></span></p>
                    <p><strong>Storage:</strong> <span class="storage"></span></p>
                    <p><strong>Serial Number:</strong> <span class="serial-number"></span></p>
                </div>
                
                <!-- Services -->
                <div class="col-md-3">
                    <h6 class="text-muted">Services</h6>
                    <div class="services-list small">
                        <!-- Services will be listed here -->
                    </div>
                </div>
                
                <!-- Status & Licenses -->
                <div class="col-md-3">
                    <h6 class="text-muted">Status & Licenses</h6>
                    <div class="licenses-list small">
                        <!-- Licenses will be listed here -->
                    </div>
                    <div class="backup-status mt-2">
                        <!-- Backup status will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Template for Hardware card -->
<template id="hardwareTemplate">
    <div class="card mb-3 hardware-instance">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <span class="status-indicator"></span>
                <span class="hardware-name"></span>
            </h6>
            <div>
                <span class="badge type-badge"></span>
                <span class="badge location-badge"></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Basic Info -->
                <div class="col-md-4">
                    <h6 class="text-muted">Hardware Information</h6>
                    <p><strong>Serial Number:</strong> <span class="serial-number"></span></p>
                    <p><strong>Model:</strong> <span class="model"></span></p>
                    <p><strong>Manufacturer:</strong> <span class="manufacturer"></span></p>
                    <p><strong>Purchase Date:</strong> <span class="purchase-date"></span></p>
                </div>
                
                <!-- Specifications -->
                <div class="col-md-4">
                    <h6 class="text-muted">Specifications</h6>
                    <p><strong>CPU:</strong> <span class="cpu"></span></p>
                    <p><strong>Memory:</strong> <span class="memory"></span></p>
                    <p><strong>Storage:</strong> <span class="storage"></span></p>
                    <p><strong>Network:</strong> <span class="network"></span></p>
                </div>
                
                <!-- Support & Maintenance -->
                <div class="col-md-4">
                    <h6 class="text-muted">Support & Maintenance</h6>
                    <div class="support-info small">
                        <!-- Support information will be listed here -->
                    </div>
                    <div class="maintenance-history mt-2">
                        <!-- Maintenance history will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Include inventory module JavaScript -->
<script src="/assets/js/modules/inventory.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>