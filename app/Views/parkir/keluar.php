<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">KENDARAAN KELUAR</p>
        <h1>Periksa &amp; terima pembayaran</h1>
        <p>Tagihan dihitung ulang saat pembayaran dikonfirmasi.</p>
    </div><a class="btn btn-outline-secondary" href="<?= url_to("parkir.aktif") ?>">Kembali ke
        parkir aktif</a>
</div>
<div class="form-columns">
    <section class="panel form-panel"><span class="badge-soft">Tiket aktif</span>
        <h2 class="checkout-plate"><?= esc($tiket["plat_nomor"]) ?></h2>
        <p class="ticket-code text-muted"><?= esc($tiket["kode_tiket"]) ?></p>
        <dl class="detail-list">
            <div>
                <dt>Jenis kendaraan</dt>
                <dd><?= esc(ucfirst($tiket["jenis"])) ?></dd>
            </div>
            <div>
                <dt>Slot parkir</dt>
                <dd><?= esc($tiket["area"] . " · " . $tiket["kode_slot"]) ?></dd>
            </div>
            <div>
                <dt>Waktu masuk</dt>
                <dd><?= esc(waktu($tiket["waktu_masuk"])) ?></dd>
            </div>
            <div>
                <dt>Waktu perhitungan</dt>
                <dd><?= esc(waktu($waktu_hitung)) ?></dd>
            </div>
            <div>
                <dt>Durasi parkir</dt>
                <dd><?= esc(durasi($durasi_menit)) ?></dd>
            </div>
            <div>
                <dt>Jam yang ditagihkan</dt>
                <dd><?= (int) $jam_tagihan ?> jam</dd>
            </div>
            <div>
                <dt>Tarif jam pertama</dt>
                <dd><?= rupiah($tiket["tarif_awal_snapshot"]) ?></dd>
            </div>
            <div>
                <dt>Tarif jam berikutnya</dt>
                <dd><?= rupiah($tiket["tarif_lanjutan_snapshot"]) ?></dd>
            </div>
        </dl>
        <a href="<?= url_to("parkir.tiket", $tiket["id"]) ?>">Lihat tiket masuk</a>
    </section>
    <section class="panel form-panel">
        <p class="eyebrow">TOTAL PEMBAYARAN</p>
        <p class="checkout-total"><?= rupiah($total) ?></p>
        <p class="text-muted">Tunai · Minimal satu jam, pembulatan ke atas.</p>
        <form method="post" action="<?= url_to("parkir.bayar", $tiket["id"]) ?>" data-submit-lock
            data-checkout data-total="<?= (int) $total ?>">
            <?= csrf_field() ?><input type="hidden" name="total_disetujui"
                value="<?= (int) $total ?>">
            <div class="mb-3"><label class="form-label" for="uang_diterima">Uang tunai diterima
                    (Rp)</label><input class="form-control" id="uang_diterima" name="uang_diterima"
                    type="number" min="<?= (int) $total ?>" max="9000000000000000" step="1"
                    inputmode="numeric" required value="<?= esc(old("uang_diterima") ?? "") ?>">
            </div>
            <div class="change-preview mb-4"><span>Estimasi kembalian</span><output id="kembalian"
                    for="uang_diterima" aria-live="polite">Rp0</output></div>
            <div class="form-check mb-4"><input class="form-check-input" id="konfirmasi"
                    name="konfirmasi" type="checkbox" value="1" required><label
                    class="form-check-label" for="konfirmasi">Uang sudah diterima dan kendaraan siap
                    keluar.</label></div>
            <button class="btn btn-primary w-100" type="submit">Konfirmasi pembayaran &amp;
                keluar</button>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
