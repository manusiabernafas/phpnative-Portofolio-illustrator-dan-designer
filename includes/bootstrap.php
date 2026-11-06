<?php
// Inti aplikasi: koneksi database, helper tampilan, keamanan, dan upload
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
session_start();

const ROOT = __DIR__ . '/..';
const UPLOAD_DIR = ROOT . '/assets/uploads/';
const UPLOAD_URL = 'assets/uploads/';
const ANGGARAN = ['Di bawah Rp5 juta', 'Rp5 - 15 juta', 'Rp15 - 50 juta', 'Di atas Rp50 juta', 'Belum tahu'];
const MIME_GAMBAR = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

require __DIR__ . '/tema.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require ROOT . '/config/database.php';
        $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '+07:00'");
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function url(string $page, array $p = []): string { return 'index.php?' . http_build_query(['page' => $page] + $p); }

function redirect(string $page, array $p = []): void { header('Location: ' . url($page, $p)); exit; }

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }
function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf_token(), (string) ($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa, silakan muat ulang halaman.');
    }
}

function ip(): string { return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45); }

/* ---------- Login & sesi ---------- */

/** Sidik jari dari hash password: kalau password diganti, semua sesi lama otomatis tidak berlaku. */
function sidik_password(string $hash): string { return hash_hmac('sha256', $hash, session_id() . '|porto'); }

function masuk(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    $_SESSION['sidik'] = sidik_password($u['password']);
}

function user(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (empty($_SESSION['uid'])) return null;
    $row = q('SELECT id, nama, username, password FROM users WHERE id = ?', [$_SESSION['uid']])->fetch();
    if ($row && hash_equals((string) ($_SESSION['sidik'] ?? ''), sidik_password($row['password']))) $u = $row;
    else unset($_SESSION['uid'], $_SESSION['sidik']);
    return $u;
}

/** Maksimal 5 percobaan gagal per IP dalam 15 menit. */
function login_terkunci(): bool
{
    return (int) q('SELECT COUNT(*) FROM login_gagal WHERE ip = ? AND waktu > NOW() - INTERVAL 15 MINUTE', [ip()])->fetchColumn() >= 5;
}

/* ---------- Pengaturan situs ---------- */

function setting(?string $k = null, string $default = '')
{
    static $s = null;
    if ($s === null) $s = q('SELECT kunci, nilai FROM pengaturan')->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($k === null) return $s;
    return (string) ($s[$k] ?? $default);
}

function simpan_setting(array $data): void
{
    $st = db()->prepare('INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)');
    foreach ($data as $k => $v) $st->execute([$k, (string) $v]);
}

function aksen(): array { return PALET[setting('aksen')] ?? reset(PALET); }

/* ---------- Teks & format ---------- */

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s), '-'));
    return $s !== '' ? substr($s, 0, 80) : 'proyek';
}

function slug_unik(string $judul, int $kecualiId = 0): string
{
    $dasar = slugify($judul); $slug = $dasar; $i = 2;
    while (q('SELECT 1 FROM proyek WHERE slug = ? AND id <> ?', [$slug, $kecualiId])->fetchColumn()) $slug = $dasar . '-' . $i++;
    return $slug;
}

/**
 * Markdown mini yang aman untuk isi proyek: teks di-escape DULU, baru diberi format.
 * Mendukung: ## judul, - daftar, **tebal**, *miring*, `kode`, [teks](https://link), paragraf.
 */
function markdown_mini(string $teks): string
{
    $html = '';
    $daftar = false;
    foreach (preg_split('/\R/', str_replace("\r", '', $teks)) as $baris) {
        $b = trim($baris);
        $isList = str_starts_with($b, '- ');
        if ($daftar && !$isList) { $html .= "</ul>\n"; $daftar = false; }
        if ($b === '') continue;
        $inline = e($isList ? substr($b, 2) : ltrim($b, '# '));
        $inline = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $inline);
        $inline = preg_replace('/(?<![\w*])\*(?!\s)(.+?)\*(?!\w)/', '<em>$1</em>', $inline);
        $inline = preg_replace('/`(.+?)`/', '<code>$1</code>', $inline);
        $inline = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $inline);
        if ($isList) { if (!$daftar) { $html .= '<ul>'; $daftar = true; } $html .= "<li>$inline</li>"; }
        elseif (str_starts_with($b, '### ')) $html .= "<h4>$inline</h4>\n";
        elseif (str_starts_with($b, '## ')) $html .= "<h3>$inline</h3>\n";
        else $html .= "<p>$inline</p>\n";
    }
    return $html . ($daftar ? '</ul>' : '');
}

const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const BLN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function bulan_tahun(?string $d): string { return $d ? BLN[(int) date('n', strtotime($d))] . ' ' . date('Y', strtotime($d)) : 'Sekarang'; }

function tgl(string $dt): string { $t = strtotime($dt); return date('j', $t) . ' ' . BLN[(int) date('n', $t)] . ' ' . date('Y', $t); }

function waktu_lalu(string $dt): string
{
    $d = time() - strtotime($dt);
    if ($d < 60) return 'baru saja';
    if ($d < 3600) return floor($d / 60) . ' menit lalu';
    if ($d < 86400) return floor($d / 3600) . ' jam lalu';
    if ($d < 86400 * 30) return floor($d / 86400) . ' hari lalu';
    return tgl($dt);
}

function rupiah(?int $n): string { return 'Rp' . number_format((int) $n, 0, ',', '.'); }

function nomor_wa(string $no): string
{
    $no = preg_replace('/\D/', '', $no);
    return str_starts_with($no, '0') ? '62' . substr($no, 1) : $no;
}

