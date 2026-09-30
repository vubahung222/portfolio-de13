"""
Tự động tạo các sơ đồ kiến trúc hệ thống dạng ảnh chất lượng cao (PNG 300 DPI)
cho Đề tài số 13 - Portfolio Cá Nhân (Vũ Bá Hùng - DTC245180186)
Sử dụng Matplotlib + Patches
"""

import matplotlib.pyplot as plt
import matplotlib.patches as patches
import os

def create_architecture_diagram(output_path):
    fig, ax = plt.subplots(figsize=(15, 10), dpi=300)
    fig.patch.set_facecolor('#F8FAFC')
    ax.set_facecolor('#F8FAFC')
    ax.set_xlim(0, 15)
    ax.set_ylim(0, 10)
    ax.axis('off')

    # Title
    ax.text(7.5, 9.6, "KIẾN TRÚC TỔNG THỂ HỆ THỐNG DOCKER COMPOSE ĐỀ TÀI SỐ 13", 
            fontsize=16, fontweight='bold', ha='center', color='#0F172A', fontfamily='sans-serif')
    ax.text(7.5, 9.25, "Sinh viên: Vũ Bá Hùng — MSSV: DTC245180186 — Môn: Triển khai và Quản trị HT Phần mềm (ICTU)", 
            fontsize=10, ha='center', color='#475569', fontfamily='sans-serif')

    # 1. External / User Zone (Left / Top)
    user_box = patches.FancyBboxPatch((0.5, 7.2), 2.2, 1.4, boxstyle="round,pad=0.2", 
                                      ec="#3B82F6", fc="#EFF6FF", lw=2)
    ax.add_patch(user_box)
    ax.text(1.6, 8.2, "[Client / User]", fontsize=10, fontweight='bold', ha='center', color='#1E3A8A')
    ax.text(1.6, 7.8, "- Trinh duyet Web\n- HTTPS :443\n- HTTP :80 (Redirect)", fontsize=8, ha='center', color='#1E40AF')

    admin_box = patches.FancyBboxPatch((0.5, 4.8), 2.2, 1.4, boxstyle="round,pad=0.2", 
                                       ec="#8B5CF6", fc="#F5F3FF", lw=2)
    ax.add_patch(admin_box)
    ax.text(1.6, 5.8, "[Quan tri vien / DevOps]", fontsize=10, fontweight='bold', ha='center', color='#5B21B6')
    ax.text(1.6, 5.4, "- Grafana :3000\n- phpMyAdmin :8081\n- Prometheus :9090", fontsize=8, ha='center', color='#6D28D9')

    # 2. Reverse Proxy Layer (Nginx)
    nginx_box = patches.FancyBboxPatch((3.5, 4.6), 2.6, 4.0, boxstyle="round,pad=0.25", 
                                       ec="#059669", fc="#ECFDF5", lw=2.5)
    ax.add_patch(nginx_box)
    ax.text(4.8, 8.2, "NGINX REVERSE PROXY", fontsize=11, fontweight='bold', ha='center', color='#065F46')
    ax.text(4.8, 7.8, "(Port 80 -> 443 HTTPS)", fontsize=9, fontweight='bold', ha='center', color='#047857')
    ax.text(4.8, 6.8, "- SSL/TLS 1.2/1.3 Tu ky\n- 7 Security Headers OWASP:\n  * Strict-Transport-Security\n  * X-Frame-Options: SAMEORIGIN\n  * X-Content-Type-Options\n  * Content-Security-Policy\n- Upstream Proxy Routing\n- Stub Status Module (:80)", 
            fontsize=7.5, ha='center', color='#064E3B')

    # Arrows from User/Admin to Nginx
    ax.annotate('', xy=(3.5, 7.9), xytext=(2.7, 7.9),
                arrowprops=dict(arrowstyle="->", color="#2563EB", lw=2))
    ax.text(3.1, 8.05, "HTTPS", fontsize=7.5, fontweight='bold', color="#2563EB", ha='center')

    # 3. Zone FRONTEND NETWORK
    front_net = patches.Rectangle((6.7, 4.5), 7.8, 4.3, ec="#2563EB", fc="#F0F9FF", lw=2, linestyle="--")
    ax.add_patch(front_net)
    ax.text(6.9, 8.55, "MANG DOCKER: frontend (Bridge Network)", fontsize=10, fontweight='bold', color="#1D4ED8")

    # Inside Frontend: Web App
    web_box = patches.FancyBboxPatch((7.0, 6.6), 3.2, 1.6, boxstyle="round,pad=0.15", 
                                     ec="#1E40AF", fc="#DBEAFE", lw=2)
    ax.add_patch(web_box)
    ax.text(8.6, 7.8, "[App] Web Portfolio", fontsize=10, fontweight='bold', ha='center', color='#1E3A8A')
    ax.text(8.6, 7.2, "- PHP 8.3 FPM / Apache\n- Non-root User (UID 1000)\n- Read-only Config / Safe State\n- MVC Architecture + PDO", 
            fontsize=7.5, ha='center', color='#1E40AF')

    # Inside Frontend: phpMyAdmin
    pma_box = patches.FancyBboxPatch((10.8, 6.6), 3.3, 1.6, boxstyle="round,pad=0.15", 
                                     ec="#D97706", fc="#FEF3C7", lw=1.5)
    ax.add_patch(pma_box)
    ax.text(12.45, 7.8, "[DB-GUI] phpMyAdmin", fontsize=10, fontweight='bold', ha='center', color='#92400E')
    ax.text(12.45, 7.2, "- GUI Quan tri CSDL\n- Port Host 8081\n- Han che quyen Root\n- Ket noi noi bo toi MySQL", 
            fontsize=7.5, ha='center', color='#B45309')

    # Inside Frontend: Grafana
    grafana_box = patches.FancyBboxPatch((7.0, 4.8), 3.2, 1.5, boxstyle="round,pad=0.15", 
                                         ec="#DC2626", fc="#FEE2E2", lw=1.8)
    ax.add_patch(grafana_box)
    ax.text(8.6, 5.9, "[Viz] Grafana Dashboard", fontsize=10, fontweight='bold', ha='center', color='#991B1B')
    ax.text(8.6, 5.3, "- Port Host 3000\n- 13 Panels Giam sat Metrics\n- LogQL Logs Viewer\n- Auto-provisioning Data Sources", 
            fontsize=7.5, ha='center', color='#B91C1C')

    # Arrows Nginx to Web / Grafana / PMA
    ax.annotate('', xy=(7.0, 7.4), xytext=(6.1, 7.4),
                arrowprops=dict(arrowstyle="->", color="#059669", lw=2))
    ax.annotate('', xy=(10.8, 7.4), xytext=(10.2, 7.4),
                arrowprops=dict(arrowstyle="->", color="#059669", lw=1.5, linestyle="dotted"))

    # 4. Zone BACKEND NETWORK (internal: true)
    back_net = patches.Rectangle((6.7, 0.4), 7.8, 3.8, ec="#DC2626", fc="#FFF1F2", lw=2, linestyle="--")
    ax.add_patch(back_net)
    ax.text(6.9, 3.9, "MANG DOCKER: backend (internal: true - Cach ly Hoan toan)", fontsize=10, fontweight='bold', color="#BE123C")

    # Inside Backend: MySQL 8.0
    mysql_box = patches.FancyBboxPatch((7.0, 2.0), 3.2, 1.6, boxstyle="round,pad=0.15", 
                                       ec="#047857", fc="#D1FAE5", lw=2)
    ax.add_patch(mysql_box)
    ax.text(8.6, 3.2, "[DB] MySQL 8.0 Server", fontsize=10, fontweight='bold', ha='center', color='#065F46')
    ax.text(8.6, 2.5, "- Cong noi bo 3306 (Khong mo Host)\n- Named Volume: mysql_data\n- Bcrypt cost=12 Passwords\n- User DB rieng biet, toi gian quyen", 
            fontsize=7.5, ha='center', color='#047857')

    # Arrow Web to MySQL
    ax.annotate('', xy=(8.6, 3.6), xytext=(8.6, 6.6),
                arrowprops=dict(arrowstyle="<->", color="#0284C7", lw=2))
    ax.text(8.8, 4.3, "PDO SQL Query (port 3306)", fontsize=7, fontweight='bold', color="#0369A1", va='center')

    # Inside Backend: Prometheus
    prom_box = patches.FancyBboxPatch((10.8, 2.0), 3.3, 1.6, boxstyle="round,pad=0.15", 
                                      ec="#EA580C", fc="#FFEDD5", lw=2)
    ax.add_patch(prom_box)
    ax.text(12.45, 3.2, "[Metrics] Prometheus", fontsize=10, fontweight='bold', ha='center', color='#9A3412')
    ax.text(12.45, 2.5, "- TSDB Luu tru Metrics\n- Scrape Interval: 15s\n- 5 Exporters ket noi\n- Port noi bo 9090", 
            fontsize=7.5, ha='center', color='#C2410C')

    # Arrow Prom to Grafana
    ax.annotate('', xy=(10.2, 5.3), xytext=(11.5, 3.6),
                arrowprops=dict(arrowstyle="->", color="#EA580C", lw=1.8))
    ax.text(11.2, 4.6, "PromQL Query", fontsize=7.5, fontweight='bold', color="#C2410C")

    # Inside Backend: Loki & Promtail
    loki_box = patches.FancyBboxPatch((7.0, 0.6), 3.2, 1.2, boxstyle="round,pad=0.15", 
                                      ec="#7C3AED", fc="#EDE9FE", lw=1.8)
    ax.add_patch(loki_box)
    ax.text(8.6, 1.5, "[Logs] Loki Engine", fontsize=9.5, fontweight='bold', ha='center', color='#5B21B6')
    ax.text(8.6, 0.95, "- Port noi bo 3100 | Volume: loki_data\n- Luu index & chunks nen toi uu", 
            fontsize=7.5, ha='center', color='#6D28D9')

    promtail_box = patches.FancyBboxPatch((10.8, 0.6), 3.3, 1.2, boxstyle="round,pad=0.15", 
                                          ec="#9333EA", fc="#FAF5FF", lw=1.8)
    ax.add_patch(promtail_box)
    ax.text(12.45, 1.5, "[Log-Agent] Promtail", fontsize=9.5, fontweight='bold', ha='center', color='#6B21A8')
    ax.text(12.45, 0.95, "- Mount: /var/run/docker.sock\n- Auto-scrape log tat ca containers", 
            fontsize=7.5, ha='center', color='#7E22CE')

    # Arrow Promtail to Loki
    ax.annotate('', xy=(10.2, 1.2), xytext=(10.8, 1.2),
                arrowprops=dict(arrowstyle="->", color="#9333EA", lw=1.8))
    ax.text(10.5, 1.35, "Push", fontsize=7.5, fontweight='bold', color="#9333EA", ha='center')

    # Arrow Loki to Grafana
    ax.annotate('', xy=(8.0, 4.8), xytext=(8.0, 1.8),
                arrowprops=dict(arrowstyle="->", color="#7C3AED", lw=1.8, linestyle="dashed"))
    ax.text(7.1, 3.8, "LogQL Query", fontsize=7.5, fontweight='bold', color="#7C3AED")

    # 5. Bottom Left: 5 Exporters info box
    exp_box = patches.FancyBboxPatch((0.5, 0.6), 5.6, 3.6, boxstyle="round,pad=0.2", 
                                     ec="#475569", fc="#F1F5F9", lw=1.5)
    ax.add_patch(exp_box)
    ax.text(3.3, 3.8, "HE SINH THAI 5 EXPORTERS (Giam sat Metrics)", fontsize=9.5, fontweight='bold', ha='center', color='#1E293B')
    ax.text(3.3, 2.2, 
            "1. cAdvisor (:8080)        -> Do CPU, RAM, Network I/O tung container\n"
            "2. Node Exporter (:9100)   -> Do tai nguyen may chu Host (Disk, Load, Memory)\n"
            "3. MySQL Exporter (:9104)  -> Do Threads, QPS, Connections, Slow Queries\n"
            "4. Nginx Exporter (:9113)  -> Do Active Connections, Requests/sec, Status Codes\n"
            "5. Prometheus Internal     -> Tu giam sat do tre Scrape & TSDB Storage", 
            fontsize=7.5, ha='center', color='#334155', fontfamily='monospace')

    # Arrow Exporters to Prometheus
    ax.annotate('', xy=(10.8, 2.7), xytext=(6.1, 2.7),
                arrowprops=dict(arrowstyle="->", color="#EA580C", lw=2, linestyle="dotted"))
    ax.text(6.4, 2.85, "Pull Metrics (15s)", fontsize=7.5, fontweight='bold', color="#EA580C")

    # 6. Hardening Badges / Highlights (Right sidebar / labels)
    badge1 = patches.FancyBboxPatch((0.5, 4.3), 5.6, 0.4, boxstyle="round,pad=0.08", ec="#16A34A", fc="#DCFCE7")
    ax.add_patch(badge1)
    ax.text(3.3, 4.45, "[OK] 9 BIEN PHAP HARDENING DAT CHUAN OWASP & CIS BENCHMARK", 
            fontsize=7.5, fontweight='bold', ha='center', color='#15803D')

    plt.tight_layout()
    plt.savefig(output_path, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"[OK] Da tao so do kien truc tai: {output_path}")

