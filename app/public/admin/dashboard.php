<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';
require_login();

$pdo = db();
$msg = '';

// -------- Handle actions (all CSRF-protected) ----------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $msg = 'CSRF token khong hop le.';
    } else {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'add_project':
                $stmt = $pdo->prepare('INSERT INTO projects (title, description, link, tech) VALUES (?,?,?,?)');
                $stmt->execute([$_POST['title'], $_POST['description'], $_POST['link'], $_POST['tech']]);
                $msg = 'Da them du an.';
                break;
            case 'del_project':
                $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Da xoa du an.';
                break;
            case 'add_skill':
                $stmt = $pdo->prepare('INSERT INTO skills (name, level) VALUES (?,?)');
                $stmt->execute([$_POST['name'], max(0, min(100, (int)$_POST['level']))]);
                $msg = 'Da them ky nang.';
                break;
            case 'del_skill':
                $pdo->prepare('DELETE FROM skills WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Da xoa ky nang.';
                break;
            case 'update_profile':
                $stmt = $pdo->prepare('UPDATE profile SET fullname=?, title=?, bio=?, email=?, github=?, linkedin=? WHERE id=?');
                $stmt->execute([$_POST['fullname'], $_POST['title'], $_POST['bio'], $_POST['email'], $_POST['github'], $_POST['linkedin'], (int)$_POST['id']]);
                $msg = 'Da cap nhat thong tin.';
                break;
            case 'change_password':
                $new = $_POST['new_password'] ?? '';
                if (strlen($new) < 8) {
                    $msg = 'Mat khau moi phai tu 8 ky tu tro len.';
                } else {
                    $hash = password_hash($new, PASSWORD_BCRYPT);
                    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash, $_SESSION['user_id']]);
                    $msg = 'Da doi mat khau.';
                }
                break;
        }
    }
}

$profile  = $pdo->query('SELECT * FROM profile LIMIT 1')->fetch();
$projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
$skills   = $pdo->query('SELECT * FROM skills ORDER BY level DESC')->fetchAll();
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 20')->fetchAll();
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản trị — Dashboard</title>
  <link rel="stylesheet" href="/assets/style.css?v=3.0">
</head>
<body>
  <div class="admin-wrap">
    <div class="topbar">
      <h1>&#9878; Quản trị</h1>
      <div>
        <a href="/" class="btn-sm">&#8592; Xem trang web</a>
        <a href="/admin/logout.php" class="btn-sm">&#10148; Đăng xuất</a>
      </div>
    </div>
    <?php if ($msg): ?><p class="flash"><?= e($msg) ?></p><?php endif; ?>

    <!-- Thông tin cá nhân -->
    <div class="card">
      <h2>Thông tin cá nhân</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="update_profile">
        <input type="hidden" name="id" value="<?= (int)$profile['id'] ?>">
        <input name="fullname" value="<?= e($profile['fullname']) ?>" placeholder="Họ tên">
        <input name="title" value="<?= e($profile['title']) ?>" placeholder="Chức danh / Mô tả ngắn">
        <textarea name="bio" rows="3" placeholder="Giới thiệu bản thân"><?= e($profile['bio']) ?></textarea>
        <input name="email" value="<?= e($profile['email']) ?>" placeholder="Địa chỉ Email">
        <input name="github" value="<?= e($profile['github']) ?>" placeholder="Đường dẫn GitHub">
        <input name="linkedin" value="<?= e($profile['linkedin']) ?>" placeholder="Đường dẫn LinkedIn">
        <button type="submit">&#10003; Lưu thay đổi</button>
      </form>
    </div>

    <!-- Dự án -->
    <div class="card">
      <h2>Dự án</h2>
      <table>
        <tr><th>Tên dự án</th><th>Công nghệ</th><th>Thao tác</th></tr>
        <?php foreach ($projects as $p): ?>
        <tr>
          <td><?= e($p['title']) ?></td>
          <td><?= e($p['tech']) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Bạn có chắc muốn xoá dự án này?')">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="action" value="del_project">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn-sm danger">&#128465; Xoá</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <h3 style="margin-top:20px;">&#43; Thêm dự án mới</h3>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_project">
        <input name="title" placeholder="Tên dự án" required>
        <textarea name="description" rows="2" placeholder="Mô tả ngắn về dự án"></textarea>
        <input name="tech" placeholder="Công nghệ (VD: PHP, MySQL, Docker)">
        <input name="link" placeholder="Đường dẫn GitHub / Demo">
        <button type="submit">&#43; Thêm dự án</button>
      </form>
    </div>

    <!-- Kỹ năng -->
    <div class="card">
      <h2>Kỹ năng</h2>
      <table>
        <tr><th>Tên kỹ năng</th><th>Mức độ</th><th>Thao tác</th></tr>
        <?php foreach ($skills as $s): ?>
        <tr>
          <td><?= e($s['name']) ?></td>
          <td><?= (int)$s['level'] ?>%</td>
          <td>
            <form method="post" onsubmit="return confirm('Bạn có chắc muốn xoá kỹ năng này?')">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="action" value="del_skill">
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button class="btn-sm danger">&#128465; Xoá</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <h3 style="margin-top:20px;">&#43; Thêm kỹ năng mới</h3>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_skill">
        <input name="name" placeholder="Tên kỹ năng" required>
        <input name="level" type="number" min="0" max="100" value="70" placeholder="Mức độ (0–100)">
        <button type="submit">&#43; Thêm kỹ năng</button>
      </form>
    </div>

    <!-- Tin nhắn liên hệ -->
    <div class="card">
      <h2>Tin nhắn liên hệ</h2>
      <?php if (empty($messages)): ?>
        <p style="color:var(--muted);font-size:.9rem;">Chưa có tin nhắn nào.</p>
      <?php else: ?>
      <table>
        <tr><th>Họ tên</th><th>Email</th><th>Nội dung</th><th>Thời gian</th></tr>
        <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= e($m['name']) ?></td>
          <td><?= e($m['email']) ?></td>
          <td><?= e($m['body']) ?></td>
          <td><?= e($m['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>
    </div>

    <!-- Đổi mật khẩu -->
    <div class="card">
      <h2>Đổi mật khẩu</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="change_password">
        <input type="password" name="new_password" placeholder="Mật khẩu mới (tối thiểu 8 ký tự)" required>
        <button type="submit">&#128274; Đổi mật khẩu</button>
      </form>
    </div>
  </div>
</body>
</html>
