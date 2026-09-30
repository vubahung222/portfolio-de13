"""
Tự động tạo báo cáo Word (.docx) cho Đề Số 13
Môn: Triển khai và Quản trị Hệ thống Phần mềm
MSSV: DTC245180186

Cài đặt: pip install python-docx
Chạy từ thư mục portfolio-de13: python generate_report.py
"""

from docx import Document
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn, nsdecls
from docx.oxml import parse_xml, OxmlElement
import os

# ─── Hằng số màu sắc ─────────────────────────────────────
NAVY      = "1A365D"
BLUE      = "2B6CB0"
DARK_GRAY = "2D3748"
WHITE     = "FFFFFF"
LIGHT_BG  = "F8FAFC"
ACCENT    = "2563EB"

def set_page_margins(doc):
    for section in doc.sections:
        section.top_margin    = Cm(2.0)
        section.bottom_margin = Cm(2.0)
        section.left_margin   = Cm(3.0)
        section.right_margin  = Cm(2.0)

def add_heading(doc, text, level=1):
    p = doc.add_heading(text, level=level)
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    if p.runs:
        run = p.runs[0]
        if level == 1:
            run.font.color.rgb = RGBColor(0x1A, 0x36, 0x5D)
            run.font.size = Pt(16)
        elif level == 2:
            run.font.color.rgb = RGBColor(0x2B, 0x6C, 0xB0)
            run.font.size = Pt(14)
        elif level == 3:
            run.font.color.rgb = RGBColor(0x2D, 0x37, 0x48)
            run.font.size = Pt(13)
    return p

def add_para(doc, text, bold=False, italic=False, size=12, align=WD_ALIGN_PARAGRAPH.JUSTIFY):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_before = Pt(3)
    p.paragraph_format.space_after  = Pt(6)
    run = p.add_run(text)
    run.font.name = "Times New Roman"
    run.font.size = Pt(size)
    run.font.bold   = bold
    run.font.italic = italic
    return p

def add_bullet(doc, text, size=12):
    p = doc.add_paragraph(style="List Bullet")
    p.paragraph_format.space_after = Pt(2)
    run = p.add_run(text)
    run.font.name = "Times New Roman"
    run.font.size = Pt(size)
    return p

def add_image(doc, image_path, caption_text="", width_inch=6.2):
    if os.path.exists(image_path):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(2)
        run = p.add_run()
        run.add_picture(image_path, width=Inches(width_inch))
        if caption_text:
            cp = doc.add_paragraph()
            cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
            cp.paragraph_format.space_before = Pt(2)
            cp.paragraph_format.space_after = Pt(8)
            crun = cp.add_run(caption_text)
            crun.font.name = "Times New Roman"
            crun.font.size = Pt(10)
            crun.font.italic = True
            crun.font.bold = True
            crun.font.color.rgb = RGBColor(0x2B, 0x6C, 0xB0)

def style_table_navy(table):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.style = "Table Grid"
    for cell in table.rows[0].cells:
        tcPr = cell._tc.get_or_add_tcPr()
        shd  = parse_xml(f'<w:shd {nsdecls("w")} w:val="clear" w:color="auto" w:fill="{NAVY}"/>')
        tcPr.append(shd)
        for p in cell.paragraphs:
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p.paragraph_format.space_before = Pt(2)
            p.paragraph_format.space_after  = Pt(2)
            for run in p.runs:
                run.font.bold  = True
                run.font.color.rgb = RGBColor(255, 255, 255)
                run.font.size  = Pt(11)
                run.font.name  = "Times New Roman"
    for i, row in enumerate(table.rows[1:], start=1):
        bg = "F8FAFC" if i % 2 == 1 else "FFFFFF"
        for cell in row.cells:
            tcPr = cell._tc.get_or_add_tcPr()
            shd  = parse_xml(f'<w:shd {nsdecls("w")} w:val="clear" w:color="auto" w:fill="{bg}"/>')
            tcPr.append(shd)
            for p in cell.paragraphs:
                p.paragraph_format.space_before = Pt(2)
                p.paragraph_format.space_after  = Pt(2)
                for run in p.runs:
                    run.font.size = Pt(11)
                    run.font.name = "Times New Roman"

def add_table(doc, headers, rows_data):
    t = doc.add_table(rows=1 + len(rows_data), cols=len(headers))
    for j, h in enumerate(headers):
        t.cell(0, j).text = h
    for i, row in enumerate(rows_data, start=1):
        for j, val in enumerate(row):
            t.cell(i, j).text = val
    style_table_navy(t)
    doc.add_paragraph()
    return t

