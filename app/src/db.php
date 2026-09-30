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

    $host = getenv('DB_HOST') ?: 'mysql';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'portfolio';
    $user = getenv('DB_USER') ?: 'portfolio_app';
    $pass = getenv('DB_PASS') ?: '';

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ]);
        seed_admin($pdo);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback to SQLite for instant local development / preview without Docker
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

