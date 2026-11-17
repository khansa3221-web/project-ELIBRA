@echo off
echo Membersihkan cache tampilan E-LIBRA...
cd /d "%~dp0"
del /q /f "storage\framework\views\*.php" 2>nul
del /q /f "storage\framework\views\*.tmp" 2>nul
del /q /f "storage\framework\cache\data\*" 2>nul
attrib -r "storage\framework\views\*" /s 2>nul
echo Selesai. Sekarang jalankan JALANKAN.bat lagi.
pause
