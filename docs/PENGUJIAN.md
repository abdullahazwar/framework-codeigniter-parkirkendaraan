# Pengujian

## Lingkungan lokal

- PHP 8.3.6, CodeIgniter 4.7.5, PHPUnit 10.5.66.
- MariaDB 10.11.14, InnoDB, database tes terpisah.
- Chromium headless untuk pemeriksaan browser.
- Pengujian dilakukan 19 Oktober 2026 dalam Asia/Jakarta.

## Cakupan otomatis

| Area | Skenario |
| --- | --- |
| Tarif | 0, 1, 60, 61, 120, 121, 135, 1440 menit, tarif dan durasi negatif |
| Masuk | Normalisasi plat, snapshot, slot aktif, plat duplikat, slot penuh tanpa sisa data |
| Keluar | Pembayaran tunai, kembalian, slot dilepas, kunjungan ulang |
| Integritas | Pembayaran kedua ditolak, kurang bayar ditolak, total yang berubah ditolak |
| Rollback | Pembaruan tiket sengaja digagalkan melalui trigger, pembayaran dibatalkan |
| Snapshot | Tarif master berubah sebelum dan setelah pembayaran, nilai lama tetap |
| Laporan | Tiket belum dibayar tidak dihitung, batas tanggal antarbulan, total sesuai pembayaran |
| Keamanan | Tamu diarahkan login, petugas mendapat 403 pada halaman admin, CSRF tanpa token ditolak |
| Sesi | Akun nonaktif dan versi sesi lama tidak dapat mengakses dashboard |
| CSV | Input formula spreadsheet dinetralkan |
| Konkurensi | Dua proses berebut slot terakhir, plat sama, pembayaran tiket sama |

Perintah: `composer lint`, `composer test`, `composer validate --strict`, dan
`composer audit`. Hasil lokal akhir: 31 tes lulus. Pemeriksaan sintaks PHP lulus, composer.json valid, dan audit Composer tidak menemukan advisory kerentanan pada dependency yang terpasang.

## Browser

Pemeriksaan menggunakan server PHP lokal dan database pengujian aplikasi:

1. Login admin dan buka dashboard serta tujuh halaman operasional/admin.
2. Buat akun petugas melalui formulir.
3. Masukkan kendaraan, tampilkan tiket, lalu kirim plat yang sama dan periksa error.
4. Cari plat, proses keluar, periksa kembalian Rp7.000 dari pembayaran Rp10.000
   untuk tarif motor Rp3.000, dan konfirmasi bukti lunas.
5. Cocokkan laporan dengan pembayaran dan unduh CSV yang berisi transaksi itu.
6. Buka menu HP dan pindah halaman melalui navigasi offcanvas.
7. Periksa tujuh halaman pada lebar 320, 390, 768, dan 1440 piksel.
   Tidak ada overflow horizontal pada halaman. Tabel tetap memiliki region gulir.
8. Login sebagai petugas dan periksa 403 pada seluruh halaman admin serta CSV.
9. Uji simpan tarif, tambah/edit/nonaktifkan/hapus slot, serta nonaktifkan petugas.
10. Uji CSRF melalui HTTP (403), akses /.env (404), keyboard pada menu HP,
    Escape untuk menutup menu, dan CSS cetak yang menyembunyikan navigasi.
11. Tinjau screenshot dashboard desktop dan kendaraan masuk pada HP.

Tidak ada error JavaScript atau respons server 500 selama alur tersebut.
Screenshot tersedia di `docs/screenshots`. Pemeriksaan viewport merupakan simulasi,
bukan pengujian perangkat fisik. Cetak memakai browser dan belum diuji pada printer
fisik. Pengujian ini tidak menyatakan sertifikasi aksesibilitas atau audit penetrasi.

## Regresi akses subfolder localhost

Simulasi pada server PHP lokal di Linux memakai pola folder pada foto:
`/gittugas/parkir/framework-codeigniter-parkirkendaraan/`.

- Root mengirim HTTP 302 dengan Location `public/`.
- Form login tampil pada `/public/` dan login berhasil.
- Seluruh stylesheet dan JavaScript memakai prefix folder yang benar, HTTP 200.
- Dashboard, kendaraan masuk, serta riwayat bekerja pada URL bersubfolder.
- Penanda menu aktif dan navigasi HP bekerja.
- Bootstrap menampilkan petunjuk jika ekstensi PHP belum aktif.

Pemeriksaan tambahan tidak mengubah data produksi atau konfigurasi XAMPP pengguna.

