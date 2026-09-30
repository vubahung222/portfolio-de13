# CẨM NANG VẤN ĐÁP BẢO VỆ ĐỒ ÁN ĐẠT ĐIỂM 10 TUYỆT ĐỐI
## MÔN: TRIỂN KHAI VÀ QUẢN TRỊ HỆ THỐNG PHẦN MỀM
**Đề tài:** Đề Số 13 — Website Portfolio / Giới thiệu Cá nhân (Docker Stack)  
**Sinh viên:** Vũ Bá Hùng — **MSSV:** DTC245180186  

---

### Câu 1: Tại sao hệ thống cần sử dụng Nginx Reverse Proxy mà không mở cổng trực tiếp của PHP-FPM hay MySQL ra ngoài?
- **Trả lời:**
  1. **Bảo mật (Security):** Ẩn hoàn toàn cấu trúc mạng nội bộ và các cổng dịch vụ nhạy cảm (PHP-FPM port 9000, MySQL port 3306). Kẻ tấn công từ ngoài Internet không thể quét thấy hay khai thác trực tiếp lỗ hổng của CSDL.
  2. **SSL/TLS Termination:** Tập trung việc giải mã và quản lý chứng chỉ HTTPS tại một điểm duy nhất (Nginx), giảm tải xử lý mã hóa cho ứng dụng PHP backend.
  3. **Security Headers & Chuyển hướng:** Tự động ép buộc chuyển hướng HTTP cổng 80 sang HTTPS 443 (Redirect 301) và gắn 7 HTTP Security Headers chuẩn OWASP (HSTS, CSP, X-Frame-Options...) bảo vệ toàn bộ ứng dụng đằng sau.
  4. **Cân bằng tải & Định tuyến:** Nginx có thể định tuyến nhiều dịch vụ trên cùng tên miền/port 443 (ví dụ: `/` vào PHP web, `/pma/` vào phpMyAdmin).

---

### Câu 2: Cơ chế Network Isolation (Cách ly Mạng) được triển khai như thế nào? Cờ `internal: true` có ý nghĩa gì?
- **Trả lời:**
  - Hệ thống chia thành 3 mạng bridge độc lập: `frontend`, `backend`, và `monitoring`.
  - Mạng `backend` được thiết lập thuộc tính **`internal: true`**.
  - **Ý nghĩa kỹ thuật:** Docker daemon sẽ cấu hình các quy tắc `iptables` trên máy chủ Host để chặn hoàn toàn mọi lưu lượng định tuyến ra khỏi mạng này (không có Default Gateway ra Internet). 
  - **Lợi ích an ninh:** Container `mysql` và `app` trong mạng backend **hoàn toàn không thể kết nối ra ngoài Internet**. Ngay cả khi hacker chiếm được quyền thực thi mã trong CSDL, chúng cũng không thể tải mã độc từ xa về (malware drop) hoặc gửi trộm dữ liệu ra máy chủ C2 (data exfiltration).

---

### Câu 3: Tại sao cần chạy Container dưới quyền Non-root và cờ `no-new-privileges:true` có tác dụng gì?
- **Trả lời:**
  - Mặc định các process trong container Docker chạy với user `root` (UID 0), tương ứng với UID 0 của máy chủ Host. Nếu xảy ra lỗ hổng Container Escape (vượt ngục container), hacker sẽ chiếm ngay quyền root của máy chủ vật lý.
  - Hệ thống đã ép các container chạy dưới quyền user giới hạn:
    - `app` (PHP-FPM): chạy với user `www-data` (UID 82).
    - `prometheus`: chạy với user `nobody` (UID 65534).
    - `loki`: chạy với user UID `10001`.
  - Cờ an ninh **`security_opt: [no-new-privileges:true]`**: Ngăn chặn các tiến trình bên trong container nhận thêm các quyền hạn mới thông qua các cơ chế `setuid` hoặc `setgid` (triệt tiêu kỹ thuật leo thang đặc quyền Local Privilege Escalation).

---

