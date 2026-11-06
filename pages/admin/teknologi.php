<?php
require ROOT . '/includes/urutan.php';
$logos = daftar_logo();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? 'simpan');
    $id = (int) ($_POST['id'] ?? 0);
    if ($aksi === 'hapus') { q('DELETE FROM teknologi WHERE id = ?', [$id]); flash('Teknologi dihapus.'); }
    elseif (in_array($aksi, ['naik', 'turun'], true)) geser_urutan('teknologi', $id, $aksi);
    else {
        $nama = mb_substr(trim((string) ($_POST['nama'] ?? '')), 0, 60);
        $logo = (string) ($_POST['logo'] ?? '');
        $d = [$nama, isset($logos[$logo]) ? $logo : '', mb_substr(trim((string) ($_POST['kategori'] ?? 'Tools')), 0, 40) ?: 'Tools', max(0, min(100, (int) ($_POST['level'] ?? 70))), isset($_POST['unggulan']) ? 1 : 0];
        if ($nama === '') flash('Nama teknologi wajib diisi.', 'error');
        elseif ($id) { q('UPDATE teknologi SET nama = ?, logo = ?, kategori = ?, level = ?, unggulan = ? WHERE id = ?', [...$d, $id]); flash('Teknologi diperbarui.'); }
        else { q('INSERT INTO teknologi (nama, logo, kategori, level, unggulan, urutan) VALUES (?, ?, ?, ?, ?, ?)', [...$d, urutan_baru('teknologi')]); flash('Teknologi ditambahkan.'); }
    }
    redirect('teknologi');
}
$edit = isset($_GET['id']) ? q('SELECT * FROM teknologi WHERE id = ?', [(int) $_GET['id']])->fetch() : null;
$edit = $edit ?: ['id' => 0, 'nama' => '', 'logo' => '', 'kategori' => '', 'level' => 80, 'unggulan' => 1];
$list = q('SELECT t.*, (SELECT COUNT(*) FROM proyek_teknologi pt WHERE pt.teknologi_id = t.id) dipakai FROM teknologi t ORDER BY urutan, id')->fetchAll();
$kategori = array_unique(array_merge(['Bahasa', 'Framework', 'Database', 'Desain', 'Tools'], array_column($list, 'kategori')));
$title = 'Teknologi & Tools';
$sub = 'Logo yang tampil di bagian keahlian dan di setiap proyek. Teknologi unggulan ikut berputar di hero.';
require ROOT . '/includes/admin_header.php';
?>
<div class="grid-side">
    <section class="card">
        <ul class="rows">
            <?php foreach ($list as $t): ?>
                <li>
                    <span class="logo-box"><?= logo($t['logo'], 24, $t['nama']) ?></span>
                    <div class="grow"><b><?= e($t['nama']) ?> <?php if ($t['unggulan']): ?><span class="pill pill-acc"><?= icon('star', 12) ?></span><?php endif; ?></b><small><?= e($t['kategori']) ?> · dipakai <?= $t['dipakai'] ?> proyek</small><span class="meter"><i style="width:<?= (int) $t['level'] ?>%"></i></span></div>
                    <div class="row-acts">
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="icon-btn" name="aksi" value="naik" title="Naikkan"><?= icon('arrow-up', 16) ?></button></form>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="icon-btn" name="aksi" value="turun" title="Turunkan"><?= icon('arrow-down', 16) ?></button></form>
                        <a class="icon-btn" href="<?= url('teknologi', ['id' => $t['id']]) ?>" title="Ubah"><?= icon('pencil', 16) ?></a>
                        <form method="post" onsubmit="return confirm('Hapus teknologi ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="icon-btn danger" name="aksi" value="hapus" title="Hapus"><?= icon('trash-2', 16) ?></button></form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <form method="post" class="card sticky">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <div class="card-h"><h2><?= $edit['id'] ? 'Ubah teknologi' : 'Tambah teknologi' ?></h2><?php if ($edit['id']): ?><a href="<?= url('teknologi') ?>">Batal</a><?php endif; ?></div>
        <label>Nama<input name="nama" value="<?= e($edit['nama']) ?>" required maxlength="60"></label>
        <label>Kategori<input name="kategori" value="<?= e($edit['kategori']) ?>" list="katTek" maxlength="40"><datalist id="katTek"><?php foreach ($kategori as $k): ?><option value="<?= e($k) ?>"><?php endforeach; ?></datalist></label>
        <label>Tingkat penguasaan: <output id="lvOut"><?= (int) $edit['level'] ?></output>%<input type="range" name="level" min="0" max="100" step="5" value="<?= (int) $edit['level'] ?>" oninput="lvOut.value=this.value"></label>
        <label class="check"><input type="checkbox" name="unggulan" <?= $edit['unggulan'] ? 'checked' : '' ?>> Unggulan (tampil di hero)</label>
        <p class="lbl">Logo <small class="muted">(klik salah satu, atau biarkan kosong untuk monogram)</small></p>
        <input type="search" class="logo-search" placeholder="Cari logo…" oninput="filterLogo(this.value)">
        <div class="logo-grid">
            <label title="Tanpa logo"><input type="radio" name="logo" value="" <?= $edit['logo'] === '' ? 'checked' : '' ?>><span><?= logo('', 22, 'Aa') ?></span></label>
            <?php foreach ($logos as $k => [$t]): ?>
                <label title="<?= e($t) ?>" data-n="<?= e(strtolower($t)) ?>"><input type="radio" name="logo" value="<?= e($k) ?>" <?= $edit['logo'] === $k ? 'checked' : '' ?>><span><?= logo($k, 22) ?></span></label>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan</button>
    </form>
</div>
<?php require ROOT . '/includes/admin_footer.php';
