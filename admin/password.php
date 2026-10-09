<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/lib.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = db()->prepare('SELECT password_hash FROM admin WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $hash = (string) $stmt->fetchColumn();

    $new = (string) ($_POST['new'] ?? '');
    if (!password_verify((string) ($_POST['current'] ?? ''), $hash)) {
        flash('Password saat ini salah.', 'danger');
    } elseif (strlen($new) < 8) {
        flash('Password baru minimal 8 karakter.', 'danger');
    } elseif ($new !== (string) ($_POST['confirm'] ?? '')) {
        flash('Konfirmasi password tidak sama.', 'danger');
    } else {
        db()->prepare('UPDATE admin SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        session_regenerate_id(true);
        flash('Password diganti.');
    }
    header('Location: password.php');
    exit;
}

admin_header('Ganti Password', 'password');
?>
<h1 class="h3 mb-4">Ganti Password</h1>
<form method="post" class="card shadow-sm border-0" style="max-width:480px" autocomplete="off">
  <div class="card-body">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Password saat ini</label><input type="password" name="current" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password baru (min. 8 karakter)</label><input type="password" name="new" class="form-control" minlength="8" required></div>
    <div class="mb-3"><label class="form-label">Ulangi password baru</label><input type="password" name="confirm" class="form-control" minlength="8" required></div>
  </div>
  <div class="card-footer bg-white"><button class="btn btn-brand">Simpan</button></div>
</form>
<?php admin_footer();
