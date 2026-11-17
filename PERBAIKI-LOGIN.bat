@echo off
title E-LIBRA - perbaiki login
cd /d "%~dp0"
set "PHP=php"
where php >nul 2>nul
if errorlevel 1 set "PHP=C:\xampp\php\php.exe"
"%PHP%" artisan migrate --force
"%PHP%" artisan optimize:clear
"%PHP%" artisan elibra:diagnose
echo.
echo Kirim tampilan di atas ke pengembang jika ada [GAGAL].
pause
