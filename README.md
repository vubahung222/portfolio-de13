# Đề 13 — Website Portfolio / Giới thiệu Cá nhân

> **Môn:** Triển khai và Quản trị Hệ thống Phần mềm  
> **MSSV:** DTC245180186  
> **Repository GitHub:** https://github.com/DTC245180186/portfolio-de13

Website portfolio cá nhân có **trang admin quản lý nội dung**, triển khai hoàn toàn
bằng **Docker Compose**, kèm hệ thống **reverse proxy (Nginx + HTTPS)**, **giám sát
(Prometheus + Grafana)**, **log tập trung (Loki + Promtail)** và các biện pháp
**hardening**.

---

## 1. Kiến trúc hệ thống

```
                         Internet / Browser
                                │  (HTTPS 443, HTTP 80 → redirect)
                        ┌───────▼────────┐
                        │     Nginx      │  reverse proxy + TLS + security headers
                        └───┬───────┬────┘
             /  , *.php     │       │   /pma/
                  ┌─────────▼──┐  ┌─▼──────────────┐
                  │  app (PHP) │  │  phpMyAdmin     │
                  │  php-fpm   │  └─────┬───────────┘
                  └──────┬─────┘        │
                         │   (network: backend, internal)
                     ┌───▼────────────────▼───┐
                     │        MySQL 8.0        │
                     └─────────────────────────┘

  Monitoring (network: monitoring)
   Prometheus ─ scrape ─► cAdvisor, node-exporter, mysql-exporter, nginx-exporter
   Grafana    ─ đọc ────► Prometheus (metrics) + Loki (logs)
   Promtail   ─ đẩy log ► Loki
```

### Mạng (network isolation)
| Network      | Mục đích                                   | internal |
|--------------|--------------------------------------------|----------|
| `frontend`   | Nginx ⇄ phpMyAdmin, Grafana                | không    |
| `backend`    | app ⇄ MySQL ⇄ exporter (không ra internet) | **có**   |
| `monitoring` | Prometheus, Grafana, Loki, exporters       | không    |

### Danh sách service
| Service          | Image                                   | Vai trò                         |
|------------------|-----------------------------------------|---------------------------------|
| `nginx`          | nginx:1.27-alpine                       | Reverse proxy + HTTPS           |
| `app`            | php:8.3-fpm-alpine (build)              | Ứng dụng portfolio + admin      |
| `mysql`          | mysql:8.0                               | Cơ sở dữ liệu                   |
| `phpmyadmin`     | phpmyadmin:5.2                          | Công cụ quản lý DB              |
| `prometheus`     | prom/prometheus                         | Thu thập metrics                |
| `grafana`        | grafana/grafana                         | Dashboard giám sát + log        |
| `cadvisor`       | cadvisor                                | Metrics container               |
| `node-exporter`  | prom/node-exporter                      | Metrics host                    |
| `mysql-exporter` | prom/mysqld-exporter                    | Metrics MySQL                   |
| `nginx-exporter` | nginx/nginx-prometheus-exporter         | Metrics Nginx                   |
| `loki`           | grafana/loki                            | Lưu trữ log tập trung           |
| `promtail`       | grafana/promtail                        | Thu thập log container → Loki   |

---

## 2. Yêu cầu môi trường
- Docker Engine + Docker Compose v2
- OpenSSL (để tạo chứng chỉ tự ký). Trên Windows dùng OpenSSL của Git for Windows.

---

## 3. Hướng dẫn chạy

### Bước 1 — Tạo file cấu hình môi trường
```bash
cp env.template .env
# Mở .env và ĐỔI TẤT CẢ mật khẩu sang giá trị mạnh của riêng bạn
```

### Bước 2 — Tạo chứng chỉ HTTPS tự ký
```bash
# Linux / macOS
bash nginx/gen-certs.sh

# Windows (PowerShell)
powershell -ExecutionPolicy Bypass -File nginx\gen-certs.ps1
```

> **Hoặc dùng file bat tự động:** Double-click vào `start.bat` — nó sẽ tự kiểm tra
> Docker, tạo .env nếu chưa có và khởi động toàn bộ stack.

### Bước 3 — Khởi động toàn bộ hệ thống
```bash
docker compose up -d --build
```

### Bước 4 — Truy cập
| Thành phần        | URL                                   | Ghi chú                               |
|-------------------|---------------------------------------|---------------------------------------|
| Website portfolio | https://localhost/                    | Chấp nhận cảnh báo cert tự ký          |
| Trang admin       | https://localhost/admin/index.php     | `admin` / `Admin@12345` (đổi ngay)     |
| phpMyAdmin        | https://localhost/pma/                | user/pass MySQL trong `.env`           |
| Grafana           | http://localhost:3000                 | tài khoản trong `.env`                 |
| Prometheus        | http://localhost:9090                 |                                        |

---

## 4. Ba mốc commit theo yêu cầu đề bài
| Commit   | Nội dung                                                              |
|----------|----------------------------------------------------------------------|
| Commit 1 | Ứng dụng + MySQL + phpMyAdmin + **Nginx reverse proxy (HTTPS)**       |
| Commit 2 | Tích hợp **Prometheus + Grafana** (cAdvisor, node/mysql/nginx exporter)|
| Commit 3 | Tích hợp **Loki + Promtail**, truy vấn **LogQL** (xem `docs/LOGQL.md`) |

