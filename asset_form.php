<?php
require_once 'config.php';
require_once 'functions.php';
check_login();

$conn = db_connect();
$asset = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get dropdown options
$projects = $conn->query("SELECT * FROM projects ORDER BY name");
$locations = $conn->query("SELECT * FROM locations ORDER BY name");
$environments = $conn->query("SELECT * FROM environments ORDER BY name");

// Define asset types
$asset_types = [
    'Server' => 'Physical or Virtual Server',
    'Storage' => 'Storage System',
    'Network' => 'Network Equipment',
    'Security' => 'Security Appliance',
    'Application' => 'Application Service',
    'Database' => 'Database System',
    'Other' => 'Other Equipment'
];

// Define status options
$status_options = [
    'Active' => 'Currently in use',
    'Inactive' => 'Not in use',
    'Maintenance' => 'Under maintenance',
    'Retired' => 'No longer in service',
    'Reserved' => 'Reserved for future use'
];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $asset = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'project_id' => $_POST['project_id'],
        'location_id' => $_POST['location_id'],
        'environment_id' => $_POST['environment_id'],
        'asset_type' => $_POST['asset_type'],
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'url' => $_POST['url'],
        'ip_address' => $_POST['ip_address'],
        'protocol' => $_POST['protocol'],
        'port' => $_POST['port'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'alternate_ip' => $_POST['alternate_ip'],
        'alternate_port' => $_POST['alternate_port'],
        'specifications' => $_POST['specifications'],
        'remarks' => $_POST['remarks'],
        'status' => $_POST['status']
    ];

    if ($id) {
        // Update existing asset
        $sql = "UPDATE assets SET 
                project_id=?, location_id=?, environment_id=?, asset_type=?,
                name=?, description=?, url=?, ip_address=?, protocol=?,
                port=?, username=?, password=?, alternate_ip=?, alternate_port=?,
                specifications=?, remarks=?, status=?, updated_by=?
                WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiissssssisssisssii",
            $data['project_id'], $data['location_id'], $data['environment_id'],
            $data['asset_type'], $data['name'], $data['description'], $data['url'],
            $data['ip_address'], $data['protocol'], $data['port'], $data['username'],
            $data['password'], $data['alternate_ip'], $data['alternate_port'],
            $data['specifications'], $data['remarks'], $data['status'],
            $_SESSION['user_id'], $id
        );
    } else {
        // Create new asset
        $sql = "INSERT INTO assets (
                project_id, location_id, environment_id, asset_type,
                name, description, url, ip_address, protocol,
                port, username, password, alternate_ip, alternate_port,
                specifications, remarks, status, created_by, updated_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiissssssisssisssii",
            $data['project_id'], $data['location_id'], $data['environment_id'],
            $data['asset_type'], $data['name'], $data['description'], $data['url'],
            $data['ip_address'], $data['protocol'], $data['port'], $data['username'],
            $data['password'], $data['alternate_ip'], $data['alternate_port'],
            $data['specifications'], $data['remarks'], $data['status'],
            $_SESSION['user_id'], $_SESSION['user_id']
        );
    }

    if ($stmt->execute()) {
        // Log the change in asset_history
        $asset_id = $id ?: $conn->insert_id;
        $change_type = $id ? 'UPDATE' : 'CREATE';
        $changes = json_encode($data);
        
        $history_sql = "INSERT INTO asset_history (asset_id, changed_by, change_type, changes) 
                       VALUES (?, ?, ?, ?)";
        $hist_stmt = $conn->prepare($history_sql);
        $hist_stmt->bind_param("iiss", $asset_id, $_SESSION['user_id'], $change_type, $changes);
        $hist_stmt->execute();

        header("Location: index.php?success=1");
        exit();
    } else {
        $error = "Error saving asset: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Asset - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-<?php echo $id ? 'pencil' : 'plus-circle'; ?>"></i>
                    <?php echo $id ? 'Edit' : 'Add New'; ?> Asset
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Basic Information</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Asset Name*</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo $asset ? htmlspecialchars($asset['name']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Asset Type*</label>
                                <select name="asset_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($asset_types as $type => $description): ?>
                                        <option value="<?php echo $type; ?>" 
                                                <?php echo ($asset && $asset['asset_type'] == $type) ? 'selected' : ''; ?>
                                                title="<?php echo $description; ?>">
                                            <?php echo $type; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Environment -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Location & Environment</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Project*</label>
                                <select name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                    <?php while ($project = $projects->fetch_assoc()): ?>
                                        <option value="<?php echo $project['id']; ?>"
                                                <?php echo ($asset && $asset['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Location*</label>
                                <select name="location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    <?php while ($location = $locations->fetch_assoc()): ?>
                                        <option value="<?php echo $location['id']; ?>"
                                                <?php echo ($asset && $asset['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($location['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Environment*</label>
                                <select name="environment_id" class="form-select" required>
                                    <option value="">Select Environment</option>
                                    <?php while ($env = $environments->fetch_assoc()): ?>
                                        <option value="<?php echo $env['id']; ?>"
                                                <?php echo ($asset && $asset['environment_id'] == $env['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($env['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Network Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Network Information</h5>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Primary IP Address</label>
                                <input type="text" name="ip_address" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['ip_address']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Protocol</label>
                                <input type="text" name="protocol" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['protocol']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" name="port" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['port']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">URL</label>
                                <input type="text" name="url" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['url']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Alternate Network Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Alternate Network Information</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alternate IP</label>
                                <input type="text" name="alternate_ip" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['alternate_ip']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alternate Port</label>
                                <input type="number" name="alternate_port" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['alternate_port']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Access Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Access Information</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['username']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control"
                                       value="<?php echo $asset ? htmlspecialchars($asset['password']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Additional Information</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo $asset ? htmlspecialchars($asset['description']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Specifications</label>
                                <textarea name="specifications" class="form-control" rows="3"><?php echo $asset ? htmlspecialchars($asset['specifications']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"><?php echo $asset ? htmlspecialchars($asset['remarks']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status*</label>
                                <select name="status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <?php foreach ($status_options as $status => $description): ?>
                                        <option value="<?php echo $status; ?>"
                                                <?php echo ($asset && $asset['status'] == $status) ? 'selected' : ''; ?>
                                                title="<?php echo $description; ?>">
                                            <?php echo $status; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Asset
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>