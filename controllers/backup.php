<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle backup creation
if ($_POST && isset($_POST['create_backup'])) {
    $backup_dir = __DIR__ . '/../storage/backups/';
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
    
    header('Location: ../views/admin_backup.php');
    exit();
}

// Handle backup deletion
if ($_POST && isset($_POST['delete_backup'])) {
    $filename = $_POST['filename'];
    $filepath = __DIR__ . '/../storage/backups/' . $filename;
    
    if (file_exists($filepath) && unlink($filepath)) {
        $_SESSION['success'] = 'Backup file deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete backup file!';
    }
    
    header('Location: ../views/admin_backup.php');
    exit();
}

// Get existing backup files
$backup_dir = __DIR__ . '/../storage/backups/';
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
?>
