@echo off
chcp 65001 >nul
title Website Portfolio - PHP Local Server (Port 8080)
cd /d "%~dp0"

echo ============================================================
echo   KHỞI CHẠY NHANH WEBSITE PORTFOLIO (LOCAL DEV SERVER)
echo ============================================================
echo.
echo [*] Dang khoi dong Web Server tai http://127.0.0.1:8080 ...
start "" http://127.0.0.1:8080
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S 127.0.0.1:8080 -t app/public
) else (
    php -S 127.0.0.1:8080 -t app/public
)
pause
