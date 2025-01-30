<?php
define('BASE_PATH', __DIR__);
require_once 'config.php';
require_once 'functions.php';
check_login();

$conn = db_connect();
$asset = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get dropdown options
$projects = get_projects();
$locations = get_locations();
$environments = get_environments();

// If editing existing asset, get its data
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $asset = $stmt->get_result()->fetch_assoc();
    if (!$asset) {
        header("Location: index.php?error=" . urlencode("Asset not found"));
        exit();
    }
}

// Handle form submission
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
        'port' => $_POST['port'] ? $_POST['port'] : null,
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'alternate_ip' => $_POST['alternate_ip'],
        'alternate_port' => $_POST['alternate_port'] ? $_POST['alternate_port'] : null,
        'specifications' => $_POST['specifications'],
        'remarks' => $_POST['remarks'],
        'status' => $_POST['status']
    ];

    try {
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
            // Log the change
            $asset_id = $id ?: $conn->insert_id;
            $change_type = $id ? 'UPDATE' : 'CREATE';
            log_activity($_SESSION['user_id'], $change_type, json_encode($data));
            
            header("Location: index.php?success=1");
            exit();
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $error = "Error saving asset: " . $e->getMessage();
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
                    <?php echo display_error($error); ?>
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
                                    <?php foreach (ASSET_TYPES as $type => $description): ?>
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
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"
                                                <?php echo ($asset && $asset['project_id'] == $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Location*</label>
                                <select name="location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $l): ?>
                                        <option value="<?php echo $l['id']; ?>"
                                                <?php echo ($asset && $asset['location_id'] == $l['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($l['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Environment*</label>
                                <select name="environment_id" class="form-select" required>
                                    <option value="">Select Environment</option>
                                    <?php foreach ($environments as $e): ?>
                                        <option value="<?php echo $e['id']; ?>"
                                                <?php echo ($asset && $asset['environment_id'] == $e['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($e['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
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
                                    <?php foreach (STATUS_OPTIONS as $status => $description): ?>
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
                            <?php if ($id): ?>
                            <a href="asset_delete.php?id=<?php echo $id; ?>" 
                               class="btn btn-danger float-end"
                               onclick="return confirm('Are you sure you want to delete this asset?')">
                                <i class="bi bi-trash"></i> Delete Asset
                            </a>
                            <?php endif; ?>
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

        // IP Address validation
        document.querySelectorAll('input[name="ip_address"], input[name="alternate_ip"]').forEach(function(input) {
            input.addEventListener('input', function() {
                let value = this.value;
                if (value && !isValidIP(value)) {
                    this.setCustomValidity('Please enter a valid IP address');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        function isValidIP(ip) {
            // Simple IP validation - can be enhanced based on requirements
            const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipPattern.test(ip)) return false;
            const parts = ip.split('.');
            return parts.every(part => parseInt(part) >= 0 && parseInt(part) <= 255);
        }

        // Port validation
        document.querySelectorAll('input[name="port"], input[name="alternate_port"]').forEach(function(input) {
            input.addEventListener('input', function() {
                let value = parseInt(this.value);
                if (value && (value < 1 || value > 65535)) {
                    this.setCustomValidity('Port must be between 1 and 65535');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    </script>
</body>
</html>