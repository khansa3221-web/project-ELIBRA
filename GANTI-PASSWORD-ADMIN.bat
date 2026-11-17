@echo off
title E-LIBRA - Buat / ganti akun admin
cd /d "%~dp0"

set "PHP=php"
where php >nul 2>nul
if errorlevel 1 set "PHP=C:\xampp\php\php.exe"

echo.
echo Untuk MENGGANTI password admin bawaan, isi:
echo    Nama   : nama Anda
echo    Email  : admin@elibra.test
echo    Kata sandi baru : (ketik, tulisan tidak terlihat - itu normal)
echo.
echo Untuk MEMBUAT admin baru, isi email yang lain.
echo.
"%PHP%" artisan elibra:admin
echo.
pause
