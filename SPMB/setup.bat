@echo off
echo ========================================
echo Setup PPDB Website untuk XAMPP
echo ========================================
echo.

echo 1. Memeriksa XAMPP...
if exist "C:\xampp\apache\bin\httpd.exe" (
    echo XAMPP ditemukan di C:\xampp
) else (
    echo XAMPP tidak ditemukan di C:\xampp
    echo Silakan install XAMPP terlebih dahulu
    pause
    exit
)

echo.
echo 2. Memeriksa folder project...
if exist "C:\xampp\htdocs\SPMB" (
    echo Folder SPMB sudah ada
) else (
    echo Folder SPMB tidak ditemukan
    echo Silakan pindahkan project ke C:\xampp\htdocs\SPMB
    pause
    exit
)

echo.
echo 3. Membuat database...
echo Buka http://localhost/phpmyadmin
echo Buat database baru dengan nama: ppdb_db
echo Import file database.sql

echo.
echo 4. Test website...
echo Test file: http://localhost/SPMB/test.php
echo Website: http://localhost/SPMB/
echo Admin: http://localhost/SPMB/admin/login.php

echo.
echo 5. Jika ada error 500:
echo - Hapus file .htaccess (sudah dilakukan)
echo - Restart Apache di XAMPP Control Panel
echo - Periksa error log di C:\xampp\apache\logs\error.log

echo.
echo Setup selesai! Tekan tombol apa saja untuk keluar...
pause > nul
