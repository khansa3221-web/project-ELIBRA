@echo off
set XLS=%~dp0BUKU_INDUK_KESELURUHAN.xlsx
cd /d "C:\xampp\htdocs\project-ELIBRA" || (echo Folder project tidak ditemukan & pause & exit /b)
copy /Y "%~dp0ImportBukuInduk.php" app\Console\Commands\ImportBukuInduk.php >nul
echo Backup database...
if not exist backup mkdir backup
copy /Y database\database.sqlite "backup\database-sebelum-ganti-buku.sqlite" >nul
php artisan migrate --force
php artisan elibra:import-induk "%XLS%" --count=50 --ganti-tanpa-cover --cari-judul
echo.
echo Selesai.
pause
