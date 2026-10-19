# Catatan desain

Produk: profil web publik, akun pengguna, dan panel operasional parkir di satu lokasi.
Tugas pengguna: menyimpan kendaraan, mengajukan masuk, membuka tiket/tagihan,
meminta keluar, dan membuka bukti bayar. Tugas petugas: memverifikasi kedatangan,
menemukan tiket aktif, dan menerima pembayaran. Target: HP bagi pengguna dan
operasional; desktop bagi pengelolaan serta laporan.

Arah visual: panel operasional navy dengan penanda amber dan aksi utama biru.
Sidebar menjaga akses fungsi tetap jelas. Formulir memprioritaskan plat dan jenis
kendaraan. Tabel dapat digulir tanpa membuat seluruh halaman melebar.

- Font sistem agar tidak memerlukan layanan font eksternal.
- Bootstrap 5.3.8 lokal sebagai dasar komponen dan navigasi HP.
- Gaya panel dan token bersama berada di `public/assets/css/style.css`.
- Gaya halaman publik berada di `public/assets/css/public.css`, dibatasi oleh
  kelas `.public-page` agar tidak mengubah panel operasional.
- Area pengguna memakai `layouts/pengguna.php` dan `pengguna.css`. Navigasi atas
  berisi Beranda, Kendaraan, Parkir saya, Riwayat, dan Profil. Panel petugas
  memakai sidebar tersendiri, dengan antrean masuk/keluar dalam `permintaan.css`.
- Tiket pengguna menampilkan tahapan kunjungan, kode untuk petugas, slot, tarif
  snapshot, estimasi biaya, dan tindakan sesuai status. Bukti bayar hanya tampil
  setelah pembayaran petugas. CSS cetak hanya menyertakan isi tiket/bukti.
- Slot kosong, hitungan ringkasan, tagihan, dan riwayat memakai database nyata.
  Pengguna memperbarui halaman melalui tombol status; tidak ada angka simulasi.
- Interaksi form, pembayaran, dan cetak berada di `public/assets/js/parkir.js`.
- Navigasi publik berada di `public/assets/js/public.js`. Menu HP dapat ditutup
  dengan Escape, klik tautan, klik di luar header, atau perpindahan fokus.
- Header publik menampilkan Masuk dan Daftar, beserta Tentang, Keunggulan,
  Alur parkir, dan FAQ. Tanpa JavaScript, seluruh tautan tetap tersedia.
- Ilustrasi area parkir memakai HTML/CSS dan diberi keterangan sebagai ilustrasi,
  bukan indikator slot aktual. FAQ memakai elemen native `details` dan `summary`.
- Fokus keyboard, label form, skip link, nama region tabel, dan reduced motion.
- State kosong, pencarian tanpa hasil, validasi, akses ditolak, dan sukses.
- Pembayaran menampilkan uang diterima dan estimasi kembalian sebelum konfirmasi.
- Data berasal dari database. Instalasi awal tidak memasukkan transaksi contoh.

Screenshot berasal dari database lokal pengujian, menggunakan kendaraan dan akun
fiktif untuk demonstrasi. Screenshot ini bukan data transaksi produksi.

| Tampilan | Berkas |
| --- | --- |
| Profil web desktop | [profil-desktop.png](screenshots/profil-desktop.png) |
| Pendaftaran di HP | [daftar-mobile.png](screenshots/daftar-mobile.png) |
| Beranda pengguna | [pengguna-desktop.png](screenshots/pengguna-desktop.png) |
| Tiket pengguna di HP | [pengguna-tiket-mobile.png](screenshots/pengguna-tiket-mobile.png) |
| Bukti bayar pengguna | [pengguna-bukti-bayar.png](screenshots/pengguna-bukti-bayar.png) |
| Dashboard desktop | [dashboard-desktop.png](screenshots/dashboard-desktop.png) |
| Kendaraan masuk di HP | [masuk-mobile.png](screenshots/masuk-mobile.png) |
| Tiket masuk | [tiket-masuk.png](screenshots/tiket-masuk.png) |
| Laporan | [laporan-desktop.png](screenshots/laporan-desktop.png) |

Asal aset: Bootstrap memakai lisensi MIT, tersimpan bersama aset vendor.
Favicon dan ilustrasi CSS dibuat untuk proyek ini. Tidak ada foto atau ikon
pihak ketiga yang memerlukan layanan eksternal.
