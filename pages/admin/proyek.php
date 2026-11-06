<?php
require ROOT . '/includes/urutan.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $aksi = (string) ($_POST['aksi'] ?? '');
    if ($aksi === 'hapus') {
        $p = q('SELECT cover FROM proyek WHERE id = ?', [$id])->fetch();
        if ($p) {
            foreach (q('SELECT file FROM proyek_gambar WHERE proyek_id = ?', [$id])->fetchAll(PDO::FETCH_COLUMN) as $f) hapus_upload($f);
            hapus_upload($p['cover']);
            q('DELETE FROM proyek WHERE id = ?', [$id]);
            flash('Proyek dihapus.');
        }
    } elseif (in_array($aksi, ['tampil', 'unggulan'], true)) {
        q("UPDATE proyek SET $aksi = 1 - $aksi WHERE id = ?", [$id]);
    } elseif (in_array($aksi, ['naik', 'turun'], true)) {
        geser_urutan('proyek', $id, $aksi);
    }
    redirect('kelola-proyek', array_filter(['kat' => $_GET['kat'] ?? '']));
}
$kat = (string) ($_GET['kat'] ?? '');
$kategori = q('SELECT kategori, COUNT(*) n FROM proyek GROUP BY kategori ORDER BY kategori')->fetchAll(PDO::FETCH_KEY_PAIR);
$list = q('SELECT p.*, (SELECT COUNT(*) FROM proyek_gambar g WHERE g.proyek_id = p.id) galeri FROM proyek p' . ($kat !== '' ? ' WHERE kategori = ?' : '') . ' ORDER BY urutan, id', $kat !== '' ? [$kat] : [])->fetchAll();
$title = 'Proyek';
$sub = count($list) . ' proyek. Urutan di sini = urutan di halaman depan.';
$actions = '<a class="btn btn-main" href="' . url('proyek-form') . '">' . icon('plus', 18) . ' Proyek baru</a>';
require ROOT . '/includes/admin_header.php';
?>
<div class="chips">
    <a href="<?= url('kelola-proyek') ?>" class="chip <?= $kat === '' ? 'on' : '' ?>">Semua</a>
    <?php foreach ($kategori as $k => $n): ?><a href="<?= url('kelola-proyek', ['kat' => $k]) ?>" class="chip <?= $kat === $k ? 'on' : '' ?>"><?= e($k) ?> <small><?= $n ?></small></a><?php endforeach; ?>
</div>
<div class="proj-list">
    <?php foreach ($list as $i => $p): ?>
        <article class="proj-row <?= $p['tampil'] ? '' : 'hidden-row' ?>">
            <img src="<?= e(gambar($p['cover'])) ?>" alt="" class="proj-thumb">
            <div class="grow">
                <b><?= e($p['judul']) ?></b>
                <small><?= e($p['kategori']) ?> · <?= $p['tahun'] ?> · <?= e($p['klien'] ?: 'Proyek pribadi') ?> · <?= $p['galeri'] ?> gambar galeri</small>
                <div class="tags">
                    <?php if ($p['unggulan']): ?><span class="pill pill-acc"><?= icon('star', 13) ?> Unggulan</span><?php endif; ?>
                    <?php if (!$p['tampil']): ?><span class="pill"><?= icon('eye-off', 13) ?> Disembunyikan</span><?php endif; ?>
                    <span class="pill"><?= icon('eye', 13) ?> <?= $p['dilihat'] ?> dilihat</span>
                </div>
            </div>
            <div class="row-acts">
                <?php foreach ([['naik', 'arrow-up', 'Naikkan'], ['turun', 'arrow-down', 'Turunkan'], ['unggulan', 'star', 'Tandai unggulan'], ['tampil', $p['tampil'] ? 'eye' : 'eye-off', 'Tampil / sembunyikan']] as [$a, $ic, $t]): ?>
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="icon-btn" name="aksi" value="<?= $a ?>" title="<?= $t ?>"><?= icon($ic, 17) ?></button></form>
                <?php endforeach; ?>
                <a class="icon-btn" href="index.php?page=proyek&amp;slug=<?= e($p['slug']) ?>" target="_blank" title="Lihat"><?= icon('external-link', 17) ?></a>
                <a class="icon-btn" href="<?= url('proyek-form', ['id' => $p['id']]) ?>" title="Ubah"><?= icon('pencil', 17) ?></a>
                <form method="post" onsubmit="return confirm('Hapus proyek ini beserta semua gambarnya?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="icon-btn danger" name="aksi" value="hapus" title="Hapus"><?= icon('trash-2', 17) ?></button></form>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$list): ?><p class="empty card">Belum ada proyek. Klik "Proyek baru" untuk menambah.</p><?php endif; ?>
</div>
<?php require ROOT . '/includes/admin_footer.php';
