# Panduan Troubleshooting PPDB Website

## Error 500 Internal Server Error

Jika Anda mendapatkan error 500 saat mengakses website, ikuti langkah-langkah berikut:

### 1. Test Dasar
- Buka `http://localhost/SPMB/test.php` - untuk test PHP
- Buka `http://localhost/SPMB/info.php` - untuk info server
- Buka `http://localhost/SPMB/index.html` - untuk test HTML

### 2. Periksa XAMPP
- Pastikan Apache dan MySQL berjalan
- Restart Apache jika diperlukan
- Periksa error log Apache di `C:\xampp\apache\logs\error.log`

### 3. Periksa File
- Pastikan semua file ada di folder `C:\xampp\htdocs\SPMB\`
- Periksa permission folder
- Pastikan tidak ada file yang corrupt

### 4. Test Database
- Buka `http://localhost/phpmyadmin`
- Pastikan database `ppdb_db` ada
- Import file `database.sql` jika belum

### 5. Aktifkan Error Display
Edit file `php.ini` di `C:\xampp\php\php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```

### 6. Restart XAMPP
- Stop Apache dan MySQL
- Start ulang keduanya
- Coba akses website lagi

### 7. File .htaccess
Jika masih error, coba:
- Hapus file `.htaccess` (sudah dilakukan)
- Atau rename `htaccess.txt` menjadi `.htaccess`

### 8. Test Langsung
Coba akses file PHP langsung:
- `http://localhost/SPMB/index.php`
- `http://localhost/SPMB/pendaftaran.php`

### 9. Periksa Error Log
- Buka `C:\xampp\apache\logs\error.log`
- Cari error yang terkait dengan folder SPMB
- Copy error message untuk analisis lebih lanjut

### 10. Alternatif Solusi
Jika masih bermasalah:
- Pindahkan project ke folder lain (misal: `C:\xampp\htdocs\ppdb\`)
- Gunakan port berbeda untuk Apache
- Update XAMPP ke versi terbaru

## Kontak Support
Jika masalah masih berlanjut, simpan error message dan hubungi administrator sistem.
