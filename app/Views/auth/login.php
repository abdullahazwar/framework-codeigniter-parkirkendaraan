<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk | Parkir</title>
    <link rel="icon" href="<?= base_url("assets/favicon.svg") ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url("assets/vendor/bootstrap/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/style.css") ?>">
    <script src="<?= base_url("assets/js/parkir.js") ?>" defer></script>
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-intro" aria-labelledby="intro-title">
            <a class="brand" href="<?= url_to("login") ?>"><span class="brand-mark">P</span>
                Parkir</a>
            <div class="login-message">
                <p class="eyebrow">PANEL OPERASIONAL</p>
                <h1 id="intro-title">Parkir tertata.<br>Pelayanan lancar.</h1>
                <p>Catat kendaraan, pantau slot, dan selesaikan pembayaran dalam satu tempat.</p>
            </div>
            <div class="parking-illustration" aria-hidden="true"><span>P</span>
                <div class="parking-lane"><i></i><i></i><i></i></div>
            </div>
            <p class="intro-footer">Sistem Informasi Manajemen Parkir</p>
        </section>
        <section class="login-form-wrap" aria-labelledby="login-title">
            <div class="login-form">
                <p class="eyebrow text-muted">SELAMAT DATANG</p>
                <h2 id="login-title">Masuk ke panel</h2>
                <p class="text-muted mb-4">Gunakan akun admin atau petugas yang terdaftar.</p>
                <?= $this->include("partials/alerts") ?>
                <form action="<?= url_to("login.submit") ?>" method="post" data-submit-lock>
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label"
                            for="username">Username</label><input class="form-control" id="username"
                            name="username" autocomplete="username" required minlength="3"
                            maxlength="50" pattern="[A-Za-z0-9_]+"
                            value="<?= esc(old("username") ?? "") ?>" autofocus></div>
                    <div class="mb-4"><label class="form-label"
                            for="password">Password</label><input class="form-control"
                            type="password" id="password" name="password"
                            autocomplete="current-password" required maxlength="72"></div>
                    <button class="btn btn-primary w-100" type="submit">Masuk ke panel</button>
                </form>
                <p class="login-help">Belum punya akun atau lupa password?<br>Hubungi administrator
                    lokasi parkir.</p>
            </div>
        </section>
    </main>
</body>
</html>
