<?php
require ROOT . '/includes/csv.php';
if (($_GET['unduh'] ?? '') === 'csv') {
    kirim_csv('pesan-masuk-' . date('Ymd') . '.csv', ['Tanggal', 'Nama', 'Email', 'Subjek', 'Anggaran', 'Pesan', 'Status'],
        (function () { foreach (q('SELECT created_at, nama, email, subjek, anggaran, isi, dibaca FROM pesan ORDER BY created_at DESC') as $r) { $r['dibaca'] = $r['dibaca'] ? 'Dibaca' : 'Baru'; yield $r; } })());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    match ((string) ($_POST['aksi'] ?? '')) {
        'hapus' => q('DELETE FROM pesan WHERE id = ?', [$id]),
        'belum' => q('UPDATE pesan SET dibaca = 0 WHERE id = ?', [$id]),
        'semua' => q('UPDATE pesan SET dibaca = 1'),
        default => null,
    };
    if (($_POST['aksi'] ?? '') === 'hapus') flash('Pesan dihapus.');
    redirect('pesan');
}
$buka = isset($_GET['id']) ? q('SELECT * FROM pesan WHERE id = ?', [(int) $_GET['id']])->fetch() : null;
if ($buka && !$buka['dibaca']) q('UPDATE pesan SET dibaca = 1 WHERE id = ?', [$buka['id']]);
$filter = ($_GET['f'] ?? '') === 'baru';
$list = q('SELECT * FROM pesan' . ($filter ? ' WHERE dibaca = 0' : '') . ' ORDER BY created_at DESC LIMIT 200')->fetchAll();
$title = 'Pesan Masuk';
$sub = 'Dari formulir kontak di halaman depan.';
$actions = '<a class="btn" href="' . url('pesan', ['unduh' => 'csv']) . '">' . icon('download', 18) . ' CSV</a><form method="post">' . csrf_field() . '<button class="btn" name="aksi" value="semua">' . icon('check', 18) . ' Tandai dibaca</button></form>';
require ROOT . '/includes/admin_header.php';
?>
<div class="chips"><a class="chip <?= $filter ? '' : 'on' ?>" href="<?= url('pesan') ?>">Semua</a><a class="chip <?= $filter ? 'on' : '' ?>" href="<?= url('pesan', ['f' => 'baru']) ?>">Belum dibaca</a></div>
<div class="grid-side inbox">
    <section class="card">
        <?php if (!$list): ?><p class="empty">Kotak masuk kosong.</p><?php endif; ?>
        <ul class="rows">
            <?php foreach ($list as $m): ?>
                <li class="<?= $m['dibaca'] ? '' : 'unread' ?> <?= $buka && $buka['id'] == $m['id'] ? 'sel' : '' ?>"><a class="row-link" href="<?= url('pesan', ['id' => $m['id']] + ($filter ? ['f' => 'baru'] : [])) ?>"><span class="av"><?= e(mb_strtoupper(mb_substr($m['nama'], 0, 1))) ?></span><div class="grow"><b><?= e($m['nama']) ?></b><small><?= e($m['subjek'] ?: 'Tanpa subjek') ?></small><small class="clip muted"><?= e(mb_strimwidth($m['isi'], 0, 80, '…')) ?></small></div><small class="muted nowrap"><?= waktu_lalu($m['created_at']) ?></small></a></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="card sticky">
        <?php if ($buka): ?>
            <div class="card-h"><h2><?= e($buka['subjek'] ?: 'Tanpa subjek') ?></h2></div>
            <p><b><?= e($buka['nama']) ?></b> &lt;<?= e($buka['email']) ?>&gt;<br><small class="muted"><?= tgl($buka['created_at']) ?>, <?= date('H.i', strtotime($buka['created_at'])) ?> WIB<?= $buka['anggaran'] ? ' · Anggaran: ' . e($buka['anggaran']) : '' ?></small></p>
            <div class="msg-body"><?= nl2br(e($buka['isi'])) ?></div>
            <div class="btn-row">
                <a class="btn btn-main" href="mailto:<?= e(rawurlencode($buka['email'])) ?>?subject=<?= e(rawurlencode('Re: ' . ($buka['subjek'] ?: 'Pesan dari portofolio'))) ?>"><?= icon('send', 17) ?> Balas email</a>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $buka['id'] ?>"><button class="btn" name="aksi" value="belum"><?= icon('mail', 17) ?> Tandai belum dibaca</button></form>
                <form method="post" onsubmit="return confirm('Hapus pesan ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $buka['id'] ?>"><button class="btn danger" name="aksi" value="hapus"><?= icon('trash-2', 17) ?> Hapus</button></form>
            </div>
        <?php else: ?>
            <p class="empty"><?= icon('inbox', 28) ?><br>Pilih pesan untuk membaca.</p>
        <?php endif; ?>
    </section>
</div>
<?php require ROOT . '/includes/admin_footer.php';