def create_monitoring_pipeline_diagram(output_path):
    fig, ax = plt.subplots(figsize=(14, 7), dpi=300)
    fig.patch.set_facecolor('#FFFFFF')
    ax.set_facecolor('#FFFFFF')
    ax.set_xlim(0, 14)
    ax.set_ylim(0, 7)
    ax.axis('off')

    # Title
    ax.text(7.0, 6.6, "LUONG THU THAP VA XU LY DU LIEU GIAM SAT (METRICS & LOGS)", 
            fontsize=14, fontweight='bold', ha='center', color='#0F172A')

    # Left: Data Sources
    ds_box = patches.FancyBboxPatch((0.5, 1.2), 3.2, 4.8, boxstyle="round,pad=0.2", ec="#2563EB", fc="#EFF6FF", lw=1.5)
    ax.add_patch(ds_box)
    ax.text(2.1, 5.6, "1. NGUON PHAT SINH", fontsize=11, fontweight='bold', ha='center', color='#1E3A8A')
    ax.text(2.1, 4.8, "[Docker Containers]\n(Web, MySQL, Nginx, Proxy)", fontsize=8.5, ha='center', color='#1D4ED8')
    ax.text(2.1, 3.6, "[Host Operating System]\n(CPU, RAM, Disk I/O)", fontsize=8.5, ha='center', color='#1D4ED8')
    ax.text(2.1, 2.4, "[Docker Daemon Socket]\n(/var/run/docker.sock)", fontsize=8.5, ha='center', color='#1D4ED8')
    ax.text(2.1, 1.5, "[Nginx Access/Error Logs]\n(JSON Structured Format)", fontsize=8.5, ha='center', color='#1D4ED8')

    # Middle 1: Collectors & Shippers
    col_box = patches.FancyBboxPatch((4.5, 1.2), 3.2, 4.8, boxstyle="round,pad=0.2", ec="#D97706", fc="#FFFBEB", lw=1.5)
    ax.add_patch(col_box)
    ax.text(6.1, 5.6, "2. THU THAP & CHUYEN TIEP", fontsize=10.5, fontweight='bold', ha='center', color='#92400E')
    ax.text(6.1, 4.7, "[5 Prometheus Exporters]\n- cAdvisor (:8080)\n- Node Exporter (:9100)\n- MySQL Exporter (:9104)\n- Nginx Exporter (:9113)", 
            fontsize=8, ha='center', color='#B45309')
    ax.text(6.1, 2.3, "[Promtail Log Agent]\n- Doc log tu Docker Socket\n- Gan nhan container_name\n- Pipeline Regex / Drop rac\n- Push batch ve Loki", 
            fontsize=8, ha='center', color='#B45309')

    # Middle 2: Storage & Query Engine
    sto_box = patches.FancyBboxPatch((8.5, 1.2), 2.5, 4.8, boxstyle="round,pad=0.2", ec="#7C3AED", fc="#F5F3FF", lw=1.5)
    ax.add_patch(sto_box)
    ax.text(9.75, 5.6, "3. LUU TRU & TRUY VAN", fontsize=10, fontweight='bold', ha='center', color='#5B21B6')
    ax.text(9.75, 4.5, "[Prometheus TSDB]\n- Port 9090 (Backend)\n- Scrape dinh ky 15s\n- PromQL Engine\n- Retention 15 ngay", 
            fontsize=8, ha='center', color='#6D28D9')
    ax.text(9.75, 2.3, "[Loki Engine]\n- Port 3100 (Backend)\n- BoltDB Shipper Index\n- Filesystem Chunks\n- LogQL Query Parser", 
            fontsize=8, ha='center', color='#6D28D9')

    # Right: Visualization
    vis_box = patches.FancyBboxPatch((11.6, 1.2), 2.0, 4.8, boxstyle="round,pad=0.2", ec="#059669", fc="#ECFDF5", lw=1.5)
    ax.add_patch(vis_box)
    ax.text(12.6, 5.6, "4. TRUC QUAN HOA", fontsize=10, fontweight='bold', ha='center', color='#065F46')
    ax.text(12.6, 4.2, "[Grafana]\n(Port 3000)\n\n- 13 Panels Metrics\n- Container Health\n- MySQL QPS/Slow\n- Nginx Traffic\n- Host Load", 
            fontsize=8, ha='center', color='#047857')
    ax.text(12.6, 1.8, "[Explore UI]\n\n- 10 LogQL Queries\n- Filter Error 5xx\n- Real-time Stream\n- Regex Filter", 
            fontsize=8, ha='center', color='#047857')

    # Connectors
    ax.annotate('', xy=(4.5, 4.5), xytext=(3.7, 4.5), arrowprops=dict(arrowstyle="->", color="#2563EB", lw=2))
    ax.annotate('', xy=(4.5, 2.5), xytext=(3.7, 2.5), arrowprops=dict(arrowstyle="->", color="#2563EB", lw=2))
    ax.annotate('', xy=(8.5, 4.5), xytext=(7.7, 4.5), arrowprops=dict(arrowstyle="->", color="#D97706", lw=2))
    ax.annotate('', xy=(8.5, 2.5), xytext=(7.7, 2.5), arrowprops=dict(arrowstyle="->", color="#D97706", lw=2))
    ax.annotate('', xy=(11.6, 4.5), xytext=(11.0, 4.5), arrowprops=dict(arrowstyle="->", color="#7C3AED", lw=2))
    ax.annotate('', xy=(11.6, 2.5), xytext=(11.0, 2.5), arrowprops=dict(arrowstyle="->", color="#7C3AED", lw=2))

    plt.tight_layout()
    plt.savefig(output_path, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"[OK] Da tao so do pipeline tai: {output_path}")

if __name__ == "__main__":
    out_dir = os.path.join(os.path.dirname(__file__), "docs", "assets")
    os.makedirs(out_dir, exist_ok=True)
    create_architecture_diagram(os.path.join(out_dir, "architecture_diagram.png"))
    create_monitoring_pipeline_diagram(os.path.join(out_dir, "monitoring_pipeline.png"))
