# KỊCH BẢN THUYẾT TRÌNH BẢO VỆ ĐỀ TÀI SỐ 13 (5 PHÚT ĂN TRỌN ĐIỂM 10)

> **Môn học:** Triển khai và Quản trị Hệ thống Phần mềm  
> **Sinh viên:** Vũ Bá Hùng — **Mã số sinh viên:** `DTC245180186`  
> **Trường:** Đại học Công nghệ Thông tin và Truyền thông (ICTU)  
> **GitHub Repository:** [https://github.com/vubahung222/portfolio-de13](https://github.com/vubahung222/portfolio-de13)

---

## ⏱️ PHÂN BỔ THỜI GIAN VÀ KỊCH BẢN NÓI

### 🕒 PHÚT 0:00 – 1:00: GIỚI THIỆU BẢN THÂN VÀ TỔNG QUAN KIẾN TRÚC
* **Hành động:** Chiếu **Slide 1 & Slide 3** (hoặc mở Sơ đồ Kiến trúc `docs/assets/architecture_diagram.png`).
* **Lời nói mẫu:**
  > *"Em xin kính chào quý thầy cô trong Hội đồng! Em là Vũ Bá Hùng, sinh viên lớp Công nghệ Thông tin, MSSV: `DTC245180186`. Hôm nay em xin phép trình bày và bảo vệ kết quả thực hiện Đề tài số 13: **Xây dựng và Quản trị toàn diện Hệ thống Website Portfolio Cá nhân**.*
  >
  > *Toàn bộ hệ thống được em thiết kế theo kiến trúc Microservices hướng production trên Docker Compose với 10 container độc lập, phân tách thành 2 vùng mạng an toàn: Mạng **frontend** cho các dịch vụ công khai và mạng **backend** (`internal: true`) cô lập hoàn toàn CSDL MySQL và hệ thống giám sát khỏi Internet. Mọi truy cập của người dùng đều bắt buộc đi qua Nginx Reverse Proxy mã hóa HTTPS TLS 1.2/1.3."*

---

### 🕒 PHÚT 1:00 – 2:00: DEMO WEB PORTFOLIO VÀ CSDL MYSQL (TIÊU CHÍ 1 & 2)
* **Hành động:**
  1. Mở trình duyệt tại: `http://localhost:8080` (hoặc `https://localhost:443`).
  2. Bấm vào các **Filter Tabs** dự án (Tất cả, 🐳 Docker & DevOps, 🌐 Web & PHP, 📊 Giám sát & Logs).
  3. Mở tab mới vào Admin: `http://localhost:8080/admin/` -> Đăng nhập tài khoản `admin` / `admin123`.
  4. Mở phpMyAdmin: `http://localhost:8081` chỉ vào 5 bảng (`admins`, `profile`, `projects`, `skills`, `messages`).
* **Lời nói mẫu:**
  > *"Trước tiên về ứng dụng web và CSDL: Em đã xây dựng một website Portfolio cá nhân với giao diện White Theme hiện đại, chuẩn SaaS. Trang chủ tích hợp bộ lọc danh mục dự án tương tác động. Trang quản trị Admin Dashboard 2 cột cho phép CRUD thông tin cá nhân, kỹ năng, dự án, đổi mật khẩu và xem hộp thư liên hệ.*
  >
  > *Về cơ sở dữ liệu, em sử dụng MySQL 8.0 chuẩn hóa 3NF gồm 5 bảng quan hệ và công cụ phpMyAdmin tại cổng 8081 để quản trị trực quan. Dữ liệu được bảo toàn vĩnh viễn qua Named Volume `mysql_data`."*

---

### 🕒 PHÚT 2:00 – 3:00: DEMO NGINX REVERSE PROXY VÀ SECURITY HEADERS (TIÊU CHÍ 3)
* **Hành động:**
  1. Nhấn `F12` trên trình duyệt -> Tab **Network** -> F5 tải lại trang.
  2. Click vào request đầu tiên `localhost` -> Tab **Headers** -> Chỉ vào mục **Response Headers**.
  3. Nhấn thử gõ `http://localhost:80` để thấy tự động redirect 301 sang `https://localhost:443`.
* **Lời nói mẫu:**
  > *"Tiêu chí thứ 3 là Nginx Reverse Proxy: Em cấu hình Nginx đóng vai trò Single Point of Ingress. Hệ thống tự động chuyển hướng toàn bộ traffic HTTP cổng 80 sang HTTPS cổng 443 bằng mã chuyển hướng 301 Moved Permanently.*
  >
  > *Như thầy cô thấy trên màn hình F12, em đã kích hoạt đủ **7 Security Headers chuẩn OWASP**: `Strict-Transport-Security` (HSTS) ép buộc HTTPS trong 1 năm, `X-Frame-Options: SAMEORIGIN` chống Clickjacking, `X-Content-Type-Options: nosniff` chống MIME-sniffing, cùng `Content-Security-Policy` và tắt hoàn toàn `server_tokens` để giấu phiên bản máy chủ."*

---

### 🕒 PHÚT 3:00 – 4:00: DEMO HỆ THỐNG GIÁM SÁT PROMETHEUS & LOG LOKI (TIÊU CHÍ 4 & 5)
* **Hành động:**
  1. Mở Prometheus: `http://localhost:9090/targets` -> Cho thấy cả 5 targets đều màu xanh `UP (5/5)`.
  2. Mở Grafana: `http://localhost:3000` -> Mở Dashboard `Portfolio System Overview` (13 panels).
  3. Mở Grafana -> **Explore** -> Chọn Data Source `Loki` -> Chạy thử truy vấn LogQL:  
     `{container="portfolio_nginx"} |~ "HTTP/[0-9.]+ [2-5][0-9]{2}"`
* **Lời nói mẫu:**
  > *"Về hệ thống giám sát và log tập trung: Prometheus tự động thu thập số liệu mỗi 15 giây từ **5 Exporters**: cAdvisor đo CPU/RAM từng container, Node Exporter đo OS host, Nginx Exporter đo lượt truy cập, MySQL Exporter đo QPS/Slow Queries, và Prometheus tự giám sát. Tất cả 5 Target đều đạt trạng thái UP 100%.*
  >
  > *Trên Grafana, em xây dựng Dashboard 13 Panels hiển thị trực quan toàn bộ sức khỏe hệ sinh thái. Đồng thời, hệ thống Loki kết hợp với Promtail đọc trực tiếp Docker Socket `/var/run/docker.sock` để gom log tập trung. Em đã chuẩn bị sẵn 10 câu truy vấn LogQL từ cơ bản đến phân tích lỗi HTTP 5xx và phát hiện dò quét lỗ hổng."*

---

### 🕒 PHÚT 4:00 – 5:00: MINH CHỨNG HARDENING VÀ CHẠY TEST TỰ ĐỘNG (TIÊU CHÍ 6 & 7)
* **Hành động:**
  1. Mở file `KIEM_THU_HE_THONG.bat` (hoặc chạy lệnh `python test_system.py`).
  2. Cho Hội đồng xem kết quả màu xanh lá: **10/10 TEST CASES PASSED (100%) - XẾP LOẠI XUẤT SẮC**.
  3. Mở trang GitHub: `https://github.com/vubahung222/portfolio-de13` cho thấy đúng **03 commit** phân tầng rõ ràng.
* **Lời nói mẫu:**
  > *"Về bảo mật Hardening: Hệ thống áp dụng 9 lớp phòng thủ: Non-root UID 1000 cho Web App, cấm leo thang đặc quyền `no-new-privileges`, cách ly mạng `backend: internal: true`, mã hóa mật khẩu Bcrypt cost=12, và Anti-CSRF Token động.*
  >
  > *Để đảm bảo độ tin cậy tuyệt đối, em đã xây dựng kịch bản kiểm thử tự động `test_system.py`. Kết quả vượt qua 10/10 ca kiểm thử với xếp loại Xuất sắc. Toàn bộ mã nguồn và tài liệu báo cáo Word 15 trang được em đồng bộ trên GitHub tại repository `vubahung222/portfolio-de13` với đúng 03 commit phân tầng theo đúng yêu cầu đề tài.*
  >
  > *Em xin chân thành cảm ơn quý thầy cô đã lắng nghe và em sẵn sàng trả lời các câu hỏi vấn đáp của Hội đồng ạ!"*

---

## 💡 BẬT MÍ 5 CÂU HỎI THẦY CÔ HAY HỎI NHẤT VÀ CÁCH TRẢ LỜI NGẮN GỌN

| STT | Câu hỏi của Giảng viên | Cách trả lời "Ăn trọn điểm 10" |
| :---: | :--- | :--- |
| **1** | *Tại sao MySQL không cần mở port ra ngoài máy host?* | **"Dạ thưa thầy, MySQL nằm trong mạng `backend` với thuộc tính `internal: true`. Chỉ container Web và phpMyAdmin nằm cùng mạng mới được phép kết nối qua port nội bộ 3306. Việc không mở port 3306 ra ngoài máy host giúp loại bỏ 100% nguy cơ bị tấn công brute-force mật khẩu hoặc quét cổng từ Internet ạ."** |
| **2** | *Promtail lấy log của các container bằng cách nào?* | **"Dạ, Promtail được mount trực tiếp Unix socket của Docker Daemon tại đường dẫn `/var/run/docker.sock`. Qua socket này, Promtail tự động lắng nghe và stream toàn bộ stdout/stderr của tất cả container rồi gán nhãn `container_name` trước khi push về Loki ạ."** |
| **3** | *Tại sao lại dùng HTTPS tự ký thay vì HTTP thông thường?* | **"Dạ, HTTPS sử dụng giao thức TLS 1.2/1.3 để mã hóa toàn bộ dữ liệu truyền giữa trình duyệt và Nginx trên đường truyền, ngăn chặn kẻ xấu nghe lén (Sniffing) hoặc tấn công Man-In-The-Middle đánh cắp cookie/mật khẩu ạ."** |
| **4** | *Nếu container Web bị sập thì dữ liệu bài viết có bị mất không?* | **"Dạ không ạ! Toàn bộ dữ liệu được lưu trữ trong Named Volume `mysql_data` của MySQL và thư mục persistent trên Host, độc lập hoàn toàn với vòng đời của container. Khi container khởi động lại, dữ liệu vẫn giữ nguyên 100% ạ."** |
| **5** | *Ý nghĩa của cờ `no-new-privileges:true` trong docker-compose là gì?* | **"Dạ, cờ này ngăn chặn các tiến trình bên trong container nâng quyền thông qua các file có cờ `setuid` hoặc `setgid`, đảm bảo rằng kẻ tấn công dù có chèn được mã độc cũng không thể leo thang lên quyền root của container hay máy host ạ."** |
