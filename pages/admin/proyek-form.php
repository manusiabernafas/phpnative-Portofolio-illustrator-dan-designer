<?php
require ROOT . '/includes/urutan.php';
$id = (int) ($_GET['id'] ?? 0);
$p = $id ? q('SELECT * FROM proyek WHERE id = ?', [$id])->fetch() : null;
if ($id && !$p) { flash('Proyek tidak ditemukan.', 'error'); redirect('kelola-proyek'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = fn(string $k, int $max = 255) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $d = [
        'judul' => $in('judul', 150), 'kategori' => $in('kategori', 50), 'ringkasan' => $in('ringkasan'),
        'isi' => trim((string) ($_POST['isi'] ?? '')), 'klien' => $in('klien', 100), 'peran' => $in('peran', 100),
        'tahun' => (int) ($_POST['tahun'] ?? date('Y')), 'durasi' => $in('durasi', 40),
        'link_demo' => link_aman($in('link_demo')), 'link_repo' => link_aman($in('link_repo')),
        'unggulan' => isset($_POST['unggulan']) ? 1 : 0, 'tampil' => isset($_POST['tampil']) ? 1 : 0,
    ];
    try {
        if ($d['judul'] === '' || $d['kategori'] === '') throw new RuntimeException('Judul dan kategori wajib diisi.');
        if ($d['tahun'] < 1990 || $d['tahun'] > (int) date('Y') + 1) throw new RuntimeException('Tahun tidak valid.');
        if (($in('link_demo') !== '' && !$d['link_demo']) || ($in('link_repo') !== '' && !$d['link_repo'])) throw new RuntimeException('Link harus diawali http:// atau https://');
        $cover = simpan_gambar($_FILES['cover'] ?? []);
        db()->beginTransaction();
        $d['slug'] = slug_unik($d['judul'], $id);
        if ($p) {
            if ($cover) { hapus_upload($p['cover']); $d['cover'] = $cover; }
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($d)));
            q("UPDATE proyek SET $set WHERE id = ?", [...array_values($d), $id]);
        } else {
            $d['cover'] = $cover ?? '';
            $d['urutan'] = urutan_baru('proyek');
            q('INSERT INTO proyek (' . implode(', ', array_keys($d)) . ') VALUES (' . implode(', ', array_fill(0, count($d), '?')) . ')', array_values($d));
            $id = (int) db()->lastInsertId();
        }
        // Teknologi yang dipakai
        q('DELETE FROM proyek_teknologi WHERE proyek_id = ?', [$id]);
        $st = db()->prepare('INSERT IGNORE INTO proyek_teknologi (proyek_id, teknologi_id) SELECT ?, id FROM teknologi WHERE id = ?');
        foreach ((array) ($_POST['tek'] ?? []) as $t) $st->execute([$id, (int) $t]);
        // Keterangan & hapus gambar galeri lama
        foreach ((array) ($_POST['ket'] ?? []) as $gid => $ket) q('UPDATE proyek_gambar SET keterangan = ? WHERE id = ? AND proyek_id = ?', [mb_substr(trim((string) $ket), 0, 150), (int) $gid, $id]);
        foreach ((array) ($_POST['hapus_gambar'] ?? []) as $gid) {
            $f = q('SELECT file FROM proyek_gambar WHERE id = ? AND proyek_id = ?', [(int) $gid, $id])->fetchColumn();
            if ($f) { hapus_upload($f); q('DELETE FROM proyek_gambar WHERE id = ?', [(int) $gid]); }
        }
        db()->commit();
        // Galeri baru (bisa banyak sekaligus)
        $g = $_FILES['galeri'] ?? null;
        $gagal = 0;
        if ($g && is_array($g['name'])) {
            $u = (int) q('SELECT COALESCE(MAX(urutan), 0) FROM proyek_gambar WHERE proyek_id = ?', [$id])->fetchColumn();
            foreach ($g['name'] as $i => $nm) {
                try {
                    $f = simpan_gambar(['name' => $nm, 'tmp_name' => $g['tmp_name'][$i], 'error' => $g['error'][$i], 'size' => $g['size'][$i]]);
                    if ($f) q('INSERT INTO proyek_gambar (proyek_id, file, urutan) VALUES (?, ?, ?)', [$id, $f, ++$u]);
                } catch (RuntimeException) { $gagal++; }
            }
        }
        flash('Proyek tersimpan.' . ($gagal ? " $gagal gambar galeri dilewati karena bukan JPG/PNG/WebP atau terlalu besar." : ''), $gagal ? 'error' : 'ok');
        redirect('proyek-form', ['id' => $id]);
    } catch (RuntimeException $ex) {
        if (db()->inTransaction()) db()->rollBack();
        flash($ex->getMessage(), 'error');
        $p = array_merge($p ?? [], $d);
    }
}
$p ??= ['judul' => '', 'kategori' => '', 'ringkasan' => '', 'isi' => '', 'klien' => '', 'peran' => '', 'tahun' => date('Y'), 'durasi' => '', 'link_demo' => '', 'link_repo' => '', 'unggulan' => 0, 'tampil' => 1, 'cover' => ''];
$galeri = $id ? q('SELECT * FROM proyek_gambar WHERE proyek_id = ? ORDER BY urutan, id', [$id])->fetchAll() : [];
$tekDipilih = $id ? q('SELECT teknologi_id FROM proyek_teknologi WHERE proyek_id = ?', [$id])->fetchAll(PDO::FETCH_COLUMN) : [];
$tekSemua = q('SELECT * FROM teknologi ORDER BY kategori, urutan')->fetchAll();
$kategoriAda = q('SELECT DISTINCT kategori FROM proyek ORDER BY kategori')->fetchAll(PDO::FETCH_COLUMN);
$title = $id ? 'Ubah proyek' : 'Proyek baru';
$navAktif = 'kelola-proyek';
$actions = '<a class="btn" href="' . url('kelola-proyek') . '">' . icon('arrow-left', 18) . ' Daftar proyek</a>';
require ROOT . '/includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="form-grid">
    <?= csrf_field() ?>
    <section class="card span-2">
        <label>Judul proyek<input name="judul" value="<?= e($p['judul']) ?>" required maxlength="150"></label>
        <div class="row-3">
            <label>Kategori<input name="kategori" value="<?= e($p['kategori']) ?>" list="kat" required maxlength="50"><datalist id="kat"><?php foreach ($kategoriAda as $k): ?><option value="<?= e($k) ?>"><?php endforeach; ?></datalist></label>
            <label>Tahun<input type="number" name="tahun" value="<?= e($p['tahun']) ?>" min="1990" max="<?= date('Y') + 1 ?>"></label>
            <label>Durasi<input name="durasi" value="<?= e($p['durasi']) ?>" placeholder="3 bulan" maxlength="40"></label>
        </div>
        <div class="row-2">
            <label>Klien<input name="klien" value="<?= e($p['klien']) ?>" placeholder="Kosongkan jika proyek pribadi" maxlength="100"></label>
            <label>Peranmu<input name="peran" value="<?= e($p['peran']) ?>" placeholder="Lead developer" maxlength="100"></label>
        </div>
        <label>Ringkasan <small class="muted">(muncul di kartu proyek)</small><input name="ringkasan" value="<?= e($p['ringkasan']) ?>" maxlength="255"></label>
        <label>Cerita proyek <small class="muted">Format: ## Subjudul, - poin, **tebal**, *miring*, `kode`, [teks](https://link)</small><textarea name="isi" rows="14" class="mono"><?= e($p['isi']) ?></textarea></label>
        <div class="row-2">
            <label>Link demo / live<input type="url" name="link_demo" value="<?= e($p['link_demo']) ?>" placeholder="https://"></label>
            <label>Link repo / dokumen<input type="url" name="link_repo" value="<?= e($p['link_repo']) ?>" placeholder="https://"></label>
        </div>
        <div class="checks">
            <label class="check"><input type="checkbox" name="tampil" <?= $p['tampil'] ? 'checked' : '' ?>> Tampilkan di situs</label>
            <label class="check"><input type="checkbox" name="unggulan" <?= $p['unggulan'] ? 'checked' : '' ?>> Proyek unggulan (tampil lebih besar)</label>
        </div>
    </section>
    <section class="card">
        <div class="card-h"><h2>Cover</h2></div>
        <img src="<?= e(gambar($p['cover'])) ?>" alt="" class="preview-cover" id="prevCover">
        <label class="file"><?= icon('image', 18) ?> Pilih cover (otomatis dikompres ke WebP)<input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-preview="prevCover"></label>
    </section>
    <section class="card">
        <div class="card-h"><h2>Teknologi dipakai</h2><a href="<?= url('teknologi') ?>">Kelola</a></div>
        <div class="tek-pick">
            <?php foreach ($tekSemua as $t): ?>
                <label><input type="checkbox" name="tek[]" value="<?= $t['id'] ?>" <?= in_array($t['id'], $tekDipilih) ? 'checked' : '' ?>><span><?= logo($t['logo'], 18, $t['nama']) ?> <?= e($t['nama']) ?></span></label>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="card span-2">
        <div class="card-h"><h2>Galeri</h2><span class="muted"><?= count($galeri) ?> gambar</span></div>
        <div class="gal-edit">
            <?php foreach ($galeri as $g): ?>
                <figure><img src="<?= e(gambar($g['file'])) ?>" alt=""><input name="ket[<?= $g['id'] ?>]" value="<?= e($g['keterangan']) ?>" placeholder="Keterangan"><label class="check"><input type="checkbox" name="hapus_gambar[]" value="<?= $g['id'] ?>"> Hapus</label></figure>
            <?php endforeach; ?>
        </div>
        <label class="file"><?= icon('upload', 18) ?> Tambah gambar galeri (bisa pilih banyak)<input type="file" name="galeri[]" accept="image/jpeg,image/png,image/webp" multiple></label>
    </section>
    <div class="span-2 sticky-save"><button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan proyek</button></div>
</form>
<?php require ROOT . '/includes/admin_footer.php';
