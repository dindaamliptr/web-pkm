<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/lib.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_login();

$icons = ['galeri' => 'bi-images', 'program' => 'bi-journal-bookmark', 'keunggulan' => 'bi-stars', 'ekskul' => 'bi-trophy', 'testimoni' => 'bi-chat-quote'];
$tones = [['#0b6e4f', '#e3f3ec'], ['#b8860b', '#fbf3d9'], ['#2563a8', '#e5eefa'], ['#8b4fb3', '#f1e8f7'], ['#c2410c', '#fdebe0']];

admin_header('Dashboard', 'dashboard');
?>
<div class="card mb-4 text-white overflow-hidden" style="background:linear-gradient(120deg,#0b6e4f,#0f8c63)!important">
  <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
    <div>
      <div class="small text-white-50 mb-1"><?= date('d M Y') ?></div>
      <h1 class="h3 fw-bold mb-1">Selamat datang, Admin</h1>
      <p class="mb-0 text-white-50">Kelola konten website MI Al-Hidayah. Perubahan langsung tampil setelah disimpan.</p>
    </div>
    <a href="../index.html" target="_blank" class="btn btn-light fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Lihat Situs</a>
  </div>
</div>

<h2 class="h6 text-uppercase text-muted fw-semibold mb-3" style="letter-spacing:.06em">Ringkasan Konten</h2>
<div class="row g-3">
  <?php $i = 0; foreach (entities() as $key => $cfg):
      $n = (int) db()->query('SELECT COUNT(*) FROM `' . $cfg['table'] . '`')->fetchColumn();
      [$fg, $bg] = $tones[$i++ % count($tones)]; ?>
    <div class="col-6 col-md-4 col-xl-3">
      <a href="data.php?e=<?= e($key) ?>" class="card stat text-decoration-none h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-icon" style="color:<?= $fg ?>;background:<?= $bg ?>"><i class="bi <?= e($icons[$key] ?? 'bi-collection') ?>"></i></span>
          <span>
            <span class="d-block fs-2 fw-bold lh-1" style="color:<?= $fg ?>"><?= $n ?></span>
            <span class="text-muted small"><?= e($cfg['label']) ?></span>
          </span>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
  <div class="col-6 col-md-4 col-xl-3">
    <a href="pengaturan.php" class="card stat text-decoration-none h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <span class="stat-icon" style="color:#475569;background:#eceff3"><i class="bi bi-gear"></i></span>
        <span>
          <span class="d-block fw-semibold" style="color:#475569">Sambutan &amp; Statistik</span>
          <span class="text-muted small">Atur di Pengaturan</span>
        </span>
      </div>
    </a>
  </div>
</div>
<style>
  .stat { transition:transform .18s, box-shadow .18s; }
  .stat:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(16,40,30,.12); }
  .stat-icon { width:52px; height:52px; border-radius:14px; display:grid; place-items:center; font-size:1.5rem; flex:none; }
</style>
<?php admin_footer();
