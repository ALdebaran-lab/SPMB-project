# Panduan Admin PPDB Website

## 🔐 Akses Admin Panel

### Login Admin
- **URL:** `http://localhost/SPMB/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

### Dashboard Admin
- **URL:** `http://localhost/SPMB/admin/dashboard.php`
- **Fitur:** Lihat, edit, hapus, dan export data pendaftar

## 🚨 Troubleshooting Dashboard

### Error 500 Internal Server Error
Jika dashboard tidak bisa diakses:

1. **Jalankan Test Dashboard:**
   - Buka: `http://localhost/SPMB/admin/test_dashboard.php`
   - Periksa status database dan queries

2. **Jalankan Fix Dashboard:**
   - Buka: `http://localhost/SPMB/admin/fix_dashboard.php`
   - Klik "🚀 Run Dashboard Diagnostics"

3. **Periksa Database:**
   - Buka: `http://localhost/phpmyadmin`
   - Pastikan database `ppdb_db` ada
   - Pastikan table `pendaftar` ada dan berisi data

### Error SQL Syntax
Jika ada error SQL:

1. **Restart Apache di XAMPP**
2. **Import ulang database.sql**
3. **Periksa file config/database.php**

## 📊 Fitur Dashboard

### 1. Data Pendaftar
- **View:** Lihat semua data pendaftar dengan pagination
- **Search:** Cari berdasarkan nama, NISN, email, asal sekolah
- **Filter:** Filter berdasarkan jurusan
- **Edit:** Edit data pendaftar
- **Delete:** Hapus data pendaftar

### 2. Export Data
- **Excel:** Download data dalam format .xls
- **PDF:** Download data dalam format print-friendly

### 3. Statistik
- Total pendaftar
- Pendaftar hari ini
- Pendaftar minggu ini
- Pendaftar bulan ini

## 🔧 File Admin

### Core Files
- `login.php` - Halaman login admin
- `dashboard.php` - Dashboard utama admin
- `logout.php` - Logout admin
- `detail.php` - Detail pendaftar
- `edit.php` - Edit data pendaftar

### Utility Files
- `test_dashboard.php` - Test dashboard functionality
- `fix_dashboard.php` - Fix dashboard issues
- `export_excel.php` - Export ke Excel
- `export_pdf.php` - Export ke PDF

## 📁 Struktur Database

### Table: pendaftar
```sql
- id (Primary Key)
- nama_lengkap
- nisn
- tempat_lahir
- tanggal_lahir
- jenis_kelamin
- alamat
- no_hp
- email
- asal_sekolah
- jurusan
- foto
- dokumen
- tanggal_daftar
```

### Table: admin
```sql
- id (Primary Key)
- username
- password
- nama_lengkap
- email
```

## 🛠️ Maintenance

### Backup Database
1. Buka phpMyAdmin
2. Pilih database `ppdb_db`
3. Klik tab "Export"
4. Pilih format SQL
5. Klik "Go"

### Restore Database
1. Buka phpMyAdmin
2. Pilih database `ppdb_db`
3. Klik tab "Import"
4. Upload file .sql
5. Klik "Go"

### Update Admin Password
1. Edit file `admin/login.php`
2. Ganti username dan password
3. Hash password dengan `password_hash()`

## 🔒 Security

### Session Management
- Session timeout: 30 menit
- Logout otomatis setelah timeout
- Redirect ke login jika tidak authenticated

### File Upload Security
- Hanya file gambar dan dokumen yang diizinkan
- Validasi file size dan type
- File disimpan di folder `uploads/`

### SQL Injection Protection
- Menggunakan PDO prepared statements
- Validasi input server-side
- Sanitasi output

## 📱 Responsive Design

### Mobile Support
- Dashboard responsive untuk mobile
- Table dengan horizontal scroll
- Button yang mudah di-tap

### Browser Compatibility
- Chrome, Firefox, Safari, Edge
- IE 11+ (dengan polyfills)

## 🚀 Performance

### Database Optimization
- Index pada kolom yang sering dicari
- Pagination untuk data besar
- Query optimization

### Caching
- Browser caching untuk static assets
- Session caching
- Database query caching

## 📞 Support

### Jika Ada Masalah
1. Jalankan `test_dashboard.php`
2. Jalankan `fix_dashboard.php`
3. Periksa error log Apache
4. Restart XAMPP
5. Import ulang database

### Error Logs
- Apache: `C:\xampp\apache\logs\error.log`
- PHP: `C:\xampp\php\logs\php_error_log`
- Database: phpMyAdmin error messages

## 🔄 Update & Maintenance

### Regular Tasks
- Backup database setiap minggu
- Monitor error logs
- Update password admin secara berkala
- Clean up file uploads yang tidak terpakai

### Version Control
- Backup semua file sebelum update
- Test di environment development
- Rollback jika ada masalah

---

**Catatan:** Website PPDB ini menggunakan PHP 8.2+, MySQL 5.7+, dan Apache 2.4+.
