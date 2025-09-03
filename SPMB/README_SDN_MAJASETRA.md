# Sistem Penerimaan Murid Baru (PPDB) - SDN Majasetra 01

## 🏫 Tentang Sekolah

**SDN Majasetra 01** adalah sekolah dasar negeri unggulan yang berkomitmen memberikan pendidikan berkualitas dan membentuk karakter siswa yang berakhlak mulia. Sekolah ini berlokasi di Jl. Majasetra No. 01, Jakarta Selatan.

## ✨ Fitur Utama

### 🏠 Halaman Utama
- Informasi lengkap tentang SDN Majasetra 01
- Program pembelajaran untuk kelas 1-6
- Jadwal pendaftaran tahun ajaran 2024/2025
- Persyaratan pendaftaran yang jelas
- Link pendaftaran online

### 📝 Formulir Pendaftaran Online
- **Data Pribadi:**
  - Nama Lengkap
  - NISN (Nomor Induk Siswa Nasional)
  - Tempat & Tanggal Lahir
  - Jenis Kelamin
  - Alamat Lengkap
  - Nomor HP
  - Email

- **Data Akademik:**
  - Asal Sekolah (TK/PAUD/SD)
  - Pilihan Kelas (1-6)
  - Upload Foto 3x4
  - Upload Dokumen Pendukung (Akta Kelahiran, KK, Ijazah TK/PAUD)

### 🔐 Panel Admin
- **Login Admin:** Username: `admin`, Password: `admin123`
- **Dashboard Admin:**
  - Lihat daftar pendaftar dengan pagination
  - Search berdasarkan nama, NISN, email, asal sekolah
  - Filter berdasarkan kelas
  - Edit data pendaftar
  - Hapus data pendaftar
  - Export data ke Excel dan PDF
  - Statistik pendaftar (total, hari ini, minggu ini, bulan ini)

## 🎯 Program Pembelajaran

### 📚 Kelas 1-6
- Program pembelajaran untuk semua jenjang kelas 1 sampai 6
- Kuota: 32 siswa per kelas
- Fokus pada pendidikan dasar yang berkualitas

### 🌟 Program Khusus
- Program pengembangan bakat dan minat siswa
- Ekstrakurikuler yang beragam
- Pembentukan karakter dan akhlak mulia

## 📅 Jadwal Pendaftaran 2024/2025

| Tahap | Tanggal | Keterangan |
|-------|---------|------------|
| **Pendaftaran Dibuka** | 1 Januari 2024 | Pendaftaran online dimulai |
| **Pendaftaran Ditutup** | 31 Maret 2024 | Batas akhir pendaftaran |
| **Verifikasi Dokumen** | 1-15 April 2024 | Pemeriksaan kelengkapan dokumen |
| **Pengumuman Hasil** | 20 April 2024 | Pengumuman siswa yang diterima |
| **Daftar Ulang** | 25 April - 10 Mei 2024 | Konfirmasi dan pembayaran |

## 📋 Persyaratan Pendaftaran

### ✅ Persyaratan Umum
- Usia minimal 6 tahun pada 1 Juli 2024 (untuk kelas 1)
- Untuk kelas 2-6: pindahan dari SD lain
- Sehat jasmani dan rohani
- Berkelakuan baik
- Bersedia mengikuti aturan sekolah

### 📄 Dokumen yang Diperlukan
- Fotokopi Akta Kelahiran
- Fotokopi Kartu Keluarga
- Fotokopi Ijazah TK/PAUD (untuk kelas 1)
- Fotokopi KTP Orang Tua
- Surat keterangan pindah (untuk kelas 2-6)
- Pas foto 3x4 (3 lembar)

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 8.2+** dengan PDO untuk database
- **MySQL 5.7+** untuk database
- **Session management** untuk keamanan admin

### Frontend
- **HTML5** dan **CSS3**
- **TailwindCSS** untuk styling modern
- **JavaScript** untuk validasi form
- **Font Awesome** untuk ikon

### Database
- **Table `pendaftar`:** Data siswa yang mendaftar
- **Table `admin`:** Kredensial admin
- **Table `log_pendaftar`:** Log perubahan data
- **View `statistik_pendaftar`:** Statistik pendaftar

