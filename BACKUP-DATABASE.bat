@echo off
title E-LIBRA - Cadangkan database
cd /d "%~dp0"
if not exist "database\database.sqlite" (
  echo File database\database.sqlite tidak ditemukan.
  pause
  exit /b
)
if not exist "backup" mkdir "backup"
set "TGL=%date:~-4%%date:~3,2%%date:~0,2%-%time:~0,2%%time:~3,2%"
set "TGL=%TGL: =0%"
copy /y "database\database.sqlite" "backup\database-%TGL%.sqlite" >nul
echo.
echo Cadangan tersimpan di folder "backup": database-%TGL%.sqlite
echo Simpan juga salinannya di flashdisk / Google Drive PRIBADI (jangan di repository publik).
echo.
pause
