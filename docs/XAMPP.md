# Menjalankan proyek di XAMPP Windows

Composer tidak perlu diinstal. Framework, autoloader, dan seluruh dependensi
runtime sudah disertakan dalam folder `vendor` di repository ini. Bootstrap,
CSS, dan JavaScript juga tersedia lokal.

## Mengapa muncul "Index of /..."

Apache sedang membuka folder utama repository. Front controller CodeIgniter ada
di `public/index.php`, bukan `index.html`. Pembaruan proyek menambahkan `index.php`
di root sebagai pengarah ke `public/` dan menonaktifkan daftar direktori.

Untuk lokasi pada foto, buka:

```text
http://localhost/gittugas/parkir/framework-codeigniter-parkirkendaraan/public/
```

## Langkah pemasangan

1. Nyalakan Apache dan MySQL dari XAMPP Control Panel.
2. Letakkan repository pada folder:

   ```text
   C:\xampp\htdocs\gittugas\parkir\framework-codeigniter-parkirkendaraan
   ```

3. Buka terminal pada folder tersebut. Untuk repository yang sudah di-clone:

   ```bat
   git pull origin main
   ```

   Alternatif tanpa Git: pilih **Code > Download ZIP** di GitHub, lalu ekstrak
   seluruh isinya ke folder pada langkah 2. Pastikan yang dibuka adalah folder
   yang berisi `app`, `public`, `vendor`, dan `spark`, bukan folder pembungkus ZIP.

   Jika folder `vendor` sebelumnya dibuat sendiri dengan Composer dan `git pull`
   menolak menimpa file lokal, pindahkan folder `vendor` lama ke luar proyek
   sebagai cadangan, lalu ulangi `git pull origin main`.

   Gunakan PHP 8.2 atau lebih baru. Versi pada foto, PHP 8.2.12, memenuhi kebutuhan.
   Opsional, periksa kelengkapan dependensi bawaan tanpa Composer:

   ```bat
   C:\xampp\php\php.exe scripts\check-runtime.php
   ```

4. Untuk pemasangan pertama, salin contoh khusus XAMPP:

   ```bat
   copy .env.xampp.example .env
   ```

   Jika `.env` sudah ada, edit file tersebut tanpa menyalin ulang. Sesuaikan bagian
   berikut. Root database di bawah adalah contoh akun lokal XAMPP. Jika password
   database sudah diatur, masukkan password yang digunakan pada komputer lokal.

   ```ini
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost/gittugas/parkir/framework-codeigniter-parkirkendaraan/public/'
   app.indexPage = ''
   database.default.hostname = 127.0.0.1
   database.default.database = parkir
   database.default.username = root
   database.default.password = ''
   database.default.port = 3306
   ```

   Ganti nama folder pada URL jika lokasi proyek berbeda. Jika Apache memakai
   port 8080, gunakan `http://localhost:8080/.../public/`. Aset CSS, JavaScript,
   form login, dan link navigasi harus memakai baseURL yang sama dengan browser.

5. Buka phpMyAdmin dan buat database `parkir` jika belum ada:

   ```sql
   CREATE DATABASE parkir
     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. Isi `parkir.adminUsername` dan `parkir.adminPassword` di `.env`. Password admin
   harus 12 sampai 72 karakter. Jalankan dari folder utama proyek:

   ```bat
   C:\xampp\php\php.exe spark migrate
   C:\xampp\php\php.exe spark db:seed InitialSeeder
   ```

   Setelah seed berhasil, hapus nilai `parkir.adminPassword` dari `.env`. Login
   memakai username dan password yang diisi sendiri sebelum proses seed.

7. Buka URL aplikasi di atas, lalu tekan Ctrl+F5 untuk memuat ulang aset.

## Memperbaiki pendaftaran pada database lama

1. Ambil source terbaru dari branch `main`.
2. Buka phpMyAdmin dan pilih database yang sedang dipakai aplikasi.
3. Pilih **Import**, lalu impor `app/Database/perbaikan_daftar_user.sql`.
4. Ulangi pendaftaran melalui halaman Daftar.

Skema lama hanya menerima peran `admin` dan `petugas`. Akun yang didaftarkan
melalui web memakai peran `pengguna`, sehingga database lama menolak penyimpanan.
SQL perbaikan memperbarui kolom peran tanpa menghapus data. SQL ini boleh diimpor
ulang dan tidak membutuhkan Composer atau perintah terminal.

Untuk database yang sudah berisi data, gunakan file perbaikan. `parkir.sql`
merupakan SQL instalasi baru untuk database kosong. Pengaturan `.env` dan seluruh
`.htaccess` tetap dipertahankan.

## Pemeriksaan berdasarkan pesan

| Gejala | Penyelesaian |
| --- | --- |
| Index of /... | Buka `/public/`, atau update repository agar pengarah root tersedia |
| Boot.php atau dependensi tidak ditemukan | Unduh ulang ZIP lengkap dan pastikan seluruh folder `vendor` ikut diekstrak |
| Ekstensi intl belum aktif | Buka `C:\xampp\php\php.ini`, aktifkan `extension=intl`, restart Apache |
| PHP CLI berbeda dari Apache | Periksa `where php`, gunakan `C:\xampp\php\php.exe` untuk perintah spark |
| Access denied | Sesuaikan username/password database di `.env` |
| Unknown database | Buat database `parkir`, lalu jalankan migration |
| Table users tidak ditemukan | Jalankan migration dan InitialSeeder |
| Daftar gagal pada database lama | Impor `app/Database/perbaikan_daftar_user.sql` pada database aplikasi |
| Halaman login tanpa desain | Cocokkan `app.baseURL` sampai `/public/`, termasuk port dan seluruh folder |
| Link berpindah ke localhost:8080 | Ubah baseURL bawaan menjadi alamat Apache yang digunakan |
| Route /login mendapat 404 | Aktifkan `mod_rewrite` dan izinkan `.htaccess` pada konfigurasi Apache |

Jika `mod_rewrite` belum aktif, pengaturan sementara `app.indexPage = 'index.php'`
memungkinkan penggunaan URL `/public/index.php/login`. Pengaturan `app.baseURL`
tetap berakhir dengan `/public/`, tanpa menambahkan `index.php`.

Proyek tetap memakai front controller di `public`. Folder `app` dan `vendor`
tidak perlu dipindah, dan tidak perlu membuat `index.html` untuk memuat aplikasi.
Untuk pemasangan server, arahkan document root langsung ke `public` seperti
petunjuk pada README.
