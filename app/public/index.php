<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$pdo = db();

// Xử lý form liên hệ
$flash = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'contact') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $body) {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, body) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $body]);
        $flash = 'Cảm ơn bạn! Tin nhắn đã được gửi thành công.';
    } else {
        $flash = 'Vui lòng điền đầy đủ và đúng định dạng email.';
    }
}

$profile  = $pdo->query('SELECT * FROM profile LIMIT 1')->fetch();
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
$skills   = $pdo->query('SELECT * FROM skills ORDER BY level DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portfolio cá nhân của <?= e($profile['fullname'] ?? 'Sinh viên CNTT') ?> — Lập trình viên Full-stack & Chuyên gia DevOps.">
  <title><?= e($profile['fullname'] ?? 'Portfolio') ?> — Portfolio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/style.css?v=3.0">
</head>
<body>
  <header class="hero">
    <nav>
      <span class="brand"><?= e($profile['fullname'] ?? 'Portfolio') ?></span>
      <div class="links">
        <a href="#about">Giới thiệu</a>
        <a href="#skills">Kỹ năng</a>
        <a href="#projects">Dự án</a>
        <a href="#contact">Liên hệ</a>
        <a href="/admin/index.php" class="admin-link">&#9998; Quản trị</a>
      </div>
    </nav>
    <div class="hero-content">
      <div class="hero-badge">&#9679; Sẵn sàng làm việc</div>
      <h1><?= e($profile['fullname'] ?? '') ?></h1>
      <p class="title"><?= e($profile['title'] ?? '') ?></p>
      <div class="hero-scroll">
        <a href="#projects" class="btn-hero btn-hero-primary">Xem dự án &darr;</a>
        <a href="#contact" class="btn-hero btn-hero-secondary">Liên hệ ngay</a>
      </div>
    </div>
  </header>

  <!-- Dải công nghệ -->
  <div class="tech-ribbon">
    <div class="tech-ribbon-inner">
      <span>Docker</span><span>Nginx</span><span>PHP 8.3</span>
      <span>MySQL 8</span><span>Prometheus</span><span>Grafana</span>
      <span>Loki</span><span>Promtail</span><span>HTTPS/TLS</span>
      <span>Linux</span><span>GitHub</span><span>CI/CD</span>
      <!-- lặp lại để cuộn vô tận -->
      <span>Docker</span><span>Nginx</span><span>PHP 8.3</span>
      <span>MySQL 8</span><span>Prometheus</span><span>Grafana</span>
      <span>Loki</span><span>Promtail</span><span>HTTPS/TLS</span>
      <span>Linux</span><span>GitHub</span><span>CI/CD</span>
    </div>
  </div>

  <main>
    <section id="about" class="card">
      <h2>Giới thiệu</h2>
      <p><?= nl2br(e($profile['bio'] ?? '')) ?></p>
      <div class="social">
        <?php if (!empty($profile['email'])): ?>
          <a href="mailto:<?= e($profile['email']) ?>">&#9993; <?= e($profile['email']) ?></a>
        <?php endif; ?>
        <?php if (!empty($profile['github'])): ?>
          <a href="<?= e($profile['github']) ?>" target="_blank" rel="noopener">&#9654; GitHub</a>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
          <a href="<?= e($profile['linkedin']) ?>" target="_blank" rel="noopener">&#9670; LinkedIn</a>
        <?php endif; ?>
      </div>
    </section>

    <section id="skills" class="card">
      <h2>Kỹ năng</h2>
      <?php foreach ($skills as $s): ?>
        <div class="skill">
          <div class="skill-header">
            <span><?= e($s['name']) ?></span>
            <span class="level-num"><?= (int)$s['level'] ?>%</span>
          </div>
          <div class="bar">
            <div class="fill" style="width: <?= (int)$s['level'] ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </section>

    <section id="projects" class="card">
      <h2>Dự án</h2>
      <div class="grid">
        <?php foreach ($projects as $p): ?>
          <article class="project">
            <h3><?= e($p['title']) ?></h3>
            <p><?= e($p['description']) ?></p>
            <div class="tech">
              <?php foreach (explode(',', $p['tech'] ?? '') as $t): ?>
                <?php if (trim($t)): ?>
                  <span><?= e(trim($t)) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($p['link'])): ?>
              <a href="<?= e($p['link']) ?>" target="_blank" rel="noopener">Xem thêm &rarr;</a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="contact" class="card">
      <h2>Liên hệ</h2>
      <?php if ($flash): ?><p class="flash"><?= e($flash) ?></p><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="contact">
        <input type="text" name="name" placeholder="Họ tên của bạn" required>
        <input type="email" name="email" placeholder="Địa chỉ email" required>
        <textarea name="body" placeholder="Nội dung tin nhắn..." rows="4" required></textarea>
        <button type="submit">Gửi tin nhắn &rarr;</button>
      </form>
    </section>
  </main>

  <footer>
    <p>&copy; <?= date('Y') ?> <span><?= e($profile['fullname'] ?? '') ?></span>. Triển khai bằng <span>Docker Compose</span>.</p>
  </footer>
</body>
</html>
