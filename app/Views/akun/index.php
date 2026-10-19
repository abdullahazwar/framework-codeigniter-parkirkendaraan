<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<div class="public-container account-content">
    <?= $this->include('partials/alerts') ?>
    <header class="account-heading">
        <p class="eyebrow">AKUN SAYA</p>
        <h1>Halo, <?= esc($user['nama']) ?>.</h1>
        <p>Informasi akun dan tarif parkir yang berlaku saat ini.</p>
    </header>
    <div class="account-grid">
        <section class="account-profile" aria-labelledby="account-title">
            <h2 id="account-title">Profil akun</h2>
            <dl class="account-details">
                <div><dt>Nama lengkap</dt><dd><?= esc($user['nama']) ?></dd></div>
                <div><dt>Username</dt><dd><?= esc($user['username']) ?></dd></div>
                <div><dt>Peran akun</dt><dd><?= esc(ucfirst($user['role'])) ?></dd></div>
                <div><dt>Terdaftar sejak</dt><dd><?= date('d/m/Y', strtotime($user['created_at'])) ?></dd></div>
            </dl>
            <?php if ($user['role'] !== 'pengguna'): ?>
            <a class="btn btn-primary" href="<?= url_to('dashboard') ?>">Buka panel operasional</a>
            <?php endif; ?>
        </section>
        <section class="account-rates" aria-labelledby="rates-title">
            <p class="eyebrow">INFORMASI LAYANAN</p>
            <h2 id="rates-title">Tarif parkir saat ini</h2>
            <?php if (!$tarif): ?>
            <p>Tarif belum tersedia. Hubungi petugas di lokasi.</p>
            <?php else: ?>
            <div class="rate-list">
                <?php foreach ($tarif as $row): ?>
                <article class="rate-item">
                    <h3><?= esc(ucfirst($row['jenis'])) ?></h3>
                    <p><strong><?= rupiah($row['tarif_awal']) ?></strong>
                        <span><?= $row['jenis'] === 'motor' ? '/ jam' : '/ jam pertama' ?></span></p>
                    <?php if ($row['jenis'] === 'mobil'): ?>
                    <small><?= rupiah($row['tarif_lanjutan']) ?> setiap jam berikutnya.</small>
                    <?php else: ?>
                    <small>Tarif yang sama untuk setiap jam berikutnya.</small>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
            <p class="rate-note">Minimal satu jam, dibulatkan ke atas. Tagihan mengikuti tarif
                yang tercatat saat kendaraan masuk. Pembayaran tunai melalui petugas.</p>
            <?php endif; ?>
            <a class="text-link" href="<?= url_to('beranda') ?>#alur">Lihat alur parkir <span aria-hidden="true">↗</span></a>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
