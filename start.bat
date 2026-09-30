@echo off
chcp 65001 >nul
title Khởi Chạy Website Portfolio - Đề Số 13 - MSSV: DTC245180186

echo ============================================================
echo   HỆ THỐNG QUẢN TRỊ VÀ TRIỂN KHAI PHẦN MỀM - ĐỀ SỐ 13
echo   Website Portfolio / Giới thiệu Cá nhân (Docker Compose)
echo   MSSV: DTC245180186
echo ============================================================
echo.

cd /d "%~dp0"

:: 1. Kiểm tra file .env
if not exist .env (
    echo [!] Chua tim thay file .env, dang copy tu env.template...
    copy env.template .env >nul
    echo [+] Da tao file .env thanh cong.
    echo.
    echo [!] LUU Y: Mo file .env va doi mat khau truoc khi chay!
    echo     File .env da duoc tao tai: %~dp0.env
    pause
)

:: 2. Kiểm tra & tạo SSL cert nếu chưa có
if not exist nginx\certs\selfsigned.crt (
    echo [*] Chua co chung chi SSL, dang tao chung chi tu ky...
    powershell -ExecutionPolicy Bypass -File nginx\gen-certs.ps1
    if %errorlevel% neq 0 (
        echo [!] Khong the tao cert bang PowerShell. Thu dung OpenSSL truc tiep...
        where openssl >nul 2>&1
        if %errorlevel% equ 0 (
            if not exist nginx\certs mkdir nginx\certs
            openssl req -x509 -nodes -newkey rsa:2048 -keyout nginx\certs\selfsigned.key -out nginx\certs\selfsigned.crt -days 365 -subj "/C=VN/ST=HN/L=Hanoi/O=Portfolio/CN=localhost" 2>nul
            echo [+] Da tao SSL cert thanh cong.
        ) else (
            echo [X] Khong tim thay OpenSSL. Vui long cai Git for Windows hoac OpenSSL.
            pause
            exit /b 1
        )
    ) else (
        echo [+] Da tao SSL cert thanh cong.
    )
)

:: 3. Kiểm tra Docker Daemon
echo [*] Dang kiem tra trang thai Docker...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Docker Desktop chua khoi dong!
    echo [*] Dang kich hoat Docker Desktop, vui long cho trong giay lat...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    
    :wait_docker
    timeout /t 5 /nobreak >nul
    docker info >nul 2>&1
    if %errorlevel% neq 0 (
        echo [...] Dang doi Docker Engine san sang...
        goto wait_docker
    )
)
echo [+] Docker da san sang hoat dong!

:: 4. Khởi chạy toàn bộ stack container
echo.
echo [*] Dang khoi dong toan bo he thong bang Docker Compose...
docker compose up -d --build

if %errorlevel% neq 0 (
    echo.
    echo [X] Co loi xay ra trong qua trinh khoi dong Docker Compose.
    docker compose logs --tail=20
    pause
    exit /b %errorlevel%
)

echo.
echo ============================================================
echo   KHOI CHAY THANH CONG TAT CA DICH VU!
echo ============================================================
echo.
echo 1. Website Portfolio:      https://localhost/
echo 2. Trang Quan Tri Admin:   https://localhost/admin/index.php
echo    - Tai khoan: admin
echo    - Mat khau:  Admin@12345
echo.
echo 3. Quan tri phpMyAdmin:    https://localhost/pma/
echo    - Server:    mysql
echo    - Username:  portfolio_app (hoac root)
echo.
echo 4. Dashboard Grafana:      http://localhost:3000
echo    - Username:  admin
echo    - Mat khau:  xem file .env (GF_SECURITY_ADMIN_PASSWORD)
echo.
echo 5. Giam sat Prometheus:    http://localhost:9090
echo ============================================================
echo.
echo Luu y khi truy cap HTTPS: Trinh duyet se hien canh bao chung chi tu ky.
echo Nhan "Nang cao" (Advanced) -> Chon "Tiep tuc truy cap" (Proceed to localhost).
echo.

:: 5. Chờ services khởi động rồi mở trình duyệt
echo [*] Cho cac service san sang (15 giay)...
timeout /t 15 /nobreak >nul
start https://localhost/

pause
