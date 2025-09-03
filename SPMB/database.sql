-- Database PPDB (Penerimaan Peserta Didik Baru)
-- SMK Negeri 1 Jakarta

-- Buat database
CREATE DATABASE IF NOT EXISTS ppdb_db;
USE ppdb_db;

-- Buat tabel pendaftar
CREATE TABLE IF NOT EXISTS pendaftar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    nisn VARCHAR(10) NOT NULL UNIQUE,
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    alamat TEXT NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    asal_sekolah VARCHAR(100) NOT NULL,
    jurusan ENUM('Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6') NOT NULL,
    foto VARCHAR(255),
    dokumen VARCHAR(255),
    akta_kelahiran VARCHAR(255) COMMENT 'Akta Kelahiran yang dilegalisir',
    kartu_keluarga VARCHAR(255) COMMENT 'Kartu Keluarga',
    surat_domisili VARCHAR(255) COMMENT 'Surat Keterangan Domisili RT/RW',
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usia INT DEFAULT NULL COMMENT 'Usia pendaftar dalam tahun',
    jarak_rumah DECIMAL(5,2) DEFAULT NULL COMMENT 'Jarak rumah ke sekolah dalam km',
    nilai_tk DECIMAL(5,2) DEFAULT NULL COMMENT 'Nilai ijazah TK (0-100)',
    nilai_akhir DECIMAL(5,2) DEFAULT NULL COMMENT 'Nilai akhir dari kalkulasi bobot point',
    status_seleksi ENUM('Menunggu', 'Diterima', 'Ditolak') DEFAULT 'Menunggu',
    tanggal_seleksi TIMESTAMP NULL,
    catatan_seleksi TEXT,
    admin_seleksi VARCHAR(50),
    INDEX idx_nisn (nisn),
    INDEX idx_email (email),
    INDEX idx_jurusan (jurusan),
    INDEX idx_tanggal_daftar (tanggal_daftar),
    INDEX idx_status_seleksi (status_seleksi),
    INDEX idx_nilai_tk (nilai_tk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel admin (untuk keamanan yang lebih baik)
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel notifikasi untuk pengiriman hasil seleksi
CREATE TABLE IF NOT EXISTS notifikasi_seleksi (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123)
-- Dalam produksi, gunakan password_hash() untuk mengamankan password
INSERT INTO admin (username, password, nama_lengkap, email) VALUES 
('admin', 'admin123', 'Administrator', 'admin@smkn1jakarta.sch.id');

-- Buat direktori uploads jika belum ada
-- Pastikan direktori uploads memiliki permission yang tepat (755)

-- Contoh data dummy untuk testing (opsional)
INSERT INTO pendaftar (nama_lengkap, nisn, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, asal_sekolah, jurusan) VALUES
('Ahmad Fadillah', '1234567890', 'Jakarta', '2018-05-15', 'L', 'Jl. Majasetra No. 123, Jakarta Selatan', '081234567890', 'ahmad@email.com', 'TK Ceria Jakarta', 'Kelas 1'),
('Siti Nurhaliza', '0987654321', 'Jakarta', '2017-08-20', 'P', 'Jl. Majasetra No. 45, Jakarta Selatan', '081234567891', 'siti@email.com', 'SDN Majasetra 02', 'Kelas 2'),
('Budi Santoso', '1122334455', 'Jakarta', '2016-03-10', 'L', 'Jl. Majasetra No. 67, Jakarta Selatan', '081234567892', 'budi@email.com', 'SDN Majasetra 03', 'Kelas 3'),
('Dewi Sartika', '5544332211', 'Jakarta', '2015-12-25', 'P', 'Jl. Majasetra No. 89, Jakarta Selatan', '081234567893', 'dewi@email.com', 'SDN Majasetra 04', 'Kelas 4'),
('Rudi Hermawan', '6677889900', 'Jakarta', '2014-07-08', 'L', 'Jl. Majasetra No. 12, Jakarta Selatan', '081234567894', 'rudi@email.com', 'SDN Majasetra 05', 'Kelas 5');

-- Buat view untuk statistik
CREATE OR REPLACE VIEW statistik_pendaftar AS
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
FROM pendaftar;

-- Buat index untuk optimasi query
CREATE INDEX idx_pendaftar_kelas_tanggal ON pendaftar(jurusan, tanggal_daftar);
CREATE INDEX idx_pendaftar_nama_search ON pendaftar(nama_lengkap, nisn, email, asal_sekolah);

-- Buat trigger untuk log perubahan data
CREATE TABLE IF NOT EXISTS log_pendaftar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_data JSON,
    new_data JSON,
    admin_username VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat trigger untuk INSERT
DELIMITER //
CREATE TRIGGER after_pendaftar_insert
AFTER INSERT ON pendaftar
FOR EACH ROW
BEGIN
    INSERT INTO log_pendaftar (pendaftar_id, action, new_data, admin_username)
    VALUES (NEW.id, 'INSERT', JSON_OBJECT(
        'nama_lengkap', NEW.nama_lengkap,
        'nisn', NEW.nisn,
        'jurusan', NEW.jurusan,
        'tanggal_daftar', NEW.tanggal_daftar
    ), 'system');
END//

-- Buat trigger untuk UPDATE
CREATE TRIGGER after_pendaftar_update
AFTER UPDATE ON pendaftar
FOR EACH ROW
BEGIN
    INSERT INTO log_pendaftar (pendaftar_id, action, old_data, new_data, admin_username)
    VALUES (NEW.id, 'UPDATE', JSON_OBJECT(
        'nama_lengkap', OLD.nama_lengkap,
        'nisn', OLD.nisn,
        'jurusan', OLD.jurusan,
        'status_seleksi', OLD.status_seleksi,
        'nilai_tk', OLD.nilai_tk
    ), JSON_OBJECT(
        'nama_lengkap', NEW.nama_lengkap,
        'nisn', NEW.nisn,
        'jurusan', NEW.jurusan,
        'status_seleksi', NEW.status_seleksi,
        'nilai_tk', NEW.nilai_tk
    ), COALESCE(NEW.admin_seleksi, 'system'));
    
    -- Auto-accept jika nilai TK >= 70
    IF NEW.nilai_tk IS NOT NULL AND NEW.nilai_tk >= 70 AND NEW.status_seleksi = 'Menunggu' THEN
        UPDATE pendaftar SET 
            status_seleksi = 'Diterima',
            tanggal_seleksi = NOW(),
            catatan_seleksi = 'Otomatis diterima berdasarkan nilai TK yang memenuhi syarat (≥70)',
            admin_seleksi = COALESCE(NEW.admin_seleksi, 'system')
        WHERE id = NEW.id;
    END IF;
END//

-- Buat trigger untuk DELETE
CREATE TRIGGER before_pendaftar_delete
BEFORE DELETE ON pendaftar
FOR EACH ROW
BEGIN
    INSERT INTO log_pendaftar (pendaftar_id, action, old_data, admin_username)
    VALUES (OLD.id, 'DELETE', JSON_OBJECT(
        'nama_lengkap', OLD.nama_lengkap,
        'nisn', OLD.nisn,
        'jurusan', OLD.jurusan,
        'tanggal_daftar', OLD.tanggal_daftar
    ), 'system');
END//
DELIMITER ;

-- Tampilkan struktur tabel
DESCRIBE pendaftar;
DESCRIBE admin;
DESCRIBE log_pendaftar;

-- Tampilkan data contoh
SELECT * FROM pendaftar LIMIT 5;
SELECT * FROM statistik_pendaftar;
