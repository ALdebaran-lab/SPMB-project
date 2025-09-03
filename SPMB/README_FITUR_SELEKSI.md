# Fitur Seleksi dan Notifikasi Otomatis SPMB

## Overview
Sistem SPMB telah diperbarui dengan fitur seleksi pendaftar dan notifikasi otomatis. Admin dapat melakukan seleksi pendaftar dengan input nilai TK, dan sistem akan otomatis mengirim notifikasi hasil seleksi ke pendaftar melalui SMS dan Email.

## Fitur Utama

### 1. Seleksi Pendaftar
- **Input Nilai TK**: Admin dapat memasukkan nilai akhir jenjang TK (0-100) untuk setiap pendaftar
- **Status Seleksi**: Tiga status: Menunggu, Diterima, Ditolak
- **Auto-Accept**: Pendaftar dengan nilai TK ≥70 otomatis diterima
- **Catatan Seleksi**: Admin dapat menambahkan catatan khusus untuk setiap pendaftar

### 2. Notifikasi Otomatis
- **SMS**: Notifikasi hasil seleksi dikirim ke nomor HP pendaftar
- **Email**: Notifikasi hasil seleksi dikirim ke email pendaftar
- **Status Tracking**: Melacak status pengiriman notifikasi (Pending, Terkirim, Gagal)
- **Auto-Retry**: Sistem dapat mengirim ulang notifikasi yang gagal

### 3. Dashboard Admin
- **Statistik Seleksi**: Menampilkan jumlah pendaftar berdasarkan status seleksi
- **Filter dan Pencarian**: Mencari pendaftar berdasarkan nama, NISN, atau status
- **Quick Actions**: Tombol cepat untuk akses ke halaman seleksi dan notifikasi

### 4. Cek Status Pendaftar
- **Public Access**: Siswa dapat mengecek status pendaftaran mereka
- **Informasi Lengkap**: Menampilkan data pribadi, akademik, dan status seleksi
- **Langkah Selanjutnya**: Panduan langkah selanjutnya berdasarkan hasil seleksi

## Struktur Database

### Tabel `pendaftar` (Updated)
```sql
ALTER TABLE pendaftar ADD COLUMN nilai_tk DECIMAL(5,2) DEFAULT NULL;
ALTER TABLE pendaftar ADD COLUMN status_seleksi ENUM('Menunggu', 'Diterima', 'Ditolak') DEFAULT 'Menunggu';
ALTER TABLE pendaftar ADD COLUMN tanggal_seleksi TIMESTAMP NULL;
ALTER TABLE pendaftar ADD COLUMN catatan_seleksi TEXT;
ALTER TABLE pendaftar ADD COLUMN admin_seleksi VARCHAR(50);
```

### Tabel `notifikasi_seleksi` (New)
```sql
CREATE TABLE notifikasi_seleksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id INT NOT NULL,
    jenis_notifikasi ENUM('SMS', 'Email') NOT NULL,
    status_pengiriman ENUM('Pending', 'Terkirim', 'Gagal') DEFAULT 'Pending',
    pesan TEXT NOT NULL,
    tanggal_kirim TIMESTAMP NULL,
    tanggal_terkirim TIMESTAMP NULL,
    error_message TEXT,
    FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id) ON DELETE CASCADE
);
```

## File yang Ditambahkan/Dimodifikasi

### File Baru
1. **`admin/seleksi.php`** - Halaman seleksi pendaftar
2. **`admin/kirim_notifikasi.php`** - Halaman manajemen notifikasi
3. **`cek_status.php`** - Halaman cek status pendaftaran
4. **`cron_send_notifications.php`** - Script cron job untuk notifikasi otomatis

### File yang Dimodifikasi
1. **`database.sql`** - Struktur database yang diperbarui
2. **`admin/dashboard.php`** - Dashboard admin dengan fitur baru

## Cara Penggunaan

### Untuk Admin

#### 1. Akses Halaman Seleksi
- Login ke admin dashboard
- Klik tombol "Seleksi Pendaftar"
- Atau akses langsung: `admin/seleksi.php`

#### 2. Melakukan Seleksi
- Pilih pendaftar yang akan diseleksi
- Klik tombol "Seleksi"
- Input nilai TK (0-100)
- Pilih status seleksi
- Tambahkan catatan jika diperlukan
- Klik "Simpan Seleksi"

