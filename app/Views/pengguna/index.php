<?= $this->extend('layouts/pengguna') ?>
<?= $this->section('content') ?>
<header class="member-heading">
    <div>
        <p class="eyebrow">AREA PENGGUNA</p>
        <h1>Halo, <?= esc($user['nama']) ?>.</h1>
        <p>Kelola kendaraan, tunjukkan tiket, dan lihat riwayat parkir dalam satu tempat.</p>
    </div>
    <a class="btn btn-primary" href="<?= url_to('pengguna.parkir') ?>">Mulai parkir <span aria-hidden="true">↗</span></a>
</header>
<div class="member-summary" aria-label="Ringkasan akun">
    <div><span>Kendaraan tersimpan</span><strong><?= (int) $kendaraanCount ?></strong>
        <a href="<?= url_to('pengguna.kendaraan') ?>">Kelola kendaraan</a></div>
    <div><span>Sedang parkir</span><strong><?= (int) $parkirCount ?></strong>
        <small>Sudah dikonfirmasi masuk</small></div>
    <div><span>Kunjungan selesai</span><strong><?= (int) $selesaiCount ?></strong>
        <a href="<?= url_to('pengguna.riwayat') ?>">Lihat riwayat</a></div>
</div>
<section class="member-section" aria-labelledby="current-title">
    <div class="member-section-heading">
        <div><p class="eyebrow">AKTIVITAS SAAT INI</p><h2 id="current-title">Parkir & permintaan terbuka</h2></div>
        <a class="text-link" href="<?= url_to('pengguna.parkir') ?>">Lihat semua</a>
    </div>
    <?php if ($terbuka): ?>
    <div class="member-visits">
        <?php foreach ($terbuka as $row): ?>
        <?= view('pengguna/_permintaan', ['row' => $row]) ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="member-empty">
        <span class="empty-parking-mark" aria-hidden="true">P</span>
        <div><h3>Belum ada parkir aktif</h3>
            <p>Tambahkan kendaraan, lalu ajukan masuk saat sudah tiba di lokasi.</p>
            <a class="text-link" href="<?= url_to($kendaraanCount ? 'pengguna.parkir' : 'pengguna.kendaraan') ?>">
                <?= $kendaraanCount ? 'Ajukan parkir' : 'Tambahkan kendaraan pertama' ?> <span aria-hidden="true">↗</span>
            </a></div>
    </div>
    <?php endif; ?>
</section>
<section class="member-section" aria-labelledby="how-title">
    <div class="member-section-heading"><h2 id="how-title">Alur parkir pengguna</h2></div>
    <ol class="member-workflow">
        <li><span aria-hidden="true">1</span><div><h3>Tambahkan kendaraan</h3><p>Simpan plat dan jenis kendaraan.</p></div></li>
        <li><span aria-hidden="true">2</span><div><h3>Ajukan di pintu masuk</h3><p>Petugas memeriksa kode, lalu menerbitkan tiket.</p></div></li>
        <li><span aria-hidden="true">3</span><div><h3>Minta keluar & bayar</h3><p>Bayar tunai kepada petugas. Bukti tersimpan di riwayat.</p></div></li>
    </ol>
</section>
<?php if ($recent): ?>
<section class="member-section" aria-labelledby="recent-title">
    <div class="member-section-heading"><h2 id="recent-title">Kunjungan terakhir</h2>
        <a class="text-link" href="<?= url_to('pengguna.riwayat') ?>">Semua riwayat</a></div>
    <div class="member-history-list">
        <?php foreach ($recent as $row): ?>
        <a class="member-history-row" href="<?= url_to('pengguna.tiket', $row['id']) ?>">
            <div><strong><?= esc($row['plat_nomor']) ?></strong><span><?= esc(waktu($row['waktu_bayar'])) ?></span></div>
            <div><strong><?= rupiah($row['total_bayar']) ?></strong><span>Lihat bukti bayar ↗</span></div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?= $this->endSection() ?>
