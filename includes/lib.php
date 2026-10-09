<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('mi_cms');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function entities(): array
{
    static $cfg = null;
    return $cfg ??= require __DIR__ . '/entities.php';
}

/* ---------- Auth & CSRF ---------- */

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $ok = isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
    if (!$ok) {
        http_response_code(419);
        exit('Sesi tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- Flash message ---------- */

function flash(string $msg, string $type = 'success'): void
{
    $_SESSION['flash'] = [$msg, $type];
}

function flash_render(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    [$msg, $type] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">'
        . e($msg)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

/* ---------- Pengaturan (key/value) ---------- */

function settings_all(): array
{
    $out = [];
    foreach (db()->query('SELECT k, v FROM pengaturan') as $r) {
        $out[$r['k']] = $r['v'];
    }
    return $out;
}

function setting_set(string $k, string $v): void
{
    db()->prepare('INSERT INTO pengaturan (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
        ->execute([$k, $v]);
}

/* ---------- Upload gambar ---------- */

function upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/';
}

/**
 * Simpan upload dari $_FILES[$field]. Mengembalikan path relatif (uploads/xxx.jpg),
 * atau $old jika tidak ada file baru. Melempar RuntimeException bila gagal.
 */
function save_upload(string $field, ?string $old): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $old;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal (kode ' . $f['error'] . '). Periksa ukuran file.');
    }
    if ($f['size'] > MAX_UPLOAD) {
        throw new RuntimeException('Ukuran gambar maksimal ' . (int) (MAX_UPLOAD / 1048576) . ' MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if ($ext === null || @getimagesize($f['tmp_name']) === false) {
        throw new RuntimeException('File harus berupa gambar JPG, PNG, WEBP, atau GIF.');
    }
    if (!is_dir(upload_dir()) && !mkdir(upload_dir(), 0755, true)) {
        throw new RuntimeException('Folder uploads tidak bisa dibuat.');
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], upload_dir() . $name)) {
        throw new RuntimeException('Gagal menyimpan file upload.');
    }
    // File lama sengaja tidak dihapus di sini; pemanggil menghapusnya setelah data tersimpan.
    return 'uploads/' . $name;
}

/** Hapus file hanya jika berada di folder uploads (URL eksternal diabaikan). */
function delete_upload(?string $path): void
{
    if ($path && preg_match('#^uploads/[a-f0-9]{16}\.(jpg|png|webp|gif)$#', $path)) {
        @unlink(dirname(__DIR__) . '/' . $path);
    }
}

/** Untuk <img src> di admin: path relatif admin → naik satu level. */
function admin_img(?string $path): string
{
    if (!$path) {
        return '';
    }
    return preg_match('#^https?://#', $path) ? $path : '../' . $path;
}
