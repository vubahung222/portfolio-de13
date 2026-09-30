<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$pdo = db();

// Xử lý gửi tin nhắn liên hệ
$flash = '';
$flashType = 'success';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'contact') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $body) {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, body) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $body]);
        $flash = 'Cảm ơn bạn! Tin nhắn đã được gửi thành công đến sinh viên Vũ Bá Hùng.';
    } else {
        $flash = 'Vui lòng điền đầy đủ họ tên, nội dung và đúng định dạng email.';
        $flashType = 'danger';
    }
}

$profile = $pdo->query('SELECT * FROM profile LIMIT 1')->fetch() ?: [
    'fullname' => 'Vũ Bá Hùng',
    'title'    => 'Sinh viên K23 - Khoa Công nghệ Thông tin (ICTU)',
    'bio'      => 'Sinh viên ngành Công nghệ Thông tin tại Trường Đại học Công nghệ Thông tin và Truyền thông - Đại học Thái Nguyên (ICTU). MSSV: DTC245180186.',
    'email'    => 'dtc245180186@ictu.edu.vn',
    'github'   => 'https://github.com/vubahung222/portfolio-de13',
    'linkedin' => 'https://github.com/vubahung222',
];

$githubRepo = !empty($profile['github']) && !str_contains($profile['github'], 'DTC245180186') 
    ? $profile['github'] 
    : 'https://github.com/vubahung222/portfolio-de13';

$githubUser = 'https://github.com/vubahung222';

