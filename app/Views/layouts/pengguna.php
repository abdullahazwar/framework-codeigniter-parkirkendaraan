<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title) ?> | Parkir Pengguna</title>
    <link rel="icon" href="<?= base_url('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/public.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/pengguna.css') ?>">
    <script src="<?= base_url('assets/js/public.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/parkir.js') ?>" defer></script>
</head>
<body class="public-page pengguna-page">
    <a class="skip-link" href="#konten">Lewati ke konten</a>
    <header class="public-header no-print">
        <div class="public-container header-inner">
            <a class="brand public-brand" href="<?= url_to('pengguna.beranda') ?>">
                <span class="brand-mark" aria-hidden="true">P</span><span>Parkir</span>
            </a>
            <nav class="public-nav" id="public-navigation" aria-label="Navigasi pengguna">
                <?php
                $path = trim(service('request')->getUri()->getRoutePath(), '/');
                foreach ([
                    ['pengguna.beranda', 'pengguna', 'Beranda'],
                    ['pengguna.kendaraan', 'pengguna/kendaraan', 'Kendaraan'],
                    ['pengguna.parkir', 'pengguna/parkir', 'Parkir saya'],
                    ['pengguna.riwayat', 'pengguna/riwayat', 'Riwayat'],
                    ['pengguna.profil', 'pengguna/profil', 'Profil'],
                ] as [$route, $match, $label]):
                    $active = $path === $match
                        || ($match === 'pengguna/parkir' && str_starts_with($path, 'pengguna/tiket/'));
                ?>
                <a href="<?= url_to($route) ?>" <?= $active ? 'aria-current="page"' : '' ?>>
                    <?= esc($label) ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="header-actions">
                <form action="<?= url_to('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-secondary" type="submit">Keluar akun</button>
                </form>
                <button class="nav-toggle" type="button" aria-expanded="false"
                    aria-controls="public-navigation" aria-label="Buka navigasi" hidden>
                    <span class="nav-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </header>
    <main class="public-container member-content" id="konten" tabindex="-1">
        <?= $this->include('partials/alerts') ?>
        <?= $this->renderSection('content') ?>
    </main>
    <footer class="member-footer no-print">
        <div class="public-container">
            <span>Parkir · Area pengguna</span>
            <a href="<?= url_to('profil') ?>#alur">Tentang layanan parkir</a>
        </div>
    </footer>
</body>
</html>
