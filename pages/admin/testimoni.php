<?php
require ROOT . '/includes/urutan.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $aksi = (string) ($_POST['aksi'] ?? 'simpan');
    $lama = $id ? q('SELECT * FROM testimoni WHERE id = ?', [$id])->fetch() : null;
    try {
        if ($aksi === 'hapus' && $lama) { hapus_upload($lama['foto']); q('DELETE FROM testimoni WHERE id = ?', [$id]); flash('Testimoni dihapus.'); }
        elseif ($aksi === 'tampil') q('UPDATE testimoni SET tampil = 1 - tampil WHERE id = ?', [$id]);
        elseif (in_array($aksi, ['naik', 'turun'], true)) geser_urutan('testimoni', $id, $aksi);
        elseif ($aksi === 'simpan') {
            $d = ['nama' => mb_substr(trim((string) ($_POST['nama'] ?? '')), 0, 100), 'jabatan' => mb_substr(trim((string) ($_POST['jabatan'] ?? '')), 0, 120), 'isi' => mb_substr(trim((string) ($_POST['isi'] ?? '')), 0, 1000), 'rating' => max(1, min(5, (int) ($_POST['rating'] ?? 5)))];
            if ($d['nama'] === '' || $d['isi'] === '') throw new RuntimeException('Nama dan isi testimoni wajib diisi.');
            if ($f = simpan_gambar($_FILES['foto'] ?? [], 400)) { if ($lama) hapus_upload($lama['foto']); $d['foto'] = $f; }
            if ($lama) { q('UPDATE testimoni SET ' . implode(', ', array_map(fn($k) => "$k = ?", array_keys($d))) . ' WHERE id = ?', [...array_values($d), $id]); flash('Testimoni diperbarui.'); }
            else { $d['urutan'] = urutan_baru('testimoni'); q('INSERT INTO testimoni (' . implode(', ', array_keys($d)) . ') VALUES (' . implode(', ', array_fill(0, count($d), '?')) . ')', array_values($d)); flash('Testimoni ditambahkan.'); }
        }
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'error'); }
    redirect('testimoni');
}
$edit = isset($_GET['id']) ? q('SELECT * FROM testimoni WHERE id = ?', [(int) $_GET['id']])->fetch() : null;
$edit = $edit ?: ['id' => 0, 'nama' => '', 'jabatan' => '', 'isi' => '', 'rating' => 5, 'foto' => ''];
$list = q('SELECT * FROM testimoni ORDER BY urutan, id')->fetchAll();
$title = 'Testimoni';
$sub = 'Kata klien dan rekan kerja tentangmu.';
require ROOT . '/includes/admin_header.php';
?>
<div class="grid-side">
    <section class="card">
        <ul class="rows">
            <?php foreach ($list as $x): ?>
                <li class="<?= $x['tampil'] ? '' : 'hidden-row' ?>"><img src="<?= e(gambar($x['foto'], 'assets/img/avatar.svg')) ?>" alt="" class="av-img"><div class="grow"><b><?= e($x['nama']) ?> <span class="stars"><?= str_repeat('&#9733;', (int) $x['rating']) ?></span></b><small><?= e($x['jabatan']) ?></small><small class="clip">“<?= e(mb_strimwidth($x['isi'], 0, 140, '…')) ?>”</small></div>
                    <div class="row-acts">
                        <?php foreach ([['naik', 'arrow-up'], ['turun', 'arrow-down'], ['tampil', $x['tampil'] ? 'eye' : 'eye-off']] as [$a, $ic]): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn" name="aksi" value="<?= $a ?>"><?= icon($ic, 16) ?></button></form><?php endforeach; ?>
                        <a class="icon-btn" href="<?= url('testimoni', ['id' => $x['id']]) ?>"><?= icon('pencil', 16) ?></a>
                        <form method="post" onsubmit="return confirm('Hapus testimoni ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="icon-btn danger" name="aksi" value="hapus"><?= icon('trash-2', 16) ?></button></form>
                    </div></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <form method="post" class="card sticky" enctype="multipart/form-data">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><input type="hidden" name="aksi" value="simpan">
        <div class="card-h"><h2><?= $edit['id'] ? 'Ubah testimoni' : 'Tambah testimoni' ?></h2><?php if ($edit['id']): ?><a href="<?= url('testimoni') ?>">Batal</a><?php endif; ?></div>
        <label>Nama<input name="nama" value="<?= e($edit['nama']) ?>" required maxlength="100"></label>
        <label>Jabatan & perusahaan<input name="jabatan" value="<?= e($edit['jabatan']) ?>" maxlength="120"></label>
        <label>Isi testimoni<textarea name="isi" rows="5" required maxlength="1000"><?= e($edit['isi']) ?></textarea></label>
        <label>Rating<select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int) $edit['rating'] === $i ? 'selected' : '' ?>><?= $i ?> bintang</option><?php endfor; ?></select></label>
        <label class="file"><?= icon('upload', 18) ?> Foto (opsional)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
        <button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan</button>
    </form>
</div>
<?php require ROOT . '/includes/admin_footer.php';
