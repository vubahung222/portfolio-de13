<?php
/**
 * Database connection + first-run admin seeding.
 * Returns a shared PDO instance (MySQL with SQLite fallback for local preview).
 */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = getenv('DB_HOST');

    // Chỉ kết nối MySQL nếu biến môi trường DB_HOST được khai báo rõ ràng (trong Docker Compose)
    if (!empty($dbHost)) {
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'portfolio';
        $user = getenv('DB_USER') ?: 'portfolio_app';
        $pass = getenv('DB_PASS') ?: '';

        try {
            $dsn = "mysql:host={$dbHost};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2,
            ]);
            seed_admin($pdo);
            return $pdo;
        } catch (PDOException $e) {
            // Nếu lỗi kết nối MySQL thì tự động chuyển sang SQLite bên dưới
        }
    }

    // Chế độ Local Dev tức thì (SQLite) - Siêu nhanh 0.001s, không delay mạng
    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    $sqliteFile = $dataDir . '/portfolio.db';
    $isNew = !file_exists($sqliteFile);
    $pdo = new PDO("sqlite:{$sqliteFile}", null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if ($isNew) {
        init_sqlite($pdo);
    }
    seed_admin($pdo);
    return $pdo;
}

/**
 * Initialize SQLite database schema and seed data when running in local dev mode.
 */
function init_sqlite(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS profile (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            fullname TEXT NOT NULL,
            title    TEXT NOT NULL,
            bio      TEXT,
            email    TEXT,
            github   TEXT,
            linkedin TEXT
        );
        CREATE TABLE IF NOT EXISTS projects (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            title       TEXT NOT NULL,
            description TEXT,
            link        TEXT,
            tech        TEXT,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS skills (
            id    INTEGER PRIMARY KEY AUTOINCREMENT,
            name  TEXT NOT NULL,
            level INTEGER NOT NULL DEFAULT 50
        );
        CREATE TABLE IF NOT EXISTS messages (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT NOT NULL,
            email      TEXT NOT NULL,
            body       TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

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
    ");
}

/**
 * Seed the default admin account on first run (idempotent).
 * Default credentials: admin / Admin@12345  -> change after first login.
 */
function seed_admin(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $username = getenv('ADMIN_USER') ?: 'admin';
        $hash = password_hash('Admin@12345', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
    }
}

