@echo off
chcp 65001 >nul
title Dừng Hệ Thống Website Portfolio - Đề Số 13

cd /d "%~dp0"
echo [*] Đang dừng tất cả container...
docker compose down
echo [+] Đã dừng hệ thống thành công (dữ liệu database vẫn được bảo lưu).
pause
