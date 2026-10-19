<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<div class="public-container auth-grid">
    <section class="auth-intro" aria-labelledby="login-intro-title">
        <p class="eyebrow">SELAMAT DATANG KEMBALI</p>
        <h1 id="login-intro-title">Satu akun.<br>Akses sesuai peran.</h1>
        <p>Pengguna dapat mengelola kendaraan, tiket, dan riwayat parkir. Admin dan petugas dapat
            melanjutkan pengelolaan kendaraan di panel operasional.</p>
        <a class="text-link" href="<?= url_to('beranda') ?>#tentang">Kenali web Parkir <span aria-hidden="true">↗</span></a>
    </section>
    <section class="auth-card" aria-labelledby="login-title">
        <p class="eyebrow">MASUK AKUN</p>
        <h2 id="login-title">Masuk ke Parkir</h2>
        <p class="auth-description">Gunakan username dan password yang terdaftar.</p>
        <?= $this->include('partials/alerts') ?>
        <form action="<?= url_to('login.submit') ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username" autocomplete="username"
                    required minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+"
                    autocapitalize="none" spellcheck="false" value="<?= old('username', '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password"
                    autocomplete="current-password" required maxlength="72">
            </div>
            <button class="btn btn-primary w-100" type="submit">Masuk ke akun</button>
        </form>
        <p class="auth-switch">Belum punya akun? <a href="<?= url_to('daftar') ?>">Daftar pengguna</a></p>
        <p class="auth-help">Lupa password? Hubungi administrator lokasi parkir.</p>
    </section>
</div>
<?= $this->endSection() ?>
