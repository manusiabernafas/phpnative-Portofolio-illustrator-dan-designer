SET NAMES utf8mb4;
SET time_zone = '+07:00';
DROP DATABASE IF EXISTS `porto_zine`;
CREATE DATABASE `porto_zine` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `porto_zine`;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE login_gagal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (ip, waktu)
) ENGINE=InnoDB;

CREATE TABLE pengaturan (
  kunci VARCHAR(50) PRIMARY KEY,
  nilai TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE teknologi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(60) NOT NULL,
  logo VARCHAR(40) NOT NULL DEFAULT '',
  kategori VARCHAR(40) NOT NULL DEFAULT 'Tools',
  level TINYINT UNSIGNED NOT NULL DEFAULT 70,
  unggulan TINYINT(1) NOT NULL DEFAULT 0,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE proyek (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  slug VARCHAR(90) NOT NULL UNIQUE,
  kategori VARCHAR(50) NOT NULL,
  ringkasan VARCHAR(255) NOT NULL DEFAULT '',
  isi TEXT NOT NULL,
  cover VARCHAR(255) NOT NULL DEFAULT '',
  klien VARCHAR(100) NOT NULL DEFAULT '',
  peran VARCHAR(100) NOT NULL DEFAULT '',
  tahun SMALLINT UNSIGNED NOT NULL,
  durasi VARCHAR(40) NOT NULL DEFAULT '',
  link_demo VARCHAR(255) NOT NULL DEFAULT '',
  link_repo VARCHAR(255) NOT NULL DEFAULT '',
  unggulan TINYINT(1) NOT NULL DEFAULT 0,
  tampil TINYINT(1) NOT NULL DEFAULT 1,
  urutan INT NOT NULL DEFAULT 0,
  dilihat INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (tampil, urutan)
) ENGINE=InnoDB;

CREATE TABLE proyek_gambar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proyek_id INT NOT NULL,
  file VARCHAR(255) NOT NULL,
  keterangan VARCHAR(150) NOT NULL DEFAULT '',
  urutan INT NOT NULL DEFAULT 0,
  FOREIGN KEY (proyek_id) REFERENCES proyek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE proyek_teknologi (
  proyek_id INT NOT NULL,
  teknologi_id INT NOT NULL,
  PRIMARY KEY (proyek_id, teknologi_id),
  FOREIGN KEY (proyek_id) REFERENCES proyek(id) ON DELETE CASCADE,
  FOREIGN KEY (teknologi_id) REFERENCES teknologi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pengalaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jenis ENUM('kerja','pendidikan') NOT NULL DEFAULT 'kerja',
  posisi VARCHAR(100) NOT NULL,
  instansi VARCHAR(120) NOT NULL,
  kota VARCHAR(60) NOT NULL DEFAULT '',
  mulai DATE NOT NULL,
  selesai DATE NULL,
  deskripsi TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE layanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(100) NOT NULL,
  deskripsi VARCHAR(255) NOT NULL,
  ikon VARCHAR(40) NOT NULL DEFAULT 'sparkles',
  harga_mulai INT UNSIGNED NULL,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE testimoni (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  jabatan VARCHAR(120) NOT NULL DEFAULT '',
  isi TEXT NOT NULL,
  foto VARCHAR(255) NOT NULL DEFAULT '',
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  tampil TINYINT(1) NOT NULL DEFAULT 1,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE pesan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subjek VARCHAR(150) NOT NULL DEFAULT '',
  isi TEXT NOT NULL,
  anggaran VARCHAR(40) NOT NULL DEFAULT '',
  ip VARCHAR(45) NOT NULL DEFAULT '',
  dibaca TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (ip, created_at)
) ENGINE=InnoDB;

CREATE TABLE kunjungan (
  tanggal DATE PRIMARY KEY,
  jumlah INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ===== Data contoh =====
INSERT INTO users (nama, username, password) VALUES
('Laras Pramesti', 'admin', '$2y$10$hfPGjluwfNku5NZEkVGdjuwvX1Vc1qLQ4EygXt7IcuAsA1lMvQQ4y');

INSERT INTO pengaturan (kunci, nilai) VALUES
('nama', 'Laras Pramesti'),
('peran', 'Ilustrator & Desainer Grafis'),
('judul_hero', 'Gambar yang bikin orang berhenti scroll.'),
('tagline', 'Ilustrator dan desainer grafis dari Yogyakarta. Spesialis branding UMKM, kemasan, buku anak, dan poster acara dengan rasa cetakan risograf.'),
('bio', 'Aku **Laras**, tukang gambar yang kebetulan juga suka menata huruf. Dulu menggambar di pinggir buku tulis, sekarang di tablet, kertas, sampai tembok kafe.

Kebanyakan klienku usaha kecil yang ingin terlihat beda tanpa harus mahal: warung kopi, produsen jamu, penerbit buku anak, dan kolektif musik kampus.

Aku percaya desain yang bagus itu jujur dan sedikit nakal. Warnanya berani, garisnya kelihatan tangan manusia, dan selalu ada detail kecil yang baru ketahuan setelah dilihat kedua kali.'),
('kota', 'Yogyakarta'),
('email', 'halo@laraspramesti.id'),
('whatsapp', '085712345678'),
('status_teks', 'Slot Januari tinggal 2'),
('tersedia', '1'),
('tahun_mulai', '2017'),
('aksen', 'pink'),
('foto', ''),
('cv', ''),
('meta_deskripsi', 'Portofolio Laras Pramesti, ilustrator dan desainer grafis Yogyakarta: branding UMKM, kemasan, buku anak, poster, mural.'),
('sosial_github', ''),
('sosial_linkedin', ''),
('sosial_instagram', 'https://www.instagram.com/laras.gambar'),
('sosial_dribbble', 'https://dribbble.com/laraspramesti'),
('sosial_behance', 'https://www.behance.net/laraspramesti'),
('sosial_youtube', '');

INSERT INTO teknologi (nama, logo, kategori, level, unggulan, urutan) VALUES
('Procreate', '', 'Gambar', 95, 1, 1),
('Photoshop', '', 'Gambar', 85, 1, 2),
('Krita', 'krita', 'Gambar', 80, 1, 3),
('Illustrator', '', 'Vektor', 90, 1, 4),
('Inkscape', 'inkscape', 'Vektor', 75, 0, 5),
('Figma', 'figma', 'Vektor', 85, 1, 6),
('InDesign', '', 'Tata Letak', 80, 1, 7),
('Sketch', 'sketch', 'Tata Letak', 60, 0, 8),
('Blender', 'blender', '3D & Gerak', 55, 1, 9),
('Framer', 'framer', '3D & Gerak', 50, 0, 10),
('Notion', 'notion', 'Kerja Bareng', 80, 0, 11),
('Miro', 'miro', 'Kerja Bareng', 75, 1, 12),
('Behance', 'behance', 'Kerja Bareng', 70, 0, 13);

INSERT INTO proyek (judul, slug, kategori, ringkasan, isi, cover, klien, peran, tahun, durasi, link_demo, link_repo, unggulan, urutan, dilihat, created_at) VALUES
('Kopi Klotok Gunung', 'kopi-klotok-gunung', 'Branding', 'Identitas baru warung kopi lereng Merapi: logo cap, kemasan biji, dan menu papan kapur.', '## Brief
Warung kopi keluarga yang sudah 20 tahun berdiri ingin terlihat lebih segar tanpa kehilangan suasana dapur desa.

## Yang aku kerjakan
- Logo berbentuk **cap kayu** yang bisa distempel langsung di kantong kertas
- Ilustrasi gunung dan tungku untuk kemasan biji
- Menu papan kapur dan stiker gelas
- Panduan warna dua tinta agar cetak tetap murah

## Hasil
Penjualan kemasan biji untuk oleh-oleh naik dua kali lipat dalam tiga bulan. Desain cap dipakai ulang untuk merchandise kaus.', 'assets/img/contoh/kopi-klotok-gunung.svg', 'Kopi Klotok Gunung', 'Ilustrator & desainer identitas', 2026, '6 minggu', '', '', 1, 1, 538, NOW() - INTERVAL 184 DAY),
('Si Kancil Naik Kereta', 'kancil-naik-kereta', 'Buku Anak', '24 halaman ilustrasi buku cerita anak tentang kancil yang belajar antre.', '## Cerita
Kancil yang biasanya cerdik kali ini harus belajar sabar mengantre di stasiun.

## Proses
- Riset karakter: 40 sketsa sebelum Kancil versi akhir
- Palet tiga warna utama agar ramah cetak dan mudah dibaca anak
- Tekstur krayon digital yang dibuat dari pindaian krayon asli

Buku ini dicetak 5.000 eksemplar dan masuk daftar bacaan pojok baca di 30 sekolah dasar.', 'assets/img/contoh/kancil-naik-kereta.svg', 'Penerbit Pelangi Kecil', 'Ilustrator', 2025, '4 bulan', '', '', 0, 2, 402, NOW() - INTERVAL 161 DAY),
('Jamu Mbok Sri', 'jamu-mbok-sri', 'Kemasan', 'Botol jamu kekinian dengan ilustrasi rempah, tampil menonjol di rak minimarket.', '## Tantangan
Jamu tradisional sering kalah pamor di rak minimarket. Mbok Sri ingin anak muda mau mencoba.

## Solusi
- Tiga varian dengan warna tegas: kunyit asam, beras kencur, dan temulawak
- Ilustrasi rempah bergaya cetak saring dengan efek titik-titik
- Label belakang yang menjelaskan manfaat dalam bahasa santai
- Mockup 3D di Blender untuk presentasi ke distributor

Sejak kemasan baru, jamu Mbok Sri masuk 120 gerai minimarket di DIY dan Jawa Tengah.', 'assets/img/contoh/jamu-mbok-sri.svg', 'Jamu Mbok Sri', 'Desainer kemasan', 2025, '2 bulan', '', '', 1, 3, 617, NOW() - INTERVAL 138 DAY),
('Festival Musik Pinggir Kali', 'musik-pinggir-kali', 'Poster', 'Poster, tiket, dan visual media sosial untuk festival musik di bantaran sungai.', '## Konsep
Bunga liar yang tumbuh di bantaran kali jadi simbol musik yang tumbuh dari pinggiran.

## Keluaran
- Seri 3 poster cetak risograf dua warna
- Tiket gelang dan stiker
- 12 template feed dan story yang bisa diedit panitia sendiri di Figma

Poster edisi terbatas habis terjual sebagai merchandise sebelum hari ketiga.', 'assets/img/contoh/musik-pinggir-kali.svg', 'Kolektif Pinggir Kali', 'Desainer visual acara', 2024, '3 minggu', '', '', 0, 4, 355, NOW() - INTERVAL 115 DAY),
('Mural Ruang Temu', 'mural-ruang-temu', 'Mural', 'Mural 4 x 9 meter berisi wajah-wajah pelanggan tetap kafe.', '## Ide
Pemilik kafe ingin dinding yang bercerita tentang orang-orang yang membuat tempat itu hidup.

## Proses
- Wawancara singkat dengan 15 pelanggan tetap
- Rancangan digital di Procreate, diproyeksikan ke dinding
- Dilukis manual dengan cat akrilik selama 10 hari

Mural ini jadi spot foto favorit dan sering muncul di unggahan pengunjung.', 'assets/img/contoh/mural-ruang-temu.svg', 'Kafe Ruang Temu', 'Ilustrator & pelukis mural', 2024, '10 hari', '', '', 0, 5, 288, NOW() - INTERVAL 92 DAY),
('Zine Pasar Pagi', 'zine-pasar-pagi', 'Editorial', 'Zine 16 halaman tentang pedagang Pasar Beringharjo sebelum matahari terbit.', '## Kenapa
Aku sering ke pasar jam empat pagi dan selalu kagum dengan ritmenya. Zine ini caraku merekam cerita mereka.

## Isi
- 8 profil pedagang dengan ilustrasi dan kutipan langsung
- Peta kecil los-los pasar
- Dicetak risograf dua warna, dijilid kawat

Sudah dicetak ulang tiga kali dan dijual di dua toko buku independen.', 'assets/img/contoh/zine-pasar-pagi.svg', '', 'Penulis & ilustrator', 2023, 'Proyek pribadi', '', '', 0, 6, 246, NOW() - INTERVAL 69 DAY),
('Ilustrasi Editorial Kota Lama', 'editorial-kota-lama', 'Editorial', 'Ilustrasi pembuka rubrik budaya setiap bulan selama satu tahun.', '## Kerja sama
Setiap bulan redaksi mengirim naskah, dan aku punya tiga hari untuk menerjemahkannya menjadi satu gambar pembuka.

## Yang aku pelajari
- Membaca naskah dua kali: sekali untuk isi, sekali untuk suasana
- Sketsa kecil ukuran perangko supaya komposisi kuat sejak awal
- Konsisten memakai dua warna aksen agar rubrik mudah dikenali

Seri ini meraih penghargaan ilustrasi editorial terbaik di sebuah ajang desain lokal.', 'assets/img/contoh/editorial-kota-lama.svg', 'Majalah Kota Lama', 'Ilustrator lepas', 2023, 'Rutin bulanan', '', '', 0, 7, 190, NOW() - INTERVAL 46 DAY),
('Stiker Pack Wong Jowo', 'stiker-wong-jowo', 'Merchandise', '36 stiker ungkapan Jawa sehari-hari, laku lebih dari 8.000 lembar.', '## Awal mula
Berawal dari iseng menggambar ungkapan khas Jawa seperti "ora popo" dan "sik sik" untuk teman-teman.

## Perkembangan
- 3 seri cetak vinyl tahan air
- Versi stiker WhatsApp yang diunduh puluhan ribu kali
- Dijual lewat toko daring dan bazar kreatif

Pendapatan dari stiker membiayai cetak zine dan alat gambar baru.', 'assets/img/contoh/stiker-wong-jowo.svg', '', 'Pembuat & penjual', 2022, 'Berjalan', '', '', 0, 8, 321, NOW() - INTERVAL 23 DAY);

INSERT INTO proyek_gambar (proyek_id, file, keterangan, urutan) VALUES
(1, 'assets/img/contoh/kopi-klotok-gunung-1.svg', 'Sistem logo cap dan variasi warna', 1),
(1, 'assets/img/contoh/kopi-klotok-gunung-2.svg', 'Kemasan biji kopi 250 gram', 2),
(2, 'assets/img/contoh/kancil-naik-kereta-1.svg', 'Sketsa karakter Kancil', 1),
(2, 'assets/img/contoh/kancil-naik-kereta-2.svg', 'Halaman stasiun yang ramai', 2),
(3, 'assets/img/contoh/jamu-mbok-sri-1.svg', 'Ilustrasi rempah untuk 3 varian', 1),
(3, 'assets/img/contoh/jamu-mbok-sri-2.svg', 'Mockup botol 3D', 2),
(4, 'assets/img/contoh/musik-pinggir-kali-1.svg', 'Seri poster tiga hari', 1),
(4, 'assets/img/contoh/musik-pinggir-kali-2.svg', 'Animasi pendek untuk story', 2),
(5, 'assets/img/contoh/mural-ruang-temu-1.svg', 'Rancangan digital sebelum dilukis', 1),
(5, 'assets/img/contoh/mural-ruang-temu-2.svg', 'Proses melukis hari ketujuh', 2),
(6, 'assets/img/contoh/zine-pasar-pagi-1.svg', 'Halaman pedagang bumbu', 1),
(6, 'assets/img/contoh/zine-pasar-pagi-2.svg', 'Sampul edisi cetak ulang', 2),
(7, 'assets/img/contoh/editorial-kota-lama-1.svg', 'Edisi Agustus: pasar malam', 1),
(7, 'assets/img/contoh/editorial-kota-lama-2.svg', 'Edisi Desember: hujan pertama', 2),
(8, 'assets/img/contoh/stiker-wong-jowo-1.svg', 'Lembar stiker seri pertama', 1),
(8, 'assets/img/contoh/stiker-wong-jowo-2.svg', 'Stiker WhatsApp versi digital', 2);

INSERT INTO proyek_teknologi (proyek_id, teknologi_id) VALUES
(1, 1),
(1, 4),
(1, 7),
(2, 1),
(2, 2),
(2, 7),
(3, 4),
(3, 9),
(3, 2),
(4, 1),
(4, 6),
(4, 10),
(5, 1),
(5, 3),
(5, 12),
(6, 7),
(6, 1),
(6, 2),
(7, 2),
(7, 4),
(8, 1),
(8, 4),
(8, 11);

INSERT INTO pengalaman (jenis, posisi, instansi, kota, mulai, selesai, deskripsi) VALUES
('kerja', 'Ilustrator & Desainer Lepas', 'Studio Laras', 'Yogyakarta', '2021-01-01', NULL, 'Menangani branding, kemasan, dan ilustrasi untuk 60+ klien UMKM, penerbit, dan komunitas.'),
('kerja', 'Desainer Grafis', 'Rumah Kreatif Tugu', 'Yogyakarta', '2018-07-01', '2020-12-01', 'Merancang materi promosi, kemasan, dan katalog untuk klien kuliner dan kerajinan.'),
('kerja', 'Ilustrator Magang', 'Penerbit Pelangi Kecil', 'Sleman', '2017-06-01', '2018-06-01', 'Membantu ilustrasi interior buku anak dan mempersiapkan file cetak.'),
('pendidikan', 'S1 Desain Komunikasi Visual', 'Institut Seni Nusantara', 'Yogyakarta', '2013-08-01', '2017-08-01', 'Tugas akhir: buku ilustrasi tentang jajanan pasar yang mulai langka.'),
('pendidikan', 'Kelas Cetak Saring & Risograf', 'Kolektif Cetak Kampung', 'Yogyakarta', '2019-03-01', '2019-05-01', 'Belajar teknik separasi warna dan cetak dua tinta.');

INSERT INTO layanan (judul, deskripsi, ikon, harga_mulai, urutan) VALUES
('Branding UMKM', 'Logo, palet warna, dan panduan sederhana yang gampang dipakai sendiri.', 'palette', 3500000, 1),
('Desain Kemasan', 'Label, kotak, dan botol yang menonjol di rak toko dan foto marketplace.', 'boxes', 2500000, 2),
('Ilustrasi Buku & Editorial', 'Buku anak, sampul novel, dan ilustrasi artikel majalah.', 'brush', 750000, 3),
('Poster & Visual Acara', 'Poster, tiket, dan paket konten media sosial untuk acara.', 'megaphone', 1200000, 4),
('Mural', 'Dinding kafe, kantor, dan sekolah. Harga per meter persegi.', 'pen-tool', 450000, 5);

INSERT INTO testimoni (nama, jabatan, isi, foto, rating, urutan) VALUES
('Pak Wahyu Santoso', 'Pemilik Kopi Klotok Gunung', 'Mbak Laras paham banget suasana warung kami. Logo capnya sekarang distempel di semua kantong, pelanggan sering minta foto bareng.', 'assets/img/contoh/av-1.svg', 5, 1),
('Fitri Handayani', 'Editor Penerbit Pelangi Kecil', 'Karakter Kancil buatan Laras disukai anak-anak di setiap sesi baca. Revisi cepat dan file cetaknya selalu rapi.', 'assets/img/contoh/av-2.svg', 5, 2),
('Bu Sri Mulyani', 'Pemilik Jamu Mbok Sri', 'Botol jamu saya jadi cantik sekali. Cucu saya sampai bangga cerita ke teman-temannya kalau itu jamu buatan neneknya.', 'assets/img/contoh/av-3.svg', 5, 3),
('Galih Nugroho', 'Ketua Kolektif Pinggir Kali', 'Posternya sampai dicuri orang dari tiang listrik, itu tanda desainnya keren. Template Figma-nya juga menyelamatkan panitia.', 'assets/img/contoh/av-4.svg', 5, 4),
('Anindya Paramita', 'Redaktur Majalah Kota Lama', 'Selalu tepat waktu dan berani menawarkan sudut pandang yang tidak kami duga. Setiap bulan kami menunggu ilustrasinya.', 'assets/img/contoh/av-5.svg', 4, 5);

INSERT INTO pesan (nama, email, subjek, isi, anggaran, ip, dibaca, created_at) VALUES
('Rizal Fahmi', 'rizal@sambalbuanyai.id', 'Desain label sambal botol', 'Mbak, kami produsen sambal rumahan di Bantul. Mau desain label untuk 4 varian, sekalian logonya diperbarui. Kira-kira berapa lama?', 'Rp5 - 15 juta', '10.0.0.12', 0, NOW() - INTERVAL 840 MINUTE),
('Clara Wibisono', 'clara@kelaskreatif.org', 'Ilustrasi buku cerita', 'Kami yayasan literasi yang ingin menerbitkan buku cerita bergambar 32 halaman. Apakah jadwal Mbak Laras masih ada untuk Februari?', 'Rp15 - 50 juta', '10.0.0.19', 0, NOW() - INTERVAL 3780 MINUTE),
('Dimas Ardiansyah', 'dimas.ard@gmail.com', 'Mural kamar anak', 'Halo, saya ingin mural tema hutan di kamar anak ukuran 3 x 2,5 meter di Sleman.', 'Di bawah Rp5 juta', '10.0.0.50', 1, NOW() - INTERVAL 16800 MINUTE);

INSERT INTO kunjungan (tanggal, jumlah) VALUES
(CURDATE() - INTERVAL 1 DAY, 70),
(CURDATE() - INTERVAL 2 DAY, 98),
(CURDATE() - INTERVAL 3 DAY, 89),
(CURDATE() - INTERVAL 4 DAY, 96),
(CURDATE() - INTERVAL 5 DAY, 112),
(CURDATE() - INTERVAL 6 DAY, 116),
(CURDATE() - INTERVAL 7 DAY, 80),
(CURDATE() - INTERVAL 8 DAY, 73),
(CURDATE() - INTERVAL 9 DAY, 73),
(CURDATE() - INTERVAL 10 DAY, 126),
(CURDATE() - INTERVAL 11 DAY, 51),
(CURDATE() - INTERVAL 12 DAY, 79),
(CURDATE() - INTERVAL 13 DAY, 75),
(CURDATE() - INTERVAL 14 DAY, 47),
(CURDATE() - INTERVAL 15 DAY, 51),
(CURDATE() - INTERVAL 16 DAY, 81),
(CURDATE() - INTERVAL 17 DAY, 41),
(CURDATE() - INTERVAL 18 DAY, 42),
(CURDATE() - INTERVAL 19 DAY, 51),
(CURDATE() - INTERVAL 20 DAY, 54),
(CURDATE() - INTERVAL 21 DAY, 58),
(CURDATE() - INTERVAL 22 DAY, 44),
(CURDATE() - INTERVAL 23 DAY, 94),
(CURDATE() - INTERVAL 24 DAY, 95),
(CURDATE() - INTERVAL 25 DAY, 78),
(CURDATE() - INTERVAL 26 DAY, 81),
(CURDATE() - INTERVAL 27 DAY, 59),
(CURDATE() - INTERVAL 28 DAY, 107),
(CURDATE() - INTERVAL 29 DAY, 57),
(CURDATE() - INTERVAL 30 DAY, 47);

