<?php
require ROOT . '/includes/data.php';
$D = data_beranda();
$s = setting();
$kontak = $_SESSION['kontak'] ?? null;
unset($_SESSION['kontak']);
$foto = gambar($s['foto'] ?? '', 'assets/img/potret.svg');
$wa = nomor_wa($s['whatsapp'] ?? '');
$edisi = str_pad((string) (((int) date('y') * 12 + (int) date('n')) % 1000), 3, '0', STR_PAD_LEFT);

/** Judul hero: kata ke-2 dan terakhir dibuat miring serif, kata ke-4 diberi blok stabilo. */
function judul_zine(string $t): string
{
    $kata = preg_split('/\s+/', trim($t));
    $n = count($kata);
    foreach ($kata as $i => &$k) {
        $k = e($k);
        if ($i === 1 || $i === $n - 1) $k = '<em>' . $k . '</em>';
        elseif ($i === 3) $k = '<mark>' . $k . '</mark>';
    }
    return implode(' ', $kata);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= meta_head($s['nama'] . ' · ' . $s['peran']) ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?<?= FONT_SITE ?>&display=swap">
    <link rel="stylesheet" href="assets/css/site.css">
    <?= css_aksen() ?>
    <script>document.documentElement.classList.add('js')</script>
</head>
<body>
<header class="mast">
    <div class="mast-top mono"><span><?= e($s['nama']) ?></span><span class="hide-sm">Zine portofolio</span><span>Edisi no. <?= $edisi ?></span><span class="hide-sm"><?= e($s['kota']) ?></span></div>
    <nav class="tabs" id="tabs">
        <a href="#karya">Karya</a><a href="#perkakas">Perkakas</a><a href="#jasa">Jasa</a><a href="#jejak">Jejak</a><a href="#surat" class="tab-hot">Kirim surat</a>
    </nav>
    <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('menu-open')"><?= icon('menu', 26) ?></button>
</header>

<main>
    <!-- SAMPUL -->
    <section class="cover wrap" id="top">
        <div class="cover-text">
            <p class="mono kicker">&#8213; <?= e($s['peran']) ?></p>
            <h1 class="misreg"><?= judul_zine($s['judul_hero']) ?></h1>
            <p class="lead"><?= e($s['tagline']) ?></p>
            <div class="cta">
                <a class="btn btn-ink" href="#karya"><?= icon('arrow-down', 18) ?> Buka halaman karya</a>
                <?php if (!empty($s['cv'])): ?><a class="btn btn-paper" href="<?= e(gambar($s['cv'])) ?>" download><?= icon('file-text', 18) ?> Unduh CV</a><?php endif; ?>
            </div>
        </div>
        <div class="cover-art" id="coverArt">
            <figure class="polaroid layer" data-depth="10">
                <span class="tape t1"></span><span class="tape t2"></span>
                <img src="<?= e($foto) ?>" alt="<?= e($s['nama']) ?>">
                <figcaption class="hand"><?= e(explode(' ', $s['nama'])[0]) ?>, <?= e($s['kota']) ?></figcaption>
            </figure>
            <?php if (($s['tersedia'] ?? '1') === '1'): ?>
            <div class="stamp layer" data-depth="40" aria-label="<?= e($s['status_teks']) ?>">
                <svg viewBox="0 0 200 200" class="spin"><defs><path id="lingkar" d="M100 100m-74 0a74 74 0 1 1 148 0a74 74 0 1 1-148 0"/></defs><text><textPath href="#lingkar"><?= e(mb_strtoupper($s['status_teks'])) ?> &#183; TERIMA PESANAN &#183;</textPath></text></svg>
                <span><?= icon('pen-tool', 34) ?></span>
            </div>
            <?php endif; ?>
            <div class="sticker s-pink layer" data-depth="28"><?= $D['angka']['tahun'] ?> th<br><small>menggambar</small></div>
            <div class="sticker s-blue layer" data-depth="18"><?= $D['angka']['proyek'] ?>+<br><small>karya</small></div>
            <svg class="doodle layer" data-depth="6" viewBox="0 0 220 120" aria-hidden="true"><path class="draw" d="M10 100C40 20 120 10 150 60s40 30 60-20" fill="none" stroke="var(--ink)" stroke-width="4" stroke-linecap="round"/><path class="draw" d="M190 22l20 18-26 8" fill="none" stroke="var(--ink)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="hand ini-aku layer" data-depth="22">ini aku!</span>
        </div>
    </section>

    <!-- SELOTIP BERJALAN -->
    <div class="tape-band" aria-hidden="true">
        <div class="tape-track mono">
            <?php $isi = array_merge(array_column($D['layanan'], 'judul'), $D['klien']); for ($r = 0; $r < 2; $r++): foreach ($isi as $k): ?><span><?= e($k) ?></span><svg viewBox="0 0 20 20" width="18" height="18"><path d="M10 0l2.4 7.6L20 10l-7.6 2.4L10 20l-2.4-7.6L0 10l7.6-2.4z"/></svg><?php endforeach; endfor; ?>
        </div>
    </div>

    <!-- TENTANG -->
    <section class="wrap page about" id="tentang">
        <div class="page-no mono">hal. 02</div>
        <div class="about-grid">
            <div class="rv turn">
                <h2 class="sec-title">Halo, <em>salam kenal</em></h2>
                <div class="dropcap"><?= markdown_mini($s['bio'] ?? '') ?></div>
            </div>
            <ul class="counts">
                <?php foreach ([['tahun', 'tahun berkarya'], ['proyek', 'karya terbit'], ['klien', 'klien & kolaborator'], ['tek', 'alat andalan']] as $i => [$k, $l]): ?>
                    <li class="rv pop" style="--r:<?= [-4, 3, -2, 5][$i] ?>deg"><b data-count="<?= $D['angka'][$k] ?>">0</b><span class="hand"><?= $l ?></span><svg class="circle" viewBox="0 0 120 80" preserveAspectRatio="none" aria-hidden="true"><path class="draw" d="M60 6C20 4 4 24 6 42s28 34 64 32 46-20 44-38S86 4 52 8" fill="none" stroke="var(--acc)" stroke-width="4" stroke-linecap="round"/></svg></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- KARYA: galeri horizontal saat di-scroll -->
    <section class="works" id="karya">
        <div class="works-sticky">
            <div class="wrap works-head">
                <h2 class="sec-title rv">Karya <em>terpilih</em></h2>
                <div class="filters" role="tablist">
                    <button class="on" data-f="*">Semua</button>
                    <?php foreach ($D['kategori'] as $k): ?><button data-f="<?= e($k) ?>"><?= e($k) ?></button><?php endforeach; ?>
                </div>
            </div>
            <div class="works-track" id="track">
                <?php foreach ($D['proyek'] as $i => $p): ?>
                    <a class="poster <?= $p['unggulan'] ? 'big' : '' ?>" href="<?= url('proyek', ['slug' => $p['slug']]) ?>" data-k="<?= e($p['kategori']) ?>" style="--rot:<?= [-3, 2, -1.5, 3, -2.5, 1.5][$i % 6] ?>deg">
                        <span class="tape"></span>
                        <span class="pin-no mono">no. <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <img src="<?= e(gambar($p['cover'])) ?>" alt="<?= e($p['judul']) ?>" loading="lazy">
                        <span class="cap">
                            <span class="mono"><?= e($p['kategori']) ?> &middot; <?= $p['tahun'] ?></span>
                            <b><?= e($p['judul']) ?></b>
                            <span class="sum"><?= e($p['ringkasan']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
                <div class="poster end"><p class="hand">Mau karya<br>berikutnya<br>punyamu?</p><span class="btn btn-ink" data-go="#surat"><?= icon('send', 18) ?> Kirim surat</span></div>
            </div>
        </div>
    </section>

    <!-- PERKAKAS -->
    <section class="wrap page" id="perkakas">
        <div class="page-no mono">hal. 04</div>
        <h2 class="sec-title rv turn">Isi <em>kotak pensil</em></h2>
        <div class="tool-groups">
            <?php foreach ($D['tek_kategori'] as $kat => $list): ?>
                <div class="tool-group rv turn">
                    <h3 class="mono">&#9632; <?= e($kat) ?></h3>
                    <div class="stamps">
                        <?php foreach ($list as $j => $t): ?>
                            <div class="tool-stamp" style="--d:<?= 14 + $j * 3 ?>s">
                                <svg viewBox="0 0 120 120" aria-hidden="true"><defs><path id="tp<?= $t['id'] ?>" d="M60 60m-46 0a46 46 0 1 1 92 0a46 46 0 1 1-92 0"/></defs><circle cx="60" cy="60" r="57" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="3 5"/><text><textPath href="#tp<?= $t['id'] ?>"><?= e(mb_strtoupper($t['nama'])) ?> &#183; <?= (int) $t['level'] ?>% &#183; <?= e(mb_strtoupper($t['nama'])) ?> &#183;</textPath></text></svg>
                                <span class="tool-logo"><?= logo($t['logo'], 34, $t['nama']) ?></span>
                                <b><?= e($t['nama']) ?></b>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- JASA -->
    <?php if ($D['layanan']): ?>
    <section class="wrap page" id="jasa">
        <div class="page-no mono">hal. 05</div>
        <h2 class="sec-title rv turn">Bisa <em>dipesan</em></h2>
        <div class="tickets">
            <?php foreach ($D['layanan'] as $i => $l): ?>
                <article class="ticket rv pop c<?= $i % 3 ?>" style="--r:<?= [-1.5, 1, -0.5, 1.5, -1][$i % 5] ?>deg">
                    <div class="tk-main">
                        <span class="tk-ic"><?= icon($l['ikon'], 28) ?></span>
                        <h3><?= e($l['judul']) ?></h3>
                        <p><?= e($l['deskripsi']) ?></p>
                    </div>
                    <div class="tk-stub mono"><?php if ($l['harga_mulai']): ?><small>mulai</small><b><?= rupiah((int) $l['harga_mulai']) ?></b><?php else: ?><b>Tanya dulu</b><?php endif; ?><small>tiket no. <?= str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) ?></small></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- JEJAK: daftar isi -->
    <section class="wrap page" id="jejak">
        <div class="page-no mono">hal. 06</div>
        <h2 class="sec-title rv turn">Daftar <em>isi hidupku</em></h2>
        <?php foreach (['Bab kerja' => $D['kerja'], 'Bab sekolah' => $D['pendidikan']] as $bab => $list): if (!$list) continue; ?>
            <h3 class="mono toc-h rv"><?= $bab ?></h3>
            <ol class="toc">
                <?php foreach ($list as $x): ?>
                    <li class="rv">
                        <div class="toc-line"><b><?= e($x['posisi']) ?></b><i></i><span class="mono"><?= bulan_tahun($x['mulai']) ?> &ndash; <?= bulan_tahun($x['selesai']) ?></span></div>
                        <p><em><?= e($x['instansi']) ?><?= $x['kota'] ? ', ' . e($x['kota']) : '' ?>.</em> <?= e($x['deskripsi']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endforeach; ?>
    </section>

    <!-- TESTIMONI: tumpukan catatan tempel -->
    <?php if ($D['testimoni']): ?>
    <section class="wrap page notes-wrap" id="kata">
        <div class="page-no mono">hal. 07</div>
        <div class="notes-grid">
            <div>
                <h2 class="sec-title rv turn">Surat <em>pembaca</em></h2>
                <p class="lead rv">Kata mereka yang pernah kerja bareng. Klik tumpukan untuk membalik catatan berikutnya.</p>
                <div class="notes-nav"><button class="btn btn-paper" data-note="prev"><?= icon('arrow-left', 18) ?></button><button class="btn btn-ink" data-note="next">Catatan berikutnya <?= icon('arrow-right', 18) ?></button></div>
            </div>
            <div class="note-stack" id="notes">
                <?php foreach ($D['testimoni'] as $i => $t): ?>
                    <figure class="note n<?= $i % 3 ?>">
                        <span class="tape"></span>
                        <blockquote><?= e($t['isi']) ?></blockquote>
                        <figcaption><img src="<?= e(gambar($t['foto'], 'assets/img/avatar.svg')) ?>" alt="" loading="lazy"><span><b><?= e($t['nama']) ?></b><small><?= e($t['jabatan']) ?></small></span><em class="mono"><?= str_repeat('&#9733;', (int) $t['rating']) ?></em></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- KONTAK: surat -->
    <section class="wrap page" id="surat">
        <div class="page-no mono">hal. 08</div>
        <div class="letter rv turn">
            <div class="letter-head">
                <div>
                    <h2 class="sec-title">Kirim <em>surat</em></h2>
                    <p>Ceritakan proyekmu: apa yang dijual, siapa pembelinya, dan kapan harus jadi. Biasanya aku balas dalam 1-2 hari kerja.</p>
                </div>
                <div class="post-stamp" aria-hidden="true"><img src="<?= e($foto) ?>" alt=""><span class="mono"><?= e(mb_strtoupper(explode(',', $s['kota'])[0])) ?></span></div>
            </div>
            <form class="lined" method="post" action="<?= url('kirim') ?>" id="cform" novalidate>
                <?= csrf_field() ?>
                <div class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <p class="line">Untuk <b><?= e($s['nama']) ?></b>, dari <input name="nama" required maxlength="100" placeholder="namamu" autocomplete="name" aria-label="Nama">,</p>
                <p class="line">balas ke <input type="email" name="email" required maxlength="150" placeholder="email@kamu.com" autocomplete="email" aria-label="Email">.</p>
                <p class="line">Perihal: <input name="subjek" maxlength="150" placeholder="desain kemasan sambal" aria-label="Subjek"> dengan anggaran
                    <select name="anggaran" aria-label="Anggaran"><option value="">pilih</option><?php foreach (ANGGARAN as $a): ?><option><?= e($a) ?></option><?php endforeach; ?></select>.</p>
                <textarea name="isi" rows="6" required minlength="10" maxlength="3000" placeholder="Tulis ceritamu di sini..." aria-label="Pesan"></textarea>
                <div class="send-row">
                    <button class="btn btn-ink" type="submit"><?= icon('send', 18) ?> Masukkan ke kotak pos</button>
                    <p class="form-msg <?= $kontak ? ($kontak[0] ? 'ok' : 'err') : '' ?>" id="formMsg" role="status"><?= $kontak ? e($kontak[1]) : '' ?></p>
                </div>
            </form>
            <ul class="contacts mono">
                <?php if (!empty($s['email'])): ?><li><?= icon('mail', 18) ?><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></li><?php endif; ?>
                <?php if ($wa): ?><li><?= icon('message-circle', 18) ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">WA <?= e($s['whatsapp']) ?></a></li><?php endif; ?>
                <?php foreach (sosial() as $so): ?><li><?= $so['svg'] ?><a href="<?= e($so['url']) ?>" target="_blank" rel="noopener"><?= e($so['label']) ?></a></li><?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>

<footer class="foot">
    <p class="foot-big misreg">Sampai jumpa di <em>edisi berikutnya</em></p>
    <div class="wrap foot-row mono"><span>&copy; <?= date('Y') ?> <?= e($s['nama']) ?> &middot; dicetak dengan PHP &amp; MySQL</span><a href="<?= url('login') ?>" rel="nofollow"><?= icon('lock', 14) ?> CMS</a></div>
</footer>
<script src="assets/js/site.js"></script>
</body>
</html>
