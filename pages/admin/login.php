<?php
if (user()) redirect('dashboard');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_terkunci()) {
        $err = 'Terlalu banyak percobaan gagal. Coba lagi 15 menit lagi.';
    } else {
        $u = q('SELECT * FROM users WHERE username = ?', [trim((string) ($_POST['username'] ?? ''))])->fetch();
        if ($u && password_verify((string) ($_POST['password'] ?? ''), $u['password'])) {
            q('DELETE FROM login_gagal WHERE ip = ?', [ip()]);
            masuk($u);
            redirect('dashboard');
        }
        q('INSERT INTO login_gagal (ip) VALUES (?)', [ip()]);
        $err = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Masuk CMS · <?= e(setting('nama')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= FONT_ADMIN ?>&display=swap">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="adm adm-login">
    <form method="post" class="login-card">
        <?= csrf_field() ?>
        <img src="<?= e(gambar(setting('foto'), 'assets/img/potret.svg')) ?>" alt="" class="login-av">
        <h1>Masuk ke CMS</h1>
        <p class="muted">Kelola isi portofolio <?= e(setting('nama')) ?>.</p>
        <?php if ($err): ?><div class="toast err"><?= icon('circle-alert', 18) ?> <?= e($err) ?></div><?php endif; ?>
        <label>Username<input name="username" required autofocus autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="btn btn-main" type="submit"><?= icon('lock', 18) ?> Masuk</button>
        <a href="index.php" class="back"><?= icon('arrow-left', 16) ?> Kembali ke situs</a>
    </form>
</body>
</html>
