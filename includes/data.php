<?php
// Pengambil data untuk halaman publik (dipakai beranda.php & proyek.php)

/** Map proyek_id => daftar teknologi, sekali query untuk semua proyek. */
function teknologi_per_proyek(): array
{
    $m = [];
    foreach (q('SELECT pt.proyek_id, t.nama, t.logo FROM proyek_teknologi pt JOIN teknologi t ON t.id = pt.teknologi_id ORDER BY t.urutan') as $r) $m[$r['proyek_id']][] = $r;
    return $m;
}

function data_beranda(): array
{
    catat_kunjungan();
    $proyek = q('SELECT * FROM proyek WHERE tampil = 1 ORDER BY urutan, id')->fetchAll();
    $tek = teknologi_per_proyek();
    foreach ($proyek as &$p) $p['tek'] = $tek[$p['id']] ?? [];
    unset($p);
    $teknologi = q('SELECT * FROM teknologi ORDER BY urutan, id')->fetchAll();
    $pengalaman = q('SELECT * FROM pengalaman ORDER BY COALESCE(selesai, "9999-12-31") DESC, mulai DESC')->fetchAll();
    $mulai = (int) setting('tahun_mulai', date('Y'));
    return [
        'proyek' => $proyek,
        'kategori' => array_values(array_unique(array_column($proyek, 'kategori'))),
        'klien' => array_values(array_unique(array_filter(array_column($proyek, 'klien')))),
        'teknologi' => $teknologi,
        'tek_kategori' => array_reduce($teknologi, function ($c, $t) { $c[$t['kategori']][] = $t; return $c; }, []),
        'unggulan_tek' => array_values(array_filter($teknologi, fn($t) => $t['unggulan'])),
        'kerja' => array_values(array_filter($pengalaman, fn($x) => $x['jenis'] === 'kerja')),
        'pendidikan' => array_values(array_filter($pengalaman, fn($x) => $x['jenis'] === 'pendidikan')),
        'layanan' => q('SELECT * FROM layanan ORDER BY urutan, id')->fetchAll(),
        'testimoni' => q('SELECT * FROM testimoni WHERE tampil = 1 ORDER BY urutan, id')->fetchAll(),
        'angka' => [
            'tahun' => max(1, (int) date('Y') - $mulai),
            'proyek' => count($proyek),
            'klien' => count(array_unique(array_filter(array_column($proyek, 'klien')))),
            'tek' => count($teknologi),
        ],
    ];
}

function data_proyek(string $slug): ?array
{
    $p = q('SELECT * FROM proyek WHERE slug = ? AND tampil = 1', [$slug])->fetch();
    if (!$p) return null;
    // Hitung dilihat sekali per sesi per proyek agar refresh tidak menggelembungkan angka
    $_SESSION['lihat'] ??= [];
    if (!in_array($p['id'], $_SESSION['lihat'], true)) {
        $_SESSION['lihat'][] = $p['id'];
        q('UPDATE proyek SET dilihat = dilihat + 1 WHERE id = ?', [$p['id']]);
    }
    catat_kunjungan();
    $p['tek'] = q('SELECT t.* FROM proyek_teknologi pt JOIN teknologi t ON t.id = pt.teknologi_id WHERE pt.proyek_id = ? ORDER BY t.urutan', [$p['id']])->fetchAll();
    $p['galeri'] = q('SELECT * FROM proyek_gambar WHERE proyek_id = ? ORDER BY urutan, id', [$p['id']])->fetchAll();
    $semua = q('SELECT id, judul, slug, cover, kategori FROM proyek WHERE tampil = 1 ORDER BY urutan, id')->fetchAll();
    $i = array_search($p['id'], array_column($semua, 'id'));
    $n = count($semua);
    $p['sebelum'] = $n > 1 ? $semua[($i - 1 + $n) % $n] : null;
    $p['berikut'] = $n > 1 ? $semua[($i + 1) % $n] : null;
    return $p;
}

/** Atribut <head> bersama: judul, meta deskripsi, Open Graph. */
function meta_head(string $judul, string $desk = '', string $img = ''): string
{
    $desk = $desk ?: setting('meta_deskripsi');
    $h = '<title>' . e($judul) . '</title><meta name="description" content="' . e($desk) . '">';
    $h .= '<meta property="og:title" content="' . e($judul) . '"><meta property="og:description" content="' . e($desk) . '"><meta property="og:type" content="website">';
    if ($img && !str_ends_with($img, '.svg')) $h .= '<meta property="og:image" content="' . e($img) . '">';
    return $h;
}

function css_aksen(): string
{
    [$nama, $a, $b, $on] = aksen() + [null, '#000', '#000', ''];
    return '<style>:root{--acc:' . $a . ';--acc2:' . $b . ($on ? ';--on-acc:' . $on : '') . '}</style>';
}
