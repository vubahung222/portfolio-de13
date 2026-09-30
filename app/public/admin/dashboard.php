<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';
require_login();

$pdo = db();
$msg = '';
$msg_type = 'success';

// -------- Handle actions (all CSRF-protected) ----------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $msg = 'Phiên làm việc đã hết hạn hoặc mã CSRF không hợp lệ. Vui lòng thử lại.';
        $msg_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'add_project':
                $title = trim($_POST['title'] ?? '');
                $desc  = trim($_POST['description'] ?? '');
                $link  = trim($_POST['link'] ?? '');
                $tech  = trim($_POST['tech'] ?? '');
                if ($title !== '') {
                    $stmt = $pdo->prepare('INSERT INTO projects (title, description, link, tech) VALUES (?,?,?,?)');
                    $stmt->execute([$title, $desc, $link, $tech]);
                    $msg = 'Đã thêm dự án mới thành công!';
                }
                break;
            case 'del_project':
                $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
                $msg = 'Đã xoá dự án khỏi hệ thống.';
                break;
            case 'add_skill':
                $name = trim($_POST['name'] ?? '');
                $level = max(0, min(100, (int)($_POST['level'] ?? 50)));
                if ($name !== '') {
                    $stmt = $pdo->prepare('INSERT INTO skills (name, level) VALUES (?,?)');
                    $stmt->execute([$name, $level]);
                    $msg = 'Đã thêm kỹ năng mới thành công!';
                }
                break;
            case 'del_skill':
                $pdo->prepare('DELETE FROM skills WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
                $msg = 'Đã xoá kỹ năng thành công.';
                break;
            case 'update_profile':
                $stmt = $pdo->prepare('UPDATE profile SET fullname=?, title=?, bio=?, email=?, github=?, linkedin=? WHERE id=?');
                $stmt->execute([
                    trim($_POST['fullname'] ?? ''),
                    trim($_POST['title'] ?? ''),
                    trim($_POST['bio'] ?? ''),
                    trim($_POST['email'] ?? ''),
                    trim($_POST['github'] ?? ''),
                    trim($_POST['linkedin'] ?? ''),
                    (int)($_POST['id'] ?? 1)
                ]);
                $msg = 'Đã cập nhật thông tin hồ sơ cá nhân!';
                break;
            case 'change_password':
                $new = $_POST['new_password'] ?? '';
                if (strlen($new) < 8) {
                    $msg = 'Mật khẩu mới phải có độ dài tối thiểu từ 8 ký tự trở lên.';
                    $msg_type = 'error';
                } else {
                    $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
                    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash, $_SESSION['user_id']]);
                    $msg = 'Đã đổi mật khẩu quản trị viên thành công!';
                }
                break;
        }
    }
}

