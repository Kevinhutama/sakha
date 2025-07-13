<?php
require_once 'includes/config.php';
requireLogin();

$page_title = "Upload Test - MaterialM Admin";

// Test directory paths
$testResults = [];

// Test 1: Check if upload directory exists and is writable
$uploadDir = '../store/images/products';
$testResults['upload_dir_exists'] = is_dir($uploadDir);
$testResults['upload_dir_writable'] = is_writable($uploadDir);
$testResults['upload_dir_path'] = realpath($uploadDir);

// Test 2: Check temp directory
$tempDir = '../store/temp_images';
$testResults['temp_dir_exists'] = is_dir($tempDir);
$testResults['temp_dir_writable'] = is_writable($tempDir);
$testResults['temp_dir_path'] = realpath($tempDir);

// Test 3: Test write permissions
$testFile = $uploadDir . '/test_write_' . time() . '.tmp';
$testResults['can_write_test_file'] = file_put_contents($testFile, 'test') !== false;
if ($testResults['can_write_test_file'] && file_exists($testFile)) {
    unlink($testFile);
}

// Test 4: PHP upload settings
$testResults['php_upload_max_filesize'] = ini_get('upload_max_filesize');
$testResults['php_post_max_size'] = ini_get('post_max_size');
$testResults['php_max_file_uploads'] = ini_get('max_file_uploads');
$testResults['php_file_uploads'] = ini_get('file_uploads') ? 'On' : 'Off';

// Test 5: System info
$testResults['php_user'] = get_current_user();
$testResults['php_version'] = PHP_VERSION;
$testResults['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Upload System Diagnostic</h5>
                </div>
                <div class="card-body">
                    
                    <h6>Directory Tests</h6>
                    <table class="table table-striped">
                        <tr>
                            <td>Upload Directory Exists</td>
                            <td><?php echo $testResults['upload_dir_exists'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'; ?></td>
                            <td><?php echo $testResults['upload_dir_path'] ?: 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Upload Directory Writable</td>
                            <td><?php echo $testResults['upload_dir_writable'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'; ?></td>
                            <td>Must be writable by web server</td>
                        </tr>
                        <tr>
                            <td>Temp Directory Exists</td>
                            <td><?php echo $testResults['temp_dir_exists'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'; ?></td>
                            <td><?php echo $testResults['temp_dir_path'] ?: 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Temp Directory Writable</td>
                            <td><?php echo $testResults['temp_dir_writable'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'; ?></td>
                            <td>Must be writable by web server</td>
                        </tr>
                        <tr>
                            <td>Can Write Test File</td>
                            <td><?php echo $testResults['can_write_test_file'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'; ?></td>
                            <td>Tests actual write permissions</td>
                        </tr>
                    </table>
                    
                    <h6>PHP Upload Settings</h6>
                    <table class="table table-striped">
                        <tr>
                            <td>File Uploads Enabled</td>
                            <td><?php echo $testResults['php_file_uploads'] === 'On' ? '<span class="text-success">✓ On</span>' : '<span class="text-danger">✗ Off</span>'; ?></td>
                            <td>file_uploads directive</td>
                        </tr>
                        <tr>
                            <td>Max File Size</td>
                            <td><?php echo $testResults['php_upload_max_filesize']; ?></td>
                            <td>upload_max_filesize directive</td>
                        </tr>
                        <tr>
                            <td>Max POST Size</td>
                            <td><?php echo $testResults['php_post_max_size']; ?></td>
                            <td>post_max_size directive</td>
                        </tr>
                        <tr>
                            <td>Max File Uploads</td>
                            <td><?php echo $testResults['php_max_file_uploads']; ?></td>
                            <td>max_file_uploads directive</td>
                        </tr>
                    </table>
                    
                    <h6>System Information</h6>
                    <table class="table table-striped">
                        <tr>
                            <td>PHP Version</td>
                            <td><?php echo $testResults['php_version']; ?></td>
                            <td>Current PHP version</td>
                        </tr>
                        <tr>
                            <td>PHP User</td>
                            <td><?php echo $testResults['php_user']; ?></td>
                            <td>User running PHP process</td>
                        </tr>
                        <tr>
                            <td>Server Software</td>
                            <td><?php echo $testResults['server_software']; ?></td>
                            <td>Web server information</td>
                        </tr>
                    </table>
                    
                    <div class="mt-4">
                        <h6>Quick Fix Commands</h6>
                        <p>If directories are not writable, run these commands in terminal:</p>
                        <code>
                            chmod 777 store/images/products/<br>
                            chmod 777 store/temp_images/
                        </code>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/auth_layout.php';
?> 