## 🚀 Cara Instalasi

### 1. Persyaratan Sistem
- XAMPP (Apache + MySQL + PHP)
- PHP 8.2 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web browser modern

### 2. Langkah Instalasi
1. **Clone/Download** project ke folder `C:\xampp\htdocs\SPMB\`
2. **Start XAMPP:** Apache dan MySQL
3. **Buat Database:** Buka `http://localhost/phpmyadmin`
4. **Import Database:** Upload file `database.sql`
5. **Test Website:** Buka `http://localhost/SPMB/`

### 3. Konfigurasi Database
File `config/database.php` sudah dikonfigurasi untuk:
- Host: `localhost`
- Database: `ppdb_db`
- Username: `root`
- Password: `` (kosong)

## 🔧 Fitur Keamanan

### 🛡️ SQL Injection Protection
- Menggunakan PDO prepared statements
- Validasi input server-side dan client-side
- Sanitasi output

### 🔐 Session Management
- Login admin dengan session
- Logout otomatis setelah timeout
- Redirect ke login jika tidak authenticated

### 📁 File Upload Security
- Validasi file type dan size
- File disimpan di folder `uploads/`
- Proteksi dari eksekusi PHP di folder uploads

## 📱 Responsive Design

### 📱 Mobile Support
- Website responsive untuk mobile dan tablet
- Form yang mudah diisi di perangkat mobile
- Dashboard admin yang mobile-friendly

### 💻 Browser Compatibility
- Chrome, Firefox, Safari, Edge
- IE 11+ (dengan polyfills)

## 📊 Fitur Admin

### 🔍 Search & Filter
- Search berdasarkan nama, NISN, email, asal sekolah
- Filter berdasarkan kelas
- Pagination untuk data besar

### 📤 Export Data
- **Excel (.xls):** Download data dalam format spreadsheet
- **PDF:** Download data dalam format print-friendly

### 📈 Statistik
- Total pendaftar
- Pendaftar hari ini
- Pendaftar minggu ini
- Pendaftar bulan ini
- Distribusi berdasarkan kelas dan jenis kelamin

## 🚨 Troubleshooting

### Error 500 Internal Server Error
1. Jalankan `http://localhost/SPMB/fix_500_error.php`
2. Restart Apache di XAMPP
3. Periksa error log Apache

### Dashboard Admin Tidak Bisa Diakses
1. Jalankan `http://localhost/SPMB/admin/test_dashboard.php`
2. Jalankan `http://localhost/SPMB/admin/fix_dashboard.php`
3. Periksa database connection

### Database Issues
1. Pastikan MySQL berjalan di XAMPP
2. Import ulang file `database.sql`
3. Periksa konfigurasi di `config/database.php`

## 📞 Kontak & Support

### 🏫 Informasi Sekolah
- **Nama:** SDN Majasetra 01
- **Alamat:** Jl. Majasetra No. 01, Jakarta Selatan
- **Telepon:** (021) 789-0123
- **Email:** info@sdnmajasetra01.sch.id

### 🔧 Technical Support
- **Test Website:** `http://localhost/SPMB/test.php`
- **Server Check:** `http://localhost/SPMB/check_server.php`
- **Fix Error:** `http://localhost/SPMB/fix_500_error.php`
- **Admin Test:** `http://localhost/SPMB/admin/test_dashboard.php`

## 🔄 Update & Maintenance

### 📝 Regular Tasks
- Backup database setiap minggu
- Monitor error logs
- Update password admin secara berkala
- Clean up file uploads yang tidak terpakai

### 🆕 Version Updates
- Backup semua file sebelum update
- Test di environment development
- Rollback jika ada masalah

## 📚 Dokumentasi Tambahan

- **README.md:** Dokumentasi umum project
- **README_ADMIN.md:** Panduan lengkap untuk admin
- **README_TROUBLESHOOTING.md:** Panduan troubleshooting
- **database.sql:** Struktur database dan data dummy

---

**© 2024 SDN Majasetra 01. All rights reserved.**

Website PPDB ini dirancang khusus untuk SDN Majasetra 01 dengan fitur yang sesuai untuk sekolah dasar. Semua informasi dan fitur telah disesuaikan untuk kebutuhan penerimaan siswa baru tingkat SD.
