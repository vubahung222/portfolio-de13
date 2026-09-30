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
('DTC245180186', 'Lập trình viên Full-stack & Chuyên gia DevOps',
 'Sinh viên ngành Công nghệ Thông tin, đam mê triển khai và quản trị hệ thống phần mềm. Yêu thích Docker, CI/CD, giám sát hệ thống và bảo mật.',
 'dtc245180186@ictu.edu.vn', 'https://github.com/DTC245180186', 'https://linkedin.com/in/DTC245180186');

INSERT INTO projects (title, description, link, tech) VALUES
('Hệ thống giám sát Docker', 'Triển khai Prometheus + Grafana + Loki giám sát toàn bộ container trong stack Docker Compose.', 'https://github.com/DTC245180186/portfolio-de13', 'Docker, Prometheus, Grafana, Loki'),
('Website Portfolio cá nhân', 'Website giới thiệu cá nhân có trang quản trị nội dung, tích hợp MySQL và phpMyAdmin.', 'https://github.com/DTC245180186/portfolio-de13', 'PHP, MySQL, Nginx, Docker'),
('DevOps Pipeline CI/CD', 'Xây dựng pipeline tự động hóa deploy ứng dụng web lên server với GitHub Actions.', 'https://github.com/DTC245180186', 'GitHub Actions, Docker, Nginx');

INSERT INTO skills (name, level) VALUES
('Docker & Compose', 85),
('Nginx / Reverse Proxy', 80),
('Prometheus & Grafana', 75),
('Loki + Promtail (Log)', 72),
('PHP / MySQL', 78),
('Quản trị Linux', 82),
('Bảo mật & Hardening', 70),
('Git / GitHub', 88);
