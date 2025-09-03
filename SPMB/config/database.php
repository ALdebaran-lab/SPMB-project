<?php
/**
 * Konfigurasi Database PPDB
 * SMK Negeri 1 Jakarta
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ppdb_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection function
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        echo "Database connection successful!";
        return true;
    } catch(Exception $e) {
        echo "Database connection failed: " . $e->getMessage();
        return false;
    }
}

// Get database statistics
function getDBStats() {
    try {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Total pendaftar
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pendaftar");
        $stats['total_pendaftar'] = $stmt->fetchColumn();
        
        // Pendaftar hari ini
        $stmt = $pdo->query("SELECT COUNT(*) as hari_ini FROM pendaftar WHERE DATE(tanggal_daftar) = CURDATE()");
        $stats['hari_ini'] = $stmt->fetchColumn();
        
        // Pendaftar minggu ini
        $stmt = $pdo->query("SELECT COUNT(*) as minggu_ini FROM pendaftar WHERE YEARWEEK(tanggal_daftar) = YEARWEEK(CURDATE())");
        $stats['minggu_ini'] = $stmt->fetchColumn();
        
        // Pendaftar bulan ini
        $stmt = $pdo->query("SELECT COUNT(*) as bulan_ini FROM pendaftar WHERE MONTH(tanggal_daftar) = MONTH(CURDATE()) AND YEAR(tanggal_daftar) = YEAR(CURDATE())");
        $stats['bulan_ini'] = $stmt->fetchColumn();
        
        // Distribusi jurusan
        $stmt = $pdo->query("SELECT jurusan, COUNT(*) as jumlah FROM pendaftar GROUP BY jurusan ORDER BY jumlah DESC");
        $stats['jurusan'] = $stmt->fetchAll();
        
        // Distribusi jenis kelamin
        $stmt = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM pendaftar GROUP BY jenis_kelamin");
        $stats['jenis_kelamin'] = $stmt->fetchAll();
        
        return $stats;
        
    } catch(Exception $e) {
        return false;
    }
}

// Database backup function (basic)
function backupDatabase($backup_path = 'backups/') {
    try {
        // Create backup directory if not exists
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }
        
        $filename = $backup_path . 'ppdb_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // For XAMPP/WAMP, you can use mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $filename
        );
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return $filename;
        } else {
            return false;
        }
        
    } catch(Exception $e) {
        return false;
    }
}

// Check if database exists
function checkDatabaseExists() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        return $stmt->fetch() !== false;
    } catch(Exception $e) {
        return false;
    }
}

// Create database if not exists
function createDatabaseIfNotExists() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        return true;
    } catch(Exception $e) {
        return false;
    }
}

// Import SQL file
function importSQLFile($sql_file) {
    try {
        if (!file_exists($sql_file)) {
            return false;
        }
        
        $pdo = getDBConnection();
        $sql = file_get_contents($sql_file);
        
        // Split SQL by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        return true;
        
    } catch(Exception $e) {
        return false;
    }
}

// Database health check
function checkDatabaseHealth() {
    $health = [
        'connection' => false,
        'database_exists' => false,
        'tables_exist' => false,
        'data_count' => 0
    ];
    
    try {
        // Check connection
        $pdo = getDBConnection();
        $health['connection'] = true;
        
        // Check if database exists
        $health['database_exists'] = checkDatabaseExists();
        
        if ($health['database_exists']) {
            // Check if tables exist
            $stmt = $pdo->query("SHOW TABLES LIKE 'pendaftar'");
            $health['tables_exist'] = $stmt->rowCount() > 0;
            
            if ($health['tables_exist']) {
                // Count data
                $stmt = $pdo->query("SELECT COUNT(*) FROM pendaftar");
                $health['data_count'] = $stmt->fetchColumn();
            }
        }
        
    } catch(Exception $e) {
        // Health check failed
    }
    
    return $health;
}
?>
