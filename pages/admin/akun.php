<?php
$u = user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mb_substr(trim((string) ($_POST['nama'] ?? '')), 0, 100);
    $username = trim((string) ($_POST['username'] ?? ''));
    $baru = (string) ($_POST['password_baru'] ?? '');
    if (!password_verify((string) ($_POST['password_lama'] ?? ''), $u['password'])) flash('Password saat ini salah.', 'error');
    elseif ($nama === '' || !preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $username)) flash('Nama wajib diisi, username 3-50 karakter (huruf, angka, titik, garis bawah).', 'error');
    elseif (q('SELECT 1 FROM users WHERE username = ? AND id <> ?', [$username, $u['id']])->fetchColumn()) flash('Username sudah dipakai.', 'error');
    elseif ($baru !== '' && (strlen($baru) < 8 || $baru !== ($_POST['password_ulang'] ?? ''))) flash('Password baru minimal 8 karakter dan harus sama dengan ulangannya.', 'error');
    else {
        q('UPDATE users SET nama = ?, username = ? WHERE id = ?', [$nama, $username, $u['id']]);
        if ($baru !== '') {
            q('UPDATE users SET password = ? WHERE id = ?', [password_hash($baru, PASSWORD_DEFAULT), $u['id']]);
            // Sesi di perangkat lain otomatis batal karena sidik password berubah; sesi ini diperbarui
            masuk(q('SELECT * FROM users WHERE id = ?', [$u['id']])->fetch());
        }
        flash($baru !== '' ? 'Akun & password diperbarui. Sesi di perangkat lain sudah dikeluarkan.' : 'Akun diperbarui.');
    }
    redirect('akun');
}
$title = 'Akun';
$sub = 'Ganti nama, username, dan password admin.';
require ROOT . '/includes/admin_header.php';
?>
<form method="post" class="card narrow">
    <?= csrf_field() ?>
    <label>Nama<input name="nama" value="<?= e($u['nama']) ?>" required maxlength="100"></label>
    <label>Username<input name="username" value="<?= e($u['username']) ?>" required maxlength="50" autocomplete="username"></label>
    <div class="row-2">
        <label>Password baru <small class="muted">(kosongkan jika tetap)</small><input type="password" name="password_baru" minlength="8" autocomplete="new-password"></label>
        <label>Ulangi password baru<input type="password" name="password_ulang" autocomplete="new-password"></label>
    </div>
    <label>Password saat ini <small class="muted">(wajib untuk menyimpan)</small><input type="password" name="password_lama" required autocomplete="current-password"></label>
    <button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan akun</button>
</form>
<?php require ROOT . '/includes/admin_footer.php';
