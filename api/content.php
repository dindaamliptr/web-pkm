<?php
declare(strict_types=1);
// API publik (read-only): konten yang dipakai halaman website.
require_once __DIR__ . '/../includes/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Content-Type-Options: nosniff');

try {
    $out = [];
    foreach (entities() as $key => $cfg) {
        $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($cfg['fields'])));
        $out[$key] = db()->query('SELECT ' . $cols . ' FROM `' . $cfg['table'] . '` ORDER BY ' . $cfg['order'])->fetchAll();
    }
    $s = settings_all();
    $out['pengaturan'] = array_intersect_key($s, array_flip([
        'stat_siswa', 'stat_guru', 'stat_tahun', 'stat_prestasi',
        'kepala_nama', 'kepala_jabatan', 'kepala_sambutan', 'kepala_foto',
    ]));
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