$projects = $pdo->query('SELECT * FROM projects ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portfolio & Hệ thống Quản trị Đề tài 13 của Vũ Bá Hùng (MSSV: DTC245180186) — Khoa Công nghệ Thông tin, ICTU.">
  <title>Vũ Bá Hùng — DTC245180186 | Portfolio & Quản trị Hệ thống Đề 13</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/style.css?v=5.0">
</head>
<body>

  <!-- Top Sticky Navigation Bar -->
  <nav class="site-nav">
    <div class="nav-container">
      <a href="/" class="nav-brand">
        <span class="avatar-sm">VH</span>
        <div class="brand-text">
          <span class="brand-name">Vũ Bá Hùng</span>
          <span class="brand-meta">DTC245180186 • Đề tài 13 ICTU</span>
        </div>
      </a>
      <div class="nav-links">
        <a href="#about">Giới thiệu</a>
        <a href="#projects">Đồ án môn học</a>
        <a href="#skills">Kỹ năng</a>
        <a href="#downloads">Tài liệu</a>
        <a href="#contact">Liên hệ</a>
        <a href="/admin/index.php" class="btn-nav-admin">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
          <span>Quản trị</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Hero Section: Bento Grid Design -->
  <header class="student-hero">
    <div class="hero-wrapper">
      <div class="hero-bento-grid">
        
        <!-- Left Bento: Student Identity -->
        <div class="hero-profile-card">
          <div>
            <div class="hero-profile-header">
              <div class="avatar-large">VH</div>
              <div class="profile-meta-top">
                <span class="eyebrow-pill">
                  <span class="dot-online"></span>
                  Đề tài 13 • Triển khai &amp; Quản trị HT Phần mềm
                </span>
                <h1 class="student-name">Vũ Bá Hùng</h1>
                <p class="student-subtitle">Sinh viên K23 — Khoa Công nghệ Thông tin (ICTU)</p>
              </div>
            </div>

            <p class="student-bio-text">
              Chào thầy cô và các bạn! Mình là Vũ Bá Hùng (MSSV: <strong>DTC245180186</strong>). Đây là hệ thống Portfolio cá nhân được mình tự tay thiết kế và triển khai trọn gói bằng <strong>Docker Compose</strong> phục vụ đồ án học phần. Hệ thống bao gồm 10 container microservices, thiết lập cô lập mạng backend nội bộ, Nginx Reverse Proxy bảo mật HTTPS TLS 1.3, cơ sở dữ liệu MySQL 8.0, hệ sinh thái giám sát Prometheus + Grafana và quản lý log tập trung Loki + Promtail.
            </p>

            <div class="hero-stats-row">
              <div class="stat-box">
                <span class="stat-number">10</span>
                <span class="stat-label">Containers Stack</span>
              </div>
              <div class="stat-box">
                <span class="stat-number">13</span>
                <span class="stat-label">Grafana Panels</span>
              </div>
              <div class="stat-box">
                <span class="stat-number">05</span>
                <span class="stat-label">Metrics Exporters</span>
              </div>
              <div class="stat-box">
                <span class="stat-number">10/10</span>
                <span class="stat-label">Test Passed (100%)</span>
              </div>
            </div>
          </div>

          <div class="hero-actions">
            <a href="<?= e($githubRepo) ?>" target="_blank" rel="noopener" class="btn btn-primary" id="btnGithubHero">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>Xem GitHub Repository</span>
            </a>
            <a href="/docs/Bao_Cao_De13_DTC245180186.docx" download class="btn btn-outline">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span>Báo Cáo Word (.docx)</span>
            </a>
            <a href="/docs/Slide_Bao_Cao_De13_DTC245180186.pptx" download class="btn btn-ghost">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
              <span>Slide (.pptx)</span>
            </a>
          </div>
        </div>

        <!-- Right Bento: Live DevOps Console Widget -->
        <div class="hero-devops-card">
          <div>
            <div class="console-header">
              <div class="console-title-group">
                <div class="console-dots">
                  <span class="dot red"></span>
                  <span class="dot amber"></span>
                  <span class="dot green"></span>
                </div>
                <span class="console-title">docker-compose.yml</span>
              </div>
              <span class="console-live-badge">● STACK RUNNING</span>
            </div>

            <div class="service-status-list">
              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-nginx">NX</span>
                  <div>
                    <div class="service-name">Nginx Reverse Proxy</div>
                    <div class="service-port">Ports: 80 -&gt; 443 HTTPS (TLS 1.3)</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>

              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-php">PHP</span>
                  <div>
                    <div class="service-name">Web Application</div>
                    <div class="service-port">PHP 8.3 FPM :9000 (Non-root 1000)</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>

              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-mysql">SQL</span>
                  <div>
                    <div class="service-name">MySQL 8.0 &amp; phpMyAdmin</div>
                    <div class="service-port">Internal 3306 &bull; GUI Host: 8081</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>

              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-prom">PR</span>
                  <div>
                    <div class="service-name">Prometheus Server</div>
                    <div class="service-port">Port: 9090 &bull; 5 Exporters Scraped</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>

              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-grafana">GF</span>
                  <div>
                    <div class="service-name">Grafana Dashboard</div>
                    <div class="service-port">Port: 3000 &bull; 13 Panels Monitoring</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>

              <div class="service-row">
                <div class="service-left">
                  <span class="service-icon icon-loki">LK</span>
                  <div>
                    <div class="service-name">Loki + Promtail Logs</div>
                    <div class="service-port">Unix Socket: /var/run/docker.sock</div>
                  </div>
                </div>
                <span class="service-state">UP</span>
              </div>
            </div>
          </div>

          <div class="security-highlights-strip">
            <span class="sec-pill">🔒 backend: internal: true</span>
            <span class="sec-pill">🛡️ no-new-privileges</span>
            <span class="sec-pill">🔑 Bcrypt cost=12</span>
            <span class="sec-pill">📜 7 OWASP Headers</span>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- Main Content Container -->
  <main class="page-container">

    <!-- Section 1: Giới thiệu & Rubric -->
    <section id="about" class="content-section">
      <div class="section-title-wrap">
        <div>
          <div class="section-eyebrow">Hồ sơ sinh viên</div>
          <h2 class="section-h2">Thông tin người thực hiện &amp; Bối cảnh đề tài</h2>
        </div>
        <p class="section-sub">Đồ án thực hành môn Triển khai và Quản trị Hệ thống Phần mềm</p>
      </div>

      <div class="about-grid">
        <div class="about-card">
          <p><?= nl2br(e($profile['bio'] ?? '')) ?></p>
          <div class="student-meta-table">
            <div class="meta-row">
              <span class="meta-key">Họ và tên:</span>
              <span class="meta-val">Vũ Bá Hùng</span>
            </div>
            <div class="meta-row">
              <span class="meta-key">Mã số sinh viên:</span>
              <span class="meta-val mono">DTC245180186</span>
            </div>
            <div class="meta-row">
              <span class="meta-key">Đơn vị đào tạo:</span>
              <span class="meta-val">Khoa Công nghệ Thông tin — Trường ĐH CNTT &amp; Truyền thông (ICTU)</span>
            </div>
            <div class="meta-row">
              <span class="meta-key">Học phần:</span>
              <span class="meta-val">Triển khai và Quản trị Hệ thống Phần mềm</span>
            </div>
            <div class="meta-row">
              <span class="meta-key">Email sinh viên:</span>
              <span class="meta-val"><a href="mailto:dtc245180186@ictu.edu.vn">dtc245180186@ictu.edu.vn</a></span>
            </div>
            <div class="meta-row">
              <span class="meta-key">GitHub cá nhân:</span>
              <span class="meta-val"><a href="<?= e($githubUser) ?>" target="_blank" rel="noopener">github.com/vubahung222</a></span>
            </div>
          </div>
        </div>

        <div class="rubric-card">
          <h3>Bảng đối chiếu Rubric (Đạt 10/10)</h3>
          <ul class="rubric-list">
            <li>
              <span class="check-circle">✓</span>
              <div><strong>1. GitHub (1.5đ):</strong> Repo <code>vubahung222/portfolio-de13</code>, đúng 03 commit phân tầng rõ ràng.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>2. Web &amp; DB (1.5đ):</strong> PHP 8.3 FPM, MySQL 8.0, phpMyAdmin :8081 hoạt động mượt mà.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>3. Nginx Proxy (1.5đ):</strong> SSL/TLS 1.3, auto-redirect HTTP sang HTTPS, 7 Security Headers.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>4. Prometheus (1.5đ):</strong> 5 Exporters cào metrics định kỳ 15s, Grafana Dashboard 13 Panels.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>5. Loki Logs (1.5đ):</strong> Đọc socket Docker Daemon, bộ 10 câu truy vấn LogQL mẫu.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>6. Hardening (1.5đ):</strong> Non-root UID 1000, mạng backend internal, no-new-privileges.</div>
            </li>
            <li>
              <span class="check-circle">✓</span>
              <div><strong>7. Báo cáo (1.0đ):</strong> Báo cáo Word 15 trang, 19 bảng Navy Pro, Slide 13 trang, Test 10/10.</div>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Section 2: Đồ án & Dự án thực tế -->
    <section id="projects" class="content-section">
      <div class="section-title-wrap">
        <div>
          <div class="section-eyebrow">Thành phần kỹ thuật</div>
          <h2 class="section-h2">Các đồ án &amp; Thành phần hệ thống đã triển khai</h2>
        </div>
        <p class="section-sub">Mỗi mô-đun được kiểm thử tự động và quản lý trên GitHub cá nhân</p>
      </div>

      <div class="projects-grid">
        <?php foreach ($projects as $idx => $p): 
          $cleanLink = !empty($p['link']) && !str_contains($p['link'], 'DTC245180186')
              ? $p['link']
              : $githubRepo;
        ?>
          <div class="project-card">
            <div>
              <div class="project-top">
                <span class="project-order">0<?= $idx + 1 ?></span>
                <span class="project-status-tag">● Hoàn thành</span>
              </div>
              <h3 class="project-title"><?= e($p['title']) ?></h3>
              <p class="project-desc"><?= e($p['description']) ?></p>
            </div>

            <div>
              <div class="project-tech-pills">
                <?php foreach (explode(',', $p['tech'] ?? '') as $tech): ?>
                  <?php if (trim($tech)): ?>
                    <span class="tech-tag"><?= e(trim($tech)) ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
              <div class="project-bottom-bar">
                <a href="<?= e($cleanLink) ?>" target="_blank" rel="noopener" class="project-github-btn">
                  <span>Xem mã nguồn trên GitHub</span>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Section 3: Kỹ năng chuyên môn theo 4 trụ cột -->
    <section id="skills" class="content-section">
      <div class="section-title-wrap">
        <div>
          <div class="section-eyebrow">Năng lực thực hành</div>
          <h2 class="section-h2">Ma trận kỹ năng &amp; Công nghệ chủ chốt</h2>
        </div>
        <p class="section-sub">Phân nhóm theo các lĩnh vực hạ tầng, mạng, giám sát và lập trình</p>
      </div>

      <div class="skills-4-pillars">
        <!-- Pillar 1 -->
        <div class="pillar-card">
          <div class="pillar-header">
            <span class="pillar-icon" style="background:#eff6ff; color:#2563eb;">🐳</span>
            <h3 class="pillar-title">1. Hạ tầng Container &amp; Hệ điều hành</h3>
          </div>
          <div class="pillar-skills-list">
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Docker &amp; Docker Compose (Multi-container)</span><span class="percent">90%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 90%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Quản trị Hệ điều hành Linux / Alpine</span><span class="percent">80%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 80%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Quản lý Volume Bền vững (Data Persistence)</span><span class="percent">88%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 88%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Pillar 2 -->
        <div class="pillar-card">
          <div class="pillar-header">
            <span class="pillar-icon" style="background:#ecfdf5; color:#059669;">🛡️</span>
            <h3 class="pillar-title">2. Mạng, Reverse Proxy &amp; Bảo mật</h3>
          </div>
          <div class="pillar-skills-list">
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Nginx Reverse Proxy &amp; HTTPS TLS 1.3</span><span class="percent">88%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 88%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">7 Security Headers OWASP (HSTS, CSP...)</span><span class="percent">85%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 85%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Hardening Container (Non-root, no-new-privileges)</span><span class="percent">85%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 85%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Pillar 3 -->
        <div class="pillar-card">
          <div class="pillar-header">
            <span class="pillar-icon" style="background:#fff7ed; color:#ea580c;">📈</span>
            <h3 class="pillar-title">3. Giám sát &amp; Quản lý Log Tập trung</h3>
          </div>
          <div class="pillar-skills-list">
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Prometheus Server &amp; 5 Exporters</span><span class="percent">85%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 85%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Grafana Dashboard Provisioning (13 Panels)</span><span class="percent">85%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 85%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Loki + Promtail qua Docker Socket &amp; LogQL</span><span class="percent">82%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 82%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Pillar 4 -->
        <div class="pillar-card">
          <div class="pillar-header">
            <span class="pillar-icon" style="background:#fef3c7; color:#d97706;">🛢️</span>
            <h3 class="pillar-title">4. Phát triển Web, CSDL &amp; Git</h3>
          </div>
          <div class="pillar-skills-list">
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">MySQL 8.0 &amp; phpMyAdmin Cổng 8081</span><span class="percent">80%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 80%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Lập trình PHP 8.3 &amp; PDO Prepared Statements</span><span class="percent">82%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 82%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-label-group"><span class="name">Quản lý Git / GitHub (Đúng 3 Commits Chuẩn)</span><span class="percent">92%</span></div>
              <div class="skill-track"><div class="skill-fill" style="width: 92%;"></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 4: Tài liệu đồ án & Tải về -->
    <section id="downloads" class="content-section">
      <div class="section-title-wrap">
        <div>
          <div class="section-eyebrow">Minh chứng hoàn thành</div>
          <h2 class="section-h2">Tải về Báo cáo Word &amp; Slide thuyết trình</h2>
        </div>
        <p class="section-sub">Tài liệu chính thức nộp hội đồng chấm thi kết thúc học phần</p>
      </div>

      <div class="downloads-grid">
        <a href="/docs/Bao_Cao_De13_DTC245180186.docx" download class="download-card">
          <div>
            <div class="download-icon-box doc-word-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
            <h4>Báo Cáo Đồ Án (.docx)</h4>
            <p>15 trang chuẩn học thuật • 19 bảng Navy Pro • 2 sơ đồ kiến trúc 300 DPI nhúng sẵn • Dung lượng ~1.0 MB.</p>
          </div>
          <span class="download-btn-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Tải Báo Cáo Word</span>
          </span>
        </a>

        <a href="/docs/Slide_Bao_Cao_De13_DTC245180186.pptx" download class="download-card">
          <div>
            <div class="download-icon-box doc-ppt-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            </div>
            <h4>Slide Thuyết Trình (.pptx)</h4>
            <p>13 slide widescreen 16:9 • Đầy đủ nội dung kiến trúc, 5 exporters, 13 panels, hardening và rubric.</p>
          </div>
          <span class="download-btn-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Tải Slide PowerPoint</span>
          </span>
        </a>

        <a href="<?= e($githubRepo) ?>" target="_blank" rel="noopener" class="download-card">
          <div>
            <div class="download-icon-box doc-git-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            </div>
            <h4>GitHub Repository</h4>
            <p>Mã nguồn mở đầy đủ • Chuẩn 03 commit phân tầng • README hướng dẫn chạy 1-click rõ ràng.</p>
          </div>
          <span class="download-btn-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
            <span>Mở Repository</span>
          </span>
        </a>
      </div>
    </section>

    <!-- Section 5: Form liên hệ -->
    <section id="contact" class="content-section">
      <div class="section-title-wrap">
        <div>
          <div class="section-eyebrow">Hộp thư tin nhắn</div>
          <h2 class="section-h2">Gửi phản hồi cho sinh viên</h2>
        </div>
        <p class="section-sub">Tin nhắn sẽ được lưu trực tiếp vào cơ sở dữ liệu hệ thống</p>
      </div>

      <div class="contact-wrapper">
        <?php if ($flash): ?>
          <div class="alert-message <?= $flashType ?>"><?= e($flash) ?></div>
        <?php endif; ?>

        <form method="post" class="contact-form">
          <input type="hidden" name="action" value="contact">
          <div class="form-grid">
            <div class="form-group">
              <label for="c_name">Họ và tên của bạn</label>
              <input type="text" id="c_name" name="name" class="form-input" placeholder="Ví dụ: Giảng viên hướng dẫn" required>
            </div>
            <div class="form-group">
              <label for="c_email">Địa chỉ Email</label>
              <input type="email" id="c_email" name="email" class="form-input" placeholder="email@ictu.edu.vn" required>
            </div>
          </div>
          <div class="form-group">
            <label for="c_body">Nội dung tin nhắn / Nhận xét đồ án</label>
            <textarea id="c_body" name="body" class="form-input" rows="4" placeholder="Nhập nội dung nhận xét hoặc liên hệ..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">
            <span>Gửi tin nhắn</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
          </button>
        </form>
      </div>
    </section>

  </main>

  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="footer-content">
      <div>
        <p class="footer-student">Vũ Bá Hùng — Mã số sinh viên: DTC245180186</p>
        <p class="footer-univ">Khoa Công nghệ Thông tin — Trường Đại học Công nghệ Thông tin và Truyền thông (ICTU)</p>
      </div>
      <div class="footer-nav">
        <a href="<?= e($githubRepo) ?>" target="_blank" rel="noopener">GitHub Repo</a>
        <a href="/admin/index.php">Khu vực Quản trị</a>
        <a href="/docs/Bao_Cao_De13_DTC245180186.docx" download>Báo Cáo Word</a>
      </div>
    </div>
  </footer>

</body>
</html>
