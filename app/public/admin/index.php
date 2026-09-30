<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';

if (is_logged_in()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Phiên làm việc đã hết hạn. Vui lòng tải lại.';
    } elseif (login(db(), $_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Tài khoản hoặc mật khẩu không chính xác.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản trị — Đăng nhập hệ thống</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css?v=3.4">
  <style>
    /* ========================================================
       Trang Đăng Nhập Quản Trị - Tinh tế, Hiện đại & Thực tế
       ======================================================== */
    body.login-page-body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 30px 16px;
      background: #f8fafc;
      position: relative;
      overflow-x: hidden;
    }

    /* Họa tiết nền lưới & ambient soft glow */
    .login-bg-dots {
      position: fixed;
      inset: 0;
      background-image: radial-gradient(rgba(37, 99, 235, 0.08) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
      z-index: 0;
    }

    .login-glow-1 {
      position: fixed;
      top: 10%;
      left: 15%;
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(37, 99, 235, 0) 70%);
      filter: blur(60px);
      pointer-events: none;
      z-index: 0;
      animation: pulseGlow 10s ease-in-out infinite alternate;
    }

    .login-glow-2 {
      position: fixed;
      bottom: 10%;
      right: 15%;
      width: 420px;
      height: 420px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0) 70%);
      filter: blur(60px);
      pointer-events: none;
      z-index: 0;
      animation: pulseGlow 8s ease-in-out infinite alternate-reverse;
    }

    @keyframes pulseGlow {
      0% { transform: scale(0.9) translate(0, 0); opacity: 0.7; }
      100% { transform: scale(1.1) translate(20px, 20px); opacity: 1; }
    }

    /* Nút quay lại trang chủ */
    .back-home-btn {
      position: absolute;
      top: 24px;
      left: 28px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 18px;
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: 999px;
      color: var(--text-body);
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
      transition: all var(--transition);
      z-index: 10;
    }

    .back-home-btn:hover {
      background: #eff6ff;
      color: var(--accent);
      border-color: #bfdbfe;
      transform: translateX(-3px);
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
    }

    .back-home-btn svg {
      transition: transform var(--transition);
    }

    .back-home-btn:hover svg {
      transform: translateX(-2px);
    }

    /* Hộp đăng nhập trung tâm */
    .login-card-container {
      width: 100%;
      max-width: 430px;
      position: relative;
      z-index: 1;
      margin: 40px auto 16px;
    }

    .login-card {
      background: #ffffff;
      border: 1px solid rgba(226, 232, 240, 0.95);
      border-radius: 24px;
      box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08), 
                  0 0 1px 1px rgba(15, 23, 42, 0.04);
      overflow: hidden;
      position: relative;
    }

    /* Thanh gradient viền trên */
    .login-top-bar {
      height: 5px;
      background: linear-gradient(90deg, #2563eb, #6366f1, #06b6d4);
      width: 100%;
    }

    .login-card-inner {
      padding: 34px 32px 30px;
    }

    /* Header trong card */
    .login-header {
      text-align: center;
      margin-bottom: 26px;
    }

    .login-badge-icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 12px;
      background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      box-shadow: 0 10px 22px -4px rgba(37, 99, 235, 0.38);
      position: relative;
    }

    .login-header h1 {
      font-size: 1.38rem;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -0.02em;
      margin-bottom: 4px;
    }

    .login-header p {
      color: var(--muted);
      font-size: 0.85rem;
      margin: 0;
    }

    /* Form Fields */
    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.84rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 6px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      width: 18px;
      height: 18px;
      color: #94a3b8;
      pointer-events: none;
      transition: color var(--transition);
    }

    .form-control {
      width: 100%;
      padding: 12px 16px 12px 44px;
      font-size: 0.92rem;
      font-family: inherit;
      color: var(--text);
      background: #f8fafc;
      border: 1.5px solid var(--border);
      border-radius: 12px;
      transition: all var(--transition);
      outline: none;
    }

    .form-control:focus {
      background: #ffffff;
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .input-wrapper:focus-within .input-icon {
      color: var(--accent);
    }

    /* Nút xem/ẩn mật khẩu */
    .toggle-password-btn {
      position: absolute;
      right: 12px;
      background: transparent;
      border: none;
      padding: 6px;
      cursor: pointer;
      color: #94a3b8;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color var(--transition);
    }

    .toggle-password-btn:hover {
      color: var(--text);
      background: #f1f5f9;
      transform: none;
      box-shadow: none;
    }

    /* Hàng meta bảo mật */
    .form-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: -4px;
      margin-bottom: 20px;
      font-size: 0.8rem;
    }

    .security-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #16a34a;
      font-weight: 500;
      background: #f0fdf4;
      padding: 3px 10px;
      border-radius: 999px;
      border: 1px solid #bbf7d0;
    }

    .security-dot {
      width: 6px;
      height: 6px;
      background: #16a34a;
      border-radius: 50%;
      box-shadow: 0 0 6px #22c55e;
    }

    /* Nút submit đăng nhập */
    .btn-login-submit {
      width: 100%;
      padding: 13px 20px;
      font-size: 0.98rem;
      font-weight: 700;
      color: #ffffff;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: none;
      border-radius: 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all var(--transition);
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.32);
    }

    .btn-login-submit:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(37, 99, 235, 0.42);
    }

    .btn-login-submit:active {
      transform: translateY(0);
    }

    /* Thông báo lỗi */
    .login-error-alert {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 18px;
      font-size: 0.88rem;
      font-weight: 500;
      animation: shake 0.4s ease-in-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }

    /* Đường phân cách */
    .demo-divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 22px 0 16px;
      color: #94a3b8;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .demo-divider::before,
    .demo-divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #e2e8f0;
    }

    .demo-divider span {
      padding: 0 10px;
    }

    /* ========================================================
       KHU VỰC TÀI KHOẢN MẪU - CLICK VÀO LÀ TỰ ĐIỀN
       ======================================================== */
    .demo-bottom-box {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 14px;
      padding: 13px 15px;
      position: relative;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;
    }

    .demo-bottom-box:hover {
      background: #f0f7ff;
      border-color: #93c5fd;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
    }

    .demo-bottom-box:active {
      transform: translateY(0);
    }

    .demo-bottom-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .demo-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.76rem;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .demo-click-hint {
      font-size: 0.74rem;
      color: var(--accent);
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: color 0.2s ease;
    }

    .demo-bottom-box:hover .demo-click-hint {
      color: var(--accent-hover);
    }

    .demo-credentials-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .demo-field-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 7px 11px;
      display: flex;
      flex-direction: column;
      transition: all 0.2s ease;
    }

    .demo-bottom-box:hover .demo-field-card {
      border-color: #bfdbfe;
      background: #ffffff;
    }

    .demo-field-label {
      font-size: 0.66rem;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 2px;
      font-weight: 600;
    }

    .demo-field-val {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.84rem;
      font-weight: 600;
      color: #0f172a;
    }

    /* Chân trang đăng nhập */
    .login-footer {
      text-align: center;
      margin-top: 18px;
      font-size: 0.8rem;
      color: var(--muted);
    }

    .login-footer strong {
      color: var(--text);
    }

    @media (max-width: 480px) {
      .back-home-btn {
        position: static;
        margin-bottom: 16px;
        align-self: flex-start;
      }
      .login-card-inner {
        padding: 26px 20px 22px;
      }
      .demo-credentials-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body class="login-page-body">

  <div class="login-bg-dots"></div>
  <div class="login-glow-1"></div>
  <div class="login-glow-2"></div>

  <!-- Nút quay lại trang chủ -->
  <a href="/" class="back-home-btn">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="19" y1="12" x2="5" y2="12"></line>
      <polyline points="12 19 5 12 12 5"></polyline>
    </svg>
    <span>Trang chủ</span>
  </a>

  <div class="login-card-container">
    <div class="login-card">
      <div class="login-top-bar"></div>

      <div class="login-card-inner">
        <!-- Icon & Tiêu đề -->
        <div class="login-header">
          <div class="login-badge-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
          </div>
          <h1>Cổng Quản Trị Hệ Thống</h1>
          <p>Đăng nhập để cập nhật Portfolio &amp; Giám sát dịch vụ</p>
        </div>

        <!-- Thông báo lỗi nếu có -->
        <?php if ($error): ?>
          <div class="login-error-alert" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>

        <!-- Form Đăng nhập chính -->
        <form method="post" id="adminLoginForm" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <!-- Tên đăng nhập -->
          <div class="form-group">
            <label class="form-label" for="username">Tên đăng nhập</label>
            <div class="input-wrapper">
              <input type="text" id="username" name="username" class="form-control" placeholder="Tên tài khoản quản trị" required autofocus>
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          </div>

          <!-- Mật khẩu -->
          <div class="form-group">
            <label class="form-label" for="password">Mật khẩu</label>
            <div class="input-wrapper">
              <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Hiện/Ẩn mật khẩu" aria-label="Hiện/Ẩn mật khẩu">
                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <!-- Bảo mật badge -->
          <div class="form-meta-row">
            <span class="security-badge">
              <span class="security-dot"></span>
              Bảo mật CSRF &amp; Session
            </span>
            <span style="color:var(--muted);font-size:0.76rem;">Đề tài 13 • ICTU</span>
          </div>

          <!-- Nút đăng nhập chính -->
          <button type="submit" class="btn-login-submit">
            <span>Đăng nhập</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
        </form>

        <!-- Đường phân cách -->
        <div class="demo-divider">
          <span>Tài khoản &amp; mật khẩu demo</span>
        </div>

        <!-- ==============================================
             KHỐI TÀI KHOẢN DEMO (NHẤN VÀO LÀ TỰ ĐIỀN)
             ============================================== -->
        <div class="demo-bottom-box" id="demoAccountBox" title="Nhấn vào để tự động điền tài khoản & mật khẩu">
          <div class="demo-bottom-header">
            <span class="demo-tag">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              Tài khoản mẫu:
            </span>
            <span class="demo-click-hint" id="demoHintText">Chạm để tự điền ↵</span>
          </div>

          <!-- Thẻ thông tin tài khoản & mật khẩu (Click vào là tự điền) -->
          <div class="demo-credentials-grid">
            <div class="demo-field-card">
              <span class="demo-field-label">Tài khoản</span>
              <span class="demo-field-val">admin</span>
            </div>
            <div class="demo-field-card">
              <span class="demo-field-label">Mật khẩu</span>
              <span class="demo-field-val">Admin@12345</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Chân trang -->
    <div class="login-footer">
      <p>&copy; 2026 <strong>DTC245180186</strong> — Nền tảng Portfolio &amp; DevOps Stack</p>
    </div>
  </div>

  <script>
    // Hiện / ẩn mật khẩu
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const eyeIcon = document.getElementById('eyeIcon');
    const usernameInput = document.getElementById('username');
    const demoBox = document.getElementById('demoAccountBox');
    const demoHintText = document.getElementById('demoHintText');

    togglePasswordBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      const isPass = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPass ? 'text' : 'password');
      if (isPass) {
        eyeIcon.innerHTML = `
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
          <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
      } else {
        eyeIcon.innerHTML = `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
          <circle cx="12" cy="12" r="3"></circle>
        `;
      }
    });

    // Chỉ cần ấn vào khối tài khoản demo là tự động điền luôn
    demoBox.addEventListener('click', function() {
      usernameInput.value = 'admin';
      passwordInput.value = 'Admin@12345';
      
      // Hiệu ứng phản hồi trạng thái
      if (demoHintText) {
        demoHintText.innerHTML = '✓ Đã tự điền';
        demoHintText.style.color = '#16a34a';
        setTimeout(function() {
          demoHintText.innerHTML = 'Chạm để tự điền ↵';
          demoHintText.style.color = '';
        }, 1800);
      }

      // Đưa con trỏ vào form hoặc nút Đăng nhập
      passwordInput.focus();
    });
  </script>
</body>
</html>
