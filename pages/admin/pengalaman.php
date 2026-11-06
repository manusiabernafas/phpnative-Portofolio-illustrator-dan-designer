<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'hapus') { q('DELETE FROM pengalaman WHERE id = ?', [$id]); flash('Data dihapus.'); redirect('pengalaman'); }
    $in = fn(string $k, int $m) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $m);
    $mulai = $in('mulai', 7); $selesai = isset($_POST['sekarang']) ? '' : $in('selesai', 7);
    $d = [in_array($_POST['jenis'] ?? '', ['kerja', 'pendidikan'], true) ? $_POST['jenis'] : 'kerja', $in('posisi', 100), $in('instansi', 120), $in('kota', 60), $mulai . '-01', $selesai ? $selesai . '-01' : null, trim((string) ($_POST['deskripsi'] ?? ''))];
    if ($d[1] === '' || $d[2] === '' || !preg_match('/^\d{4}-\d{2}$/', $mulai) || ($selesai && !preg_match('/^\d{4}-\d{2}$/', $selesai))) flash('Posisi, instansi, dan bulan mulai wajib diisi dengan benar.', 'error');
    elseif ($selesai && $selesai < $mulai) flash('Bulan selesai tidak boleh sebelum bulan mulai.', 'error');
    elseif ($id) { q('UPDATE pengalaman SET jenis = ?, posisi = ?, instansi = ?, kota = ?, mulai = ?, selesai = ?, deskripsi = ? WHERE id = ?', [...$d, $id]); flash('Pengalaman diperbarui.'); }
    else { q('INSERT INTO pengalaman (jenis, posisi, instansi, kota, mulai, selesai, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)', $d); flash('Pengalaman ditambahkan.'); }
    redirect('pengalaman');
}
$edit = isset($_GET['id']) ? q('SELECT * FROM pengalaman WHERE id = ?', [(int) $_GET['id']])->fetch() : null;
$edit = $edit ?: ['id' => 0, 'jenis' => 'kerja', 'posisi' => '', 'instansi' => '', 'kota' => '', 'mulai' => date('Y-m-01'), 'selesai' => null, 'deskripsi' => ''];
$list = q('SELECT * FROM pengalaman ORDER BY jenis, COALESCE(selesai, "9999-12-31") DESC, mulai DESC')->fetchAll();
$title = 'Pengalaman & Pendidikan';
$sub = 'Diurutkan otomatis dari yang terbaru.';
require ROOT . '/includes/admin_header.php';
?>
<div class="grid-side">
    <section class="card">
        <ul class="rows timeline-rows">
            <?php foreach ($list as $x): ?>
                <li><span class="logo-box"><?= icon($x['jenis'] === 'kerja' ? 'briefcase' : 'graduation-cap', 20) ?></span><div class="grow"><b><?= e($x['posisi']) ?></b><small><?= e($x['instansi']) ?><?= $x['kota'] ? ' · ' . e($x['kota']) : '' ?> · <?= bulan_tahun($x['mulai']) ?> – <?= bulan_tahun($x['selesai']) ?></small></div>
                    <div class="row-acts"><a class="icon-btn" href="<?= url('pengalaman', ['id' => $x['id']]) ?>"><?= icon('pencil', 16) ?></a><form method="post" onsubmit="return confirm('Hapus data ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn danger" name="aksi" value="hapus"><?= icon('trash-2', 16) ?></button></form></div></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <form method="post" class="card sticky">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <div class="card-h"><h2><?= $edit['id'] ? 'Ubah' : 'Tambah' ?></h2><?php if ($edit['id']): ?><a href="<?= url('pengalaman') ?>">Batal</a><?php endif; ?></div>
        <div class="seg"><label><input type="radio" name="jenis" value="kerja" <?= $edit['jenis'] === 'kerja' ? 'checked' : '' ?>><span>Kerja</span></label><label><input type="radio" name="jenis" value="pendidikan" <?= $edit['jenis'] === 'pendidikan' ? 'checked' : '' ?>><span>Pendidikan</span></label></div>
        <label>Posisi / jurusan<input name="posisi" value="<?= e($edit['posisi']) ?>" required maxlength="100"></label>
        <label>Perusahaan / kampus<input name="instansi" value="<?= e($edit['instansi']) ?>" required maxlength="120"></label>
        <label>Kota<input name="kota" value="<?= e($edit['kota']) ?>" maxlength="60"></label>
        <div class="row-2"><label>Mulai<input type="month" name="mulai" value="<?= e(substr($edit['mulai'], 0, 7)) ?>" required></label><label>Selesai<input type="month" name="selesai" value="<?= e(substr((string) $edit['selesai'], 0, 7)) ?>"></label></div>
        <label class="check"><input type="checkbox" name="sekarang" <?= $edit['selesai'] ? '' : 'checked' ?>> Masih berlangsung</label>
        <label>Deskripsi<textarea name="deskripsi" rows="4"><?= e($edit['deskripsi']) ?></textarea></label>
        <button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan</button>
    </form>
</div>
<?php require ROOT . '/includes/admin_footer.php';
