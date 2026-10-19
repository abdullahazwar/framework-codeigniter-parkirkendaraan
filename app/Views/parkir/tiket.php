<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading no-print">
    <div>
        <p class="eyebrow">DOKUMEN PARKIR</p>
        <h1><?= esc($title) ?></h1>
        <p><?= $bayar ? "Pembayaran lunas. Kendaraan sudah keluar." : "Simpan tiket ini sampai kendaraan keluar." ?>
        </p>
    </div>
    <div class="button-row"><button class="btn btn-primary" type="button" data-print>Cetak
            <?= $bayar ? "bukti" : "tiket" ?></button><a class="btn btn-outline-secondary"
            href="<?= url_to("parkir.masuk") ?>">Kendaraan berikutnya</a></div>
</div>
<article class="receipt panel">
    <div class="receipt-heading"><span class="brand-mark">P</span>
        <h2>PARKIR</h2>
        <p><?= $bayar ? "BUKTI PEMBAYARAN TUNAI" : "TIKET KENDARAAN MASUK" ?></p>
    </div>
    <p class="receipt-plate"><?= esc($tiket["plat_nomor"]) ?></p>
    <p class="receipt-code"><?= esc($tiket["kode_tiket"]) ?></p>
    <dl class="detail-list">
        <div>
            <dt>Kendaraan</dt>
            <dd><?= esc(ucfirst($tiket["jenis"])) ?></dd>
        </div>
        <div>
            <dt>Area / slot</dt>
            <dd><?= esc($tiket["area"] . " / " . $tiket["kode_slot"]) ?></dd>
        </div>
        <div>
            <dt>Masuk</dt>
            <dd><?= esc(waktu($tiket["waktu_masuk"])) ?></dd>
        </div>
        <div>
            <dt>Petugas masuk</dt>
            <dd><?= esc($tiket["petugas_masuk"]) ?></dd>
        </div>
        <?php if ($bayar): ?><div>
            <dt>Keluar / bayar</dt>
            <dd><?= esc(waktu($bayar["waktu_bayar"])) ?></dd>
        </div>
        <div>
            <dt>Durasi</dt>
            <dd><?= esc(durasi((int) $bayar["durasi_menit"])) ?></dd>
        </div>
        <div>
            <dt>Petugas keluar</dt>
            <dd><?= esc($bayar["petugas_keluar"]) ?></dd>
        </div>
        <div class="receipt-total">
            <dt>Total</dt>
            <dd><?= rupiah($bayar["total"]) ?></dd>
        </div>
        <div>
            <dt>Uang tunai</dt>
            <dd><?= rupiah($bayar["uang_diterima"]) ?></dd>
        </div>
        <div>
            <dt>Kembalian</dt>
            <dd><?= rupiah($bayar["kembalian"]) ?></dd>
        </div>
        <?php else: ?><div>
            <dt>Jam pertama</dt>
            <dd><?= rupiah($tiket["tarif_awal_snapshot"]) ?></dd>
        </div>
        <div>
            <dt>Jam berikutnya</dt>
            <dd><?= rupiah($tiket["tarif_lanjutan_snapshot"]) ?></dd>
        </div><?php endif; ?>
    </dl>
    <p class="receipt-footnote">
        <?= $bayar ? "LUNAS · Terima kasih atas kunjungannya." : "Minimal 1 jam. Durasi dibulatkan ke atas. Tunjukkan kode tiket saat keluar." ?>
    </p>
    <?php if (!$bayar): ?><a class="btn btn-outline-primary w-100 no-print"
        href="<?= url_to("parkir.keluar", $tiket["id"]) ?>">Proses kendaraan
        keluar</a><?php endif; ?>
</article>
<?= $this->endSection() ?>
