<?php
// Router: halaman publik portofolio + panel CMS (admin)
require __DIR__ . '/includes/bootstrap.php';

$publik = [
    'beranda' => 'pages/beranda.php',
    'proyek'  => 'pages/proyek.php',
    'kirim'   => 'pages/kirim.php',
];
$admin = [
    'login'         => 'pages/admin/login.php',
    'logout'        => 'pages/admin/logout.php',
    'dashboard'     => 'pages/admin/dashboard.php',
    'profil'        => 'pages/admin/profil.php',
    'kelola-proyek' => 'pages/admin/proyek.php',
    'proyek-form'   => 'pages/admin/proyek-form.php',
    'teknologi'     => 'pages/admin/teknologi.php',
    'pengalaman'    => 'pages/admin/pengalaman.php',
    'layanan'       => 'pages/admin/layanan.php',
    'testimoni'     => 'pages/admin/testimoni.php',
    'pesan'         => 'pages/admin/pesan.php',
    'akun'          => 'pages/admin/akun.php',
];

$page = (string) ($_GET['page'] ?? 'beranda');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (isset($publik[$page])) {
    verify_csrf();
    require __DIR__ . '/' . $publik[$page];
    exit;
}
if (!isset($admin[$page])) {
    http_response_code(404);
    require __DIR__ . '/pages/404.php';
    exit;
}
header('X-Frame-Options: DENY');
if ($page !== 'login' && !user()) redirect('login');
verify_csrf();
require __DIR__ . '/' . $admin[$page];
