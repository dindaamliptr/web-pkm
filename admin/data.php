<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/lib.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_login();

$key = (string) ($_GET['e'] ?? '');
$all = entities();
if (!isset($all[$key])) {
    http_response_code(404);
    exit('Konten tidak ditemukan.');
}
$cfg    = $all[$key];
$table  = '`' . $cfg['table'] . '`';
$fields = $cfg['fields'];
$self   = 'data.php?e=' . urlencode($key);

/* ---------- Proses POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);

    if (($_POST['do'] ?? '') === 'delete') {
        $stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            db()->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
            foreach ($fields as $name => $f) {
                if ($f['type'] === 'image') {
                    delete_upload($row[$name]);
                }
            }
            flash('Data dihapus.');
        }
        header('Location: ' . $self);
        exit;
    }

    $old = [];
    if ($id) {
        $stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch() ?: [];
        if (!$old) {
            $id = 0;
        }
    }

    $values = [];
    $errors = [];
    $newUploads = [];
    foreach ($fields as $name => $f) {
        $label = preg_replace('/\s*\(.*\)$/', '', $f['label']);
        switch ($f['type']) {
            case 'checkbox':
                $values[$name] = isset($_POST[$name]) ? 1 : 0;
                break;
            case 'number':
                $values[$name] = (int) ($_POST[$name] ?? 0);
                break;
            case 'image':
                try {
                    $prev = $old[$name] ?? null;
                    $path = save_upload($name, $prev);
                    if ($path !== $prev) {
                        $newUploads[] = $path;
                    }
                    if (!$path && !empty($f['required'])) {
                        $errors[] = "$label wajib diisi.";
                    }
                    $values[$name] = $path;
                } catch (RuntimeException $ex) {
                    $errors[] = $ex->getMessage();
                }
                break;
            default: // text, textarea, icon
                $v = trim((string) ($_POST[$name] ?? ''));
                if (!empty($f['required']) && $v === '') {
                    $errors[] = "$label wajib diisi.";
                }
                if (isset($f['max']) && mb_strlen($v) > $f['max']) {
                    $errors[] = "$label maksimal {$f['max']} karakter.";
                }
                if ($f['type'] === 'icon' && $v !== '' && !preg_match('/^bi-[a-z0-9-]+$/', $v)) {
                    $errors[] = 'Ikon harus berformat bi-nama-ikon (huruf kecil, angka, tanda minus).';
                }
                $values[$name] = $v;
        }
    }

    if ($errors) {
        // Buang file baru yang sudah terlanjur terupload agar tidak jadi sampah.
        foreach ($newUploads as $p) {
            delete_upload($p);
        }
        flash(implode(' ', $errors), 'danger');
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . $self . '&a=' . ($id ? 'edit&id=' . $id : 'new'));
        exit;
    }

    if ($id) {
        $set = implode(', ', array_map(fn($n) => "`$n` = ?", array_keys($values)));
        db()->prepare("UPDATE $table SET $set WHERE id = ?")->execute([...array_values($values), $id]);
    } else {
        $cols = implode(', ', array_map(fn($n) => "`$n`", array_keys($values)));
        $ph   = implode(', ', array_fill(0, count($values), '?'));
        db()->prepare("INSERT INTO $table ($cols) VALUES ($ph)")->execute(array_values($values));
    }
    foreach ($fields as $name => $f) {
        if ($f['type'] === 'image' && !empty($old[$name]) && $old[$name] !== $values[$name]) {
            delete_upload($old[$name]);
        }
    }
    flash('Data disimpan.');
    header('Location: ' . $self);
    exit;
}

$action = $_GET['a'] ?? 'list';

/* ---------- Form tambah / ubah ---------- */
if ($action === 'new' || $action === 'edit') {
    $id  = (int) ($_GET['id'] ?? 0);
    $row = [];
    if ($action === 'edit') {
        $stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            exit('Data tidak ditemukan.');
        }
    }
    $input = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    admin_header(($row ? 'Ubah ' : 'Tambah ') . $cfg['label'], $key);
    ?>
    <h1 class="h3 mb-4"><?= $row ? 'Ubah' : 'Tambah' ?> <?= e($cfg['label']) ?></h1>
    <form method="post" enctype="multipart/form-data" class="card shadow-sm border-0">
      <div class="card-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
        <?php foreach ($fields as $name => $f):
            $val = $input[$name] ?? $row[$name] ?? ($f['default'] ?? ''); ?>
          <div class="mb-3">
            <?php if ($f['type'] === 'checkbox'): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="f_<?= e($name) ?>" name="<?= e($name) ?>" value="1"<?= $val ? ' checked' : '' ?>>
                <label class="form-check-label" for="f_<?= e($name) ?>"><?= e($f['label']) ?></label>
              </div>
            <?php else: ?>
              <label class="form-label" for="f_<?= e($name) ?>"><?= e($f['label']) ?><?= !empty($f['required']) ? ' <span class="text-danger">*</span>' : '' ?></label>
              <?php if ($f['type'] === 'textarea'): ?>
                <textarea class="form-control" rows="4" id="f_<?= e($name) ?>" name="<?= e($name) ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= !empty($f['required']) ? ' required' : '' ?>><?= e($val) ?></textarea>
              <?php elseif ($f['type'] === 'image'): ?>
                <?php if (!empty($row[$name])): ?>
                  <div class="mb-2"><img src="<?= e(admin_img($row[$name])) ?>" alt="" style="max-height:140px;border-radius:8px"></div>
                <?php endif; ?>
                <input class="form-control" type="file" id="f_<?= e($name) ?>" name="<?= e($name) ?>" accept="image/jpeg,image/png,image/webp,image/gif"<?= (!empty($f['required']) && empty($row[$name])) ? ' required' : '' ?>>
                <div class="form-text">JPG/PNG/WEBP/GIF, maksimal <?= (int) (MAX_UPLOAD / 1048576) ?> MB.<?= !empty($row[$name]) ? ' Kosongkan jika tidak ingin mengganti.' : '' ?></div>
              <?php else: ?>
                <input class="form-control" id="f_<?= e($name) ?>" name="<?= e($name) ?>" type="<?= $f['type'] === 'number' ? 'number' : 'text' ?>" value="<?= e($val) ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= !empty($f['required']) ? ' required' : '' ?>>
              <?php endif; ?>
              <?php if (!empty($f['help'])): ?><div class="form-text"><?= e($f['help']) ?></div><?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="card-footer bg-white d-flex gap-2">
        <button class="btn btn-brand">Simpan</button>
        <a class="btn btn-outline-secondary" href="<?= e($self) ?>">Batal</a>
      </div>
    </form>
    <?php
    admin_footer();
    exit;
}

