# Dependensi runtime bawaan

Repository menyertakan `vendor` agar hasil clone atau Download ZIP dapat langsung
dipakai di XAMPP tanpa menginstal Composer. Pengaturan `.env`, database, migration,
dan akun admin tetap mengikuti [XAMPP.md](XAMPP.md).

| Paket | Versi dari composer.lock | Lokasi lisensi |
| --- | --- | --- |
| CodeIgniter | 4.7.5 | `vendor/codeigniter4/framework/LICENSE` |
| Laminas Escaper | 2.18.0 | `vendor/laminas/laminas-escaper/LICENSE` |
| PSR Log | 3.0.2 | `vendor/psr/log/LICENSE` |
| Autoloader Composer | Dibuat saat menyiapkan bundle | `vendor/composer/LICENSE` |

Kode dependensi dipertahankan sebagaimana distribusi aslinya. Framework tetap
berada di `vendor/codeigniter4/framework/system`, sesuai `app/Config/Paths.php`.
Autoloader memakai path relatif sehingga folder proyek dapat dipindah ke `htdocs`.
PHPUnit dan paket pengembangan tidak disertakan dalam bundle runtime.

## Memeriksa bundle

Jalankan dengan PHP XAMPP, tanpa Composer:

```bat
C:\xampp\php\php.exe scripts\check-runtime.php
```

Pemeriksaan mencocokkan versi dan referensi paket dengan `composer.lock`,
memastikan tidak ada paket pengembangan dalam metadata bundle, dan memuat
autoloader framework, aplikasi, Laminas Escaper, serta PSR Log.

## Pemeliharaan oleh pengembang

Composer diperlukan hanya saat mengubah dependensi atau menjalankan PHPUnit.
Jangan mengedit kode dalam `vendor` secara manual. Setelah memperbarui lockfile:

1. Siapkan folder sementara di luar repository, salin `composer.json`,
   `composer.lock`, dan direktori `app` ke dalamnya.
2. Dari folder sementara itu, jalankan
   `composer install --no-dev --optimize-autoloader --no-scripts --no-plugins`.
3. Ganti `vendor` proyek dengan hasil runtime tersebut. Jangan menyertakan paket
   PHPUnit, `.env`, data database, session, log, atau cache.
4. Jalankan `php scripts/check-runtime.php`, uji instalasi bersih dan alur aplikasi,
   lalu commit perubahan `composer.lock` beserta `vendor` yang sesuai.

CI memeriksa instalasi dari checkout bersih tanpa menjalankan Composer, termasuk
migration, seed, dan halaman login. Job PHPUnit terpisah memasang alat pengujian.