## Instalasi bersih tanpa Composer

Salinan dari berkas Git yang akan dikirim diuji di folder terpisah, menggunakan
bundle runtime bawaan dan database lokal `parkir_standalone_test` yang terpisah.
Tidak ada perintah Composer pada salinan aplikasi tersebut.

- Versi dan referensi tiga paket runtime cocok dengan `composer.lock`.
- Autoloader berhasil memuat framework, aplikasi, Laminas Escaper, dan PSR Log.
- `php spark migrate` dan `php spark db:seed InitialSeeder` berhasil.
- Login admin berhasil, stylesheet/JavaScript HTTP 200, navigasi subfolder dan
  menu HP bekerja tanpa error JavaScript.
- Pemeriksaan sintaks 107 berkas PHP aplikasi lulus.

## Menjalankan ulang

Checkout atau ZIP bersih sudah berisi dependensi runtime. Jalankan
`php scripts/check-runtime.php` dan `php scripts/lint.php` tanpa Composer untuk
memeriksa bundle. Job **Runtime without Composer** memakai PHP 8.2 dan MariaDB
10.4, lalu menjalankan migration, seed, serta memuat halaman login dan aset lokal
langsung dari berkas yang di-checkout, tanpa perintah Composer.

Composer hanya diperlukan untuk memasang PHPUnit melalui `composer install`.
Siapkan `database.tests.*` di `.env`, lalu jalankan `composer test`. Migration dan
fixture dibuat ulang per test. Jangan memakai data produksi sebagai database tes.
Fungsi proc_open harus tersedia untuk menjalankan worker konkurensi.

Workflow `.github/workflows/tests.yml` menyiapkan PHP 8.2 dengan MySQL 8.0,
MariaDB 10.11, dan MariaDB 10.4 untuk memeriksa kompatibilitas XAMPP.
Status workflow di GitHub harus dilihat terpisah dari hasil MariaDB lokal.

## Profil publik dan pendaftaran pengguna

Pembaruan 19 Oktober 2026 diperiksa dengan PHP 8.3.6, MariaDB 10.11.14,
PHPUnit 10.5.66, dan Chromium headless 133. Dependensi untuk pengujian ditempatkan
terpisah dari bundle runtime aplikasi.

- Seluruh suite lulus: 43 tes, 182 assertion. Termasuk 12 tes baru untuk halaman
  publik, pendaftaran, validasi, CSRF, throttling, login, akun nonaktif, dan izin.
- Pendaftaran mengabaikan input `role`, `aktif`, dan `auth_version` dari browser.
  Akun baru tetap menjadi pengguna aktif biasa.
- Username duplikat tidak menimpa akun. Konfirmasi yang salah dan password terlalu
  panjang ditolak. Session old input hanya menyimpan nama dan username.
- Pengguna mendapat HTTP 403 pada seluruh route baca/tulis operasional dan admin
  yang diuji, termasuk tiket dan CSV. Akun hanya memuat identitas sendiri dan tarif.
- Browser memeriksa profil, login, dan daftar pada lebar 320, 390, 768, serta
  1440 piksel. Tidak ditemukan overflow horizontal pada halaman tersebut.
- Alur browser mencakup validasi konfirmasi, daftar berhasil, login pengguna,
  pembacaan tarif, akses ditolak, logout, login admin, dan halaman pengaturan.
- Alur yang sama lulus pada URL biasa dan simulasi subfolder
  `/gittugas/parkir/framework-codeigniter-parkirkendaraan/public/` memakai server
  PHP lokal. Pemeriksaan ini tidak menjalankan Apache Windows secara langsung.
- Menu HP, Escape, pengembalian fokus, tautan bagian halaman, dan FAQ bekerja.
  Navigasi juga tersedia ketika JavaScript dimatikan. Tidak ada error JavaScript
  selama alur yang diperiksa.
- Dump SQL dengan skema peran lama diimpor ke database pengujian terpisah.
  Migration menambah peran pengguna. Sidik seluruh baris dalam enam tabel aplikasi
  tetap sama setelah pembaruan.
- Pemeriksaan sintaks 117 berkas PHP dan pemeriksaan runtime tanpa Composer lulus.
  `composer validate --strict` lulus. Seluruh `.htaccess`, contoh `.env`, serta
  bundle `vendor` aplikasi tetap sama. Tidak ada `.env` baru yang dibuat.

Screenshot yang ditinjau tersedia pada `screenshots/profil-desktop.png` dan
`screenshots/daftar-mobile.png`. Tampilan HP diperiksa melalui simulasi viewport.

