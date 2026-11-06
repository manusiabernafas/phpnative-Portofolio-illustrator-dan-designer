<?php
require ROOT . '/includes/data.php';
$p = data_proyek((string) ($_GET['slug'] ?? ''));
if (!$p) { http_response_code(404); require ROOT . '/pages/404.php'; exit; }
$s = setting();
$info = array_filter(['Klien' => $p['klien'] ?: 'Proyek pribadi', 'Peran' => $p['peran'], 'Tahun' => $p['tahun'], 'Durasi' => $p['durasi']]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= meta_head($p['judul'] . ' · ' . $s['nama'], $p['ringkasan'], gambar($p['cover'])) ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= FONT_SITE ?>&display=swap">
    <link rel="stylesheet" href="assets/css/site.css">
    <?= css_aksen() ?>
    <script>document.documentElement.classList.add('js')</script>
</head>
<body class="detail">
<header class="mast">
    <div class="mast-top mono"><a href="index.php"><?= icon('arrow-left', 14) ?> <?= e($s['nama']) ?></a><span class="hide-sm">Lembar karya</span><span><?= e($p['kategori']) ?></span></div>
</header>
<main>
    <section class="wrap d-head">
        <p class="mono kicker">&#8213; <?= e($p['kategori']) ?> &middot; <?= $p['tahun'] ?></p>
        <h1 class="misreg"><?= e($p['judul']) ?></h1>
        <p class="lead"><?= e($p['ringkasan']) ?></p>
    </section>
    <section class="wrap d-spread">
        <figure class="d-cover rv turn"><span class="tape t1"></span><span class="tape t2"></span><img src="<?= e(gambar($p['cover'])) ?>" alt="<?= e($p['judul']) ?>"></figure>
        <aside class="d-card rv pop" style="--r:2deg">
            <dl><?php foreach ($info as $k => $v): ?><dt class="mono"><?= $k ?></dt><dd><?= e($v) ?></dd><?php endforeach; ?></dl>
            <?php if ($p['tek']): ?><p class="mono">Alat</p><ul class="d-tek"><?php foreach ($p['tek'] as $t): ?><li><?= logo($t['logo'], 18, $t['nama']) ?> <?= e($t['nama']) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if ($u = link_aman($p['link_demo'])): ?><a class="btn btn-ink" href="<?= e($u) ?>" target="_blank" rel="noopener"><?= icon('external-link', 16) ?> Lihat langsung</a><?php endif; ?>
            <?php if ($u = link_aman($p['link_repo'])): ?><a class="btn btn-paper" href="<?= e($u) ?>" target="_blank" rel="noopener"><?= icon('file-text', 16) ?> Dokumen</a><?php endif; ?>
        </aside>
    </section>
    <article class="wrap prose rv"><?= markdown_mini($p['isi']) ?></article>
    <?php if ($p['galeri']): ?>
    <section class="wrap d-gallery">
        <?php foreach ($p['galeri'] as $i => $g): ?>
            <figure class="rv pop" style="--r:<?= [-2, 2.5, -1, 1.5][$i % 4] ?>deg"><span class="tape"></span><a href="<?= e(gambar($g['file'])) ?>" data-lb><img src="<?= e(gambar($g['file'])) ?>" alt="<?= e($g['keterangan']) ?>" loading="lazy"></a><?php if ($g['keterangan']): ?><figcaption class="hand"><?= e($g['keterangan']) ?></figcaption><?php endif; ?></figure>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    <?php if ($p['berikut']): ?>
    <nav class="wrap d-next">
        <a href="<?= url('proyek', ['slug' => $p['sebelum']['slug']]) ?>"><img src="<?= e(gambar($p['sebelum']['cover'])) ?>" alt=""><span><small class="mono"><?= icon('arrow-left', 14) ?> Halaman sebelumnya</small><b><?= e($p['sebelum']['judul']) ?></b></span></a>
        <a href="<?= url('proyek', ['slug' => $p['berikut']['slug']]) ?>" class="nx"><img src="<?= e(gambar($p['berikut']['cover'])) ?>" alt=""><span><small class="mono">Halaman berikutnya <?= icon('arrow-right', 14) ?></small><b><?= e($p['berikut']['judul']) ?></b></span></a>
    </nav>
    <?php endif; ?>
</main>
<div class="lightbox" id="lb" hidden><button class="btn btn-paper" aria-label="Tutup"><?= icon('x', 22) ?></button><img alt=""></div>
<footer class="foot"><div class="wrap foot-row mono"><span>&copy; <?= date('Y') ?> <?= e($s['nama']) ?></span><a href="index.php#surat">Kirim surat</a></div></footer>
<script src="assets/js/site.js"></script>
</body>
</html>
