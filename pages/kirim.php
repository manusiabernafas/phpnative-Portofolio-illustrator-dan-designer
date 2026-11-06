<?php
// Penerima formulir kontak publik: CSRF (di router), honeypot, batas kirim per IP, validasi
$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$jawab = function (bool $ok, string $msg) use ($ajax) {
    if ($ajax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok, 'msg' => $msg]); exit; }
    $_SESSION['kontak'] = [$ok, $msg];
    header('Location: index.php#kontak');
    exit;
};
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

// Honeypot: kolom "website" disembunyikan dari manusia; bot biasanya mengisinya
if (trim((string) ($_POST['website'] ?? '')) !== '') $jawab(true, 'Terima kasih, pesanmu sudah terkirim.');

$nama = trim((string) ($_POST['nama'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subjek = trim((string) ($_POST['subjek'] ?? ''));
$isi = trim((string) ($_POST['isi'] ?? ''));
$anggaran = in_array($_POST['anggaran'] ?? '', ANGGARAN, true) ? $_POST['anggaran'] : '';

if (mb_strlen($nama) < 2 || mb_strlen($nama) > 100) $jawab(false, 'Nama minimal 2 karakter.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $jawab(false, 'Alamat email belum benar.');
if (mb_strlen($subjek) > 150) $jawab(false, 'Subjek terlalu panjang.');
if (mb_strlen($isi) < 10 || mb_strlen($isi) > 3000) $jawab(false, 'Pesan minimal 10 karakter, maksimal 3000.');

$kirimTerakhir = (int) q('SELECT COUNT(*) FROM pesan WHERE ip = ? AND created_at > NOW() - INTERVAL 10 MINUTE', [ip()])->fetchColumn();
if ($kirimTerakhir >= 3) $jawab(false, 'Kamu sudah mengirim beberapa pesan. Coba lagi 10 menit lagi, ya.');
if (q('SELECT 1 FROM pesan WHERE email = ? AND isi = ? AND created_at > NOW() - INTERVAL 1 DAY', [$email, $isi])->fetchColumn()) $jawab(false, 'Pesan yang sama sudah kami terima.');

q('INSERT INTO pesan (nama, email, subjek, isi, anggaran, ip) VALUES (?, ?, ?, ?, ?, ?)', [$nama, $email, $subjek, $isi, $anggaran, ip()]);
$jawab(true, 'Terima kasih, ' . $nama . '! Pesanmu sudah masuk. Aku balas secepatnya.');
