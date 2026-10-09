<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/lib.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_login();

$text = [
    'stat_siswa'      => ['Jumlah siswa aktif', 'number'],
    'stat_guru'       => ['Jumlah guru & staff', 'number'],
    'stat_tahun'      => ['Tahun berdiri (lama berdiri, dalam tahun)', 'number'],
    'stat_prestasi'   => ['Jumlah prestasi diraih', 'number'],
    'kepala_nama'     => ['Nama kepala madrasah', 'text'],
    'kepala_jabatan'  => ['Jabatan', 'text'],
    'kepala_sambutan' => ['Kata sambutan', 'textarea'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cur = settings_all();
    try {
        $new = [];
        foreach ($text as $k => [$label, $type]) {
            $v = trim((string) ($_POST[$k] ?? ''));
            if ($type === 'number') {
                if (!ctype_digit($v) || strlen($v) > 6) {
                    throw new RuntimeException("$label harus berupa angka.");
                }
            } elseif (mb_strlen($v) > ($type === 'textarea' ? 1500 : 120)) {
                throw new RuntimeException("$label terlalu panjang.");
            }
            $new[$k] = $v;
        }
        $foto = save_upload('kepala_foto', $cur['kepala_foto'] ?? null);
        $new['kepala_foto'] = (string) $foto;
        foreach ($new as $k => $v) {
            setting_set($k, $v);
        }
        if (!empty($cur['kepala_foto']) && $cur['kepala_foto'] !== $new['kepala_foto']) {
            delete_upload($cur['kepala_foto']);
        }
        flash('Pengaturan disimpan.');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'danger');
        $_SESSION['old_input'] = $_POST;
    }
    header('Location: pengaturan.php');
    exit;
}

$s = settings_all();
$input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
$s = array_merge($s, $input);

admin_header('Pengaturan Situs', 'pengaturan');
?>
<h1 class="h3 mb-4">Pengaturan Situs</h1>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold">Statistik Beranda</div>
    <div class="card-body row g-3">
      <?php foreach (['stat_siswa', 'stat_guru', 'stat_tahun', 'stat_prestasi'] as $k): ?>
        <div class="col-md-6">
          <label class="form-label" for="<?= $k ?>"><?= e($text[$k][0]) ?></label>
          <input class="form-control" type="number" min="0" id="<?= $k ?>" name="<?= $k ?>" value="<?= e($s[$k] ?? '0') ?>" required>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold">Sambutan Kepala Madrasah</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="kepala_nama"><?= e($text['kepala_nama'][0]) ?></label>
          <input class="form-control" id="kepala_nama" name="kepala_nama" maxlength="120" value="<?= e($s['kepala_nama'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="kepala_jabatan"><?= e($text['kepala_jabatan'][0]) ?></label>
          <input class="form-control" id="kepala_jabatan" name="kepala_jabatan" maxlength="120" value="<?= e($s['kepala_jabatan'] ?? '') ?>" required>
        </div>
        <div class="col-12">
          <label class="form-label" for="kepala_sambutan"><?= e($text['kepala_sambutan'][0]) ?></label>
          <textarea class="form-control" rows="6" id="kepala_sambutan" name="kepala_sambutan" maxlength="1500" required><?= e($s['kepala_sambutan'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label" for="kepala_foto">Foto kepala madrasah</label>
          <?php if (!empty($s['kepala_foto'])): ?>
            <div class="mb-2"><img src="<?= e(admin_img($s['kepala_foto'])) ?>" alt="" style="max-height:160px;border-radius:8px"></div>
          <?php endif; ?>
          <input class="form-control" type="file" id="kepala_foto" name="kepala_foto" accept="image/jpeg,image/png,image/webp,image/gif">
          <div class="form-text">Kosongkan jika tidak ingin mengganti. Maksimal <?= (int) (MAX_UPLOAD / 1048576) ?> MB.</div>
        </div>
      </div>
    </div>
  </div>
  <button class="btn btn-brand">Simpan Pengaturan</button>
</form>
<?php admin_footer();
