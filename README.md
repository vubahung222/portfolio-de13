# Đề 13 — Website Portfolio / Giới thiệu Cá nhân

> **Môn:** Triển khai và Quản trị Hệ thống Phần mềm  
> **Sinh viên:** Vũ Bá Hùng  
> **MSSV:** DTC245180186  
> **Repository:** https://github.com/DTC245180186/portfolio-de13

---

## 1. Tổng quan hệ thống (Commit 1)
Hệ thống Website Portfolio cá nhân tích hợp trang quản trị Admin, triển khai bằng **Docker Compose** bao gồm:
- **Ứng dụng Web PHP 8.3 (PHP-FPM)**: Portfolio giới thiệu bản thân, kỹ năng, dự án và trang Admin quản trị.
- **Cơ sở dữ liệu MySQL 8.0**: Lưu trữ dữ liệu profile, kỹ năng, dự án và tin nhắn liên hệ.
- **Công cụ quản lý DB phpMyAdmin 5.2**: Quản lý trực quan CSDL qua giao diện web.
- **Nginx Reverse Proxy**: Cổng tiếp nhận duy nhất, hỗ trợ HTTPS TLS 1.2/1.3 và 7 Security Headers bảo vệ an ninh.

---

## 2. Hướng dẫn khởi chạy nhanh
1. Tạo file `.env` từ file mẫu:
   ```bash
   cp env.template .env
   ```
2. Khởi tạo chứng chỉ HTTPS tự ký:
   ```powershell
   powershell -ExecutionPolicy Bypass -File nginx\gen-certs.ps1
   ```
3. Khởi chạy hệ thống bằng Docker Compose:
   ```bash
   docker compose up -d --build
   ```
4. Truy cập các dịch vụ:
   - **Website:** https://localhost/
   - **Admin:** https://localhost/admin/index.php (Tài khoản: `admin` / `Admin@12345`)
   - **phpMyAdmin:** https://localhost/pma/