def add_callout(doc, text, title="LUU Y:"):
    t = doc.add_table(rows=1, cols=1)
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = t.cell(0, 0)
    tcPr = cell._tc.get_or_add_tcPr()
    tcBorders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'  <w:top w:val="none"/>'
        f'  <w:left w:val="single" w:sz="36" w:space="0" w:color="{ACCENT}"/>'
        f'  <w:bottom w:val="none"/>'
        f'  <w:right w:val="none"/>'
        f'</w:tcBorders>'
    )
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:val="clear" w:color="auto" w:fill="EBF4FF"/>')
    tcPr.append(tcBorders)
    tcPr.append(shd)
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after  = Pt(4)
    r1 = p.add_run(f">> {title} ")
    r1.bold = True
    r1.font.color.rgb = RGBColor(37, 99, 235)
    r1.font.name = "Times New Roman"
    r2 = p.add_run(text)
    r2.font.size = Pt(11)
    r2.font.name = "Times New Roman"
    doc.add_paragraph()

def add_code(doc, text, size=9):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    run = p.add_run(text)
    run.font.name = "Courier New"
    run.font.size = Pt(size)
    doc.add_paragraph()

def build_report():
    doc = Document()
    set_page_margins(doc)
    style = doc.styles["Normal"]
    style.font.name = "Times New Roman"
    style.font.size = Pt(13)

    # ══════════════════════════════════════
    # TRANG BÌA
    # ══════════════════════════════════════
    def center_bold(text, size):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(text)
        r.font.bold = True; r.font.size = Pt(size); r.font.name = "Times New Roman"
        return p

    def center_text(text, size, italic=False, color=None):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(text)
        r.font.size = Pt(size); r.font.name = "Times New Roman"
        r.font.italic = italic
        if color: r.font.color.rgb = color
        return p

    center_bold("TRUONG DAI HOC CONG NGHE THONG TIN VA TRUYEN THONG", 14)
    center_bold("KHOA CONG NGHE THONG TIN", 13)
    doc.add_paragraph(); doc.add_paragraph()
    center_bold("BAO CAO THUC HANH", 16)
    center_bold("MON: TRIEN KHAI VA QUAN TRI HE THONG PHAN MEM", 14)
    doc.add_paragraph()
    p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("DE SO 13"); r.font.bold = True; r.font.size = Pt(22)
    r.font.color.rgb = RGBColor(0x2B, 0x6C, 0xB0); r.font.name = "Times New Roman"
    p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("Website Portfolio / Gioi Thieu Ca Nhan")
    r.font.bold = True; r.font.size = Pt(18); r.font.name = "Times New Roman"
    doc.add_paragraph(); doc.add_paragraph()
    for label, value in [
        ("Sinh vien:", "DTC245180186"),
        ("GitHub:", "https://github.com/DTC245180186/portfolio-de13"),
        ("Nam hoc:", "2025 - 2026"),
    ]:
        p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r1 = p.add_run(f"{label} "); r1.font.bold = True; r1.font.size = Pt(13); r1.font.name = "Times New Roman"
        r2 = p.add_run(value); r2.font.size = Pt(13); r2.font.name = "Times New Roman"
    doc.add_paragraph(); doc.add_paragraph()
    center_text("Thai Nguyen, thang 9 nam 2026", 13, italic=True)
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 1: TONG QUAN
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 1: TONG QUAN DE TAI", level=1)
    add_heading(doc, "1.1 Gioi thieu de tai", level=2)
    add_para(doc, "De So 13 yeu cau xay dung va trien khai hoan chinh mot website Portfolio / Gioi thieu Ca nhan su dung cong nghe Docker Compose ket hop voi he thong giam sat, thu thap log va cac bien phap bao mat theo chuan production.")
    add_para(doc, "He thong bao gom: Ung dung web PHP 8.3, co so du lieu MySQL 8.0, cong cu quan ly phpMyAdmin, reverse proxy Nginx voi HTTPS tu ky, he thong giam sat Prometheus + Grafana va he thong log tap trung Loki + Promtail. Tong cong 13 container duoc quan ly boi Docker Compose.")
    add_heading(doc, "1.2 Muc tieu", level=2)
    for g in [
        "Trien khai ung dung web hoan chinh voi Docker Compose (multi-container).",
        "Cau hinh Nginx lam reverse proxy voi HTTPS va security headers.",
        "Tich hop Prometheus + Grafana de giam sat containers, web server va database.",
        "Trien khai Loki + Promtail de thu thap va truy van log tap trung.",
        "Ap dung cac bien phap hardening: non-root container, network isolation, mat khau manh.",
        "Quan ly ma nguon tren GitHub voi 3 commit ro rang va README day du.",
    ]: add_bullet(doc, g)
    add_heading(doc, "1.3 Cong nghe su dung", level=2)
    add_table(doc, ["Cong nghe", "Phien ban", "Vai tro"], [
        ["PHP-FPM", "8.3-alpine", "Ung dung web backend"],
        ["MySQL", "8.0", "Co so du lieu quan he"],
        ["phpMyAdmin", "5.2", "Giao dien quan ly DB"],
        ["Nginx", "1.27-alpine", "Reverse proxy + HTTPS"],
        ["Prometheus", "v2.53.0", "Thu thap metrics"],
        ["Grafana", "11.1.0", "Dashboard giam sat"],
        ["cAdvisor", "v0.49.1", "Metrics container"],
        ["Node Exporter", "v1.8.1", "Metrics host OS"],
        ["MySQL Exporter", "v0.15.1", "Metrics MySQL"],
        ["Nginx Exporter", "1.1.0", "Metrics Nginx"],
        ["Loki", "3.1.0", "Luu tru log tap trung"],
        ["Promtail", "3.1.0", "Thu thap log Docker"],
        ["Docker Compose", "v5.x", "Orchestration"],
    ])
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 2: KIEN TRUC
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 2: KIEN TRUC HE THONG", level=1)
    add_heading(doc, "2.1 So do tong quan", level=2)
    add_para(doc, "He thong duoc thiet ke theo kien truc microservices, moi thanh phan chay trong container rieng biet va giao tiep qua Docker network noi bo. Nginx dong vai tro la diem vao duy nhat (single entry point) tu Internet.")
    diag_img = os.path.join(os.path.dirname(__file__), "docs", "assets", "architecture_diagram.png")
    add_image(doc, diag_img, "Hinh 2.1: So do kien truc tong the he thong Docker Compose va co lap mang (Network Isolation)")
    add_code(doc,
        "Internet / Browser\n"
        "       | (HTTPS :443, HTTP :80 -> redirect)\n"
        "  +----+--------+\n"
        "  |    Nginx    |  reverse proxy + TLS 1.2/1.3 + security headers\n"
        "  +--+------+---+\n"
        "     |      |\n"
        "  /,*.php  /pma/\n"
        "     |      |\n"
        "  +--+---+  +----------+\n"
        "  | PHP  |  | phpMyAdmin|\n"
        "  | FPM  |  +----+------+\n"
        "  +--+---+       |\n"
        "     |   backend (internal: true)\n"
        "     +------+----+\n"
        "            |\n"
        "     +------+-------+\n"
        "     |  MySQL 8.0  |\n"
        "     +-------------+\n\n"
        "Monitoring:\n"
        "Prometheus --scrape--> cAdvisor, node-exporter, mysql-exporter, nginx-exporter\n"
        "Grafana    --read----> Prometheus (metrics) + Loki (logs)\n"
        "Promtail   --push----> Loki"
    )
    add_heading(doc, "2.2 Phan vung mang (Network Isolation)", level=2)
    add_table(doc, ["Network", "Muc dich", "Internal?", "Thanh phan"], [
        ["frontend", "Traffic tu browser vao app", "Khong", "Nginx, phpMyAdmin, Grafana"],
        ["backend", "Giao tiep app <-> database noi bo", "Co", "app, MySQL, exporter"],
        ["monitoring", "Thu thap metrics va log", "Khong", "Prometheus, Grafana, Loki, exporters"],
    ])
    add_callout(doc,
        "Network 'backend' duoc dat internal: true - MySQL va PHP app KHONG the ket noi ra Internet. "
        "Chi Nginx moi la diem vao duy nhat tu ben ngoai. Day la bien phap network isolation quan trong.",
        "BAO MAT MANG:"
    )
    add_heading(doc, "2.3 Danh sach 13 container", level=2)
    add_table(doc, ["Service", "Image", "Port", "Vai tro"], [
        ["nginx", "nginx:1.27-alpine", "80, 443", "Reverse proxy + HTTPS"],
        ["app", "php:8.3-fpm-alpine (build)", "9000", "Portfolio + Admin"],
        ["mysql", "mysql:8.0", "-", "Co so du lieu"],
        ["phpmyadmin", "phpmyadmin:5.2", "-", "Quan ly DB"],
        ["prometheus", "prom/prometheus:v2.53.0", "9090", "Thu thap metrics"],
        ["grafana", "grafana/grafana:11.1.0", "3000", "Dashboard giam sat"],
        ["cadvisor", "gcr.io/cadvisor/cadvisor:v0.49.1", "-", "Metrics container"],
        ["node-exporter", "prom/node-exporter:v1.8.1", "-", "Metrics host"],
        ["mysql-exporter", "prom/mysqld-exporter:v0.15.1", "-", "Metrics MySQL"],
        ["nginx-exporter", "nginx/nginx-prometheus-exporter:1.1.0", "-", "Metrics Nginx"],
        ["loki", "grafana/loki:3.1.0", "-", "Log storage"],
        ["promtail", "grafana/promtail:3.1.0", "-", "Log collector"],
    ])
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 3: TRIEN KHAI UNG DUNG + DB (Commit 1)
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 3: TRIEN KHAI UNG DUNG + DATABASE (COMMIT 1)", level=1)
    add_callout(doc, "Commit 1: feat: PHP app + MySQL + phpMyAdmin + Nginx HTTPS reverse proxy", "COMMIT 1 --")
    add_heading(doc, "3.1 Ung dung PHP Portfolio", level=2)
    add_para(doc, "Ung dung PHP 8.3 chay tren PHP-FPM, giao tiep voi Nginx qua FastCGI. Dockerfile su dung image php:8.3-fpm-alpine. Ung dung chay voi user www-data (non-root, uid 82).")
    add_table(doc, ["Trang / Endpoint", "Chuc nang"], [
        ["/ (index.php)", "Hien thi portfolio: thong tin ca nhan, ky nang, du an, form lien he"],
        ["/admin/index.php", "Dang nhap admin voi CSRF token bao ve"],
        ["/admin/dashboard.php", "CRUD: quan ly profile, du an, ky nang; xem tin nhan; doi mat khau"],
        ["/health.php", "Health check endpoint tra ve JSON trang thai ket noi DB"],
    ])
    add_para(doc, "Bao mat ung dung:")
    for s in [
        "Prepared statements (PDO) chong SQL Injection toan bo query.",
        "password_hash() bcrypt de luu mat khau admin.",
        "CSRF token cho tat ca form POST.",
        "Session HttpOnly + SameSite=Strict.",
        "htmlspecialchars() escape output chong XSS.",
    ]: add_bullet(doc, s)

    add_heading(doc, "3.2 Co so du lieu MySQL 8.0", level=2)
    add_para(doc, "MySQL 8.0 khoi tao tu dong qua file db/init.sql khi container start lan dau. 5 bang chinh:")
    add_table(doc, ["Bang", "Mo ta", "Truong chinh"], [
        ["users", "Tai khoan quan tri (bcrypt hash)", "id, username, password_hash"],
        ["profile", "Thong tin ca nhan (1 dong)", "fullname, title, bio, email, github"],
        ["projects", "Danh sach du an", "title, description, link, tech"],
        ["skills", "Ky nang va muc do (0-100%)", "name, level"],
        ["messages", "Tin nhan lien he tu khach", "name, email, body, created_at"],
    ])
    add_para(doc, "Tai khoan admin duoc tao tu dong qua ham seed_admin() trong db.php khi ung dung khoi dong lan dau: username='admin', password='Admin@12345' (bcrypt hash).")
    add_para(doc, "User MySQL phan quyen: portfolio_app chi co SELECT/INSERT/UPDATE/DELETE tren DB portfolio. User exporter chi co SELECT/PROCESS/REPLICATION CLIENT voi MAX_USER_CONNECTIONS 3.")

    add_heading(doc, "3.3 phpMyAdmin", level=2)
    add_para(doc, "phpMyAdmin 5.2 duoc proxy qua Nginx tai /pma/ (khong mo cong truc tiep ra ngoai). Truy cap: https://localhost/pma/ -> dang nhap bang portfolio_app hoac root.")

    add_heading(doc, "3.4 Nginx Reverse Proxy + HTTPS", level=2)
    add_para(doc, "Nginx 1.27-alpine cau hinh file nginx/conf.d/default.conf voi 3 server block:")
    for b in [
        "Server block 1 (port 8080): stub_status cho nginx-exporter, chi cho phep Docker network.",
        "Server block 2 (port 80): Redirect tat ca HTTP request sang HTTPS (301).",
        "Server block 3 (port 443): HTTPS chinh, TLS 1.2/1.3, proxy PHP-FPM va phpMyAdmin.",
    ]: add_bullet(doc, b)
    add_table(doc, ["Security Header", "Gia tri"], [
        ["X-Frame-Options", "SAMEORIGIN"],
        ["X-Content-Type-Options", "nosniff"],
        ["Referrer-Policy", "no-referrer-when-downgrade"],
        ["X-XSS-Protection", "1; mode=block"],
        ["Strict-Transport-Security", "max-age=31536000; includeSubDomains"],
        ["Content-Security-Policy", "default-src 'self'; style-src 'self' 'unsafe-inline'"],
        ["server_tokens", "off (an phien ban Nginx)"],
    ])
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 4: GIAM SAT (Commit 2)
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 4: HE THONG GIAM SAT - PROMETHEUS + GRAFANA (COMMIT 2)", level=1)
    add_callout(doc, "Commit 2: feat: Tich hop Prometheus + Grafana (cAdvisor, node/mysql/nginx exporter)", "COMMIT 2 --")
    add_heading(doc, "4.1 Prometheus - Thu thap Metrics", level=2)
    add_para(doc, "Prometheus v2.53.0 scrape 5 nguon metrics voi interval 15 giay, luu du lieu 15 ngay (retention 15d):")
    pipe_img = os.path.join(os.path.dirname(__file__), "docs", "assets", "monitoring_pipeline.png")
    add_image(doc, pipe_img, "Hinh 4.1: Luong thu thap va xu ly du lieu giam sat Prometheus Metrics va Loki Logs tap trung")
    add_table(doc, ["Job name", "Target", "Loai metrics"], [
        ["prometheus", "localhost:9090", "Self-monitoring"],
        ["cadvisor", "cadvisor:8080", "CPU/RAM/Network/Disk theo container"],
        ["node", "node-exporter:9100", "CPU/RAM/Disk cua host OS"],
        ["mysql", "mysql-exporter:9104", "Queries/s, connections, InnoDB"],
        ["nginx", "nginx-exporter:9113", "Requests/s, connections, status"],
    ])
    add_para(doc, "Ket qua: Prometheus -> Status -> Targets: tat ca 5 target o trang thai UP.")
    add_para(doc, "Truy cap: http://localhost:9090")

    add_heading(doc, "4.2 Grafana - Dashboard giam sat", level=2)
    add_para(doc, "Grafana 11.1.0 duoc provisioning tu dong (khong can setup thu cong) voi 2 datasource va 1 dashboard chinh:")
    add_table(doc, ["Datasource", "URL", "Muc dich"], [
        ["Prometheus", "http://prometheus:9090", "Metrics container, host, DB, web"],
        ["Loki", "http://loki:3100", "Log tap trung tat ca container"],
    ])
    add_para(doc, "Dashboard 'Portfolio System Overview' gom 13 panels:", bold=True)
    for i, panel in enumerate([
        "Stat: So container dang chay",
        "Stat: MySQL status (UP/DOWN)",
        "Stat: Nginx requests/s",
        "Stat: MySQL queries/s",
        "Stat: CPU host %",
        "Stat: RAM host %",
        "Timeseries: CPU usage theo container",
        "Timeseries: RAM usage theo container",
        "Timeseries: Nginx connections",
        "Timeseries: MySQL queries va threads",
        "Timeseries: Network I/O",
        "Timeseries: Disk I/O",
        "Logs Panel: Loki logs real-time",
    ], 1): add_bullet(doc, f"Panel {i}: {panel}")
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 5: LOG TAP TRUNG (Commit 3)
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 5: HE THONG LOG TAP TRUNG - LOKI + PROMTAIL (COMMIT 3)", level=1)
    add_callout(doc, "Commit 3: feat: Tich hop Loki + Promtail log tap trung, them 10 LogQL query mau", "COMMIT 3 --")
    add_heading(doc, "5.1 Kien truc thu thap log", level=2)
    add_para(doc, "Docker Containers --> Promtail (doc qua Docker socket) --> Loki (luu tru) --> Grafana (truy van)")
    add_para(doc, "Promtail tu dong gan labels cho moi log stream:")
    for l in [
        'job="docker" -- tat ca container trong Docker stack.',
        'container="<ten_container>" -- ten container cu the.',
        'stream="stdout" hoac "stderr" -- luong output.',
    ]: add_bullet(doc, l)
    add_heading(doc, "5.2 Cau hinh Loki va Promtail", level=2)
    add_para(doc, "Loki 3.1.0 su dung local filesystem storage, schema v13 (TSDB), luu log 168h (7 ngay). Chay voi user 10001 (non-root).")
    add_para(doc, "Promtail 3.1.0 su dung Docker service discovery qua Unix socket, tu dong phat hien container moi. Cau hinh relabel_configs de trich xuat ten container va stream.")
    add_heading(doc, "5.3 Cac truy van LogQL mau (10 query)", level=2)
    add_para(doc, "Thuc hien trong Grafana -> Explore -> Datasource: Loki -> Mode: Code:")
    add_table(doc, ["#", "LogQL Query", "Muc dich"], [
        ["1", '{job="docker"}', "Xem toan bo log tat ca container real-time"],
        ["2", '{container="portfolio_nginx"}', "Log cua Nginx (access + error)"],
        ["3", '{container="portfolio_nginx"} |~ "\\" [45][0-9]{2} "', "Loc request loi HTTP 4xx/5xx"],
        ["4", '{container="portfolio_app"} |~ "(?i)(error|warning|fatal)"', "Loi trong ung dung PHP"],
        ["5", 'sum(rate({container="portfolio_mysql"} |~ "(?i)error" [5m]))', "Toc do loi MySQL 5 phut"],
        ["6", 'topk(5, sum by (container) (count_over_time({job="docker"}[1m])))', "Top 5 container nhieu log nhat"],
        ["7", '{container="portfolio_nginx"} |= "/admin"', "Theo doi truy cap admin"],
        ["8", 'sum by (container) (rate({job="docker"}[1m]))', "Toc do log theo container"],
        ["9", '{job="docker"} |~ "(?i)(connection refused|timeout)"', "Phat hien su co mang"],
        ["10", '{container="portfolio_promtail"}', "Kiem tra trang thai Promtail"],
    ])
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 6: HARDENING
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 6: HARDENING HE THONG BAO MAT", level=1)
    add_heading(doc, "6.1 Tong hop cac bien phap bao mat da ap dung (9 bien phap)", level=2)
    add_table(doc, ["#", "Bien phap", "Chi tiet"], [
        ["1", "Non-root container", "PHP-FPM: www-data (uid 82); Grafana: uid 472; Prometheus: uid 65534; Loki: uid 10001"],
        ["2", "Network isolation", "backend network: internal=true -> MySQL khong ra Internet; Nginx la diem vao duy nhat"],
        ["3", "Mat khau manh", "Toan bo credential trong .env (git-ignored); template nhac doi mat khau"],
        ["4", "Han che quyen DB", "portfolio_app chi co quyen tren DB portfolio; exporter chi SELECT/PROCESS/REPLICATION CLIENT"],
        ["5", "Security headers Nginx", "7 headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS, CSP, X-XSS-Protection, server_tokens off"],
        ["6", "HTTPS TLS 1.2/1.3", "Tu dong redirect HTTP->HTTPS; cipher HIGH:!aNULL:!MD5; HTTP/2"],
        ["7", "no-new-privileges", "security_opt: no-new-privileges:true cho tat ca 13 container"],
        ["8", "Read-only mount", "Source code mount readonly (:ro) trong container app va nginx"],
        ["9", "Bao mat ung dung PHP", "Prepared statements; bcrypt; CSRF token; HttpOnly + SameSite=Strict; htmlspecialchars()"],
    ])
    add_heading(doc, "6.2 So sanh truoc va sau hardening", level=2)
    add_table(doc, ["Tieu chi", "Khong hardening", "Da hardening"], [
        ["User container", "root (uid 0)", "Non-root (uid 82/472/65534/10001)"],
        ["DB access", "Mo cong ra ngoai", "Internal network, chi app moi truy cap"],
        ["Mat khau", "Hardcode trong code", "Bien moi truong .env (git-ignored)"],
        ["HTTP headers", "Khong co", "7 security headers day du"],
        ["TLS", "HTTP thuan", "HTTPS TLS 1.2/1.3, redirect tu dong"],
        ["SQL Injection", "Co nguy co", "PDO prepared statements toan bo"],
    ])
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 7: GITHUB VA 3 COMMIT
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 7: QUAN LY MA NGUON TREN GITHUB", level=1)
    add_heading(doc, "7.1 Repository GitHub", level=2)
    add_para(doc, "Repository: https://github.com/DTC245180186/portfolio-de13")
    add_para(doc, "Tai khoan GitHub: DTC245180186 (dat theo MSSV theo yeu cau de bai)")

    add_heading(doc, "7.2 3 Commit theo yeu cau de bai", level=2)
    add_table(doc, ["Commit", "Message", "Thanh phan"], [
        ["Commit 1", "feat: PHP app + MySQL + phpMyAdmin + Nginx HTTPS reverse proxy", "app/, db/, nginx/, docker-compose.yml core, .env, README.md"],
        ["Commit 2", "feat: Tich hop Prometheus + Grafana monitoring", "prometheus/, grafana/, docker-compose.yml monitoring"],
        ["Commit 3", "feat: Tich hop Loki + Promtail log tap trung + LogQL queries", "loki/, promtail/, docs/LOGQL.md"],
    ])

    add_heading(doc, "7.3 README.md", level=2)
    add_para(doc, "README.md gom 10 muc chinh:")
    for m in [
        "Kien truc he thong (ASCII diagram + bang network + bang service).",
        "Yeu cau moi truong.",
        "Huong dan chay (4 buoc ro rang).",
        "Bang truy cap URL cac thanh phan.",
        "3 moc commit theo yeu cau de bai.",
        "Giam sat Prometheus + Grafana (5 nguon metrics, 13 panels).",
        "Log tap trung Loki + Promtail (3 LogQL query co ban).",
        "Hardening (8 bien phap bao mat).",
        "Cau truc thu muc day du.",
        "Dung / don dep (docker compose down).",
    ]: add_bullet(doc, m)
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 8: HUONG DAN CHAY
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 8: HUONG DAN TRIEN KHAI VA VAN HANH", level=1)
    add_heading(doc, "8.1 Yeu cau moi truong", level=2)
    for r in [
        "Docker Engine >= 24.x + Docker Compose v2.",
        "OpenSSL (co trong Git for Windows).",
        "RAM toi thieu 4GB (khuyen nghi 8GB cho 13 container).",
        "Cong 80, 443, 3000, 9090 khong bi chiem dung.",
    ]: add_bullet(doc, r)
    add_heading(doc, "8.2 Cac buoc trien khai", level=2)
    steps = [
        ("Buoc 1 - Clone repository:", "git clone https://github.com/DTC245180186/portfolio-de13.git\ncd portfolio-de13"),
        ("Buoc 2 - Tao file .env:", "cp env.template .env\n# Mo .env va doi TAT CA mat khau"),
        ("Buoc 3 - Tao chung chi HTTPS:", "# Windows:\npowershell -ExecutionPolicy Bypass -File nginx\\gen-certs.ps1"),
        ("Buoc 4 - Khoi dong he thong:", "docker compose up -d --build"),
        ("Buoc 5 - Kiem tra:", "docker compose ps  # tat ca service Up/healthy\ncurl -k https://localhost/health.php"),
    ]
    for title, code in steps:
        add_para(doc, title, bold=True)
        add_code(doc, code)
    add_heading(doc, "8.3 Dia chi truy cap", level=2)
    add_table(doc, ["Thanh phan", "URL", "Tai khoan"], [
        ["Website Portfolio", "https://localhost/", "--"],
        ["Admin Panel", "https://localhost/admin/index.php", "admin / Admin@12345"],
        ["phpMyAdmin", "https://localhost/pma/", "portfolio_app / (xem .env)"],
        ["Grafana", "http://localhost:3000", "admin / (xem .env)"],
        ["Prometheus", "http://localhost:9090", "--"],
    ])
    add_callout(doc, "HTTPS: Trinh duyet hien canh bao cert tu ky. Nhan 'Advanced' -> 'Proceed to localhost' de tiep tuc.", "LUU Y:")
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 9: KET QUA VA TU DANH GIA
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 9: KET QUA VA TU DANH GIA", level=1)
    add_heading(doc, "9.1 Ket qua dat duoc", level=2)
    for r in [
        "OK - Ung dung web PHP portfolio chay on dinh, ket noi MySQL thanh cong.",
        "OK - phpMyAdmin hoat dong, truy cap qua /pma/ (khong mo cong truc tiep).",
        "OK - Nginx reverse proxy HTTPS TLS 1.2/1.3 hoat dong, redirect HTTP->HTTPS.",
        "OK - 7 Security headers day du, server_tokens off.",
        "OK - Prometheus thu thap metrics tu 5 nguon, tat ca target UP.",
        "OK - Grafana dashboard 13 panels hien thi metrics real-time.",
        "OK - Loki + Promtail thu thap log tat ca container qua Docker socket.",
        "OK - 10 LogQL query mau hoat dong trong Grafana Explore.",
        "OK - Hardening: 9 bien phap bao mat (non-root, network isolation, CSRF, bcrypt...).",
        "OK - GitHub repository voi 3 commit ro rang va README day du.",
    ]: add_bullet(doc, r)
    add_heading(doc, "9.2 Tu danh gia theo tieu chi cham diem", level=2)
    add_table(doc, ["Tieu chi", "Noi dung", "Diem toi da", "Tu danh gia"], [
        ["1. GitHub", "Repository day du, 3 commit, README ro rang", "1.5", "1.5 / 1.5"],
        ["2. App + DB", "PHP chay on dinh, MySQL, phpMyAdmin hoat dong", "1.5", "1.5 / 1.5"],
        ["3. Nginx Proxy", "Reverse proxy, HTTPS, security headers", "1.5", "1.5 / 1.5"],
        ["4. Prometheus + Grafana", "5 nguon metrics, dashboard 13 panels", "1.5", "1.5 / 1.5"],
        ["5. Loki + LogQL", "Loki + Promtail, 10 LogQL query (yeu cau >= 3)", "1.5", "1.5 / 1.5"],
        ["6. Hardening", "9 bien phap bao mat (yeu cau >= 3-4)", "1.5", "1.5 / 1.5"],
        ["7. Tong the", "He thong chay hoan chinh bang docker compose", "--", "Hoan chinh"],
        ["", "TONG CONG", "9.0", "9.0 / 9.0"],
    ])
    add_heading(doc, "9.3 Nhan xet va kinh nghiem", level=2)
    for l in [
        "Docker Compose giup trien khai he thong phuc tap (13 service) don gian va tai tao duoc.",
        "Network isolation (backend: internal: true) la bien phap bao mat hieu qua, ngan DB truy cap Internet.",
        "Grafana provisioning (datasource + dashboard JSON) giup cau hinh tu dong, khong can setup thu cong.",
        "Promtail Docker service discovery tu dong phat hien container moi khong can cau hinh lai.",
        "Can chu y thu tu depends_on va healthcheck de MySQL san sang truoc khi app connect.",
        "Non-root container la thoi quen tot - neu container bi xam nhap, attacker khong co quyen root.",
    ]: add_bullet(doc, l)
    doc.add_page_break()

    # ══════════════════════════════════════
    # PHAN 10: TAI LIEU THAM KHAO
    # ══════════════════════════════════════
    add_heading(doc, "PHAN 10: TAI LIEU THAM KHAO", level=1)
    for ref in [
        "[1] Docker Documentation -- https://docs.docker.com",
        "[2] Nginx Documentation -- https://nginx.org/en/docs/",
        "[3] Prometheus Documentation -- https://prometheus.io/docs/",
        "[4] Grafana Documentation -- https://grafana.com/docs/",
        "[5] Loki / LogQL Documentation -- https://grafana.com/docs/loki/latest/",
        "[6] MySQL 8.0 Reference Manual -- https://dev.mysql.com/doc/refman/8.0/en/",
        "[7] PHP 8.3 Manual -- https://www.php.net/manual/",
        "[8] OWASP Top 10 2021 -- https://owasp.org/Top10/",
        "[9] cAdvisor GitHub -- https://github.com/google/cadvisor",
        "[10] Promtail Docker SD -- https://grafana.com/docs/loki/latest/send-data/promtail/",
    ]: add_bullet(doc, ref)

    # SAVE
    output_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
    os.makedirs(output_dir, exist_ok=True)
    output_path = os.path.join(output_dir, "Bao_Cao_De13_DTC245180186.docx")
    doc.save(output_path)
    print(f"Bao cao da duoc tao: {output_path}")
    return output_path


if __name__ == "__main__":
    build_report()
