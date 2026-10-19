<?php
$errors = session()->getFlashdata('errors') ?? [];
?>
<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<div class="public-container auth-grid registration-grid">
    <section class="auth-intro" aria-labelledby="register-intro-title">
        <p class="eyebrow">AKUN PENGGUNA</p>
        <h1 id="register-intro-title">Kenali layanan.<br>Mulai dari akun.</h1>
        <p>Buat akun untuk melihat informasi akun dan tarif parkir motor serta mobil.</p>
        <ul class="registration-points">
            <li>Cukup nama, username, dan password.</li>
            <li>Tarif mengikuti pengaturan pengelola.</li>
            <li>Pencatatan kendaraan tetap dilayani petugas di lokasi.</li>
        </ul>
        <a class="text-link" href="<?= url_to('beranda') ?>#alur">Pelajari alur parkir <span aria-hidden="true">↗</span></a>
    </section>
    <section class="auth-card" aria-labelledby="register-title">
        <p class="eyebrow">DAFTAR PENGGUNA</p>
        <h2 id="register-title">Buat akun Parkir</h2>
        <p class="auth-description">Isi data berikut untuk membuat akun pengguna.</p>
        <?= $this->include('partials/alerts') ?>
        <form action="<?= url_to('daftar.submit') ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="nama">Nama lengkap</label>
                <input class="form-control" id="nama" name="nama" autocomplete="name"
                    required maxlength="100" value="<?= old('nama', '') ?>"
                    <?= isset($errors['nama']) ? 'aria-invalid="true" aria-describedby="nama-error"' : '' ?>>
                <?php if (isset($errors['nama'])): ?>
                <p class="field-error" id="nama-error"><?= esc($errors['nama']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username" autocomplete="username"
                    required minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+"
                    autocapitalize="none" spellcheck="false" value="<?= old('username', '') ?>"
                    aria-describedby="username-help<?= isset($errors['username']) ? ' username-error' : '' ?>"
                    <?= isset($errors['username']) ? 'aria-invalid="true"' : '' ?>>
                <p class="form-text" id="username-help">3 sampai 50 huruf, angka, atau underscore.</p>
                <?php if (isset($errors['username'])): ?>
                <p class="field-error" id="username-error"><?= esc($errors['username']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password"
                    autocomplete="new-password" required minlength="12" maxlength="72"
                    aria-describedby="password-help<?= isset($errors['password']) ? ' password-error' : '' ?>"
                    <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>>
                <p class="form-text" id="password-help">Minimal 12 karakter. Gunakan password yang unik.</p>
                <?php if (isset($errors['password'])): ?>
                <p class="field-error" id="password-error"><?= esc($errors['password']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                <input class="form-control" type="password" id="password_confirmation"
                    name="password_confirmation" autocomplete="new-password" required
                    minlength="12" maxlength="72"
                    <?= isset($errors['password_confirmation'])
                        ? 'aria-invalid="true" aria-describedby="password_confirmation-error"'
                        : '' ?>>
                <?php if (isset($errors['password_confirmation'])): ?>
                <p class="field-error" id="password_confirmation-error"><?= esc($errors['password_confirmation']) ?></p>
                <?php endif; ?>
            </div>
            <p class="registration-notice">Nama dan username dipakai untuk identitas akun pengguna.</p>
            <button class="btn btn-primary w-100" type="submit">Daftar akun pengguna</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="<?= url_to('login') ?>">Masuk di sini</a></p>
    </section>
</div>
<?= $this->endSection() ?>
