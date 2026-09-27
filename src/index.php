<?php
// src/index.php

$title = "Dashboard";
ob_start();
?>

<div class="row mb-4">
    <!-- Quick Stats -->
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Servers</h6>
                <h2 class="mb-0" id="totalServers">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Expiring Licenses</h6>
                <h2 class="mb-0" id="expiringLicenses">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Overdue Backups</h6>
                <h2 class="mb-0" id="overdueBackups">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Active Services</h6>
                <h2 class="mb-0" id="activeServices">0</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Server Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Servers by Location</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="serversByLocation"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Environment Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Servers by Environment</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="serversByEnvironment"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Alerts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Alerts</h5>
            </div>
            <div class="card-body">
                <div id="alertsList" class="list-group">
                    <!-- Alerts will be populated here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expiring Licenses -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Upcoming License Expirations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Server</th>
                                <th>License</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody id="expiringLicensesList">
                            <!-- License data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Include dashboard module JavaScript -->
<script src="/assets/js/modules/dashboard.js"></script>

<?php
$content = ob_get_clean();
include 'includes/layout/header.php';
?>