# Zine — Template Portofolio Ilustrator + CMS (PHP Native + MySQL)

Template portofolio bergaya zine cetak risograf untuk ilustrator, desainer grafis, dan seniman. Halamannya seperti majalah kecil: kertas bertekstur, tinta pink-biru yang sengaja "salah cetak", foto polaroid berselotip, stiker yang bergerak mengikuti kursor, karya yang digeser menyamping saat di-scroll, cap alat gambar yang berputar, tiket jasa, daftar isi riwayat, tumpukan catatan testimoni, dan formulir kontak berbentuk surat.

## Isi folder

| Path | Fungsi |
|---|---|
| `index.php` | Router: halaman publik (`beranda`, `proyek`, `kirim`) + halaman CMS |
| `config/database.php` | Koneksi MySQL |
| `includes/bootstrap.php` | PDO, CSRF, login & sesi, upload gambar → WebP, ikon & logo |
| `includes/data.php` | Pengambil data halaman publik |
| `includes/tema.php` | Pilihan warna aksen & font tema Zine |
| `includes/logos.php`, `includes/icons.php` | Logo aplikasi satu warna & ikon garis |
| `pages/beranda.php`, `pages/proyek.php` | Halaman depan & lembar detail karya |
| `pages/kirim.php` | Penerima formulir "kirim surat" |
| `pages/admin/*` | Halaman CMS |
| `assets/css`, `assets/js` | Tampilan & efek (tanpa framework) |
| `assets/img` | Tekstur kertas, potret & avatar ilustrasi, poster karya contoh |
| `assets/uploads` | File yang diunggah dari CMS |
| `database/porto_zine.sql` | Skema + data contoh |

## Instalasi singkat

1. Salin folder `porto-zine` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Jalankan Apache & MySQL, impor `database/porto_zine.sql` lewat phpMyAdmin.
3. Sesuaikan `config/database.php` bila perlu.
4. Situs: `http://localhost/porto-zine/` — CMS: `http://localhost/porto-zine/index.php?page=login`
   Login awal **admin / admin123**, segera ganti di menu **Akun**.

## Yang bisa diatur dari CMS

- **Profil & Situs**: nama, profesi, kalimat sampul (kata ke-2 & terakhir otomatis jadi huruf miring, kata ke-4 diberi stabilo), tagline, bio, kota, status cap berputar, foto, CV PDF, kontak, media sosial, SEO, 4 warna aksen.
- **Proyek / karya**: judul, kategori (jadi filter), ringkasan, cerita, klien, peran, tahun, durasi, link, cover, galeri, alat yang dipakai, unggulan (poster lebih lebar), sembunyikan, urutan.
- **Teknologi / alat**: nama, logo (atau monogram 2 huruf untuk aplikasi tanpa logo), kategori kotak pensil, tingkat penguasaan.
- **Pengalaman**, **Layanan** (tiket dengan harga mulai), **Testimoni** (catatan tempel dengan foto & rating).
- **Pesan masuk**, ekspor CSV, dan **Dashboard** pengunjung.

## Catatan keamanan

- PDO prepared statement, escape output, CSRF di semua form.
- Password hash, login dikunci 15 menit setelah 5 kali gagal, sesi lain otomatis keluar saat password diganti.
- Gambar dicek MIME aslinya lalu digambar ulang menjadi WebP; CV hanya PDF; folder upload diberi `.htaccess` anti-eksekusi.
- Hanya link `http(s)://` yang diterima.
- Formulir surat: honeypot, maksimal 3 pesan per 10 menit per IP, tolak pesan dobel.
- CSV aman dari formula injection.

## Catatan tampilan

- Galeri karya menyamping hanya aktif di layar lebar (> 980px). Di HP, karya tampil sebagai tumpukan poster vertikal.
- Semua animasi otomatis mati bila pengunjung mengaktifkan "kurangi gerakan" di perangkatnya.
