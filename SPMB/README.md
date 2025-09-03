# Sistem Penerimaan Murid Baru (PPDB) - SMK Negeri 1 Jakarta

Website sistem penerimaan murid baru berbasis web menggunakan PHP, TailwindCSS, dan MySQL dengan fitur lengkap untuk pendaftaran online dan administrasi.

## 🚀 Fitur Utama

### Halaman Utama
- ✅ Informasi sekolah yang menarik dan informatif
- ✅ Jadwal pendaftaran yang jelas
- ✅ Persyaratan pendaftaran yang detail
- ✅ Program keahlian (jurusan) dengan deskripsi
- ✅ Desain responsif dan modern

### Formulir Pendaftaran
- ✅ Formulir lengkap dengan semua field yang diperlukan
- ✅ Validasi JavaScript real-time
- ✅ Upload foto dan dokumen
- ✅ Validasi format dan ukuran file
- ✅ Penyimpanan data ke database

### Panel Admin
- ✅ Login admin yang aman
- ✅ Dashboard dengan statistik
- ✅ Manajemen data pendaftar (CRUD)
- ✅ Fitur pencarian dan filter
- ✅ Export data ke Excel dan PDF
- ✅ Pagination untuk data yang banyak

### Keamanan
- ✅ Prepared statements untuk mencegah SQL Injection
- ✅ Session management
- ✅ Validasi input yang ketat
- ✅ Sanitasi data output

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+ dengan PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: TailwindCSS
- **Icons**: Font Awesome 6.0
- **Server**: Apache/Nginx (XAMPP/WAMP)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Browser modern dengan JavaScript enabled
- Ekstensi PHP: PDO, PDO_MySQL

## 🚀 Cara Instalasi

### 1. Clone atau Download Project
```bash
git clone [repository-url]
cd SPMB
```

### 2. Setup Database
1. Buka phpMyAdmin atau MySQL client
2. Import file `database.sql` atau jalankan query SQL yang ada di file tersebut
3. Pastikan database `ppdb_db` sudah dibuat

### 3. Konfigurasi Database
Edit file `pendaftaran.php` dan file admin lainnya, sesuaikan konfigurasi database:
```php
$host = 'localhost';
$dbname = 'ppdb_db';
$username = 'root';
$password = '';
```

### 4. Setup Web Server
1. Letakkan semua file di direktori web server (htdocs untuk XAMPP)
2. Pastikan direktori `uploads/` memiliki permission 755
3. Akses website melalui browser

### 5. Login Admin
- **URL**: `http://localhost/SPMB/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

## 📁 Struktur File

```
SPMB/
├── index.php                 # Halaman utama
├── pendaftaran.php          # Formulir pendaftaran
├── database.sql             # File SQL database
├── README.md                # Dokumentasi ini
├── uploads/                 # Direktori upload file
│   └── .gitkeep
└── admin/                   # Panel admin
    ├── login.php            # Login admin
    ├── dashboard.php        # Dashboard utama
    ├── detail.php           # Detail pendaftar
    ├── edit.php             # Edit pendaftar
    ├── export_excel.php     # Export ke Excel
    ├── export_pdf.php       # Export ke PDF
    └── logout.php           # Logout admin
```

## 🔧 Konfigurasi

### Database
- Database name: `ppdb_db`
- Tabel utama: `pendaftar`
- Tabel admin: `admin`
- Tabel log: `log_pendaftar`

### File Upload
- Direktori upload: `uploads/`
- Format foto: JPG, JPEG, PNG (max 2MB)
- Format dokumen: PDF, JPG, JPEG, PNG (max 5MB)

### Admin Panel
- Default username: `admin`
- Default password: `admin123`
- **PENTING**: Ganti password default di produksi!

## 📱 Fitur Responsif

Website ini didesain responsif dan dapat diakses dengan nyaman dari:
- Desktop/Laptop
- Tablet
- Smartphone

## 🔒 Keamanan

- Menggunakan PDO dengan prepared statements
- Validasi input di sisi client dan server
- Sanitasi output untuk mencegah XSS
- Session management yang aman
- Validasi file upload yang ketat

## 📊 Fitur Admin

### Dashboard
- Statistik total pendaftar
- Pendaftar hari ini, minggu ini, bulan ini
- Grafik distribusi jurusan

### Manajemen Data
- Lihat semua data pendaftar
- Edit data pendaftar
- Hapus data pendaftar
- Detail lengkap pendaftar

### Export Data
- Export ke Excel (.xls)
- Export ke PDF (print-friendly)
- Filter berdasarkan jurusan
- Pencarian berdasarkan nama, NISN, email

## 🎨 Customization

### Mengubah Informasi Sekolah
Edit file `index.php` bagian:
- Nama sekolah
- Alamat dan kontak
- Program keahlian
- Jadwal pendaftaran

### Mengubah Field Formulir
Edit file `pendaftar.php` dan `admin/edit.php` untuk:
- Menambah/mengurangi field
- Mengubah validasi
- Mengubah label dan placeholder

### Mengubah Tampilan
- Gunakan TailwindCSS classes
- Edit warna dan style
- Tambahkan logo sekolah

## 🐛 Troubleshooting

### Error Database Connection
- Pastikan MySQL service berjalan
- Periksa username dan password database
- Pastikan database `ppdb_db` sudah dibuat

### Error Upload File
- Periksa permission direktori `uploads/`
- Pastikan direktori memiliki permission 755
- Periksa ukuran file upload

### Error Session
- Pastikan ekstensi PHP session sudah aktif
- Periksa konfigurasi PHP session

## 📞 Support

Untuk bantuan dan support:
- Email: admin@smkn1jakarta.sch.id
- Website: www.smkn1jakarta.sch.id

## 📄 License

Project ini dibuat untuk SMK Negeri 1 Jakarta. Dilarang menggunakan untuk tujuan komersial tanpa izin.

## 🔄 Update dan Maintenance

### Regular Maintenance
- Backup database secara berkala
- Update password admin secara berkala
- Monitor log file untuk aktivitas mencurigakan
- Update PHP dan MySQL ke versi terbaru

### Backup
- Backup database MySQL
- Backup file upload
- Backup source code

---

**Dibuat dengan ❤️ untuk SMK Negeri 1 Jakarta**

*Last updated: December 2024*
