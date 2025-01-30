<?php
require_once 'config.php';
require_once 'functions.php';
check_login();

$conn = db_connect();

// Get filter parameters
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$environment_id = isset($_GET['environment_id']) ? (int)$_GET['environment_id'] : null;
$asset_type = isset($_GET['asset_type']) ? $_GET['asset_type'] : null;

// Build query with filters
$query = "SELECT a.*, 
          p.name as project_name, 
          l.name as location_name,
          e.name as environment_name,
          u.username as created_by_name
          FROM assets a 
          LEFT JOIN projects p ON a.project_id = p.id 
          LEFT JOIN locations l ON a.location_id = l.id 
          LEFT JOIN environments e ON a.environment_id = e.id
          LEFT JOIN users u ON a.created_by = u.id
          WHERE 1=1";

if ($project_id) $query .= " AND a.project_id = $project_id";
if ($location_id) $query .= " AND a.location_id = $location_id";
if ($environment_id) $query .= " AND a.environment_id = $environment_id";
if ($asset_type) $query .= " AND a.asset_type = '$asset_type'";

$result = $conn->query($query);

// Get filter options
$projects = $conn->query("SELECT * FROM projects ORDER BY name");
$locations = $conn->query("SELECT * FROM locations ORDER BY name");
$environments = $conn->query("SELECT * FROM environments ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Asset Inventory</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select id="projectFilter" class="form-select" onchange="applyFilters()">
                            <option value="">All Projects</option>
                            <?php while ($p = $projects->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($project_id == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="locationFilter" class="form-select" onchange="applyFilters()">
                            <option value="">All Locations</option>
                            <?php while ($l = $locations->fetch_assoc()): ?>
                                <option value="<?php echo $l['id']; ?>" <?php echo ($location_id == $l['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($l['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="environmentFilter" class="form-select" onchange="applyFilters()">
                            <option value="">All Environments</option>
                            <?php while ($e = $environments->fetch_assoc()): ?>
                                <option value="<?php echo $e['id']; ?>" <?php echo ($environment_id == $e['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success" onclick="window.location='asset_form.php'">
                            <i class="bi bi-plus-circle"></i> Add New Asset
                        </button>
                    </div>
                </div>

                <!-- Assets Table -->
                <table id="assetsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Project</th>
                            <th>Location</th>
                            <th>Environment</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['asset_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['environment_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo get_status_color($row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['updated_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="asset_view.php?id=<?php echo $row['id']; ?>" class="btn btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="asset_form.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="asset_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" title="Delete" 
                                       onclick="return confirm('Are you sure you want to delete this asset?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                pageLength: 25,
                order: [[7, 'desc']], // Sort by last updated by default
                responsive: true
            });
        });

        function applyFilters() {
            const project = document.getElementById('projectFilter').value;
            const location = document.getElementById('locationFilter').value;
            const environment = document.getElementById('environmentFilter').value;
            
            let url = 'index.php?';
            if (project) url += `project_id=${project}&`;
            if (location) url += `location_id=${location}&`;
            if (environment) url += `environment_id=${environment}&`;
            
            window.location.href = url;
        }
    </script>
</body>
</html>