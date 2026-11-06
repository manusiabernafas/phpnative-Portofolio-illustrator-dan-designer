<?php
require ROOT . '/includes/urutan.php';
const IKON_LAYANAN = ['code-xml', 'monitor', 'smartphone', 'server', 'database', 'cloud', 'palette', 'pen-tool', 'brush', 'layout-template', 'figma', 'rocket', 'shield-check', 'gauge', 'megaphone', 'camera', 'compass', 'ruler', 'boxes', 'terminal', 'wand', 'sparkles', 'zap', 'git-branch'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $aksi = (string) ($_POST['aksi'] ?? 'simpan');
    if ($aksi === 'hapus') { q('DELETE FROM layanan WHERE id = ?', [$id]); flash('Layanan dihapus.'); }
    elseif (in_array($aksi, ['naik', 'turun'], true)) geser_urutan('layanan', $id, $aksi);
    else {
        $harga = preg_replace('/\D/', '', (string) ($_POST['harga_mulai'] ?? ''));
        $d = [mb_substr(trim((string) ($_POST['judul'] ?? '')), 0, 100), mb_substr(trim((string) ($_POST['deskripsi'] ?? '')), 0, 255), in_array($_POST['ikon'] ?? '', IKON_LAYANAN, true) ? $_POST['ikon'] : 'sparkles', $harga === '' ? null : (int) $harga];
        if ($d[0] === '' || $d[1] === '') flash('Judul dan deskripsi wajib diisi.', 'error');
        elseif ($id) { q('UPDATE layanan SET judul = ?, deskripsi = ?, ikon = ?, harga_mulai = ? WHERE id = ?', [...$d, $id]); flash('Layanan diperbarui.'); }
        else { q('INSERT INTO layanan (judul, deskripsi, ikon, harga_mulai, urutan) VALUES (?, ?, ?, ?, ?)', [...$d, urutan_baru('layanan')]); flash('Layanan ditambahkan.'); }
    }
    redirect('layanan');
}
$edit = isset($_GET['id']) ? q('SELECT * FROM layanan WHERE id = ?', [(int) $_GET['id']])->fetch() : null;
$edit = $edit ?: ['id' => 0, 'judul' => '', 'deskripsi' => '', 'ikon' => 'code-xml', 'harga_mulai' => null];
$list = q('SELECT * FROM layanan ORDER BY urutan, id')->fetchAll();
$title = 'Layanan';
$sub = 'Jasa yang kamu tawarkan. Kosongkan harga jika tidak ingin menampilkan tarif.';
require ROOT . '/includes/admin_header.php';
?>
<div class="grid-side">
    <section class="card">
        <ul class="rows">
            <?php foreach ($list as $x): ?>
                <li><span class="logo-box"><?= icon($x['ikon'], 22) ?></span><div class="grow"><b><?= e($x['judul']) ?></b><small><?= e($x['deskripsi']) ?></small><?php if ($x['harga_mulai']): ?><small class="acc">Mulai <?= rupiah((int) $x['harga_mulai']) ?></small><?php endif; ?></div>
                    <div class="row-acts">
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn" name="aksi" value="naik"><?= icon('arrow-up', 16) ?></button></form>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn" name="aksi" value="turun"><?= icon('arrow-down', 16) ?></button></form>
                        <a class="icon-btn" href="<?= url('layanan', ['id' => $x['id']]) ?>"><?= icon('pencil', 16) ?></a>
                        <form method="post" onsubmit="return confirm('Hapus layanan ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn danger" name="aksi" value="hapus"><?= icon('trash-2', 16) ?></button></form>
                    </div></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <form method="post" class="card sticky">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <div class="card-h"><h2><?= $edit['id'] ? 'Ubah layanan' : 'Tambah layanan' ?></h2><?php if ($edit['id']): ?><a href="<?= url('layanan') ?>">Batal</a><?php endif; ?></div>
        <label>Judul<input name="judul" value="<?= e($edit['judul']) ?>" required maxlength="100"></label>
        <label>Deskripsi singkat<textarea name="deskripsi" rows="3" maxlength="255" required><?= e($edit['deskripsi']) ?></textarea></label>
        <label>Harga mulai (Rp)<input name="harga_mulai" value="<?= $edit['harga_mulai'] ? number_format((int) $edit['harga_mulai'], 0, ',', '.') : '' ?>" inputmode="numeric" placeholder="Opsional"></label>
        <p class="lbl">Ikon</p>
        <div class="logo-grid">
            <?php foreach (IKON_LAYANAN as $ic): ?><label title="<?= $ic ?>"><input type="radio" name="ikon" value="<?= $ic ?>" <?= $edit['ikon'] === $ic ? 'checked' : '' ?>><span><?= icon($ic, 20) ?></span></label><?php endforeach; ?>
        </div>
        <button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan</button>
    </form>
</div>
<?php require ROOT . '/includes/admin_footer.php';
