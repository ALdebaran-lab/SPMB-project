<?php
/**
 * Script Update Database untuk Fitur Seleksi dan Notifikasi
 * 
 * Script ini akan menambahkan kolom dan tabel baru yang diperlukan
 * untuk fitur seleksi pendaftar dan notifikasi otomatis.
 * 
 * PERINGATAN: Backup database Anda terlebih dahulu sebelum menjalankan script ini!
 */

// Database connection
$host = 'localhost';
$dbname = 'ppdb_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Koneksi database berhasil\n";
} catch(PDOException $e) {
    die("✗ Koneksi database gagal: " . $e->getMessage() . "\n");
}

// Array of SQL commands to execute
$sql_commands = [
    // Add new columns to pendaftar table
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS nilai_tk DECIMAL(5,2) DEFAULT NULL COMMENT 'Nilai ijazah TK (0-100)'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS usia INT DEFAULT NULL COMMENT 'Usia pendaftar dalam tahun'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS jarak_rumah DECIMAL(5,2) DEFAULT NULL COMMENT 'Jarak rumah ke sekolah dalam km'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS nilai_akhir DECIMAL(5,2) DEFAULT NULL COMMENT 'Nilai akhir dari kalkulasi bobot point'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS akta_kelahiran VARCHAR(255) COMMENT 'Akta Kelahiran yang dilegalisir'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS kartu_keluarga VARCHAR(255) COMMENT 'Kartu Keluarga'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS surat_domisili VARCHAR(255) COMMENT 'Surat Keterangan Domisili RT/RW'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS status_seleksi ENUM('Menunggu', 'Diterima', 'Ditolak') DEFAULT 'Menunggu'",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS tanggal_seleksi TIMESTAMP NULL",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS catatan_seleksi TEXT",
    "ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS admin_seleksi VARCHAR(50)",
    
    // Add new indexes
    "CREATE INDEX IF NOT EXISTS idx_status_seleksi ON pendaftar(status_seleksi)",
    "CREATE INDEX IF NOT EXISTS idx_nilai_tk ON pendaftar(nilai_tk)",
    
    // Create notifikasi_seleksi table
    "CREATE TABLE IF NOT EXISTS notifikasi_seleksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pendaftar_id INT NOT NULL,
        jenis_notifikasi ENUM('SMS', 'Email') NOT NULL,
        status_pengiriman ENUM('Pending', 'Terkirim', 'Gagal') DEFAULT 'Pending',
        pesan TEXT NOT NULL,
        tanggal_kirim TIMESTAMP NULL,
        tanggal_terkirim TIMESTAMP NULL,
        error_message TEXT,
        FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id) ON DELETE CASCADE,
        INDEX idx_pendaftar_id (pendaftar_id),
        INDEX idx_status_pengiriman (status_pengiriman)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Update existing records to have default status
    "UPDATE pendaftar SET status_seleksi = 'Menunggu' WHERE status_seleksi IS NULL",
    
    // Create or update view for statistics
    "CREATE OR REPLACE VIEW statistik_pendaftar AS
    SELECT 
        COUNT(*) as total_pendaftar,
        COUNT(CASE WHEN DATE(tanggal_daftar) = CURDATE() THEN 1 END) as hari_ini,
        COUNT(CASE WHEN YEARWEEK(tanggal_daftar) = YEARWEEK(CURDATE()) THEN 1 END) as minggu_ini,
        COUNT(CASE WHEN MONTH(tanggal_daftar) = MONTH(CURDATE()) AND YEAR(tanggal_daftar) = YEAR(CURDATE()) THEN 1 END) as bulan_ini,
        COUNT(CASE WHEN jenis_kelamin = 'L' THEN 1 END) as laki_laki,
        COUNT(CASE WHEN jenis_kelamin = 'P' THEN 1 END) as perempuan,
        COUNT(CASE WHEN jurusan = 'Kelas 1' THEN 1 END) as kelas_1,
        COUNT(CASE WHEN jurusan = 'Kelas 2' THEN 1 END) as kelas_2,
        COUNT(CASE WHEN jurusan = 'Kelas 3' THEN 1 END) as kelas_3,
        COUNT(CASE WHEN jurusan = 'Kelas 4' THEN 1 END) as kelas_4,
        COUNT(CASE WHEN jurusan = 'Kelas 5' THEN 1 END) as kelas_5,
        COUNT(CASE WHEN jurusan = 'Kelas 6' THEN 1 END) as kelas_6,
        COUNT(CASE WHEN status_seleksi = 'Menunggu' THEN 1 END) as menunggu_seleksi,
        COUNT(CASE WHEN status_seleksi = 'Diterima' THEN 1 END) as diterima,
        COUNT(CASE WHEN status_seleksi = 'Ditolak' THEN 1 END) as ditolak
    FROM pendaftar"
];

// Execute SQL commands
echo "\nMemulai update database...\n";
echo "================================\n";

$success_count = 0;
$error_count = 0;

foreach ($sql_commands as $index => $sql) {
    try {
        $pdo->exec($sql);
        echo "✓ Command " . ($index + 1) . " berhasil dieksekusi\n";
        $success_count++;
    } catch (PDOException $e) {
        echo "✗ Command " . ($index + 1) . " gagal: " . $e->getMessage() . "\n";
        $error_count++;
    }
}

echo "================================\n";
echo "Update database selesai!\n";
echo "Berhasil: {$success_count}\n";
echo "Gagal: {$error_count}\n";

// Verify the changes
echo "\nVerifikasi perubahan...\n";
echo "================================\n";

try {
    // Check if new columns exist
    $stmt = $pdo->query("DESCRIBE pendaftar");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['nilai_tk', 'status_seleksi', 'tanggal_seleksi', 'catatan_seleksi', 'admin_seleksi'];
    $missing_columns = [];
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (empty($missing_columns)) {
        echo "✓ Semua kolom baru berhasil ditambahkan ke tabel pendaftar\n";
    } else {
        echo "✗ Kolom yang hilang: " . implode(', ', $missing_columns) . "\n";
    }
    
    // Check if notifikasi_seleksi table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifikasi_seleksi'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabel notifikasi_seleksi berhasil dibuat\n";
    } else {
        echo "✗ Tabel notifikasi_seleksi gagal dibuat\n";
    }
    
    // Check if view exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'statistik_pendaftar'");
    if ($stmt->rowCount() > 0) {
        echo "✓ View statistik_pendaftar berhasil dibuat/diupdate\n";
    } else {
        echo "✗ View statistik_pendaftar gagal dibuat/diupdate\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error saat verifikasi: " . $e->getMessage() . "\n";
}

// Show current database structure
echo "\nStruktur database saat ini:\n";
echo "================================\n";

try {
    $stmt = $pdo->query("SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE TABLE_SCHEMA = '$dbname' ORDER BY TABLE_NAME");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tables as $table) {
        echo "Tabel: " . $table['TABLE_NAME'] . " (" . $table['TABLE_ROWS'] . " rows)\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error saat menampilkan struktur tabel: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "Update database selesai!\n";
echo "Sekarang Anda dapat menggunakan fitur seleksi dan notifikasi.\n";
echo "\nLangkah selanjutnya:\n";
echo "1. Login ke admin dashboard\n";
echo "2. Akses halaman 'Seleksi Pendaftar'\n";
echo "3. Mulai melakukan seleksi pendaftar\n";
echo "4. Setup notifikasi SMS/Email sesuai kebutuhan\n";
echo "5. Buat cron job untuk notifikasi otomatis\n";
echo "\nUntuk informasi lebih lanjut, lihat README_FITUR_SELEKSI.md\n";
?>

