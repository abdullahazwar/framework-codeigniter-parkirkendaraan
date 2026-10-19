<?php
$currentPath = trim(service('request')->getUri()->getRoutePath(), '/');
$accountRoute = session('user.role') === 'pengguna' ? 'akun' : 'dashboard';
?>
<header class="public-header">
    <div class="public-container header-inner">
        <a class="brand public-brand" href="<?= url_to('beranda') ?>" aria-label="Parkir, beranda">
            <span class="brand-mark" aria-hidden="true">P</span><span>Parkir</span>
        </a>
        <nav class="public-nav" id="public-navigation" aria-label="Navigasi utama">
            <a href="<?= url_to('beranda') ?>#tentang">Tentang</a>
            <a href="<?= url_to('beranda') ?>#keunggulan">Keunggulan</a>
            <a href="<?= url_to('beranda') ?>#alur">Alur parkir</a>
            <a href="<?= url_to('beranda') ?>#faq">FAQ</a>
        </nav>
        <div class="header-actions">
            <?php if (session('user')): ?>
            <a class="header-login" href="<?= url_to($accountRoute) ?>">Akun saya</a>
            <form action="<?= url_to('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary" type="submit">Keluar</button>
            </form>
            <?php else: ?>
            <a class="header-login" href="<?= url_to('login') ?>"
                <?= $currentPath === 'login' ? 'aria-current="page"' : '' ?>>Masuk</a>
            <a class="btn btn-primary" href="<?= url_to('daftar') ?>"
                <?= $currentPath === 'daftar' ? 'aria-current="page"' : '' ?>>Daftar</a>
            <?php endif; ?>
            <button class="nav-toggle" type="button" aria-expanded="false"
                aria-controls="public-navigation" aria-label="Buka navigasi" hidden>
                <span class="nav-toggle-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>
