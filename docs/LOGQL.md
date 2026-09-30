# LogQL — Truy vấn log tập trung qua Loki

> **Grafana → Explore → chọn datasource Loki → chuyển sang mode Code → Dán query → Run query**
>
> Hệ thống thu thập log tất cả container qua Promtail với label:
> - `job="docker"` — tất cả container
> - `container="<tên_container>"` — theo từng container

---

## 1. Xem toàn bộ log tất cả container

```logql
{job="docker"}
```
**Mục đích:** Hiển thị real-time log của mọi container trong stack.

---

## 2. Xem log Nginx (reverse proxy)

```logql
{container="portfolio_nginx"}
```
**Mục đích:** Xem toàn bộ access log và error log của Nginx.

---

## 3. Lọc request lỗi 4xx/5xx trên Nginx

```logql
{container="portfolio_nginx"} |~ "\" [45][0-9]{2} "
```
**Mục đích:** Tìm tất cả HTTP request có status code 4xx hoặc 5xx.

---

## 4. Lọc log ứng dụng PHP có lỗi

```logql
{container="portfolio_app"} |~ "(?i)(error|warning|fatal)"
```
**Mục đích:** Tìm các dòng log có chứa error, warning hoặc fatal trong container PHP.

---

## 5. Đếm tốc độ log lỗi MySQL trong 5 phút

```logql
sum(rate({container="portfolio_mysql"} |~ "(?i)error" [5m]))
```
**Mục đích:** Đo tần suất lỗi MySQL (lỗi/giây), dùng trong panel Grafana dạng timeseries.

---

## 6. Top 5 container nhiều log nhất (1 phút)

```logql
topk(5, sum by (container) (count_over_time({job="docker"}[1m])))
```
**Mục đích:** Xem container nào sinh ra nhiều log nhất trong 1 phút, giúp phát hiện bất thường.

---

## 7. Tìm request đến trang admin

```logql
{container="portfolio_nginx"} |= "/admin"
```
**Mục đích:** Theo dõi tất cả lần truy cập vào khu vực admin.

---

## 8. Tốc độ log theo container (biểu đồ)

```logql
sum by (container) (rate({job="docker"}[1m]))
```
**Mục đích:** Tạo panel Grafana dạng timeseries thể hiện tốc độ sinh log của từng container.

---

## 9. Tìm kết nối bị từ chối hoặc timeout

```logql
{job="docker"} |~ "(?i)(connection refused|timeout|timed out)"
```
**Mục đích:** Phát hiện sự cố mạng nội bộ giữa các container.

---

## 10. Lọc log Promtail (kiểm tra log collector)

```logql
{container="portfolio_promtail"}
```
**Mục đích:** Xem trạng thái của Promtail — đảm bảo nó đang đẩy log lên Loki thành công.

---

## Hướng dẫn chạy trong Grafana

1. Mở Grafana tại **http://localhost:3000**
2. Đăng nhập (tài khoản trong `.env`)
3. Vào menu **Explore** (biểu tượng la bàn ở thanh trái)
4. Chọn **Datasource**: `Loki`
5. Chuyển sang chế độ **Code** (không phải Builder)
6. Dán query vào ô input
7. Nhấn **Run query** hoặc `Shift+Enter`

> **Tip demo báo cáo:** Chụp màn hình ít nhất 3 query (query 1, 3 và 6) để minh chứng Loki + LogQL hoạt động.