#### 3. Mengelola Notifikasi
- Akses halaman "Notifikasi"
- Lihat status pengiriman notifikasi
- Kirim notifikasi manual jika diperlukan
- Gunakan "Kirim Semua Otomatis" untuk batch processing

### Untuk Siswa

#### 1. Cek Status Pendaftaran
- Akses: `cek_status.php`
- Masukkan NISN dan email
- Klik "Cek Status"
- Lihat hasil seleksi dan langkah selanjutnya

## Setup Notifikasi

### SMS Gateway
Untuk mengirim SMS otomatis, integrasikan dengan layanan SMS:

1. **Twilio** (Recommended)
   ```php
   require_once 'vendor/autoload.php';
   $account_sid = 'YOUR_ACCOUNT_SID';
   $auth_token = 'YOUR_AUTH_TOKEN';
   $twilio_number = 'YOUR_TWILIO_NUMBER';
   ```

2. **Nexmo/Vonage**
3. **SMS Gateway Lokal**

### Email SMTP
Untuk mengirim email otomatis, gunakan PHPMailer:

1. **Gmail SMTP**
   ```php
   $mail->Host = 'smtp.gmail.com';
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'your-app-password';
   ```

2. **SMTP Server Lain**
   - Office 365
   - SendGrid
   - Amazon SES

### Cron Job Setup
Untuk pengiriman otomatis, buat cron job:

```bash
# Setiap 5 menit
*/5 * * * * php /path/to/your/project/cron_send_notifications.php

# Setiap jam
0 * * * * php /path/to/your/project/cron_send_notifications.php

# Setiap hari jam 8 pagi
0 8 * * * php /path/to/your/project/cron_send_notifications.php
```

## Workflow Sistem

### 1. Pendaftaran
1. Siswa mendaftar melalui `pendaftaran.php`
2. Data tersimpan dengan status "Menunggu"
3. Admin dapat melihat pendaftar baru di dashboard

### 2. Seleksi
1. Admin mengakses halaman seleksi
2. Input nilai TK dan pilih status
3. Jika nilai TK ≥70, otomatis diterima
4. Sistem membuat record notifikasi

### 3. Notifikasi
1. Sistem mengirim notifikasi ke SMS dan Email
2. Update status pengiriman
3. Log semua aktivitas untuk tracking

### 4. Cek Status
1. Siswa dapat cek status kapan saja
2. Lihat hasil seleksi dan langkah selanjutnya
3. Informasi lengkap tentang pendaftaran

## Keamanan

### Admin Authentication
- Session-based authentication
- Redirect ke login jika belum login
- Validasi admin privileges

### Data Validation
- Input sanitization untuk mencegah SQL injection
- Validasi format email dan nomor HP
- Rate limiting untuk mencegah spam

### File Upload Security
- Validasi file type dan size
- Secure file naming
- Directory permission management

## Monitoring dan Logging

### Error Logging
- Semua error dicatat di `error_log.txt`
- Database error tracking
- Notification failure logging

### Performance Monitoring
- Execution time tracking
- Database query optimization
- Memory usage monitoring

## Troubleshooting

### Common Issues

#### 1. Notifikasi Tidak Terkirim
- Cek konfigurasi SMS/Email
- Pastikan cron job berjalan
- Cek error log untuk detail

#### 2. Database Error
- Pastikan struktur database sudah benar
- Cek koneksi database
- Verifikasi user permissions

#### 3. File Upload Error
- Cek folder permissions
- Validasi file size limits
- Pastikan disk space cukup

### Debug Mode
Untuk debugging, aktifkan error reporting:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Maintenance

### Regular Tasks
1. **Database Backup**: Backup database secara berkala
2. **Log Rotation**: Rotate error logs untuk mencegah disk penuh
3. **Performance Check**: Monitor query performance
4. **Security Update**: Update dependencies dan security patches

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz uploads/
```

## Support dan Kontak

### Technical Support
- Email: admin@school.com
- Phone: +62-xxx-xxx-xxxx
- Documentation: README files

### Development Team
- Lead Developer: [Nama]
- Database Admin: [Nama]
- System Admin: [Nama]

---

**Catatan**: Dokumen ini akan diperbarui sesuai dengan perkembangan sistem. Pastikan selalu menggunakan versi terbaru untuk informasi yang akurat.

