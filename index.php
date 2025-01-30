<?php
define('BASE_PATH', __DIR__);
require_once 'config.php';
require_once 'functions.php';
check_login();

$conn = db_connect();

// Get filter parameters
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
$environment_id = isset($_GET['environment_id']) ? (int)$_GET['environment_id'] : null;

// Build query with filters
$query = "SELECT a.*, 
          p.name as project_name, 
          l.name as location_name,
          e.name as environment_name
          FROM assets a 
          LEFT JOIN projects p ON a.project_id = p.id 
          LEFT JOIN locations l ON a.location_id = l.id 
          LEFT JOIN environments e ON a.environment_id = e.id
          WHERE 1=1";

if ($project_id) $query .= " AND a.project_id = $project_id";
if ($location_id) $query .= " AND a.location_id = $location_id";
if ($environment_id) $query .= " AND a.environment_id = $environment_id";

$query .= " ORDER BY a.updated_at DESC";

$result = $conn->query($query);

// Get filter options
$projects = get_projects();
$locations = get_locations();
$environments = get_environments();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <?php 
        if (isset($_GET['success'])) {
            echo display_success("Operation completed successfully!");
        }
        if (isset($_GET['error'])) {
            echo display_error($_GET['error']);
        }
        ?>

        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <select name="project_id" class="form-select">
                                    <option value="">All Projects</option>
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" 
                                                <?php echo ($project_id == $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="location_id" class="form-select">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $l): ?>
                                        <option value="<?php echo $l['id']; ?>"
                                                <?php echo ($location_id == $l['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($l['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="environment_id" class="form-select">
                                    <option value="">All Environments</option>
                                    <?php foreach ($environments as $e): ?>
                                        <option value="<?php echo $e['id']; ?>"
                                                <?php echo ($environment_id == $e['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($e['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="index.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Asset Inventory</h5>
                        <a href="asset_form.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Asset
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="assetsTable" class="table table-striped table-hover">
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
                                        <td><?php echo format_datetime($row['updated_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="asset_form.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="asset_delete.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this asset?')"
                                                   title="Delete">
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
    </script>
</body>
</html>