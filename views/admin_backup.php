<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Database Backup";

// Handle backup creation
if ($_POST && isset($_POST['create_backup'])) {
    $backup_dir = '../storage/backups/';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    
    $filename = 'mealprep_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backup_dir . $filename;
    
    // Database credentials
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'mealprep';
    
    // Create backup using mysqldump
    $command = "mysqldump --host=$host --user=$username --password=$password $database > \"$filepath\"";
    $result = shell_exec($command);
    
    if (file_exists($filepath)) {
        $_SESSION['success'] = 'Database backup created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create backup!';
    }
    
    header('Location: admin_backup.php');
    exit();
}

// Get existing backup files
$backup_dir = '../storage/backups/';
$backup_files = [];
if (file_exists($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file)
            ];
        }
    }
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-database me-2"></i>Database Backup</h1>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-plus me-2"></i>Create New Backup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Create a backup of the current database. This will include all tables and data.</p>
                <form method="POST">
                    <button type="submit" name="create_backup" class="btn btn-success w-100">
                        <i class="fas fa-download me-2"></i>Create Backup Now
                    </button>
                </form>
                
                <hr>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Backups are stored in the <code>/storage/backups/</code> directory.
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Backup History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($backup_files)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Backups Found</h5>
                        <p class="text-muted">Create your first backup to see it listed here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backup_files as $file): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file-archive text-primary me-2"></i>
                                        <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo number_format($file['size'] / 1024, 2); ?> KB
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i:s', $file['date']); ?></td>
                                    <td>
                                        <a href="<?php echo $backup_dir . $file['name']; ?>" class="btn btn-sm btn-outline-primary" download>
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Backup Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>What's Included</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-users text-primary me-2"></i>All user accounts</li>
                            <li><i class="fas fa-utensils text-primary me-2"></i>Meal menu data</li>
                            <li><i class="fas fa-shopping-cart text-primary me-2"></i>Order history</li>
                            <li><i class="fas fa-list text-primary me-2"></i>Order items details</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-shield-alt text-warning me-2"></i>Best Practices</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-clock text-primary me-2"></i>Create regular backups</li>
                            <li><i class="fas fa-cloud text-primary me-2"></i>Store backups off-site</li>
                            <li><i class="fas fa-test text-primary me-2"></i>Test restore procedures</li>
                            <li><i class="fas fa-lock text-primary me-2"></i>Secure backup files</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
