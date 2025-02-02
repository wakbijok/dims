<?php
// src/locations/index.php

$title = "Location Management";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Locations</h5>
        <button type="button" class="btn btn-primary" onclick="locationModule.showAddModal()">
            Add Location
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="locationTableBody">
                    <!-- Location data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalTitle">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="locationForm">
                <div class="modal-body">
                    <input type="hidden" id="locationId" name="id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label required">Location Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
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

<!-- Include location module JavaScript -->
<script src="/assets/js/modules/location.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>