## Regresi SQL pendaftaran pada database lama

Kolom `users.role` dikembalikan menjadi `ENUM('admin','petugas')` untuk mereproduksi
penolakan akun pengguna. Sebuah tiket dan pembayaran juga dibuat agar pengujian
mencakup database dengan transaksi. Pendaftaran valid ditolak pada skema lama.

File `app/Database/perbaikan_daftar_user.sql` lalu dijalankan dua kali. Pendaftaran
dengan data yang sama berhasil dan akun tersimpan sebagai `pengguna`. Seluruh baris
yang sudah ada pada enam tabel aplikasi tetap sama, termasuk akun dan pembayaran.
SQL tidak membutuhkan Composer atau perintah migration.

Suite akhir lulus: 44 tes, 198 assertion pada PHP 8.3.6 dan MariaDB 10.11.14.
Pemeriksaan sintaks controller dan tes yang diubah juga lulus. Seluruh `.htaccess`
dan contoh `.env` tetap sama; `.env` lokal tidak dibuat atau diedit.

## Alur pengguna parkir

Pembaruan area `/pengguna` diperiksa pada PHP 8.3.6, MariaDB 10.11.14,
PHPUnit 10.5.66, dan Chromium headless 133. Seluruh suite lulus: **68 tes**.
Pemeriksaan sintaks **132 berkas PHP** dan runtime tanpa Composer juga lulus.

| Area | Hasil yang diperiksa |
| --- | --- |
| Kunjungan lengkap | Simpan kendaraan, ajukan masuk, verifikasi petugas, tiket/slot aktif, minta keluar, tunai, bukti bayar/riwayat |
| Kepemilikan | Kendaraan akun lain tidak bisa dipakai/dilepas; permintaan akun lain tidak bisa dibaca, dibatalkan, atau diminta keluar |
| Riwayat | Tiket walk-in tidak diklaim lewat plat; melepas dan mendaftarkan kembali kendaraan tidak memindahkan riwayat |
| Kapasitas | Pengajuan tidak mengunci slot; slot penuh saat pengajuan/verifikasi ditolak tanpa tiket sisa |
| Status | Pembatalan sebelum masuk, penolakan dengan alasan, pengajuan ulang, pembayaran langsung petugas |
| Izin | Tamu harus login; pengguna mendapat 403 pada antrean/konfirmasi/pembayaran petugas; admin tidak masuk area pengguna |
| Integritas | Verifikasi wajib mencocokkan kendaraan; CSRF wajib; gagal mengaitkan tiket atau menyelesaikan permintaan membatalkan seluruh transaksi |
| Konkurensi | Dua proses mengajukan/menerima permintaan sama, membatalkan sambil menerima, dan meminta keluar sambil membayar |
| SQL | Pembaruan dapat dijalankan berulang tanpa mengubah data kunjungan |

Dump skema lama dengan tiket dan pembayaran diimpor ke database tes terpisah.
`fitur_pengguna_parkir.sql` dijalankan dua kali, kemudian migration dijalankan.
Peran dan dua tabel baru tersedia; sidik seluruh baris pada enam tabel aplikasi
lama tetap sama.

Browser menguji alur yang sama pada URL biasa dan simulasi subfolder
`/gittugas/parkir/framework-codeigniter-parkirkendaraan/public/`. Beranda pengguna,
kendaraan, parkir, riwayat, profil, tiket aktif, bukti bayar, dan antrean petugas
diperiksa pada lebar 320, 390, 768, dan 1440 piksel tanpa overflow horizontal.
Alur juga memeriksa alasan penolakan, pembatalan, akses akun lain (404), dan panel
petugas (403). Tidak ditemukan error JavaScript selama alur tersebut.

Menu pengguna dapat ditutup dengan Escape dan mengembalikan fokus ke tombol.
CSS cetak menyembunyikan navigasi serta panel tindakan. Login, navigasi, riwayat,
dan bukti bayar juga berhasil pada viewport 320 piksel dengan JavaScript dimatikan.
Pemeriksaan ini memakai Linux dengan server PHP lokal, bukan Apache Windows atau
printer fisik.

Screenshot dengan data fiktif tersedia pada `screenshots/pengguna-desktop.png`,
`screenshots/pengguna-tiket-mobile.png`, dan `screenshots/pengguna-bukti-bayar.png`.
Seluruh `.htaccess`, contoh `.env`, `vendor`, dan dependensi aplikasi tetap sama.
Tidak ada `.env` lokal yang dibuat atau diubah.
