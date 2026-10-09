<?php
declare(strict_types=1);
// Installer sekali pakai: membuat tabel, mengisi konten awal, dan membuat akun admin.
// HAPUS file ini dari server setelah instalasi selesai.
require_once __DIR__ . '/includes/config.php';

$done = false;
$error = '';
$locked = false;

function connect(bool $withDb): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ($withDb ? ';dbname=' . DB_NAME : '') . ';charset=utf8mb4';
    return new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

try {
    try {
        $pdo = connect(true);
    } catch (PDOException $ex) {
        if ((int) $ex->getCode() !== 1049) { // 1049 = database belum ada
            throw $ex;
        }
        connect(false)->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo = connect(true);
    }
    $exists = $pdo->query("SHOW TABLES LIKE 'admin'")->fetchColumn();
    $locked = $exists && (int) $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn() > 0;
} catch (PDOException $ex) {
    $pdo = null;
    $error = 'Tidak bisa terhubung ke database: ' . $ex->getMessage() . ' — periksa includes/config.php.';
}

if ($pdo && !$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string) ($_POST['username'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $user)) {
        $error = 'Username 3–40 karakter (huruf, angka, titik, minus, garis bawah).';
    } elseif (strlen($pass) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        $ddl = [
            'CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(40) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL
            )',
            'CREATE TABLE IF NOT EXISTS pengaturan (
                k VARCHAR(60) PRIMARY KEY,
                v TEXT NOT NULL
            )',
            'CREATE TABLE IF NOT EXISTS galeri (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(150) NOT NULL,
                gambar VARCHAR(255) NOT NULL,
                urutan INT NOT NULL DEFAULT 0
            )',
            'CREATE TABLE IF NOT EXISTS program (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(120) NOT NULL,
                kategori VARCHAR(50) NOT NULL DEFAULT \'\',
                deskripsi VARCHAR(600) NOT NULL,
                gambar VARCHAR(255) NOT NULL,
                beranda TINYINT(1) NOT NULL DEFAULT 0,
                urutan INT NOT NULL DEFAULT 0
            )',
            'CREATE TABLE IF NOT EXISTS keunggulan (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(100) NOT NULL,
                deskripsi VARCHAR(400) NOT NULL,
                ikon VARCHAR(60) NOT NULL,
                urutan INT NOT NULL DEFAULT 0
            )',
            'CREATE TABLE IF NOT EXISTS ekskul (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(80) NOT NULL,
                ikon VARCHAR(60) NOT NULL,
                urutan INT NOT NULL DEFAULT 0
            )',
            'CREATE TABLE IF NOT EXISTS testimoni (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(80) NOT NULL,
                keterangan VARCHAR(80) NOT NULL DEFAULT \'\',
                isi VARCHAR(500) NOT NULL,
                urutan INT NOT NULL DEFAULT 0
            )',
        ];
        foreach ($ddl as $sql) {
            $pdo->exec($sql . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        $ph = 'https://placehold.co/';
        $seed = [
            'galeri' => [
                ['judul', 'gambar', 'urutan'],
                [
                    ['Kegiatan Belajar Mengajar', $ph . '400x300/0b6e4f/ffffff?text=Kegiatan+Belajar'],
                    ['Upacara Bendera', $ph . '400x300/0f8c63/ffffff?text=Upacara+Bendera'],
                    ['Kegiatan Tahfidz', $ph . '400x300/d4af37/ffffff?text=Tahfidz+Qur%27an'],
                    ['Kegiatan Pramuka', $ph . '400x300/0b6e4f/ffffff?text=Pramuka'],
                    ['Lomba Antar Sekolah', $ph . '400x300/0f8c63/ffffff?text=Lomba+Sekolah'],
                    ['Wisuda Tahfidz', $ph . '400x300/d4af37/ffffff?text=Wisuda+Tahfidz'],
                    ['Praktik Sains', $ph . '400x300/0b6e4f/ffffff?text=Praktik+Sains'],
                    ['Gedung Madrasah', $ph . '400x300/0f8c63/ffffff?text=Gedung+Madrasah'],
                    ['Kegiatan Olahraga', $ph . '400x300/d4af37/ffffff?text=Kegiatan+Olahraga'],
                ],
            ],
            'program' => [
                ['judul', 'kategori', 'deskripsi', 'gambar', 'beranda', 'urutan'],
                [
                    ['Tahfidz Al-Qur\'an', 'Keagamaan', 'Program hafalan Al-Qur\'an bertahap (juz 30 dan seterusnya) dengan metode talaqqi dan muraja\'ah rutin.', $ph . '400x220/0f8c63/ffffff?text=Tahfidz+Qur%27an', 1],
                    ['Kurikulum Merdeka', 'Akademik', 'Pembelajaran tematik yang berpusat pada siswa dengan penguatan literasi dan numerasi.', $ph . '400x220/0b6e4f/ffffff?text=Kurikulum+Merdeka', 1],
                    ['Bahasa Arab & Inggris', 'Bahasa', 'Pembelajaran dua bahasa asing sejak dini untuk membekali siswa di era global.', $ph . '400x220/d4af37/ffffff?text=Bahasa+Arab+%26+Inggris', 1],
                    ['Sains & Komputer Dasar', 'Akademik', 'Pengenalan sains melalui eksperimen sederhana dan literasi teknologi/komputer dasar.', $ph . '400x220/0b6e4f/ffffff?text=Sains+%26+Komputer', 0],
                    ['Pembinaan Akhlak', 'Keagamaan', 'Pembiasaan salat berjamaah, adab harian, dan kajian akhlak untuk pembentukan karakter.', $ph . '400x220/0f8c63/ffffff?text=Pembinaan+Akhlak', 0],
                    ['UKS & Kesehatan Siswa', 'Kesehatan', 'Pemeriksaan kesehatan berkala dan pembiasaan pola hidup bersih dan sehat.', $ph . '400x220/d4af37/ffffff?text=UKS+%26+Kesehatan', 0],
                ],
            ],
            'keunggulan' => [
                ['judul', 'deskripsi', 'ikon', 'urutan'],
                [
                    ['Kurikulum Terpadu', 'Kurikulum Merdeka dipadukan dengan muatan diniyah dan tahfidz Al-Qur\'an.', 'bi-book-half'],
                    ['Guru Berpengalaman', 'Tenaga pendidik profesional yang ramah anak dan berkompeten di bidangnya.', 'bi-person-hearts'],
                    ['Lingkungan Aman & Islami', 'Lingkungan belajar yang nyaman, aman, dan menanamkan akhlak mulia sejak dini.', 'bi-shield-check'],
                    ['Ekstrakurikuler Beragam', 'Pramuka, seni hadrah, olahraga, dan pengembangan bakat lainnya.', 'bi-easel2'],
                    ['Prestasi Akademik', 'Aktif mengikuti dan meraih juara di berbagai perlombaan tingkat kecamatan hingga provinsi.', 'bi-mortarboard'],
                    ['Komunikasi Wali Murid', 'Laporan perkembangan siswa berkala dan komunikasi aktif dengan orang tua.', 'bi-people'],
                ],
            ],
            'ekskul' => [
                ['judul', 'ikon', 'urutan'],
                [
                    ['Pramuka', 'bi-flag'],
                    ['Seni Hadrah', 'bi-music-note-beamed'],
                    ['Olahraga', 'bi-trophy'],
                    ['Seni & Kaligrafi', 'bi-brush'],
                ],
            ],
            'testimoni' => [
                ['nama', 'keterangan', 'isi', 'urutan'],
                [
                    ['Ibu Siti Rahmawati', 'Wali Murid Kelas 3', 'Anak saya jadi lebih rajin mengaji dan lebih mandiri sejak sekolah di sini. Guru-gurunya sangat sabar.'],
                    ['Bapak Ahmad Fauzi', 'Wali Murid Kelas 1', 'Fasilitas cukup lengkap dan lingkungannya nyaman untuk anak belajar. Komunikasi dengan wali kelas juga lancar.'],
                    ['Ibu Nur Aini', 'Wali Murid Kelas 4', 'Program tahfidznya bagus, anak saya sudah hafal beberapa juz sejak masuk MI Al-Hidayah.'],
                ],
            ],
        ];

        $pdo->beginTransaction();
        try {
            foreach ($seed as $table => [$cols, $rows]) {
                if ($pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() > 0) {
                    continue;
                }
                $sql = "INSERT INTO `$table` (`" . implode('`, `', $cols) . '`) VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')';
                $ins = $pdo->prepare($sql);
                foreach ($rows as $i => $r) {
                    // Kolom terakhir adalah 'urutan': isi otomatis jika tidak disediakan.
                    $r = array_pad($r, count($cols), null);
                    $r[count($cols) - 1] ??= $i + 1;
                    $ins->execute($r);
                }
            }

            $settings = [
                'stat_siswa' => '300', 'stat_guru' => '25', 'stat_tahun' => '15', 'stat_prestasi' => '10',
                'kepala_nama' => 'Nama Kepala Madrasah',
                'kepala_jabatan' => 'Kepala MI Al-Hidayah Tulang Kuning',
                'kepala_sambutan' => 'Pendidikan dasar adalah pondasi utama dalam membentuk karakter anak. Di MI Al-Hidayah, kami berkomitmen mendampingi tumbuh kembang putra-putri Bapak/Ibu dengan pendekatan yang menyenangkan, islami, dan berkualitas, agar kelak menjadi generasi yang cerdas, mandiri, dan berakhlak mulia.',
                'kepala_foto' => $ph . '380x420/0b6e4f/ffffff?text=Kepala+Madrasah&font=poppins',
            ];
            $set = $pdo->prepare('INSERT IGNORE INTO pengaturan (k, v) VALUES (?, ?)');
            foreach ($settings as $k => $v) {
                $set->execute([$k, $v]);
            }

            $pdo->prepare('INSERT INTO admin (username, password_hash) VALUES (?, ?)')
                ->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
            $pdo->commit();
            $done = true;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $error = 'Instalasi gagal: ' . $ex->getMessage();
        }
    }
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Instalasi CMS | MI Al-Hidayah</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:480px">
  <div class="card shadow-sm border-0"><div class="card-body p-4">
    <h1 class="h4 mb-3" style="color:#0b6e4f">Instalasi CMS</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
    <?php if ($done): ?>
      <div class="alert alert-success">Instalasi berhasil. <strong>Hapus file install.php dari server</strong>, lalu masuk ke panel admin.</div>
      <a class="btn text-white" style="background:#0b6e4f" href="admin/login.php">Ke halaman login</a>
    <?php elseif ($locked): ?>
      <div class="alert alert-warning mb-0">CMS sudah terpasang. Hapus file install.php dari server.</div>
    <?php elseif ($pdo): ?>
      <p class="text-muted">Buat akun admin pertama. Tabel dan konten awal (dari isi website saat ini) akan dibuat otomatis.</p>
      <form method="post" autocomplete="off">
        <div class="mb-3"><label class="form-label">Username admin</label><input name="username" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password (min. 8 karakter)</label><input name="password" type="password" minlength="8" class="form-control" required></div>
        <button class="btn text-white w-100" style="background:#0b6e4f">Pasang CMS</button>
      </form>
    <?php endif; ?>
  </div></div>
</div>
</body>
</html>
