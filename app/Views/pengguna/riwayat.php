<?= $this->extend('layouts/pengguna') ?>
<?= $this->section('content') ?>
<header class="member-heading">
    <div><p class="eyebrow">RIWAYAT SAYA</p><h1>Kunjungan dan bukti pembayaran.</h1>
        <p><?= (int) $total ?> kunjungan yang selesai, dibatalkan, atau ditolak pada akun ini.</p></div>
    <a class="btn btn-primary" href="<?= url_to('pengguna.parkir') ?>">Mulai parkir lagi</a>
</header>
<?php if (!$rows): ?>
<div class="member-empty"><span class="empty-parking-mark" aria-hidden="true">P</span>
    <div><h2>Belum ada riwayat kunjungan</h2><p>Kunjungan yang sudah selesai akan tampil di sini.</p>
        <a class="text-link" href="<?= url_to('pengguna.parkir') ?>">Lihat parkir saat ini ↗</a></div></div>
<?php else: ?>
<div class="member-history-list">
    <?php foreach ($rows as $row): ?>
    <article class="member-history-entry">
        <div class="visit-heading"><div><p class="member-caption"><?= esc(ucfirst($row['jenis'])) ?></p>
                <h2 class="member-plate"><?= esc($row['plat_nomor']) ?></h2></div>
            <span class="member-status <?= warna_status_pengguna($row['status']) ?>"><?= esc(status_pengguna($row['status'])) ?></span></div>
        <div class="history-entry-details">
            <div><span><?= $row['waktu_bayar'] ? 'Selesai pada' : 'Diajukan pada' ?></span>
                <strong><?= esc(waktu($row['waktu_bayar'] ?? $row['created_at'])) ?></strong></div>
            <div><span><?= $row['total_bayar'] !== null ? 'Pembayaran tunai' : 'Biaya parkir' ?></span>
                <strong><?= $row['total_bayar'] !== null ? rupiah($row['total_bayar']) : 'Tidak ada tagihan' ?></strong></div>
            <a class="btn btn-outline-primary" href="<?= url_to('pengguna.tiket', $row['id']) ?>">
                <?= $row['status'] === 'selesai' ? 'Buka bukti bayar' : 'Lihat detail' ?>
            </a>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?= $pagination ?>
<?php endif; ?>
<?= $this->endSection() ?>
