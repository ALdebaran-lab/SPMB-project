<?php
// Test file untuk admin dashboard
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Test Admin Dashboard - PPDB</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: green; background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: red; background: #ffe8e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: blue; background: #e8f0ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🧪 Test Admin Dashboard - PPDB</h1>";

// Test 1: Database Connection
echo "<h2>1. Test Database Connection</h2>";
try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Test 2: Check if table exists
    echo "<h2>2. Test Table Pendaftar</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'pendaftar'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Table 'pendaftar' exists</div>";
        
        // Test 3: Count records
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM pendaftar");
        $total = $count_stmt->fetchColumn();
        echo "<div class='info'>ℹ️ Total records in pendaftar: $total</div>";
        
        // Test 4: Test LIMIT query
        echo "<h2>3. Test LIMIT Query</h2>";
        try {
            $stmt = $pdo->prepare("SELECT * FROM pendaftar ORDER BY tanggal_daftar DESC LIMIT ?, ?");
            $stmt->execute([0, 5]);
            $result = $stmt->fetchAll();
            echo "<div class='success'>✅ LIMIT query successful. Found " . count($result) . " records</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ LIMIT query failed: " . $e->getMessage() . "</div>";
        }
        
        // Test 5: Test WHERE clause
        echo "<h2>4. Test WHERE Clause</h2>";
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftar WHERE jurusan = ?");
            $stmt->execute(['RPL']);
            $count = $stmt->fetchColumn();
            echo "<div class='success'>✅ WHERE clause query successful. RPL count: $count</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ WHERE clause query failed: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Table 'pendaftar' not found</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

// Test 6: Session
echo "<h2>5. Test Session</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ Session is active</div>";
} else {
    echo "<div class='error'>❌ Session is not active</div>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='login.php' class='btn'>🔐 Admin Login</a> ";
echo "<a href='dashboard.php' class='btn'>📊 Dashboard</a> ";
echo "<a href='../index.php' class='btn'>🏠 Website Utama</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