### Câu 4: Phân biệt vai trò của cAdvisor và Node Exporter trong hệ thống giám sát Prometheus?
- **Trả lời:**
  - **Node Exporter:** Giám sát **tài nguyên mức Hệ điều hành / Máy chủ Host vật lý** (Tổng CPU của máy, Tổng RAM vật lý, Ổ cứng Disk I/O, Network interfaces của máy chủ).
  - **cAdvisor (Container Advisor):** Giám sát **tài nguyên chi tiết của từng Container Docker** (Container `portfolio_app` đang ăn bao nhiêu % CPU, `portfolio_mysql` đang chiếm bao nhiêu MB RAM, lượng byte mạng truyền nhận của từng container).
  - Kết hợp cả hai giúp quản trị viên vừa thấy bức tranh tổng quan của máy chủ, vừa chỉ đích danh container nào đang chiếm dụng tài nguyên bất thường.

---

### Câu 5: Trình bày cơ chế thu thập metrics của Prometheus và ý nghĩa của 5 Exporters?
- **Trả lời:**
  - Prometheus hoạt động theo mô hình **Pull Model**: Định kỳ mỗi 15 giây (`scrape_interval: 15s`), máy chủ Prometheus gửi HTTP GET request tới endpoint `/metrics` của từng exporter để kéo dữ liệu chuỗi thời gian (time-series).
  - Hệ thống tích hợp đủ **5 Exporters**:
    1. `prometheus`: Tự giám sát độ trễ và dung lượng cơ sở dữ liệu TSDB nội tại.
    2. `cadvisor:8080`: Thu thập chỉ số tài nguyên của 13 containers.
    3. `node-exporter:9100`: Thu thập chỉ số phần cứng Host OS.
    4. `mysql-exporter:9104`: Thu thập số truy vấn/s, kết nối active, InnoDB buffer pool của MySQL.
    5. `nginx-exporter:9113`: Đọc trạng thái `stub_status` của Nginx (active connections, requests processed).

---

### Câu 6: Hệ thống thu thập Log tập trung bằng cơ chế nào?
- **Trả lời:**
  - Không bắt từng ứng dụng phải can thiệp mã nguồn để ghi log rải rác.
  - Thay vào đó, **Promtail** được gắn kết trực tiếp với Docker Daemon Socket qua đường dẫn `/var/run/docker.sock` ở chế độ chỉ đọc (`:ro`).
  - Cơ chế **Docker Service Discovery** của Promtail tự động lắng nghe Docker API, tự động phát hiện mọi container đang chạy, bắt trọn các dòng log xuất ra luồng chuẩn `stdout` / `stderr`.
  - Promtail gắn nhãn metadata (tên container, compose service, log stream) rồi nén gửi qua HTTP tới máy chủ **Grafana Loki** để lưu trữ và lập chỉ mục (indexing).

---

### Câu 7: Hãy viết và giải thích 3 câu truy vấn LogQL thực tế?
- **Trả lời:**
  1. `{container_name="portfolio_nginx"}`:  
     *Ý nghĩa:* Lọc toàn bộ nhật ký truy cập (access log) và lỗi của Web Server Nginx.
  2. `{container_name=~".+"} |= "error"`:  
     *Ý nghĩa:* Tìm kiếm toàn bộ hệ thống các dòng log có chứa chuỗi `"error"` (không phân biệt hoa thường).
  3. `rate({container_name="portfolio_nginx"}[1m])`:  
     *Ý nghĩa:* Chuyển đổi luồng log thành biểu đồ số lượng log sinh ra mỗi giây trong khoảng thời gian trượt 1 phút (tương tự như PromQL).

---

### Câu 8: Tại sao lại dùng Grafana Provisioning thay vì cấu hình thủ công trên giao diện?
- **Trả lời:**
  - Áp dụng nguyên lý **Infrastructure as Code (IaC)**.
  - Toàn bộ nguồn dữ liệu (Prometheus, Loki) và Dashboard giám sát 13 panels đều được định nghĩa sẵn trong các tệp YAML/JSON tại thư mục `grafana/provisioning/`.
  - **Lợi ích:** Khi chạy `docker compose up`, Grafana tự động khởi tạo đầy đủ Dashboard và kết nối Database mà **không cần con người phải click chuột cấu hình bằng tay**, đảm bảo tính nhất quán 100% khi nhân bản hệ thống sang server mới.

