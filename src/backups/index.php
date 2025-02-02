<?php
// src/backups/index.php

$title = "Backup Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Backup Configurations</h5>
        <button type="button" class="btn btn-primary" onclick="backupModule.showAddModal()">
            Add Backup Config
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="backupSearch" placeholder="Search backups...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="serverFilter">
                    <option value="">All Servers</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="Full">Full Backup</option>
                    <option value="Incremental">Incremental</option>
                    <option value="Differential">Differential</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="overdueFilter">
                    <label class="form-check-label" for="overdueFilter">
                        Show Overdue Only
                    </label>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>Backup Type</th>
                        <th>Schedule</th>
                        <th>Retention Period</th>
                        <th>Last Backup</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="backupTableBody">
                    <!-- Backup data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Backup Modal -->
<div class="modal fade" id="backupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="backupModalTitle">Add Backup Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="backupForm">
                <div class="modal-body">
                    <input type="hidden" id="backupId" name="id">
                    
                    <div class="mb-3">
                        <label for="server_id" class="form-label required">Server</label>
                        <select class="form-select" id="server_id" name="server_id" required>
                            <option value="">Select Server</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="backup_type" class="form-label required">Backup Type</label>
                        <select class="form-select" id="backup_type" name="backup_type" required>
                            <option value="">Select Type</option>
                            <option value="Full">Full Backup</option>
                            <option value="Incremental">Incremental</option>
                            <option value="Differential">Differential</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="schedule" class="form-label required">Schedule</label>
                        <select class="form-select" id="schedule" name="schedule" required>
                            <option value="">Select Schedule</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="customScheduleDiv" style="display: none;">
                        <label for="custom_schedule" class="form-label">Custom Schedule</label>
                        <input type="text" class="form-control" id="custom_schedule" name="custom_schedule" 
                               placeholder="e.g., Every Monday at 2 AM">
                    </div>
                    
                    <div class="mb-3">
                        <label for="retention_period" class="form-label">Retention Period</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="retention_number" name="retention_number" min="1">
                            <select class="form-select" id="retention_unit" name="retention_unit">
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                                <option value="years">Years</option>
                            </select>
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

<!-- Include backup module JavaScript -->
<script src="/assets/js/modules/backup.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>