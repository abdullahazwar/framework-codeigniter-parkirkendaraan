<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">OVERVIEW · <?= esc(date("d F Y")) ?></p>
        <h1>Aktivitas parkir hari ini</h1>
        <p>Ringkasan operasional dan pembayaran pada <?= esc(date("d/m/Y")) ?>, WIB.</p>
    </div><a class="btn btn-primary" href="<?= url_to("parkir.masuk") ?>">+ Kendaraan masuk</a>
</div>
<div class="stats-grid">
    <article class="stat-card stat-accent"><span>Pendapatan hari
            ini</span><strong><?= rupiah($summary["pendapatan"]) ?></strong><small>Dari pembayaran
            tunai yang lunas</small></article>
    <article class="stat-card"><span>Kendaraan masuk</span><strong><?= (int) $masuk ?><small>
                kendaraan</small></strong><small>Tiket baru hari ini</small></article>
    <article class="stat-card"><span>Sedang
            parkir</span><strong><?= array_sum(array_column($slots, "terisi")) ?><small>
                kendaraan</small></strong><small>Semua tiket berstatus aktif</small></article>
    <article class="stat-card"><span>Transaksi
            selesai</span><strong><?= (int) $summary["transaksi"] ?><small>
                transaksi</small></strong><small>Pembayaran tercatat hari ini</small></article>
</div>
<div class="dashboard-columns">
    <section class="panel">
        <div class="panel-header">
            <h2>Ketersediaan slot</h2><?php if ( session("user.role") === "admin" ): ?><a
                href="<?= url_to("slot") ?>">Kelola slot</a><?php endif; ?>
        </div>
        <?php if ( !$slots ): ?><div class="empty-state">Belum ada slot aktif. Hubungi admin untuk
            menambahkan slot.
        </div><?php endif; ?>
        <?php foreach ($slots as $slot): $kosong = (int) $slot["kapasitas"] - (int) $slot["terisi"]; ?>
        <div class="capacity-row">
            <div class="capacity-title"><strong><?= esc(ucfirst($slot["jenis"])) ?></strong><span
                    class="badge-soft <?= $kosong ? "success" : "danger" ?>"><?= $kosong ?>
                    tersedia</span></div><meter min="0" max="<?= (int) $slot["kapasitas"] ?>"
                value="<?= (int) $slot["terisi"] ?>"
                aria-label="Slot <?= esc($slot["jenis"]) ?> terisi"><?= (int) $slot["terisi"] ?>
                terisi</meter>
            <p><?= (int) $slot["terisi"] ?> terisi dari <?= (int) $slot["kapasitas"] ?> slot</p>
        </div>
        <?php endforeach; ?>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h2>Pendapatan 7 hari</h2><span class="text-muted small">IDR · Lunas</span>
        </div>
        <div class="weekly-list">
            <?php $byDate = array_column($harian, null, "tanggal"); for ($i = 6; $i >= 0; $i--): $tgl = date("Y-m-d", strtotime("-" . $i . " days")); ?>
            <div>
                <span><?= esc(date("d/m", strtotime($tgl))) . ($i === 0 ? " · Hari ini" : "") ?></span><strong><?= rupiah($byDate[$tgl]["pendapatan"] ?? 0) ?></strong>
            </div>
            <?php endfor; ?>
        </div>
    </section>
</div>
<section class="panel mt-4">
    <div class="panel-header">
        <div>
            <h2>Kendaraan terakhir masuk</h2>
            <p>Tiket aktif, diurutkan dari yang terbaru.</p>
        </div><a href="<?= url_to("parkir.aktif") ?>">Lihat semua</a>
    </div>
    <?= view("parkir/_table", ["rows" => $recent, "riwayat" => false]) ?>
</section>
<?= $this->endSection() ?>