/** Hanya izinkan link http/https agar tidak bisa disisipi javascript: */
function link_aman(?string $u): string
{
    $u = trim((string) $u);
    return preg_match('#^https?://#i', $u) ? $u : '';
}

/* ---------- Gambar ---------- */

/** URL gambar: file upload (assets/uploads) atau ilustrasi bawaan (assets/img). */
function gambar(?string $f, string $cadangan = 'assets/img/kosong.svg'): string
{
    if (!$f) return $cadangan;
    return str_starts_with($f, 'assets/img/') ? $f : UPLOAD_URL . $f;
}

function pastikan_folder_upload(): void
{
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
    // Cegah file apa pun di folder upload dieksekusi sebagai script
    if (!is_file(UPLOAD_DIR . '.htaccess')) {
        file_put_contents(UPLOAD_DIR . '.htaccess', "Options -Indexes -ExecCGI\nRemoveHandler .php .phtml .php5 .phar\n<FilesMatch \"\\.(php|phtml|php5|phar|cgi|pl|py|sh)$\">\n  Require all denied\n</FilesMatch>\n");
    }
}

/**
 * Simpan gambar upload: cek ukuran + MIME asli, lalu digambar ulang dengan GD
 * menjadi WebP (lebar maks $maks px). Gambar ulang membuang metadata & sisipan berbahaya.
 */
function simpan_gambar(array $f, int $maks = 1600, int $maksMb = 5): ?string
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gagal (kode ' . $f['error'] . ').');
    if ($f['size'] > $maksMb * 1024 * 1024) throw new RuntimeException('Ukuran gambar maksimal ' . $maksMb . ' MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(MIME_GAMBAR[$mime]) || !getimagesize($f['tmp_name'])) throw new RuntimeException('File harus gambar JPG, PNG, atau WebP.');
    $src = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($f['tmp_name']),
        'image/png' => imagecreatefrompng($f['tmp_name']),
        'image/webp' => imagecreatefromwebp($f['tmp_name']),
    };
    if (!$src) throw new RuntimeException('Gambar rusak atau tidak bisa dibaca.');
    [$w, $h] = [imagesx($src), imagesy($src)];
    $skala = min(1, $maks / max($w, 1));
    $nw = max(1, (int) round($w * $skala)); $nh = max(1, (int) round($h * $skala));
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    pastikan_folder_upload();
    $nama = date('Ym') . '-' . bin2hex(random_bytes(6)) . '.webp';
    if (!imagewebp($dst, UPLOAD_DIR . $nama, 82)) throw new RuntimeException('Gagal menyimpan gambar.');
    return $nama;
}

/** Simpan CV (PDF saja). */
function simpan_pdf(array $f, int $maksMb = 5): ?string
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gagal (kode ' . $f['error'] . ').');
    if ($f['size'] > $maksMb * 1024 * 1024) throw new RuntimeException('Ukuran PDF maksimal ' . $maksMb . ' MB.');
    if ((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) !== 'application/pdf') throw new RuntimeException('CV harus berformat PDF.');
    pastikan_folder_upload();
    $nama = 'cv-' . bin2hex(random_bytes(6)) . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $nama)) throw new RuntimeException('Gagal menyimpan file.');
    return $nama;
}

function hapus_upload(?string $f): void
{
    if ($f && !str_starts_with($f, 'assets/') && preg_match('/^[\w.-]+$/', $f) && is_file(UPLOAD_DIR . $f)) unlink(UPLOAD_DIR . $f);
}

/* ---------- Ikon ---------- */

function icon(string $name, int $size = 18, string $cls = 'ic'): string
{
    static $ic = null;
    $ic ??= require __DIR__ . '/icons.php';
    return '<svg class="' . $cls . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($ic[$name] ?? '') . '</svg>';
}

function daftar_logo(): array
{
    static $l = null;
    return $l ??= require __DIR__ . '/logos.php';
}

/** Logo teknologi satu warna. Kalau kunci logo tidak ada, tampil monogram huruf depan. */
function logo(?string $key, int $size = 28, string $nama = ''): string
{
    $l = daftar_logo()[$key ?? ''] ?? null;
    if (!$l) {
        $m = e(mb_strtoupper(mb_substr($nama ?: '?', 0, 2)));
        return '<svg class="logo" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" aria-hidden="true"><rect x="1.5" y="1.5" width="21" height="21" rx="5" fill="none" stroke="currentColor" stroke-width="1.6"/><text x="12" y="16" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" font-family="sans-serif">' . $m . '</text></svg>';
    }
    return '<svg class="logo" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" role="img" aria-label="' . e($l[0]) . '"><path d="' . $l[1] . '"/></svg>';
}

function sosial(): array
{
    $out = [];
    foreach (['github' => ['GitHub', 'logo'], 'linkedin' => ['LinkedIn', 'icon'], 'instagram' => ['Instagram', 'logo'], 'dribbble' => ['Dribbble', 'logo'], 'behance' => ['Behance', 'logo'], 'youtube' => ['YouTube', 'logo']] as $k => [$label, $jenis]) {
        $u = link_aman(setting('sosial_' . $k));
        if ($u) $out[] = ['key' => $k, 'label' => $label, 'url' => $u, 'svg' => $jenis === 'logo' ? logo($k, 18) : icon($k, 18)];
    }
    return $out;
}

/** Catat kunjungan harian (satu kali per sesi per hari). */
function catat_kunjungan(): void
{
    $hari = date('Y-m-d');
    if (($_SESSION['kunjung'] ?? '') === $hari) return;
    $_SESSION['kunjung'] = $hari;
    q('INSERT INTO kunjungan (tanggal, jumlah) VALUES (?, 1) ON DUPLICATE KEY UPDATE jumlah = jumlah + 1', [$hari]);
}
