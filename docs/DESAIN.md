# Catatan desain

Produk: panel operasional parkir untuk admin dan petugas di satu lokasi.
Tugas utama: mencatat kendaraan masuk, menemukan tiket aktif, menerima pembayaran.
Target: HP untuk operasional dan desktop untuk pengelolaan serta laporan.

Arah visual: panel operasional navy dengan penanda amber dan aksi utama biru.
Sidebar menjaga akses fungsi tetap jelas. Formulir memprioritaskan plat dan jenis
kendaraan. Tabel dapat digulir tanpa membuat seluruh halaman melebar.

- Font sistem agar tidak memerlukan layanan font eksternal.
- Bootstrap 5.3.8 lokal sebagai dasar komponen dan navigasi HP.
- Semua gaya aplikasi berada di `public/assets/css/style.css`.
- Semua interaksi aplikasi berada di `public/assets/js/parkir.js`.
- Fokus keyboard, label form, skip link, nama region tabel, dan reduced motion.
- State kosong, pencarian tanpa hasil, validasi, akses ditolak, dan sukses.
- Pembayaran menampilkan uang diterima dan estimasi kembalian sebelum konfirmasi.
- Data berasal dari database. Instalasi awal tidak memasukkan transaksi contoh.

Screenshot berasal dari database lokal pengujian, menggunakan kendaraan dan akun
fiktif untuk demonstrasi. Screenshot ini bukan data transaksi produksi.

| Tampilan | Berkas |
| --- | --- |
| Dashboard desktop | [dashboard-desktop.png](screenshots/dashboard-desktop.png) |
| Kendaraan masuk di HP | [masuk-mobile.png](screenshots/masuk-mobile.png) |
| Tiket masuk | [tiket-masuk.png](screenshots/tiket-masuk.png) |
| Laporan | [laporan-desktop.png](screenshots/laporan-desktop.png) |

Asal aset: Bootstrap memakai lisensi MIT, tersimpan bersama aset vendor.
Favicon dan ilustrasi CSS dibuat untuk proyek ini. Tidak ada foto atau ikon
pihak ketiga yang memerlukan layanan eksternal.
