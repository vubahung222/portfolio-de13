-- ============================================================
--  Portfolio database schema + seed data
--  Runs automatically on first MySQL container start.
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio;

-- ---- Admin users -------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(64)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- Profile (single row) ----------------------------------
CREATE TABLE IF NOT EXISTS profile (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(120) NOT NULL,
  title    VARCHAR(160) NOT NULL,
  bio      TEXT,
  email    VARCHAR(160),
  github   VARCHAR(200),
  linkedin VARCHAR(200)
) ENGINE=InnoDB;

-- ---- Projects ----------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(160) NOT NULL,
  description TEXT,
  link        VARCHAR(255),
  tech        VARCHAR(200),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- Skills ------------------------------------------------
CREATE TABLE IF NOT EXISTS skills (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  name    VARCHAR(80) NOT NULL,
  level   INT NOT NULL DEFAULT 50   -- 0..100
) ENGINE=InnoDB;

-- ---- Contact messages --------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(160) NOT NULL,
  body       TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
--  Seed data
-- ============================================================
INSERT INTO profile (fullname, title, bio, email, github, linkedin) VALUES
('Vũ Bá Hùng', 'Sinh viên K23 - Khoa Công nghệ Thông tin (ICTU)',
 'Chào thầy cô và các bạn, mình là Vũ Bá Hùng (MSSV: DTC245180186), sinh viên chuyên ngành Công nghệ Thông tin tại Trường ĐH Công nghệ Thông tin và Truyền thông - ĐH Thái Nguyên (ICTU).\n\nĐây là hệ thống Portfolio cá nhân được mình tự xây dựng và triển khai thực tế trên hạ tầng Docker Compose phục vụ học phần Triển khai và Quản trị Hệ thống Phần mềm (Đề tài số 13). Hệ thống tích hợp đầy đủ Nginx Reverse Proxy HTTPS TLS 1.3, CSDL MySQL 8.0, cụm giám sát Prometheus + Grafana và quản lý log tập trung Loki + Promtail.',
 'dtc245180186@ictu.edu.vn', 'https://github.com/vubahung222/portfolio-de13', 'https://github.com/vubahung222');

INSERT INTO projects (title, description, link, tech) VALUES
('Đề tài 13: Hệ sinh thái Web Portfolio & Quản trị Hệ thống Docker Compose', 'Đồ án môn học hoàn chỉnh gồm 10 container microservices: Web PHP 8.3 FPM, MySQL 8.0, phpMyAdmin 8081, Nginx Reverse Proxy HTTPS, Prometheus, Grafana, Loki và Promtail. Cấu hình cô lập mạng backend nội bộ và kiểm thử tự động 10/10 Passed.', 'https://github.com/vubahung222/portfolio-de13', 'Docker Compose, Nginx HTTPS, PHP 8.3, MySQL 8.0, Prometheus, Grafana, Loki'),
('Hệ thống Giám sát Hạ tầng & Container với Prometheus và Grafana', 'Tích hợp 5 Exporters chuyên dụng (cAdvisor, Node-Exporter, Nginx-Exporter, MySQL-Exporter, Prometheus self-monitor) cào metrics định kỳ 15s và trực quan hóa qua Dashboard 13 Panels theo dõi tải CPU, RAM, QPS, Disk I/O.', 'https://github.com/vubahung222/portfolio-de13', 'Prometheus TSDB, Grafana Dashboard, cAdvisor, Node Exporter, MySQL Exporter'),
('Hệ thống Thu thập và Phân tích Log Tập trung Grafana Loki & Promtail', 'Thiết lập Promtail đọc Docker Daemon Unix socket (/var/run/docker.sock) thu thập log thời gian thực của toàn bộ container; cấu hình Loki lưu trữ chỉ mục và xây dựng 10 truy vấn LogQL phát hiện lỗi HTTP 5xx, SQL injection.', 'https://github.com/vubahung222/portfolio-de13/blob/main/docs/LOGQL.md', 'Grafana Loki, Promtail Agent, Docker Socket, LogQL'),
('Cấu hình Nginx Reverse Proxy & 9 Biện pháp Hardening Bảo mật Hệ thống', 'Triển khai Nginx làm Single Ingress với SSL/TLS 1.2/1.3, auto-redirect HTTP sang HTTPS, 7 Security Headers OWASP, quyền Non-root UID 1000, no-new-privileges và mã hóa mật khẩu Bcrypt cost=12.', 'https://github.com/vubahung222/portfolio-de13', 'Nginx Reverse Proxy, TLS 1.3, OWASP Headers, Non-root, CIS Hardening');

INSERT INTO skills (name, level) VALUES
('Docker & Docker Compose', 90),
('Nginx Reverse Proxy & HTTPS / TLS 1.3', 88),
('Prometheus & Giám sát Hệ thống (5 Exporters)', 85),
('Grafana Dashboard & Provisioning IaC', 85),
('Loki + Promtail & Truy vấn LogQL', 82),
('MySQL 8.0 & Quản trị CSDL (phpMyAdmin)', 80),
('Lập trình Web PHP 8.3 & PDO Security', 82),
('Quản trị Hệ điều hành Linux / Alpine', 80),
('Hardening Bảo mật Container & Network Isolation', 85),
('Quản lý Mã nguồn Git / GitHub (3 Commits Chuẩn)', 92);
