<?php
// Fix Dashboard Issues Script
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Fix Dashboard Issues - PPDB</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: green; background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: red; background: #ffe8e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".warning { color: orange; background: #fff8e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: blue; background: #e8f0ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Fix Dashboard Issues - PPDB Admin</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>🔄 Proses Perbaikan Dashboard</h2>";
    
    // 1. Test database connection
    echo "<h3>1. Testing Database Connection</h3>";
    try {
        require_once __DIR__ . '/../config/database.php';
        $pdo = getDBConnection();
        echo "<div class='success'>✅ Database connection successful</div>";
        
        // 2. Test table existence
        echo "<h3>2. Testing Table Structure</h3>";
        $stmt = $pdo->query("SHOW TABLES LIKE 'pendaftar'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Table 'pendaftar' exists</div>";
            
            // 3. Test basic queries
            echo "<h3>3. Testing Basic Queries</h3>";
            
            // Test count query
            try {
                $count_stmt = $pdo->query("SELECT COUNT(*) FROM pendaftar");
                $total = $count_stmt->fetchColumn();
                echo "<div class='success'>✅ Count query successful. Total: $total</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Count query failed: " . $e->getMessage() . "</div>";
            }
            
            // Test LIMIT query
            try {
                $stmt = $pdo->prepare("SELECT * FROM pendaftar ORDER BY tanggal_daftar DESC LIMIT ?, ?");
                $stmt->execute([0, 5]);
                $result = $stmt->fetchAll();
                echo "<div class='success'>✅ LIMIT query successful. Found " . count($result) . " records</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ LIMIT query failed: " . $e->getMessage() . "</div>";
            }
            
            // Test WHERE clause
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftar WHERE jurusan = ?");
                $stmt->execute(['RPL']);
                $count = $stmt->fetchColumn();
                echo "<div class='success'>✅ WHERE clause query successful. RPL count: $count</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ WHERE clause query failed: " . $e->getMessage() . "</div>";
            }
            
            // Test DISTINCT query
            try {
                $stmt = $pdo->query("SELECT DISTINCT jurusan FROM pendaftar WHERE jurusan IS NOT NULL AND jurusan != '' ORDER BY jurusan");
                $jurusan_list = $stmt->fetchAll();
                echo "<div class='success'>✅ DISTINCT query successful. Found " . count($jurusan_list) . " unique jurusan</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ DISTINCT query failed: " . $e->getMessage() . "</div>";
            }
            
        } else {
            echo "<div class='error'>❌ Table 'pendaftar' not found</div>";
            echo "<div class='warning'>⚠️ Please import database.sql first</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    }
    
    // 4. Test session
    echo "<h3>4. Testing Session</h3>";
    session_start();
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<div class='success'>✅ Session is active</div>";
    } else {
        echo "<div class='error'>❌ Session is not active</div>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>If all tests passed, try accessing <a href='dashboard.php' class='btn'>Dashboard</a></li>";
    echo "<li>If there are errors, check the database configuration</li>";
    echo "<li>Make sure database 'ppdb_db' exists and table 'pendaftar' is imported</li>";
    echo "</ol>";
    
} else {
    echo "<h2>📋 Dashboard Issues Diagnosis</h2>";
    
    echo "<div class='info'>";
    echo "<strong>Common Dashboard Issues:</strong>";
    echo "<ul>";
    echo "<li>SQL syntax errors in LIMIT/OFFSET queries</li>";
    echo "<li>Database connection problems</li>";
    echo "<li>Missing or corrupted database tables</li>";
    echo "<li>Session management issues</li>";
    echo "<li>Parameter binding problems</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🔧 Automatic Fix:</h3>";
    echo "<form method='post'>";
    echo "<button type='submit' class='btn'>🚀 Run Dashboard Diagnostics</button>";
    echo "</form>";
    
    echo "<h3>📝 Manual Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Check Database:</strong> Ensure 'ppdb_db' exists in phpMyAdmin</li>";
    echo "<li><strong>Import Tables:</strong> Import database.sql if tables are missing</li>";
    echo "<li><strong>Test Connection:</strong> Use <a href='test_dashboard.php' class='btn'>Test Dashboard</a></li>";
    echo "<li><strong>Check Error Logs:</strong> Look at Apache error logs</li>";
    echo "</ol>";
    
    echo "<h3>🔗 Quick Links:</h3>";
    echo "<p><a href='test_dashboard.php' class='btn'>🧪 Test Dashboard</a> ";
    echo "<a href='login.php' class='btn'>🔐 Admin Login</a> ";
    echo "<a href='dashboard.php' class='btn'>📊 Dashboard</a> ";
    echo "<a href='../index.php' class='btn'>🏠 Website Utama</a></p>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>
