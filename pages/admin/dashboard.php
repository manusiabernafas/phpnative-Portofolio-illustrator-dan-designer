<?php
$title = 'Dashboard';
$sub = 'Halo, ' . e(user()['nama']) . '. Ini ringkasan portofoliomu.';
$angka = q("SELECT
    (SELECT COUNT(*) FROM proyek WHERE tampil = 1) proyek,
    (SELECT COALESCE(SUM(dilihat), 0) FROM proyek) dilihat,
    (SELECT COUNT(*) FROM pesan WHERE dibaca = 0) pesan_baru,
    (SELECT COALESCE(SUM(jumlah), 0) FROM kunjungan WHERE tanggal > CURDATE() - INTERVAL 30 DAY) kunjungan30")->fetch();

// Grafik kunjungan 14 hari terakhir (hari tanpa data tetap ditampilkan 0)
$raw = q('SELECT tanggal, jumlah FROM kunjungan WHERE tanggal > CURDATE() - INTERVAL 14 DAY')->fetchAll(PDO::FETCH_KEY_PAIR);
$grafik = [];
for ($i = 13; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $grafik[$d] = (int) ($raw[$d] ?? 0); }
$maks = max(1, max($grafik));

$populer = q('SELECT id, judul, kategori, dilihat, cover FROM proyek ORDER BY dilihat DESC LIMIT 5')->fetchAll();
$pesanBaru = q('SELECT * FROM pesan ORDER BY created_at DESC LIMIT 5')->fetchAll();
$kosong = [];
if (!setting('foto')) $kosong[] = 'foto profil';
if (!setting('cv')) $kosong[] = 'file CV (PDF)';
if (!(int) q('SELECT COUNT(*) FROM testimoni')->fetchColumn()) $kosong[] = 'testimoni';
$actions = '<a class="btn btn-main" href="' . url('proyek-form') . '">' . icon('plus', 18) . ' Proyek baru</a>';
require ROOT . '/includes/admin_header.php';
?>
<div class="stats">
    <div class="stat"><span class="ic-box"><?= icon('folder-kanban', 22) ?></span><b><?= $angka['proyek'] ?></b><small>Proyek tampil</small></div>
    <div class="stat"><span class="ic-box"><?= icon('eye', 22) ?></span><b><?= number_format((int) $angka['dilihat'], 0, ',', '.') ?></b><small>Total proyek dilihat</small></div>
    <div class="stat"><span class="ic-box"><?= icon('trending-up', 22) ?></span><b><?= number_format((int) $angka['kunjungan30'], 0, ',', '.') ?></b><small>Pengunjung 30 hari</small></div>
    <a class="stat" href="<?= url('pesan') ?>"><span class="ic-box"><?= icon('inbox', 22) ?></span><b><?= $angka['pesan_baru'] ?></b><small>Pesan belum dibaca</small></a>
</div>

<?php if ($kosong): ?><div class="note"><?= icon('sparkles', 18) ?> Biar portofolio makin meyakinkan, lengkapi: <?= e(implode(', ', $kosong)) ?>.</div><?php endif; ?>

<div class="grid-2">
    <section class="card">
        <div class="card-h"><h2>Kunjungan 14 hari</h2><span class="muted"><?= array_sum($grafik) ?> sesi</span></div>
        <div class="bars">
            <?php foreach ($grafik as $d => $n): ?>
                <div class="bar" title="<?= tgl($d) ?>: <?= $n ?> kunjungan"><i style="height:<?= max(3, round($n / $maks * 100)) ?>%"></i><span><?= date('j', strtotime($d)) ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="card">
        <div class="card-h"><h2>Proyek paling sering dilihat</h2><a href="<?= url('kelola-proyek') ?>">Semua</a></div>
        <ul class="rows">
            <?php foreach ($populer as $p): ?>
                <li><img src="<?= e(gambar($p['cover'])) ?>" alt="" class="thumb"><div class="grow"><b><?= e($p['judul']) ?></b><small><?= e($p['kategori']) ?></small></div><span class="pill"><?= icon('eye', 14) ?> <?= $p['dilihat'] ?></span></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<section class="card">
    <div class="card-h"><h2>Pesan terbaru</h2><a href="<?= url('pesan') ?>">Buka kotak masuk</a></div>
    <?php if (!$pesanBaru): ?><p class="empty">Belum ada pesan dari pengunjung.</p><?php endif; ?>
    <ul class="rows">
        <?php foreach ($pesanBaru as $m): ?>
            <li class="<?= $m['dibaca'] ? '' : 'unread' ?>"><span class="av"><?= e(mb_strtoupper(mb_substr($m['nama'], 0, 1))) ?></span><div class="grow"><b><?= e($m['nama']) ?> <small>· <?= e($m['subjek'] ?: 'Tanpa subjek') ?></small></b><small class="clip"><?= e(mb_strimwidth($m['isi'], 0, 110, '…')) ?></small></div><small class="muted nowrap"><?= waktu_lalu($m['created_at']) ?></small></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php require ROOT . '/includes/admin_footer.php';