---

### Câu 9: Trình bày cơ chế phòng vệ SQL Injection và CSRF trong ứng dụng PHP?
- **Trả lời:**
  - **Chống SQL Injection:** 100% câu truy vấn CSDL đều sử dụng thư viện **PDO với Prepared Statements** (sử dụng dấu `?` hoặc `:param` để ràng buộc kiểu dữ liệu). Dữ liệu nhập từ người dùng không bao giờ được nối trực tiếp vào chuỗi SQL.
  - **Chống CSRF:** Mỗi phiên đăng nhập sinh ra một mã token ngẫu nhiên bảo mật cao bằng `bin2hex(random_bytes(32))` lưu trong `$_SESSION`. Mọi form gửi dữ liệu (POST) bắt buộc phải kèm trường ẩn `csrf`. Server kiểm tra token bằng hàm so sánh an toàn `hash_equals()` trước khi xử lý.

---

### Câu 10: Cơ chế Dual-Mode Database Fallback trong code PHP hoạt động như thế nào?
- **Trả lời:**
  - Trong tệp `app/src/db.php`, hàm `db()` sử dụng khối lệnh `try...catch`:
    - Đầu tiên cố gắng kết nối tới MySQL container qua biến môi trường `DB_HOST=mysql`, cổng 3306 (với timeout ngắn 2 giây).
    - Nếu chạy trên môi trường máy trạm không có Docker hoặc MySQL chưa sẵn sàng, hàm `catch (PDOException)` lập tức tự động kích hoạt **SQLite Fallback**, tự động tạo tệp `app/data/portfolio.db` và nạp sẵn schema + seed dữ liệu.
  - Nhờ đó, ứng dụng có thể test tức thì ở mọi môi trường mà không bao giờ bị sập (Fatal error).

---

### Câu 11: Mật khẩu Admin được lưu trữ như thế nào? Tại sao không dùng MD5 hay SHA256?
- **Trả lời:**
  - Mật khẩu được mã hóa bằng hàm chuẩn của PHP: `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])`.
  - **Không dùng MD5/SHA256 vì:** MD5 và SHA256 là các hàm băm nhanh (fast hash), tốc độ tính toán hàng tỷ hash/giây trên card đồ họa (GPU) nên rất dễ bị bẻ khóa bằng bảng tra cứu trước (Rainbow Table) hoặc tấn công vét cạn (Brute Force).
  - **Bcrypt:** Là thuật toán băm chậm thích ứng (slow adaptive key derivation function), tự động thêm muối ngẫu nhiên (salt) và có thể tăng độ khó (cost factor), triệt tiêu hoàn toàn tấn công Rainbow Table.

---

### Câu 12: Hãy kể tên 7 Security Headers được cấu hình trong Nginx và tác dụng của từng header?
- **Trả lời:**
  1. `Strict-Transport-Security (HSTS)`: Ép buộc trình duyệt chỉ kết nối bằng HTTPS trong 1 năm.
  2. `X-Frame-Options: SAMEORIGIN`: Ngăn chặn nhúng website vào thẻ `<iframe>` của web khác (chống Clickjacking).
  3. `X-Content-Type-Options: nosniff`: Chặn trình duyệt tự đoán sai định dạng tệp (MIME Sniffing).
  4. `X-XSS-Protection: 1; mode=block`: Bật bộ lọc chống Cross-site Scripting của trình duyệt.
  5. `Referrer-Policy: strict-origin-when-cross-origin`: Bảo vệ rò rỉ URL nội bộ khi chuyển trang.
  6. `Content-Security-Policy (CSP)`: Giới hạn nguồn gốc tải mã script và tài nguyên hợp lệ.
  7. `server_tokens off`: Ẩn chuỗi số phiên bản Nginx trong HTTP response header để hacker không thể dò tìm CVE phiên bản.
