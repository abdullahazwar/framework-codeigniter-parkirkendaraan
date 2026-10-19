<?= $this->extend('layouts/pengguna') ?>
<?= $this->section('content') ?>
<header class="member-heading no-print">
    <div><p class="eyebrow">KUNJUNGAN SAYA</p><h1><?= esc($title) ?></h1>
        <p>Informasi kunjungan untuk <?= esc($row['plat_nomor']) ?>.</p></div>
    <div class="member-actions">
        <a class="btn btn-outline-secondary" href="<?= url_to('pengguna.tiket', $row['id']) ?>">Perbarui status</a>
        <?php if ($row['tiket_id']): ?>
        <button class="btn btn-primary" type="button" data-print>
            <?= $row['status'] === 'selesai' ? 'Cetak bukti bayar' : 'Cetak tiket' ?>
        </button>
        <?php endif; ?>
    </div>
</header>
<?php if (in_array($row['status'], ['menunggu', 'aktif', 'minta_keluar', 'selesai'], true)): ?>
<?php $currentStep = ['menunggu' => 2, 'aktif' => 3, 'minta_keluar' => 4, 'selesai' => 5][$row['status']]; ?>
<ol class="member-progress no-print" aria-label="Tahapan kunjungan">
    <?php foreach (['Permintaan tercatat', 'Verifikasi masuk', 'Sedang parkir', 'Bayar & keluar'] as $index => $label): ?>
    <li class="<?= $index + 1 < $currentStep ? 'step-complete' : '' ?>"
        <?= $index + 1 === $currentStep ? 'aria-current="step"' : '' ?>>
        <span aria-hidden="true"><?= $index + 1 < $currentStep ? '✓' : $index + 1 ?></span><?= esc($label) ?>
    </li>
    <?php endforeach; ?>
