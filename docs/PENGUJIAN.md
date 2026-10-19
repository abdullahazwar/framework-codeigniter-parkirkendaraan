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
