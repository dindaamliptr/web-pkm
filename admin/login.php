<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/lib.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$now = time();
$lockedUntil = $_SESSION['lock_until'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($lockedUntil > $now) {
        $error = 'Terlalu banyak percobaan. Coba lagi dalam ' . ($lockedUntil - $now) . ' detik.';
    } else {
        $stmt = db()->prepare('SELECT id, password_hash FROM admin WHERE username = ?');
        $stmt->execute([trim((string) ($_POST['username'] ?? ''))]);
        $row = $stmt->fetch();
        // Verifikasi selalu dijalankan agar waktu respons seragam.
        $hash = $row['password_hash'] ?? password_hash('dummy', PASSWORD_DEFAULT);
        $ok = password_verify((string) ($_POST['password'] ?? ''), $hash) && $row;
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $row['id'];
            unset($_SESSION['fails'], $_SESSION['lock_until']);
            header('Location: index.php');
            exit;
        }
        $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
        if ($_SESSION['fails'] >= 5) {
            $_SESSION['lock_until'] = $now + 60;
            $_SESSION['fails'] = 0;
        }
        usleep(400000);
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Masuk | Admin MI Al-Hidayah</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#0b6e4f;min-height:100vh;display:flex;align-items:center}</style>
</head>
<body>
<div class="container" style="max-width:400px">
  <div class="card shadow border-0">
    <div class="card-body p-4">
      <h1 class="h4 text-center mb-1" style="color:#0b6e4f">Admin MI Al-Hidayah</h1>
      <p class="text-center text-muted mb-4">Masuk untuk mengelola konten website</p>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <button class="btn w-100 text-white" style="background:#0b6e4f">Masuk</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