</ol>
<?php endif; ?>
<div class="member-ticket-grid">
    <article class="member-surface member-ticket">
        <div class="visit-heading"><div><p class="eyebrow">PARKIR · <?= $row['tiket_id'] ? 'TIKET KENDARAAN' : 'PERMINTAAN MASUK' ?></p>
                <h2 class="ticket-plate"><?= esc($row['plat_nomor']) ?></h2></div>
            <span class="member-status <?= warna_status_pengguna($row['status']) ?>">
                <?= esc(status_pengguna($row['status'])) ?>
            </span></div>
        <div class="member-ticket-code"><span><?= $row['tiket_id'] ? 'Kode tiket' : 'Kode permintaan' ?></span>
            <strong><?= esc($row['kode_tiket'] ?? $row['kode_permintaan']) ?></strong>
            <small>Tunjukkan kode ini kepada petugas di lokasi.</small></div>
        <dl class="member-ticket-details">
            <div><dt>Jenis kendaraan</dt><dd><?= esc(ucfirst($row['jenis'])) ?></dd></div>
            <div><dt>Permintaan diajukan</dt><dd><?= esc(waktu($row['created_at'])) ?></dd></div>
            <?php if ($row['tiket_id']): ?>
            <div><dt>Waktu masuk</dt><dd><?= esc(waktu($row['waktu_masuk'])) ?></dd></div>
            <div><dt>Area & slot</dt><dd><?= esc($row['area'] . ' · ' . $row['kode_slot']) ?></dd></div>
            <div><dt>Tarif jam pertama</dt><dd><?= rupiah($row['tarif_awal_snapshot']) ?></dd></div>
            <div><dt>Tarif jam lanjutan</dt><dd><?= rupiah($row['tarif_lanjutan_snapshot']) ?></dd></div>
            <?php endif; ?>
            <?php if ($row['status'] === 'selesai'): ?>
            <div><dt>Waktu keluar & bayar</dt><dd><?= esc(waktu($row['waktu_bayar'])) ?></dd></div>
            <div><dt>Durasi parkir</dt><dd><?= esc(durasi((int) $row['durasi_menit'])) ?></dd></div>
            <div><dt>Pembayaran</dt><dd>Tunai · Lunas</dd></div>
            <div class="paid-total"><dt>Total dibayar</dt><dd><?= rupiah($row['total_bayar']) ?></dd></div>
            <div><dt>Uang diterima</dt><dd><?= rupiah($row['uang_diterima']) ?></dd></div>
            <div><dt>Kembalian</dt><dd><?= rupiah($row['kembalian']) ?></dd></div>
            <?php endif; ?>
        </dl>
        <?php if ($row['status'] === 'selesai'): ?>
        <p class="member-receipt-note">LUNAS · Pembayaran dikonfirmasi petugas. Terima kasih atas kunjungannya.</p>
        <?php endif; ?>
    </article>
    <aside class="member-surface member-ticket-side no-print">
        <?php if ($row['status'] === 'menunggu'): ?>
        <p class="eyebrow">LANGKAH BERIKUTNYA</p><h2>Tunjukkan kode di pintu masuk.</h2>
        <p>Petugas mencocokkan kode dan plat kendaraan. Tiket serta lokasi parkir muncul setelah masuk dikonfirmasi.</p>
        <p class="member-notice">Durasi dan biaya belum berjalan. Slot belum dialokasikan.</p>
        <form action="<?= url_to('pengguna.batal', $row['id']) ?>" method="post" data-submit-lock
            data-confirm="Batalkan permintaan parkir untuk <?= esc($row['plat_nomor']) ?>?">
            <?= csrf_field() ?><button class="btn btn-outline-secondary w-100" type="submit">Batalkan permintaan</button>
        </form>
        <?php elseif (in_array($row['status'], ['aktif', 'minta_keluar'], true)): ?>
        <p class="eyebrow">ESTIMASI TAGIHAN</p>
        <?php if ($tagihan): ?>
        <p class="member-bill"><?= rupiah($tagihan['total']) ?></p>
        <dl class="visit-details">
            <div><dt>Durasi saat dihitung</dt><dd><?= esc(durasi($tagihan['durasi_menit'])) ?></dd></div>
            <div><dt>Jam yang ditagihkan</dt><dd><?= (int) $tagihan['jam_tagihan'] ?> jam</dd></div>
            <div><dt>Perhitungan terakhir</dt><dd><?= esc(waktu($tagihan['waktu_hitung'])) ?></dd></div>
        </dl>
        <?php else: ?><p>Tagihan sedang diperbarui. Perbarui status atau hubungi petugas.</p><?php endif; ?>
        <p class="form-text">Minimal 1 jam, dibulatkan ke atas. Biaya tetap berjalan sampai petugas mengonfirmasi pembayaran.</p>
        <?php if ($row['status'] === 'aktif'): ?>
        <form action="<?= url_to('pengguna.keluar', $row['id']) ?>" method="post" data-submit-lock>
            <?= csrf_field() ?><button class="btn btn-primary w-100" type="submit">Minta keluar & bayar tunai</button>
        </form>
        <?php else: ?>
        <div class="member-notice"><strong>Permintaan keluar terkirim.</strong>
            Tunjukkan tiket ini dan bayar tunai kepada petugas. Status menjadi Selesai setelah pembayaran dikonfirmasi.</div>
        <?php endif; ?>
        <?php elseif ($row['status'] === 'selesai'): ?>
        <p class="eyebrow">KUNJUNGAN SELESAI</p><h2>Bukti bayar sudah tersimpan.</h2>
        <p>Buka kembali bukti ini melalui Riwayat. Slot sudah dilepas oleh sistem.</p>
        <a class="btn btn-primary w-100" href="<?= url_to('pengguna.riwayat') ?>">Lihat riwayat parkir</a>
        <?php else: ?>
        <p class="eyebrow">PERMINTAAN DITUTUP</p><h2><?= esc(status_pengguna($row['status'])) ?></h2>
        <?php if ($row['alasan_penolakan']): ?><p><?= esc($row['alasan_penolakan']) ?></p><?php endif; ?>
        <p>Permintaan ini tidak menerbitkan tiket atau biaya parkir. Pengajuan baru tersedia melalui Parkir saya.</p>
        <a class="btn btn-primary w-100" href="<?= url_to('pengguna.parkir') ?>">Ajukan kunjungan baru</a>
        <?php endif; ?>
    </aside>
</div>
<?= $this->endSection() ?>
