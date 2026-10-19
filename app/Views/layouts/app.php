<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title) ?> | Parkir</title>
    <link rel="icon" href="<?= base_url("assets/favicon.svg") ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url("assets/vendor/bootstrap/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/style.css") ?>">
    <script src="<?= base_url("assets/vendor/bootstrap/bootstrap.bundle.min.js") ?>" defer></script>
    <script src="<?= base_url("assets/js/parkir.js") ?>" defer></script>
</head>
<body>
    <a class="skip-link" href="#konten">Lewati ke konten</a>
    <header class="mobile-header d-lg-none no-print">
        <a class="brand" href="<?= url_to("dashboard") ?>"><span class="brand-mark">P</span>
            Parkir</a>
        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebar" aria-controls="sidebar"
            aria-label="Buka navigasi">Menu</button>
    </header>
    <aside class="sidebar offcanvas-lg offcanvas-start no-print" tabindex="-1" id="sidebar"
        aria-labelledby="sidebar-title">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5" id="sidebar-title">Navigasi</h2>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebar" aria-label="Tutup navigasi"></button>
        </div>
        <div class="sidebar-content">
            <a class="brand desktop-brand" href="<?= url_to("dashboard") ?>"><span
                    class="brand-mark">P</span><span>Parkir<small>Panel
                        operasional</small></span></a>
            <p class="nav-label">OPERASIONAL</p>
            <nav aria-label="Navigasi utama">
                <?php foreach (
                [
                    ["dashboard", "dashboard", "Dashboard"],
                    ["parkir.masuk", "parkir/masuk", "Kendaraan masuk"],
                    ["parkir.aktif", "parkir/aktif", "Parkir aktif"],
                    ["riwayat", "riwayat", "Riwayat parkir"],
                ]
                as [$route, $path, $label]
            ): ?>
                <a class="nav-item <?= nav_aktif($path) ?>" href="<?= url_to($route) ?>"
                    <?= nav_aktif($path) ? 'aria-current="page"' : "" ?>><?= esc($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <?php if (session("user.role") === "admin"): ?>
            <p class="nav-label">ADMINISTRASI</p>
            <nav aria-label="Navigasi admin">
                <?php foreach (
                    [
                        ["laporan", "admin/laporan", "Laporan pendapatan"],
                        ["tarif", "admin/tarif", "Tarif parkir"],
                        ["slot", "admin/slot", "Slot parkir"],
                        ["petugas", "admin/petugas", "Akun petugas"],
                    ]
                    as [$route, $path, $label]
                ): ?>
                <a class="nav-item <?= nav_aktif($path) ?>" href="<?= url_to($route) ?>"
                    <?= nav_aktif($path) ? 'aria-current="page"' : "" ?>><?= esc($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
            <div class="sidebar-account">
                <span class="account-avatar"
                    aria-hidden="true"><?= esc(mb_substr(session("user.nama") ?? "", 0, 1)) ?></span>
                <div>
                    <strong><?= esc(session("user.nama")) ?></strong><small><?= esc(ucfirst(session("user.role") ?? "")) ?></small>
                </div>
                <form action="<?= url_to("logout") ?>" method="post"><?= csrf_field() ?><button
                        class="logout-button" type="submit">Keluar</button></form>
            </div>
        </div>
    </aside>
    <main class="main-content" id="konten" tabindex="-1">
        <div class="topbar no-print"><span>Sistem Manajemen Parkir</span><span>Waktu Indonesia Barat
                · IDR</span></div>
        <?= $this->include("partials/alerts") ?>
        <?= $this->renderSection("content") ?>
        <footer class="page-footer no-print">Parkir · Satu lokasi, semua aktivitas tercatat.
        </footer>
    </main>
</body>
</html>
