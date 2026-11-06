<?php
$title = 'Profil & Situs';
$sub = 'Semua teks di halaman depan diatur dari sini.';
$teks = [
    'nama' => ['Nama tampil', 100], 'peran' => ['Peran / profesi', 100], 'judul_hero' => ['Kalimat utama (hero)', 160],
    'tagline' => ['Tagline singkat', 200], 'kota' => ['Kota', 60], 'email' => ['Email', 150], 'whatsapp' => ['No. WhatsApp', 20],
    'status_teks' => ['Teks status', 80], 'tahun_mulai' => ['Mulai berkarya (tahun)', 4], 'meta_deskripsi' => ['Deskripsi SEO', 200],
    'sosial_github' => ['GitHub', 255], 'sosial_linkedin' => ['LinkedIn', 255], 'sosial_instagram' => ['Instagram', 255],
    'sosial_dribbble' => ['Dribbble', 255], 'sosial_behance' => ['Behance', 255], 'sosial_youtube' => ['YouTube', 255],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [];
        foreach ($teks as $k => [$label, $max]) {
            $v = trim((string) ($_POST[$k] ?? ''));
            if (mb_strlen($v) > $max) throw new RuntimeException("$label maksimal $max karakter.");
            if (str_starts_with($k, 'sosial_') && $v !== '' && !link_aman($v)) throw new RuntimeException("Link $label harus diawali https://");
            $data[$k] = $v;
        }
        if ($data['nama'] === '') throw new RuntimeException('Nama tidak boleh kosong.');
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Format email tidak valid.');
        if ($data['tahun_mulai'] !== '' && !preg_match('/^(19|20)\d\d$/', $data['tahun_mulai'])) throw new RuntimeException('Tahun mulai harus 4 digit.');
        $data['bio'] = trim((string) ($_POST['bio'] ?? ''));
        $data['tersedia'] = isset($_POST['tersedia']) ? '1' : '0';
        $data['aksen'] = isset(PALET[$_POST['aksen'] ?? '']) ? $_POST['aksen'] : array_key_first(PALET);
        if ($f = simpan_gambar($_FILES['foto'] ?? [], 900)) { hapus_upload(setting('foto')); $data['foto'] = $f; }
        if (isset($_POST['hapus_foto'])) { hapus_upload(setting('foto')); $data['foto'] = ''; }
        if ($f = simpan_pdf($_FILES['cv'] ?? [])) { hapus_upload(setting('cv')); $data['cv'] = $f; }
        if (isset($_POST['hapus_cv'])) { hapus_upload(setting('cv')); $data['cv'] = ''; }
        simpan_setting($data);
        flash('Profil tersimpan.');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('profil');
}
$s = setting();
$fld = fn(string $k, string $type = 'text') => '<label>' . e($teks[$k][0]) . '<input type="' . $type . '" name="' . $k . '" value="' . e($s[$k] ?? '') . '" maxlength="' . $teks[$k][1] . '"></label>';
require ROOT . '/includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="form-grid">
    <?= csrf_field() ?>
    <section class="card span-2">
        <div class="card-h"><h2>Identitas</h2></div>
        <div class="row-2">
            <?= $fld('nama') ?><?= $fld('peran') ?>
        </div>
        <?= $fld('judul_hero') ?>
        <?= $fld('tagline') ?>
        <label>Bio / tentang saya <small class="muted">(boleh beberapa paragraf, mendukung **tebal**)</small><textarea name="bio" rows="7"><?= e($s['bio'] ?? '') ?></textarea></label>
        <div class="row-3"><?= $fld('kota') ?><?= $fld('tahun_mulai') ?><?= $fld('status_teks') ?></div>
        <label class="check"><input type="checkbox" name="tersedia" <?= ($s['tersedia'] ?? '1') === '1' ? 'checked' : '' ?>> Tampilkan lencana "tersedia untuk proyek"</label>
    </section>
    <section class="card">
        <div class="card-h"><h2>Foto profil</h2></div>
        <img src="<?= e(gambar($s['foto'] ?? '', 'assets/img/potret.svg')) ?>" alt="" class="preview-av">
        <label class="file"><?= icon('upload', 18) ?> Ganti foto (JPG/PNG/WebP, maks 5 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
        <?php if (!empty($s['foto'])): ?><label class="check"><input type="checkbox" name="hapus_foto"> Kembali ke ilustrasi bawaan</label><?php endif; ?>
        <div class="card-h mt"><h2>CV (PDF)</h2></div>
        <?php if (!empty($s['cv'])): ?><p><a href="<?= e(gambar($s['cv'])) ?>" target="_blank"><?= icon('file-text', 16) ?> Lihat CV saat ini</a></p><label class="check"><input type="checkbox" name="hapus_cv"> Hapus CV</label><?php else: ?><p class="muted">Belum ada CV. Tombol unduh CV disembunyikan.</p><?php endif; ?>
        <label class="file"><?= icon('upload', 18) ?> Upload CV<input type="file" name="cv" accept="application/pdf"></label>
    </section>
    <section class="card">
        <div class="card-h"><h2>Kontak</h2></div>
        <?= $fld('email', 'email') ?><?= $fld('whatsapp') ?>
        <div class="card-h mt"><h2>Warna aksen</h2></div>
        <div class="swatches">
            <?php foreach (PALET as $k => $p): ?>
                <label class="swatch"><input type="radio" name="aksen" value="<?= $k ?>" <?= ($s['aksen'] ?? array_key_first(PALET)) === $k ? 'checked' : '' ?>><span style="background:<?= $p[1] ?>"></span><?= e($p[0]) ?></label>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="card">
        <div class="card-h"><h2>Media sosial</h2></div>
        <?php foreach (['github', 'linkedin', 'instagram', 'dribbble', 'behance', 'youtube'] as $k) echo $fld('sosial_' . $k, 'url'); ?>
    </section>
    <section class="card">
        <div class="card-h"><h2>SEO</h2></div>
        <?= $fld('meta_deskripsi') ?>
        <p class="muted">Muncul di hasil pencarian Google dan pratinjau link WhatsApp.</p>
    </section>
    <div class="span-2 sticky-save"><button class="btn btn-main" type="submit"><?= icon('check', 18) ?> Simpan profil</button></div>
</form>
<?php require ROOT . '/includes/admin_footer.php';
