<?php
// src/licenses/index.php

$title = "License Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Licenses</h5>
        <button type="button" class="btn btn-primary" onclick="licenseModule.showAddModal()">
            Add License
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-warning" id="expiringLicensesAlert" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="expiringLicensesCount"></span> licenses are expiring within 30 days.
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="licenseSearch" placeholder="Search licenses...">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="serverFilter">
                    <option value="">All Servers</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="expiring">Expiring Soon</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="supportFilter">
                    <option value="">All Support Levels</option>
                    <option value="Premium">Premium</option>
                    <option value="Standard">Standard</option>
                    <option value="Basic">Basic</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>License Type</th>
                        <th>Expiry Date</th>
                        <th>Support Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="licenseTableBody">
                    <!-- License data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit License Modal -->
<div class="modal fade" id="licenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="licenseModalTitle">Add License</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="licenseForm">
                <div class="modal-body">
                    <input type="hidden" id="licenseId" name="id">
                    
                    <div class="mb-3">
                        <label for="server_id" class="form-label required">Server</label>
                        <select class="form-select" id="server_id" name="server_id" required>
                            <option value="">Select Server</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="license_type" class="form-label required">License Type</label>
                        <input type="text" class="form-control" id="license_type" name="license_type" required
                               placeholder="e.g., Windows Server 2019, Oracle DB, etc.">
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label required">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="support_level" class="form-label">Support Level</label>
                        <select class="form-select" id="support_level" name="support_level">
                            <option value="">Select Support Level</option>
                            <option value="Premium">Premium</option>
                            <option value="Standard">Standard</option>
                            <option value="Basic">Basic</option>
                        </select>
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

<!-- Include license module JavaScript -->
<script src="/assets/js/modules/license.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>