/* ---------- Daftar ---------- */
$rows = db()->query("SELECT * FROM $table ORDER BY {$cfg['order']}")->fetchAll();
admin_header($cfg['label'], $key);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?= e($cfg['label']) ?></h1>
  <a class="btn btn-brand" href="<?= e($self) ?>&a=new"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <?php foreach ($cfg['list'] as $col):
              $lbl = $col === 'beranda' ? 'Di Beranda' : preg_replace('/\s*\(.*\)$/', '', $fields[$col]['label']); ?>
            <th><?= e($lbl) ?></th>
          <?php endforeach; ?>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <?php foreach ($cfg['list'] as $col): $t = $fields[$col]['type']; ?>
            <td>
              <?php if ($t === 'image'): ?>
                <img class="thumb" src="<?= e(admin_img($r[$col])) ?>" alt="">
              <?php elseif ($t === 'icon'): ?>
                <i class="bi <?= e($r[$col]) ?> fs-4" style="color:#0b6e4f"></i>
              <?php elseif ($t === 'checkbox'): ?>
                <?= $r[$col] ? '<span class="badge text-bg-success">Ya</span>' : '<span class="badge text-bg-secondary">Tidak</span>' ?>
              <?php else: ?>
                <?= e(mb_strimwidth((string) $r[$col], 0, 70, '…')) ?>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="<?= e($self) ?>&a=edit&id=<?= (int) $r['id'] ?>">Ubah</a>
            <form method="post" action="<?= e($self) ?>" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="do" value="delete">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= count($cfg['list']) + 1 ?>" class="text-center text-muted py-4">Belum ada data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted small mt-3 mb-0">Jika semua data satu jenis dihapus, website menampilkan isi bawaan dari file HTML.</p>
<?php admin_footer();
