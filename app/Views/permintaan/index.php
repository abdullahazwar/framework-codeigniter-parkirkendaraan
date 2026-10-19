<?= $this->extend('layouts/app') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/permintaan.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><p class="eyebrow">LAYANAN PENGGUNA</p><h1>Permintaan pengguna</h1>
        <p>Cocokkan kode permintaan dan kendaraan di pintu masuk. Pembayaran tetap dikonfirmasi petugas.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url_to('permintaan') . '?status=' . $status ?>">Perbarui antrean</a></div>
<nav class="request-tabs" aria-label="Jenis permintaan">
    <a href="<?= url_to('permintaan') ?>" <?= $status === 'menunggu' ? 'aria-current="page"' : '' ?>>
        Menunggu masuk <strong><?= (int) $menungguCount ?></strong></a>
    <a href="<?= url_to('permintaan') ?>?status=minta_keluar" <?= $status === 'minta_keluar' ? 'aria-current="page"' : '' ?>>
        Minta keluar <strong><?= (int) $keluarCount ?></strong></a>
</nav>
<form class="panel request-search" action="<?= url_to('permintaan') ?>" method="get">
    <input type="hidden" name="status" value="<?= $status ?>">
    <div><label class="form-label" for="q">Cari plat, kode permintaan, atau nama pengguna</label>
        <input class="form-control" id="q" name="q" maxlength="80" value="<?= esc($q) ?>"></div>
    <button class="btn btn-primary" type="submit">Cari permintaan</button>
</form>
<p class="form-text"><?= (int) $total ?> permintaan ditampilkan sesuai filter. Urutan paling awal lebih dulu.</p>
<?php if (!$rows): ?><section class="panel form-panel"><h2>Belum ada permintaan</h2>
    <p class="text-muted mb-0">Permintaan pengguna akan muncul di antrean ini. Gunakan Perbarui antrean untuk memeriksa kembali.</p></section><?php endif; ?>
<div class="request-queue">
    <?php foreach ($rows as $row): ?>
    <article class="panel request-card">
        <div class="request-card-heading"><div><p class="eyebrow"><?= esc(ucfirst($row['jenis'])) ?></p>
                <h2><?= esc($row['plat_nomor']) ?></h2></div>
            <span class="badge-soft"><?= esc(status_pengguna($row['status'])) ?></span></div>
        <dl class="request-details">
            <div><dt>Pengguna</dt><dd><?= esc($row['nama_pengguna']) ?></dd></div>
            <div><dt>Kode permintaan</dt><dd class="ticket-code"><?= esc($row['kode_permintaan']) ?></dd></div>
            <div><dt><?= $status === 'minta_keluar' ? 'Meminta keluar' : 'Diajukan pada' ?></dt>
                <dd><?= esc(waktu($status === 'minta_keluar' ? $row['waktu_minta_keluar'] : $row['created_at'])) ?></dd></div>
        </dl>
        <?php if ($status === 'menunggu'): ?>
        <form action="<?= url_to('permintaan.terima', $row['id']) ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1"
                    id="masuk-<?= (int) $row['id'] ?>" name="konfirmasi_masuk" required>
                <label class="form-check-label" for="masuk-<?= (int) $row['id'] ?>">
                    Kendaraan sudah tiba. Plat dan kode sudah dicocokkan dengan pengguna.
                </label></div>
            <button class="btn btn-primary" type="submit">Konfirmasi masuk <?= esc($row['plat_nomor']) ?></button>
        </form>
        <details class="request-reject"><summary>Tolak permintaan ini</summary>
            <form action="<?= url_to('permintaan.tolak', $row['id']) ?>" method="post" data-submit-lock>
                <?= csrf_field() ?><label class="form-label" for="alasan-<?= (int) $row['id'] ?>">Alasan penolakan</label>
                <input class="form-control mb-3" id="alasan-<?= (int) $row['id'] ?>" name="alasan" required
                    maxlength="200" placeholder="Contoh: jenis kendaraan tidak sesuai">
                <button class="btn btn-outline-secondary" type="submit">Simpan penolakan</button>
            </form></details>
        <?php else: ?>
        <div class="info-note mb-3">Tiket <?= esc($row['kode_tiket']) ?> · <?= esc($row['area'] . ' / ' . $row['kode_slot']) ?>.
            Periksa tagihan dan terima uang tunai sebelum mengonfirmasi keluar.</div>
        <a class="btn btn-primary" href="<?= url_to('parkir.keluar', $row['tiket_id']) ?>">Proses pembayaran <?= esc($row['plat_nomor']) ?></a>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</div>
<?= $pagination ?>
<?= $this->endSection() ?>
