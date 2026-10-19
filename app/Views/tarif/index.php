<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">ADMINISTRASI</p>
        <h1>Tarif parkir</h1>
        <p>Tarif baru berlaku untuk kendaraan yang masuk setelah perubahan.</p>
    </div>
</div>
<div class="info-note mb-4">Semua nominal dalam rupiah. Minimal 1 jam, kelebihan waktu dibulatkan ke
    atas. Tiket aktif dan histori menyimpan tarif saat kendaraan masuk.</div>
<div class="rate-grid">
    <?php foreach ($rows as $row): ?>
    <section class="panel form-panel">
        <p class="eyebrow"><?= $row["jenis"] === "motor" ? "TARIF PER JAM" : "TARIF BERTINGKAT" ?>
        </p>
        <h2><?= esc(ucfirst($row["jenis"])) ?></h2>
        <form method="post" action="<?= url_to("tarif.update", $row["jenis"]) ?>" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-3"><label class="form-label"
                    for="awal-<?= esc($row["jenis"]) ?>"><?= $row["jenis"] === "motor" ? "Tarif setiap jam (Rp)" : "Jam pertama (Rp)" ?></label><input
                    class="form-control" id="awal-<?= esc($row["jenis"]) ?>" type="number"
                    name="tarif_awal" min="0" max="1000000000" step="1" required
                    value="<?= (int) $row["tarif_awal"] ?>"></div>
            <?php if ( $row["jenis"] === "mobil" ): ?><div class="mb-3"><label class="form-label"
                    for="lanjutan-mobil">Setiap jam
                    berikutnya (Rp)</label><input class="form-control" id="lanjutan-mobil"
                    type="number" name="tarif_lanjutan" min="0" max="1000000000" step="1" required
                    value="<?= (int) $row["tarif_lanjutan"] ?>"></div>
            <?php else: ?><input type="hidden" name="tarif_lanjutan"
                value="<?= (int) $row["tarif_lanjutan"] ?>">
            <p class="form-text">Tarif motor sama untuk jam pertama dan jam berikutnya.</p>
            <?php endif; ?>
            <p class="form-text">Diperbarui <?= esc(waktu($row["updated_at"])) ?></p>
            <button class="btn btn-primary" type="submit">Simpan tarif
                <?= esc($row["jenis"]) ?></button>
        </form>
    </section>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
