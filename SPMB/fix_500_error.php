<?php
// Fix 500 Error Script untuk PPDB
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Fix 500 Error - PPDB</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: green; background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: red; background: #ffe8e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".warning { color: orange; background: #fff8e8; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: blue; background: #e8f0ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Fix 500 Error - PPDB Website</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>🔄 Proses Perbaikan Error 500</h2>";
    
    // 1. Hapus .htaccess yang bermasalah
    if (file_exists('.htaccess')) {
        if (unlink('.htaccess')) {
            echo "<div class='success'>✅ File .htaccess berhasil dihapus</div>";
        } else {
            echo "<div class='error'>❌ Gagal menghapus file .htaccess</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ File .htaccess tidak ditemukan</div>";
    }
    
    // 2. Buat .htaccess yang sederhana
    $htaccess_content = "# PPDB .htaccess Configuration\n# Simple configuration for XAMPP\n\n# Protect SQL files only\n<Files \"*.sql\">\n    Require all denied\n</Files>";
    
    if (file_put_contents('.htaccess', $htaccess_content)) {
        echo "<div class='success'>✅ File .htaccess baru berhasil dibuat</div>";
    } else {
        echo "<div class='error'>❌ Gagal membuat file .htaccess baru</div>";
    }
    
    // 3. Periksa permission folder
    $folders = ['uploads', 'config', 'admin'];
    foreach ($folders as $folder) {
        if (is_dir($folder)) {
            if (is_writable($folder)) {
                echo "<div class='success'>✅ Folder $folder: Writable</div>";
            } else {
                echo "<div class='warning'>⚠️ Folder $folder: Not writable</div>";
            }
        } else {
            echo "<div class='error'>❌ Folder $folder: Not found</div>";
        }
    }
    
    // 4. Test database connection
    if (file_exists('config/database.php')) {
        try {
            include 'config/database.php';
            $pdo = getDBConnection();
            echo "<div class='success'>✅ Database connection: OK</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Database config file not found</div>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 Langkah Selanjutnya:</h3>";
    echo "<ol>";
    echo "<li>Restart Apache di XAMPP Control Panel</li>";
    echo "<li>Buka <a href='index.php' class='btn'>Website PPDB</a></li>";
    echo "<li>Jika masih error, buka <a href='check_server.php' class='btn'>Server Check</a></li>";
    echo "</ol>";
    
} else {
    echo "<h2>📋 Langkah-langkah Perbaikan Error 500</h2>";
    
    echo "<div class='info'>";
    echo "<strong>Error 500 Internal Server Error</strong> biasanya disebabkan oleh:";
    echo "<ul>";
    echo "<li>File .htaccess yang bermasalah</li>";
    echo "<li>Konfigurasi Apache yang tidak kompatibel</li>";
    echo "<li>Permission folder yang salah</li>";
    echo "<li>Database connection yang gagal</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🔧 Perbaikan Otomatis:</h3>";
    echo "<form method='post'>";
    echo "<button type='submit' class='btn'>🚀 Jalankan Perbaikan Otomatis</button>";
    echo "</form>";
    
    echo "<h3>📝 Perbaikan Manual:</h3>";
    echo "<ol>";
    echo "<li><strong>Restart Apache:</strong> Buka XAMPP Control Panel → Stop Apache → Start Apache</li>";
    echo "<li><strong>Hapus .htaccess:</strong> Hapus file .htaccess dari folder SPMB</li>";
    echo "<li><strong>Test website:</strong> Buka http://localhost/SPMB/</li>";
    echo "<li><strong>Jika masih error:</strong> Buka http://localhost/SPMB/check_server.php</li>";
    echo "</ol>";
    
    echo "<h3>🔗 Link Test:</h3>";
    echo "<p><a href='test.php' class='btn'>🧪 Test PHP</a> ";
    echo "<a href='index.html' class='btn'>📄 Test HTML</a> ";
    echo "<a href='check_server.php' class='btn'>🔍 Server Check</a></p>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>
