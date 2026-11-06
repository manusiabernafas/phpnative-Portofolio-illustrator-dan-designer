<?php
$navPesan = (int) q('SELECT COUNT(*) FROM pesan WHERE dibaca = 0')->fetchColumn();
$navMenu = [
    ['dashboard', 'Dashboard', 'layout-dashboard', 0],
    ['profil', 'Profil & Situs', 'user', 0],
    ['kelola-proyek', 'Proyek', 'folder-kanban', 0],
    ['teknologi', 'Teknologi', 'cpu', 0],
    ['pengalaman', 'Pengalaman', 'briefcase', 0],
    ['layanan', 'Layanan', 'layers', 0],
    ['testimoni', 'Testimoni', 'quote', 0],
    ['pesan', 'Pesan Masuk', 'inbox', $navPesan],
    ['akun', 'Akun', 'key-round', 0],
];
$navCur = $navAktif ?? ($_GET['page'] ?? 'dashboard');
$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= e($title ?? 'Dashboard') ?> · CMS <?= e(setting('nama')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= FONT_ADMIN ?>&display=swap">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="adm">
<div class="adm-layout">
    <aside class="adm-side" id="side">
        <a class="adm-brand" href="<?= url('dashboard') ?>"><span class="mark"><?= e(mb_strtoupper(mb_substr(setting('nama', 'P'), 0, 1))) ?></span><span><b><?= e(setting('nama')) ?></b><small>CMS Portofolio</small></span></a>
        <nav>
            <?php foreach ($navMenu as [$k, $l, $ic, $n]): ?>
                <a href="<?= url($k) ?>" class="<?= $navCur === $k ? 'on' : '' ?>"><?= icon($ic, 19) ?><span><?= $l ?></span><?php if ($n): ?><em class="badge"><?= $n ?></em><?php endif; ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="adm-side-foot">
            <a href="index.php" target="_blank"><?= icon('external-link', 17) ?> Lihat situs</a>
            <a href="<?= url('logout') ?>"><?= icon('log-out', 17) ?> Keluar</a>
        </div>
    </aside>
    <div class="adm-shade" onclick="document.body.classList.remove('nav-open')"></div>
    <main class="adm-main">
        <header class="adm-top">
            <button class="icon-btn adm-burger" type="button" onclick="document.body.classList.toggle('nav-open')" aria-label="Menu"><?= icon('menu', 22) ?></button>
            <div class="adm-title"><h1><?= e($title ?? 'Dashboard') ?></h1><?php if (!empty($sub)): ?><p><?= $sub ?></p><?php endif; ?></div>
            <div class="adm-acts"><?= $actions ?? '' ?></div>
        </header>
        <?php if ($flash): ?><div class="toast <?= $flash['type'] === 'error' ? 'err' : '' ?>"><?= icon($flash['type'] === 'error' ? 'circle-alert' : 'circle-check', 18) ?> <?= e($flash['msg']) ?></div><?php endif; ?>
        <div class="adm-body">