Hardening được áp dụng xuyên suốt (mô tả ở mục 6).

---

## 5. Giám sát (Prometheus + Grafana)
- Prometheus scrape 5 nguồn: chính nó, `cadvisor`, `node-exporter`,
  `mysql-exporter`, `nginx-exporter` (xem `prometheus/prometheus.yml`).
- Grafana tự động nạp 2 datasource (Prometheus, Loki) và **1 dashboard**
  *"Portfolio System Overview"* gồm **13 panels**:
  - **Stat cards:** Containers đang chạy, MySQL status, Nginx req/s, MySQL queries/s, CPU% host, RAM% host
  - **Timeseries:** CPU theo container, RAM theo container, Nginx connections, MySQL queries/threads, Network I/O, Disk I/O
  - **Log panel:** Loki logs real-time tất cả container

---

## 6. Log tập trung (Loki + Promtail)
- Promtail thu thập log Docker qua `/var/run/docker.sock`
- Label tự động: `job="docker"`, `container="<tên>"`, `stream="stdout/stderr"`
- Xem các truy vấn LogQL mẫu trong [`docs/LOGQL.md`](docs/LOGQL.md)

**3 query cơ bản:**
```logql
# 1. Toàn bộ log tất cả container
{job="docker"}

# 2. Log Nginx, lọc lỗi 4xx/5xx
{container="portfolio_nginx"} |~ "\" [45][0-9]{2} "

# 3. Tốc độ log theo container
sum by (container) (rate({job="docker"}[1m]))
```

---

## 7. Hardening — các biện pháp đã áp dụng
1. **Non-root container**: `app` chạy bằng user `www-data`; Grafana (472),
   Prometheus (65534), Loki (10001) chạy bằng UID không đặc quyền.
2. **Network isolation**: network `backend` đặt `internal: true` → MySQL và app
   không truy cập được ra Internet; chỉ Nginx là điểm vào duy nhất.
3. **Mật khẩu mạnh**: toàn bộ credential nằm trong `.env` (không commit), template
   nhắc đổi mật khẩu; admin app buộc đổi mật khẩu ≥ 8 ký tự.
4. **Hạn chế quyền DB**: user ứng dụng chỉ có quyền trên DB `portfolio`; user
   giám sát (`exporter`) chỉ `SELECT/PROCESS/REPLICATION CLIENT` và giới hạn 3 kết nối.
5. **Security headers Nginx**: `X-Frame-Options`, `X-Content-Type-Options`,
   `Referrer-Policy`, `Strict-Transport-Security`, `Content-Security-Policy`,
   `X-XSS-Protection`, ẩn `server_tokens`.
6. **HTTPS**: TLS 1.2/1.3, tự động redirect HTTP → HTTPS.
7. **`no-new-privileges`** cho mọi container; source code mount **read-only**.
8. **Bảo mật ứng dụng**: prepared statements (chống SQL Injection), `password_hash`
   bcrypt, chống CSRF bằng token, session `HttpOnly` + `SameSite=Strict`,
   escape output (chống XSS), phpMyAdmin không mở cổng trực tiếp.

---

## 8. Cấu trúc thư mục
```
portfolio-de13/
├── docker-compose.yml
├── env.template            # copy thành .env
├── .gitignore
├── README.md
├── start.bat               # Script khởi động Windows
├── stop.bat                # Script dừng hệ thống
├── app/                    # ứng dụng PHP
│   ├── Dockerfile          # php:8.3-fpm-alpine, run as www-data
│   ├── src/                # db.php, auth.php
│   └── public/             # index.php, health.php, admin/, assets/
├── db/
│   ├── init.sql            # schema + seed data
│   └── 02-create-exporter-user.sh   # tạo MySQL monitoring user
├── nginx/
│   ├── conf.d/default.conf # reverse proxy + HTTPS + security headers
│   ├── certs/              # cert tự ký (được .gitignore)
│   ├── gen-certs.sh        # tạo cert Linux/macOS
│   └── gen-certs.ps1       # tạo cert Windows
├── prometheus/prometheus.yml
├── grafana/provisioning/
│   ├── datasources/datasources.yml  # Prometheus + Loki
│   └── dashboards/portfolio-overview.json   # 13-panel dashboard
├── loki/loki-config.yml
├── promtail/promtail-config.yml
└── docs/LOGQL.md           # 10 truy vấn LogQL mẫu
```

---

## 9. Dừng / dọn dẹp
```bash
docker compose down          # dừng, giữ dữ liệu
docker compose down -v       # dừng và XOÁ toàn bộ volume (mất dữ liệu)
```

---

## 10. Kiểm tra nhanh
```bash
docker compose ps                       # tất cả service Up/healthy
curl -k https://localhost/health.php    # {"status":"ok",...}
# Prometheus > Status > Targets: tất cả target UP
# Grafana > Dashboards: "Portfolio System Overview" có đủ 13 panel
# Grafana > Explore (Loki): chạy các query trong docs/LOGQL.md
```
