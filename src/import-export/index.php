<?php
// src/import-export/index.php

$title = "Import/Export Data";
ob_start();
?>

<div class="row">
    <!-- Export Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Export Data</h5>
            </div>
            <div class="card-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label class="form-label">Select Data to Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="servers" id="exportServers" checked>
                            <label class="form-check-label" for="exportServers">Servers</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="hardware" id="exportHardware">
                            <label class="form-check-label" for="exportHardware">Hardware Specifications</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="services" id="exportServices">
                            <label class="form-check-label" for="exportServices">Services</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="backups" id="exportBackups">
                            <label class="form-check-label" for="exportBackups">Backup Configurations</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="licenses" id="exportLicenses">
                            <label class="form-check-label" for="exportLicenses">Licenses</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" value="excel" id="formatExcel" checked>
                            <label class="form-check-label" for="formatExcel">Excel (XLSX)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" value="csv" id="formatCSV">
                            <label class="form-check-label" for="formatCSV">CSV</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Export Data</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Import Data</h5>
            </div>
            <div class="card-body">
                <form id="importForm">
                    <div class="mb-3">
                        <label class="form-label">Select Data Type to Import</label>
                        <select class="form-select" id="importType" required>
                            <option value="">Choose data type...</option>
                            <option value="servers">Servers</option>
                            <option value="hardware">Hardware Specifications</option>
                            <option value="services">Services</option>
                            <option value="backups">Backup Configurations</option>
                            <option value="licenses">Licenses</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="importFile" accept=".xlsx,.csv" required>
                        <div class="form-text">
                            Supported formats: Excel (XLSX), CSV
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="validateOnly">
                            <label class="form-check-label" for="validateOnly">
                                Validate only (no import)
                            </label>
                        </div>
                        <div class="form-text">
                            Check this to validate the file without importing the data
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Import Data</button>
                    
                    <!-- Download template links -->
                    <div class="mt-3">
                        <label class="form-label">Download Templates:</label>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action" onclick="importExportModule.downloadTemplate('servers')">
                                Servers Template
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" onclick="importExportModule.downloadTemplate('hardware')">
                                Hardware Template
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" onclick="importExportModule.downloadTemplate('services')">
                                Services Template
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" onclick="importExportModule.downloadTemplate('backups')">
                                Backup Configurations Template
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" onclick="importExportModule.downloadTemplate('licenses')">
                                Licenses Template
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Validation Results Modal -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validation Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="validationResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="proceedImport" style="display: none;">
                    Proceed with Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Processing</h5>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%"
                         id="importProgress">0%</div>
                </div>
                <div id="progressMessage" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include import/export module JavaScript -->
<script src="/assets/js/modules/import-export.js"></script>

<?php
$content = ob_get_clean();
include '../includes/layout/header.php';
?>