<?= $this->extend('layouts/pengguna') ?>
<?= $this->section('content') ?>
<header class="member-heading">
    <div><p class="eyebrow">PARKIR SAYA</p><h1>Mulai kunjungan parkir.</h1>
        <p>Ajukan saat sudah tiba di pintu masuk, lalu tunjukkan kode kepada petugas.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url_to('pengguna.parkir') ?>">Perbarui status</a>
</header>
<div class="member-columns">
    <section class="member-surface" aria-labelledby="start-title">
        <h2 id="start-title">Ajukan masuk</h2>
        <?php if (!$kendaraan): ?>
        <p>Tambahkan kendaraan terlebih dahulu sebelum mengajukan parkir.</p>
        <a class="btn btn-primary" href="<?= url_to('pengguna.kendaraan') ?>">Tambah kendaraan</a>
        <?php else: ?>
        <form action="<?= url_to('pengguna.ajukan') ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-4"><label class="form-label" for="kendaraan_id">Pilih kendaraan</label>
                <select class="form-select" id="kendaraan_id" name="kendaraan_id" required>
                    <option value="">Pilih plat kendaraan</option>
                    <?php foreach ($kendaraan as $vehicle): ?>
                    <option value="<?= (int) $vehicle['id'] ?>" <?= $vehicle['permintaan_id'] ? 'disabled' : '' ?>>
                        <?= esc($vehicle['plat_nomor'] . ' · ' . ucfirst($vehicle['jenis'])) ?>
                        <?= $vehicle['permintaan_id'] ? ' (masih ada permintaan)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select></div>
            <div class="member-notice mb-4">Permintaan belum mengunci slot. Petugas memeriksa
                kendaraan, ketersediaan tempat, dan tarif saat mengonfirmasi masuk.</div>
            <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="sudah_tiba"
                    name="sudah_tiba" value="1" required>
                <label class="form-check-label" for="sudah_tiba">Saya sudah tiba di pintu masuk dengan kendaraan yang dipilih.</label></div>
            <button class="btn btn-primary w-100" type="submit">Ajukan masuk parkir</button>
        </form>
        <?php endif; ?>
    </section>
    <aside class="member-surface" aria-labelledby="availability-title">
        <h2 id="availability-title">Ketersediaan saat ini</h2>
        <?php foreach ($slots as $slot): ?>
        <div class="member-availability"><span><?= esc(ucfirst($slot['jenis'])) ?></span>
            <strong><?= max(0, (int) $slot['kapasitas'] - (int) $slot['terisi']) ?> slot kosong</strong></div>
        <?php endforeach; ?>
        <?php if (!$slots): ?><p>Belum ada slot aktif.</p><?php endif; ?>
        <p class="form-text">Diperiksa saat halaman dimuat. Slot diperiksa ulang oleh petugas saat masuk.</p>
        <h3 class="member-subtitle">Tarif saat ini</h3>
        <?php foreach ($tarif as $rate): ?>
        <div class="member-rate"><strong><?= esc(ucfirst($rate['jenis'])) ?></strong>
            <span><?= rupiah($rate['tarif_awal']) ?> jam pertama</span>
            <small><?= rupiah($rate['tarif_lanjutan']) ?> setiap jam berikutnya</small></div>
        <?php endforeach; ?>
        <p class="form-text mb-0">Minimal 1 jam, dibulatkan ke atas. Pembayaran tunai di pintu keluar.</p>
    </aside>
</div>
<section class="member-section" aria-labelledby="open-title">
    <div class="member-section-heading"><h2 id="open-title">Permintaan & parkir terbuka <span class="member-count"><?= (int) $total ?></span></h2></div>
    <?php if (!$rows): ?><div class="member-empty compact-empty"><p>Belum ada permintaan atau parkir aktif.</p></div><?php endif; ?>
    <div class="member-visits">
        <?php foreach ($rows as $row): ?><?= view('pengguna/_permintaan', ['row' => $row]) ?><?php endforeach; ?>
    </div>
    <?= $pagination ?>
</section>
<?= $this->endSection() ?>
