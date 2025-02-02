<?php
// src/logs/index.php

$title = "System Logs";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">System Logs</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="resourceFilter" style="width: auto;">
                <option value="">All Resources</option>
                <option value="servers">Servers</option>
                <option value="hardware">Hardware</option>
                <option value="services">Services</option>
                <option value="licenses">Licenses</option>
                <option value="backups">Backups</option>
            </select>
            <select class="form-select form-select-sm" id="actionFilter" style="width: auto;">
                <option value="">All Actions</option>
                <option value="CREATE">Create</option>
                <option value="UPDATE">Update</option>
                <option value="DELETE">Delete</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>Resource</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <!-- Logs will be populated here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Log navigation" class="mt-3">
            <ul class="pagination justify-content-center" id="logsPagination">
            </ul>
        </nav>
    </div>
</div>

<!-- Include logs module JavaScript -->
<script src="/assets/js/modules/logs.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>