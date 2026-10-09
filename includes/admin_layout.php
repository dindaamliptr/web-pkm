<?php
declare(strict_types=1);

function admin_header(string $title, string $active = ''): void
{
    $icons = [
        'dashboard' => 'bi-speedometer2', 'galeri' => 'bi-images', 'program' => 'bi-journal-bookmark',
        'keunggulan' => 'bi-stars', 'ekskul' => 'bi-trophy', 'testimoni' => 'bi-chat-quote',
        'pengaturan' => 'bi-gear',
    ];
    $links = [];
    foreach (entities() as $key => $cfg) {
        $links['data.php?e=' . $key] = [$key, $cfg['label']];
    }
    ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> | Admin MI Al-Hidayah</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  :root { --sb-w:262px; --brand:#0b6e4f; --brand-dark:#064e38; --gold:#d4af37; --ink:#1f2d27; }
  body { background:#f1f5f3; color:var(--ink); }
  .sidebar { background:linear-gradient(180deg,var(--brand) 0%,var(--brand-dark) 100%) !important; color:#fff; width:var(--sb-w); }
  .sidebar .brand { display:flex; align-items:center; gap:.75rem; padding:1.25rem 1.25rem 1.1rem; color:#fff; text-decoration:none; }
  .sidebar .brand-icon { width:42px; height:42px; border-radius:12px; background:rgba(255,255,255,.15); display:grid; place-items:center; font-size:1.4rem; color:var(--gold); flex:none; }
  .sidebar .brand-title { font-weight:700; line-height:1.15; font-size:1rem; }
  .sidebar .brand-sub { font-size:.72rem; color:rgba(255,255,255,.65); }
  .sidebar .menu-label { padding:1.1rem 1.5rem .4rem; font-size:.68rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.45); }
  .sidebar .nav-link { position:relative; display:flex; align-items:center; gap:.75rem; color:rgba(255,255,255,.8); padding:.62rem .9rem; margin:.12rem .85rem; border-radius:10px; font-size:.93rem; transition:background .15s,color .15s,transform .15s; }
  .sidebar .nav-link i { font-size:1.08rem; width:1.3rem; text-align:center; }
  .sidebar .nav-link:hover { color:#fff; background:rgba(255,255,255,.1); transform:translateX(2px); }
  .sidebar .nav-link.active { color:var(--brand-dark); background:#fff; font-weight:600; box-shadow:0 4px 12px rgba(0,0,0,.18); }
  .sidebar .nav-link.active i { color:var(--brand); }
  .sidebar .nav-link.active::before { content:""; position:absolute; left:-.85rem; top:22%; bottom:22%; width:4px; border-radius:0 4px 4px 0; background:var(--gold); }
  .sidebar .nav-link.logout:hover { background:rgba(220,53,69,.4); }
  .sidebar button.nav-link { width:calc(100% - 1.7rem); background:none; border:0; text-align:left; }
  .topbar-m { background:var(--brand); }
  .card { border:0 !important; border-radius:14px; box-shadow:0 1px 3px rgba(16,40,30,.06),0 4px 14px rgba(16,40,30,.04); }
  .table > :not(caption) > * > * { padding:.8rem .9rem; }
  .table thead th { font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#5f6f67; background:#f7faf8; border-bottom:1px solid #e3ebe6; }
  .form-control:focus, .form-select:focus { border-color:var(--brand); box-shadow:0 0 0 .2rem rgba(11,110,79,.15); }
  @media (min-width:992px) {
    .sidebar { position:fixed; top:0; bottom:0; left:0; overflow-y:auto; }
    .content { margin-left:var(--sb-w); }
  }
  .thumb { width:72px; height:54px; object-fit:cover; border-radius:8px; background:#e5e9e7; }
  .btn-brand { background:var(--brand); color:#fff; border:0; border-radius:8px; }
  .btn-brand:hover { background:var(--brand-dark); color:#fff; }
</style>
</head>
<body>
<aside class="sidebar offcanvas-lg offcanvas-start d-flex flex-column" tabindex="-1" id="sidebar">
  <div class="offcanvas-header d-lg-none justify-content-end py-2">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup"></button>
  </div>
  <a class="brand" href="index.php">
    <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
    <span><span class="brand-title d-block">MI Al-Hidayah</span><span class="brand-sub">Panel Admin</span></span>
  </a>
  <div class="offcanvas-body d-flex flex-column p-0">
    <div class="menu-label">Utama</div>
    <nav class="nav flex-column">
      <a class="nav-link<?= $active === 'dashboard' ? ' active' : '' ?>" href="index.php"><i class="bi <?= $icons['dashboard'] ?>"></i>Dashboard</a>
    </nav>
    <div class="menu-label">Konten Website</div>
    <nav class="nav flex-column">
      <?php foreach ($links as $href => [$k, $label]): ?>
        <a class="nav-link<?= $active === $k ? ' active' : '' ?>" href="<?= e($href) ?>"><i class="bi <?= e($icons[$k] ?? 'bi-circle') ?>"></i><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="menu-label">Pengaturan</div>
    <nav class="nav flex-column">
      <a class="nav-link<?= $active === 'pengaturan' ? ' active' : '' ?>" href="pengaturan.php"><i class="bi <?= $icons['pengaturan'] ?>"></i>Pengaturan Situs</a>
      <a class="nav-link<?= $active === 'password' ? ' active' : '' ?>" href="password.php"><i class="bi bi-key"></i>Ganti Password</a>
    </nav>
    <div class="mt-auto pt-3 pb-3">
      <a class="nav-link" href="../index.html" target="_blank"><i class="bi bi-box-arrow-up-right"></i>Lihat Situs</a>
      <form method="post" action="logout.php" class="m-0"><?= csrf_field() ?>
        <button class="nav-link logout"><i class="bi bi-box-arrow-right"></i>Keluar</button>
      </form>
    </div>
  </div>
</aside>
<div class="content">
  <header class="topbar-m d-lg-none d-flex align-items-center gap-3 px-3 py-2 text-white">
    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu"><i class="bi bi-list fs-5"></i></button>
    <span class="fw-semibold">MI Al-Hidayah · Admin</span>
  </header>
  <main class="container-xl py-4 px-3 px-lg-4">
<?= flash_render() ?>
<?php
}

function admin_footer(): void
{
    ?>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
