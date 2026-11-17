@echo off
title E-LIBRA - jangan ditutup selama web dipakai
cd /d "%~dp0"

set "PHP=php"
where php >nul 2>nul
if errorlevel 1 set "PHP=C:\xampp\php\php.exe"

"%PHP%" -v >nul 2>nul
if errorlevel 1 (
  echo.
  echo PHP belum terpasang di komputer ini.
  echo Pasang XAMPP dulu dari https://www.apachefriends.org lalu klik file ini lagi.
  echo.
  pause
  exit /b
)

if not exist "vendor\autoload.php" (
  echo.
  echo Folder "vendor" tidak ditemukan. Pastikan zip diekstrak sampai selesai.
  echo.
  pause
  exit /b
)

echo.
echo ===============================================
echo   E-LIBRA sedang dinyalakan...
echo   Chrome akan terbuka otomatis.
echo   JANGAN TUTUP jendela ini selama web dipakai.
echo   Untuk mematikan: tutup jendela ini.
echo ===============================================
echo.

"%PHP%" artisan migrate --force
start "" /min cmd /c "timeout /t 3 /nobreak >nul & start http://localhost:8000"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
pause