$profile  = $pdo->query('SELECT * FROM profile LIMIT 1')->fetch() ?: [
    'id' => 1, 'fullname' => 'Vũ Bá Hùng (DTC245180186)', 'title' => 'Lập trình viên & DevOps',
    'bio' => '', 'email' => 'dtc245180186@ictu.edu.vn', 'github' => '', 'linkedin' => ''
];
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
$skills   = $pdo->query('SELECT * FROM skills ORDER BY level DESC')->fetchAll();
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 30')->fetchAll();
$csrf = csrf_token();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? 'MySQL 8.0' : 'SQLite Local';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bảng Điều Khiển Quản Trị — Portfolio Đề 13</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css?v=4.0">
  <style>
    /* ========================================================
       PREMIUM WHITE & LIGHT SAAS ADMIN DASHBOARD
       ======================================================== */
    body {
      background-color: #f1f5f9;
      color: #0f172a;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .admin-container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 24px 20px 60px;
    }

    /* Top Navigation Bar */
    .admin-navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 14px 24px;
      margin-bottom: 24px;
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .brand-avatar {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
    }

    .brand-info h1 {
      font-size: 1.08rem;
      font-weight: 700;
      color: #0f172a;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .brand-info p {
      font-size: 0.78rem;
      color: #64748b;
      margin: 2px 0 0;
    }

    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-nav {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      font-size: 0.84rem;
      font-weight: 600;
      border-radius: 10px;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .btn-nav-primary {
      background: #eff6ff;
      color: #2563eb;
      border: 1px solid #bfdbfe;
    }
    .btn-nav-primary:hover {
      background: #dbeafe;
      color: #1d4ed8;
      transform: translateY(-1px);
    }

    .btn-nav-danger {
      background: #fef2f2;
      color: #dc2626;
      border: 1px solid #fecaca;
    }
    .btn-nav-danger:hover {
      background: #fee2e2;
      color: #b91c1c;
      transform: translateY(-1px);
    }

    /* Alerts */
    .alert-banner {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 22px;
      animation: slideDown 0.3s ease-out;
    }

    .alert-success {
      background: #ecfdf5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }

    .alert-error {
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* DevOps Summary Card */
    .devops-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 22px 24px;
      margin-bottom: 24px;
      box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    }

    .devops-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 18px;
      padding-bottom: 14px;
      border-bottom: 1px solid #f1f5f9;
    }

    .devops-title {
      font-size: 1.12rem;
      font-weight: 700;
      color: #1e3a8a;
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 0;
    }

    .devops-icon-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background: #eff6ff;
      color: #2563eb;
      font-size: 1.05rem;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
      padding: 5px 14px;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 14px;
      margin-bottom: 18px;
    }

    .metric-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px 16px;
      transition: all 0.2s ease;
    }

    .metric-box:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
    }

    .metric-label {
      font-size: 0.72rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 6px;
    }

    .metric-num {
      font-size: 1.65rem;
      font-weight: 800;
      line-height: 1.2;
    }

    .color-blue { color: #2563eb; }
    .color-purple { color: #7c3aed; }
    .color-amber { color: #d97706; }
    .color-emerald { color: #059669; }

    .quick-links {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .quick-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 8px;
      font-size: 0.82rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .quick-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .quick-orange { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .quick-pink { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
    .quick-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }

    .quick-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    /* Main Dashboard Layout (2 Columns) */
    .dashboard-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
    }

    @media (max-width: 992px) {
      .dashboard-layout {
        grid-template-columns: 1fr;
      }
    }

    .section-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .card-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 12px;
      border-bottom: 1px solid #f1f5f9;
    }

    .card-header-row h2 {
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 0;
    }

    /* Forms */
    .admin-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .full-width {
      grid-column: 1 / -1;
    }

    .form-field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-field label {
      font-size: 0.8rem;
      font-weight: 600;
      color: #334155;
    }

    .form-input {
      width: 100%;
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 0.88rem;
      font-family: inherit;
      color: #0f172a;
      transition: all 0.2s ease;
      outline: none;
    }

    .form-input:focus {
      background: #ffffff;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .btn-submit {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
      border: none;
      padding: 11px 20px;
      border-radius: 10px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, #1d4ed8, #1e40af);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
    }

    /* Tables */
    .table-responsive {
      overflow-x: auto;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      background: #ffffff;
    }

    .modern-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .modern-table th {
      background: #f8fafc;
      padding: 12px 16px;
      font-size: 0.76rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-bottom: 1px solid #e2e8f0;
    }

    .modern-table td {
      padding: 12px 16px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 0.88rem;
      color: #1e293b;
      vertical-align: middle;
    }

    .modern-table tr:last-child td {
      border-bottom: none;
    }

    .modern-table tr:hover td {
      background: #f8fafc;
    }

    /* Badges */
    .badge-tag {
      display: inline-block;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      padding: 2px 8px;
      border-radius: 6px;
      font-size: 0.74rem;
      font-weight: 600;
      margin-right: 4px;
      margin-bottom: 4px;
    }

    .btn-icon-del {
      background: #fee2e2;
      color: #dc2626;
      border: 1px solid #fecaca;
      padding: 6px 10px;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.2s ease;
    }

    .btn-icon-del:hover {
      background: #dc2626;
      color: #ffffff;
      border-color: #dc2626;
    }

    /* Progress bar for skill */
    .skill-progress-bar {
      width: 100%;
      height: 8px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
      margin-top: 4px;
    }

    .skill-progress-fill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }

    /* Messages List */
    .message-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 12px;
      transition: all 0.2s ease;
    }

    .message-card:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .message-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .message-sender {
      font-weight: 700;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .sender-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #e0e7ff;
      color: #4338ca;
      font-size: 0.78rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .message-email {
      font-size: 0.8rem;
      color: #64748b;
      font-weight: 400;
    }

    .message-time {
      font-size: 0.74rem;
      color: #94a3b8;
    }

    .message-content {
      font-size: 0.88rem;
      color: #334155;
      line-height: 1.5;
      background: #ffffff;
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #f1f5f9;
    }
  </style>
</head>
<body>

<div class="admin-container">

  <!-- Top Navbar -->
  <header class="admin-navbar">
    <div class="navbar-brand">
      <div class="brand-avatar">VH</div>
      <div class="brand-info">
        <h1>
          <span>Bảng Quản Trị Hệ Thống</span>
          <span style="font-size: 0.75rem; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px; font-weight: 600;">Đề Số 13</span>
        </h1>
        <p>Quản trị viên: <strong><?= e($profile['fullname']) ?></strong> (MSSV: DTC245180186)</p>
      </div>
    </div>
    <div class="navbar-actions">
      <a href="/" target="_blank" class="btn-nav btn-nav-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        <span>Xem Trang Chủ</span>
      </a>
      <a href="/admin/logout.php" class="btn-nav btn-nav-danger" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        <span>Đăng Xuất</span>
      </a>
    </div>
  </header>

  <!-- Flash Message -->
  <?php if ($msg): ?>
  <div class="alert-banner alert-<?= $msg_type === 'error' ? 'error' : 'success' ?>">
    <span><?= $msg_type === 'error' ? '⚠️' : '✓' ?></span>
    <span><?= e($msg) ?></span>
  </div>
  <?php endif; ?>

  <!-- DevOps & System Overview Bar -->
  <section class="devops-card">
    <div class="devops-header">
      <h2 class="devops-title">
        <span class="devops-icon-badge">⚡</span>
        <span>Trạng Thái Hạ Tầng DevOps & Microservices</span>
      </h2>
      <div class="status-pill">
        <span class="status-dot"></span>
        <span>Hệ Thống Hoạt Động 100% (Healthy)</span>
      </div>
    </div>

    <div class="metrics-grid">
      <div class="metric-box">
        <div class="metric-label">Dự án công khai</div>
        <div class="metric-num color-blue"><?= count($projects) ?></div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Kỹ năng năng lực</div>
        <div class="metric-num color-purple"><?= count($skills) ?></div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Tin nhắn liên hệ</div>
        <div class="metric-num color-amber"><?= count($messages) ?></div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Môi trường & DB</div>
        <div style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-top: 6px;">PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> • <?= $driver ?></div>
      </div>
    </div>

    <div class="quick-links">
      <a href="/health.php" target="_blank" class="quick-btn quick-blue">
        <span>🔍</span> <span>Healthcheck API</span>
      </a>
      <a href="/pma/" target="_blank" class="quick-btn quick-orange">
        <span>🗄️</span> <span>Quản trị CSDL (phpMyAdmin)</span>
      </a>
      <a href="http://localhost:3000" target="_blank" class="quick-btn quick-pink">
        <span>📊</span> <span>Grafana Dashboard (:3000)</span>
      </a>
      <a href="http://localhost:9090" target="_blank" class="quick-btn quick-yellow">
        <span>📈</span> <span>Prometheus Metrics (:9090)</span>
      </a>
    </div>
  </section>

  <!-- 2-Column Content Grid -->
  <div class="dashboard-layout">

    <!-- CỘT 1: Quản lý Dự án -->
    <div class="section-card">
      <div class="card-header-row">
        <h2><span>🚀</span> Quản Lý Dự Án (<?= count($projects) ?>)</h2>
      </div>

      <!-- Danh sách Dự án -->
      <div class="table-responsive">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Tên Dự Án</th>
              <th>Công Nghệ</th>
              <th style="width: 80px; text-align: center;">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($projects)): ?>
            <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Chưa có dự án nào được tạo.</td></tr>
            <?php else: foreach ($projects as $p): ?>
            <tr>
              <td>
                <div style="font-weight: 600; color: #0f172a;"><?= e($p['title']) ?></div>
                <?php if (!empty($p['description'])): ?>
                <div style="font-size: 0.78rem; color: #64748b;"><?= e(mb_strimwidth($p['description'], 0, 50, '...')) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php foreach (explode(',', $p['tech'] ?? '') as $tag): if(trim($tag)): ?>
                  <span class="badge-tag"><?= e(trim($tag)) ?></span>
                <?php endif; endforeach; ?>
              </td>
              <td style="text-align: center;">
                <form method="post" onsubmit="return confirm('Bạn có chắc muốn xoá dự án này?')">
                  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                  <input type="hidden" name="action" value="del_project">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn-icon-del" title="Xoá dự án">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <span>Xoá</span>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Form Thêm Dự Án Mới -->
      <form method="post" style="display: flex; flex-direction: column; gap: 12px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_project">
        <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b;">+ Thêm Dự Án Mới</div>

        <div class="admin-form-grid">
          <div class="form-field full-width">
            <label>Tên dự án *</label>
            <input class="form-input" name="title" placeholder="VD: Hệ Thống Giám Sát Cloud DevOps" required>
          </div>
          <div class="form-field">
            <label>Tags công nghệ</label>
            <input class="form-input" name="tech" placeholder="VD: Docker, Nginx, Prometheus">
          </div>
          <div class="form-field">
            <label>Đường dẫn dự án</label>
            <input class="form-input" name="link" placeholder="VD: https://github.com/vubahung222/portfolio-de13" value="https://github.com/vubahung222/portfolio-de13">
          </div>
          <div class="form-field full-width">
            <label>Mô tả tóm tắt</label>
            <textarea class="form-input" name="description" rows="2" placeholder="Mô tả chức năng chính của dự án..."></textarea>
          </div>
        </div>

        <button type="submit" class="btn-submit" style="align-self: flex-start;">
          <span>+ Lưu Dự Án</span>
        </button>
      </form>
    </div>

    <!-- CỘT 2: Quản lý Kỹ năng & Hồ sơ -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

      <!-- Quản lý Kỹ năng -->
      <div class="section-card">
        <div class="card-header-row">
          <h2><span>💡</span> Quản Lý Kỹ Năng (<?= count($skills) ?>)</h2>
        </div>

        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Tên Kỹ Năng</th>
                <th>Thành Thạo</th>
                <th style="width: 80px; text-align: center;">Thao Tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($skills)): ?>
              <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Chưa có kỹ năng nào.</td></tr>
              <?php else: foreach ($skills as $s): ?>
              <tr>
                <td style="font-weight: 600; color: #0f172a;">
                  <?= e($s['name']) ?>
                </td>
                <td style="width: 45%;">
                  <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 700; color: #475569;">
                    <span>Tiến độ</span>
                    <span><?= (int)$s['level'] ?>%</span>
                  </div>
                  <div class="skill-progress-bar">
                    <div class="skill-progress-fill" style="width: <?= (int)$s['level'] ?>%;"></div>
                  </div>
                </td>
                <td style="text-align: center;">
                  <form method="post" onsubmit="return confirm('Bạn có chắc muốn xoá kỹ năng này?')">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="action" value="del_skill">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button type="submit" class="btn-icon-del">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                      <span>Xoá</span>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Form Thêm Kỹ Năng -->
        <form method="post" style="display: flex; gap: 10px; background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; align-items: flex-end;">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="add_skill">
          <div class="form-field" style="flex: 2;">
            <label>Tên kỹ năng *</label>
            <input class="form-input" name="name" placeholder="VD: Kubernetes, Terraform" required>
          </div>
          <div class="form-field" style="flex: 1;">
            <label>Mức độ (%)</label>
            <input class="form-input" name="level" type="number" min="0" max="100" value="80" required>
          </div>
          <button type="submit" class="btn-submit" style="padding: 10px 16px;">
            <span>+ Thêm</span>
          </button>
        </form>
      </div>

      <!-- Hồ sơ Cá nhân -->
      <div class="section-card">
        <div class="card-header-row">
          <h2><span>👤</span> Cập Nhật Hồ Sơ Cá Nhân</h2>
        </div>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="update_profile">
          <input type="hidden" name="id" value="<?= (int)$profile['id'] ?>">

          <div class="admin-form-grid">
            <div class="form-field">
              <label>Họ và tên *</label>
              <input class="form-input" name="fullname" value="<?= e($profile['fullname']) ?>" required>
            </div>
            <div class="form-field">
              <label>Chức danh hiển thị</label>
              <input class="form-input" name="title" value="<?= e($profile['title']) ?>" required>
            </div>
            <div class="form-field">
              <label>Địa chỉ Email</label>
              <input class="form-input" name="email" type="email" value="<?= e($profile['email']) ?>" required>
            </div>
            <div class="form-field">
              <label>Đường dẫn GitHub</label>
              <input class="form-input" name="github" value="<?= e($profile['github']) ?>">
            </div>
            <div class="form-field full-width">
              <label>Đường dẫn LinkedIn</label>
              <input class="form-input" name="linkedin" value="<?= e($profile['linkedin']) ?>">
            </div>
            <div class="form-field full-width">
              <label>Tiểu sử giới thiệu (Bio)</label>
              <textarea class="form-input" name="bio" rows="2"><?= e($profile['bio']) ?></textarea>
            </div>
          </div>

          <button type="submit" class="btn-submit" style="margin-top: 14px;">
            <span>✓ Lưu Thay Đổi Hồ Sơ</span>
          </button>
        </form>
      </div>

      <!-- Đổi Mật Khẩu Admin -->
      <div class="section-card">
        <div class="card-header-row">
          <h2><span>🔑</span> Bảo Mật & Đổi Mật Khẩu Admin</h2>
        </div>
        <form method="post" style="display: flex; gap: 12px; align-items: flex-end;">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="change_password">
          <div class="form-field" style="flex: 1;">
            <label>Mật khẩu mới (Tối thiểu 8 ký tự, băm Bcrypt cost=12)</label>
            <input class="form-input" type="password" name="new_password" placeholder="Nhập mật khẩu an toàn mới..." required>
          </div>
          <button type="submit" class="btn-submit" style="padding: 10px 18px;">
            <span>Đổi Mật Khẩu</span>
          </button>
        </form>
      </div>

    </div>

  </div>

  <!-- Full-Width: Hộp thư Tin nhắn Khách hàng -->
  <section class="section-card">
    <div class="card-header-row">
      <h2><span>📬</span> Hộp Thư Tin Nhắn Khách Hàng (<?= count($messages) ?>)</h2>
      <span style="font-size: 0.8rem; color: #64748b;">Lưu trữ trong CSDL MySQL bảng `messages`</span>
    </div>

    <?php if (empty($messages)): ?>
      <div style="text-align: center; padding: 30px; color: #94a3b8; font-size: 0.95rem;">
        <span style="font-size: 2rem; display: block; margin-bottom: 8px;">📭</span>
        Chưa có tin nhắn liên hệ nào từ khách truy cập.
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 14px;">
        <?php foreach ($messages as $m): 
          $initial = mb_substr($m['name'], 0, 1, 'UTF-8');
        ?>
        <div class="message-card">
          <div class="message-meta">
            <div class="message-sender">
              <div class="sender-avatar"><?= e(strtoupper($initial)) ?></div>
              <div>
                <div><?= e($m['name']) ?></div>
                <div class="message-email"><?= e($m['email']) ?></div>
              </div>
            </div>
            <div class="message-time"><?= e(date('H:i d/m/Y', strtotime($m['created_at'] ?? 'now'))) ?></div>
          </div>
          <div class="message-content">
            <?= nl2br(e($m['body'])) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>

</body>
</html>
