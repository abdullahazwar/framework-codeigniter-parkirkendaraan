# Menjalankan proyek di XAMPP Windows

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
   composer install
   ```

   Composer memasang folder `vendor`. Folder ini memang tidak disertakan dalam
   Git. Composer harus memakai PHP 8.2 atau lebih baru, sama seperti Apache XAMPP.
   Versi PHP yang tampak pada foto adalah 8.2.12 dan memenuhi kebutuhan aplikasi.

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

## Pemeriksaan berdasarkan pesan

| Gejala | Penyelesaian |
| --- | --- |
| Index of /... | Buka `/public/`, atau update repository agar pengarah root tersedia |
| Boot.php atau dependency tidak ditemukan | Jalankan `composer install` dari root proyek |
| Ekstensi intl belum aktif | Buka `C:\xampp\php\php.ini`, aktifkan `extension=intl`, restart Apache |
| PHP CLI berbeda dari Apache | Periksa `where php`, gunakan `C:\xampp\php\php.exe` untuk perintah spark |
| Access denied | Sesuaikan username/password database di `.env` |
| Unknown database | Buat database `parkir`, lalu jalankan migration |
| Table users tidak ditemukan | Jalankan migration dan InitialSeeder |
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
