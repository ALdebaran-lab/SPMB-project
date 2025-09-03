# Panduan Fitur Admin Actions - SPMB SDN Majasetra 01

## Overview
Dokumen ini menjelaskan cara menggunakan fitur-fitur admin yang sudah diperbaiki dan dioptimalkan untuk sistem SPMB SDN Majasetra 01.

## Fitur yang Tersedia

### 1. Dashboard Admin (`dashboard.php`)
- **Fungsi**: Halaman utama admin untuk melihat semua data pendaftar
- **Fitur**:
  - Tampilan statistik (Total, Hari Ini, Minggu Ini, Bulan Ini)
  - Tabel data pendaftar dengan pagination
  - Search dan filter berdasarkan kelas
  - Export data ke Excel dan PDF
  - Test Actions untuk verifikasi fitur

### 2. View/Detail Pendaftar (`detail.php`)
- **Fungsi**: Melihat detail lengkap data pendaftar
- **Cara Akses**: Klik tombol biru (👁️) di kolom Aksi
- **Informasi yang Ditampilkan**:
  - Data pribadi (nama, NISN, tempat/tanggal lahir, jenis kelamin)
  - Kontak (alamat, no HP, email)
  - Data akademik (asal sekolah, kelas)
  - Foto dan dokumen pendaftar
  - Tanggal pendaftaran

### 3. Edit Pendaftar (`edit.php`)
- **Fungsi**: Mengubah data pendaftar yang sudah ada
- **Cara Akses**: Klik tombol hijau (✏️) di kolom Aksi
- **Fitur**:
  - Form edit dengan validasi
  - Upload foto dan dokumen baru
  - Hapus file lama otomatis
  - Preview data sebelum update

### 4. Delete Pendaftar
- **Fungsi**: Menghapus data pendaftar dari sistem
- **Cara Akses**: Klik tombol merah (🗑️) di kolom Aksi
- **Keamanan**:
  - Konfirmasi sebelum hapus
  - Hapus file foto dan dokumen terkait
  - Redirect otomatis setelah hapus
  - Pesan sukses/error

## Cara Penggunaan

### Melihat Detail Pendaftar
1. Buka dashboard admin
2. Cari pendaftar yang ingin dilihat detailnya
3. Klik tombol biru (👁️) di kolom Aksi
4. Halaman detail akan terbuka dengan informasi lengkap

### Mengedit Data Pendaftar
1. Buka dashboard admin
2. Cari pendaftar yang ingin diedit
3. Klik tombol hijau (✏️) di kolom Aksi
4. Ubah data yang diperlukan
5. Upload file baru jika diperlukan
6. Klik "Update Data" untuk menyimpan

### Menghapus Data Pendaftar
1. Buka dashboard admin
2. Cari pendaftar yang ingin dihapus
3. Klik tombol merah (🗑️) di kolom Aksi
4. Konfirmasi penghapusan
5. Data akan dihapus otomatis

## Troubleshooting

### Masalah Umum

#### 1. Tombol Aksi Tidak Berfungsi
- **Penyebab**: JavaScript tidak dimuat atau ada error
- **Solusi**: 
  - Refresh halaman
  - Periksa console browser untuk error
  - Pastikan Font Awesome dimuat dengan benar

#### 2. Halaman Detail/Edit Tidak Buka
- **Penyebab**: ID pendaftar tidak valid atau database error
- **Solusi**:
  - Periksa koneksi database
  - Pastikan tabel `pendaftar` ada dan berisi data
  - Periksa file `config/database.php`

#### 3. Fitur Delete Tidak Berfungsi
- **Penyebab**: Permission file atau database error
- **Solusi**:
  - Periksa permission folder `uploads`
  - Pastikan database connection berfungsi
  - Periksa error log PHP

### Test Actions
Gunakan tombol "Test Actions" (kuning) di dashboard untuk:
- Memverifikasi semua fitur berfungsi
- Melihat data sample untuk testing
- Memastikan koneksi database normal

## Keamanan

### Fitur Keamanan yang Diterapkan
1. **Session Management**: Hanya admin yang login yang bisa akses
2. **Input Validation**: Validasi semua input user
3. **SQL Injection Protection**: Menggunakan prepared statements
4. **File Upload Security**: Validasi tipe dan ukuran file
5. **XSS Protection**: Output escaping untuk semua data

### Best Practices
1. Selalu logout setelah selesai menggunakan admin panel
2. Jangan share kredensial login
3. Backup database secara berkala
4. Monitor log akses admin
5. Update sistem secara rutin

## Struktur File

```
admin/
├── dashboard.php          # Dashboard utama
├── detail.php            # View detail pendaftar
├── edit.php              # Edit data pendaftar
├── login.php             # Login admin
├── logout.php            # Logout admin
├── export_excel.php      # Export ke Excel
├── export_pdf.php        # Export ke PDF
├── test_actions.php      # Test fitur admin
└── README_ACTIONS.md     # Dokumen ini
```

## Support

Jika mengalami masalah dengan fitur admin:
1. Periksa file `admin/README_ADMIN.md` untuk troubleshooting umum
2. Gunakan fitur "Test Actions" untuk diagnosis
3. Periksa error log PHP dan database
4. Pastikan semua file yang diperlukan ada dan dapat diakses

---

**Versi**: 1.0  
**Update Terakhir**: 2025  
**Sistem**: SPMB SDN Majasetra 01
