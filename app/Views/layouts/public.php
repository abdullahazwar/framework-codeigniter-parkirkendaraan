<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="<?= esc($description ?? 'Informasi layanan parkir motor dan mobil, alur parkir, serta akun pengguna.') ?>">
    <?php if (!empty($privatePage)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <title><?= esc($title) ?> | Parkir</title>
    <link rel="icon" href="<?= base_url('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/public.css') ?>">
    <script src="<?= base_url('assets/js/public.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/parkir.js') ?>" defer></script>
</head>
<body class="public-page">
    <a class="skip-link" href="#konten">Lewati ke konten</a>
    <?= $this->include('partials/public_header') ?>
    <main id="konten" tabindex="-1">
        <?= $this->renderSection('content') ?>
    </main>
    <footer class="public-footer">
        <div class="public-container footer-inner">
            <div>
                <a class="brand public-brand" href="<?= url_to('beranda') ?>">
                    <span class="brand-mark" aria-hidden="true">P</span><span>Parkir</span>
                </a>
                <p>Sistem Informasi Manajemen Parkir.<br>Untuk motor, mobil, dan aktivitas yang tercatat.</p>
            </div>
            <nav class="footer-links" aria-label="Navigasi footer">
                <a href="<?= url_to('beranda') ?>#tentang">Tentang Parkir</a>
                <a href="<?= url_to('beranda') ?>#alur">Alur parkir</a>
                <a href="<?= url_to('beranda') ?>#faq">Pertanyaan umum</a>
            </nav>
        </div>
    </footer>
</body>
</html>
