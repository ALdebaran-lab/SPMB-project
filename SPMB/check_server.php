<?php
// Server Check Script untuk PPDB
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Server Check - PPDB</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo ".warning { color: orange; }";
echo ".info { color: blue; }";
echo "table { border-collapse: collapse; width: 100%; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Server Check - PPDB Website</h1>";

// 1. PHP Version Check
echo "<h2>1. PHP Version</h2>";
$php_version = phpversion();
echo "<p class='success'>✅ PHP Version: " . $php_version . "</p>";

// 2. Server Software Check
echo "<h2>2. Server Software</h2>";
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "<p class='info'>ℹ️ Server: " . $server_software . "</p>";

// 3. File Permissions Check
echo "<h2>3. File Permissions</h2>";
$files_to_check = [
    'index.php' => 'Main website file',
    'pendaftaran.php' => 'Registration form',
    'admin/login.php' => 'Admin login',
    'config/database.php' => 'Database config',
    'uploads/' => 'Uploads directory'
];

echo "<table>";
echo "<tr><th>File/Directory</th><th>Status</th><th>Description</th></tr>";

foreach ($files_to_check as $file => $desc) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<tr><td>$file</td><td class='success'>✅ Readable</td><td>$desc</td></tr>";
        } else {
            echo "<tr><td>$file</td><td class='error'>❌ Not Readable</td><td>$desc</td></tr>";
        }
    } else {
        echo "<tr><td>$file</td><td class='error'>❌ Not Found</td><td>$desc</td></tr>";
    }
}
echo "</table>";

// 4. Database Connection Check
echo "<h2>4. Database Connection</h2>";
if (file_exists('config/database.php')) {
    try {
        include 'config/database.php';
        $pdo = getDBConnection();
        echo "<p class='success'>✅ Database connection successful</p>";
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pendaftar");
        $result = $stmt->fetch();
        echo "<p class='info'>ℹ️ Total pendaftar: " . $result['total'] . "</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='warning'>⚠️ Database config file not found</p>";
}

// 5. PHP Extensions Check
echo "<h2>5. PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'fileinfo'];
echo "<table>";
echo "<tr><th>Extension</th><th>Status</th></tr>";

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<tr><td>$ext</td><td class='success'>✅ Loaded</td></tr>";
    } else {
        echo "<tr><td>$ext</td><td class='error'>❌ Not Loaded</td></tr>";
    }
}
echo "</table>";

// 6. Directory Permissions
echo "<h2>6. Directory Permissions</h2>";
$dirs_to_check = ['uploads', 'config', 'admin'];
echo "<table>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th></tr>";

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✅ Yes' : '❌ No';
        echo "<tr><td>$dir</td><td class='success'>✅ Yes</td><td>$writable</td></tr>";
    } else {
        echo "<tr><td>$dir</td><td class='error'>❌ No</td><td>-</td></tr>";
    }
}
echo "</table>";

// 7. Error Reporting Status
echo "<h2>7. Error Reporting</h2>";
$display_errors = ini_get('display_errors') ? 'On' : 'Off';
$error_reporting = ini_get('error_reporting');
echo "<p class='info'>ℹ️ Display Errors: $display_errors</p>";
echo "<p class='info'>ℹ️ Error Reporting Level: $error_reporting</p>";

// 8. Recommendations
echo "<h2>8. Recommendations</h2>";
echo "<ul>";
echo "<li>Jika ada error 500, coba restart Apache di XAMPP</li>";
echo "<li>Pastikan database 'ppdb_db' sudah dibuat</li>";
echo "<li>Import file database.sql jika belum</li>";
echo "<li>Periksa error log Apache di C:\\xampp\\apache\\logs\\error.log</li>";
echo "</ul>";

// 9. Quick Links
echo "<h2>9. Quick Links</h2>";
echo "<p><a href='index.php'>🏠 Website PPDB</a></p>";
echo "<p><a href='pendaftaran.php'>📝 Form Pendaftaran</a></p>";
echo "<p><a href='admin/login.php'>🔐 Admin Login</a></p>";
echo "<p><a href='test.php'>🧪 Test PHP</a></p>";

echo "</body>";
echo "</html>";
?>
