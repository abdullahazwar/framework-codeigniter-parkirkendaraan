<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">OPERASIONAL</p>
        <h1>Kendaraan masuk</h1>
        <p>Slot dipilih otomatis sesuai jenis kendaraan.</p>
    </div><a class="btn btn-outline-secondary" href="<?= url_to("parkir.aktif") ?>">Lihat parkir
        aktif</a>
</div>
<div class="form-columns">
    <section class="panel form-panel">
        <h2>Data kendaraan</h2>
        <p class="text-muted">Pastikan plat sesuai dengan kendaraan yang masuk.</p>
        <form action="<?= url_to("parkir.simpan") ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-4"><label class="form-label" for="plat_nomor">Plat nomor</label><input
                    class="form-control plate-input" id="plat_nomor" name="plat_nomor" required
                    maxlength="16" autocomplete="off" placeholder="BE 1234 AB"
                    value="<?= esc(old("plat_nomor") ?? "") ?>" aria-describedby="plat-help"
                    autofocus>
                <div class="form-text" id="plat-help">Huruf dan angka. Spasi akan dirapikan
                    otomatis.</div>
            </div>
            <fieldset class="mb-4">
                <legend class="form-label">Jenis kendaraan</legend>
                <div class="vehicle-options">
                    <?php foreach (["motor" => "Motor", "mobil" => "Mobil"] as $value => $label): ?>
                    <label class="vehicle-option"><input class="form-check-input" type="radio"
                            name="jenis" value="<?= $value ?>" required
                            <?= (old("jenis") ?? "motor") === $value ? "checked" : "" ?>><span><?= $label ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <div class="info-note mb-4">Waktu masuk mengikuti jam server. Tarif pada tiket tetap
                berlaku sampai kendaraan keluar.</div>
            <button class="btn btn-primary w-100" type="submit">Simpan &amp; buat tiket</button>
        </form>
    </section>
    <aside>
        <section class="panel form-panel">
            <h2>Slot tersedia</h2>
            <?php foreach ($slots as $slot): ?><div class="detail-row">
                <span><?= esc(ucfirst($slot["jenis"])) ?></span><strong><?= (int) $slot["kapasitas"] - (int) $slot["terisi"] ?>
                    / <?= (int) $slot["kapasitas"] ?> kosong</strong></div><?php endforeach; ?>
            <?php if (!$slots): ?><p class="text-muted">Belum ada slot aktif.</p><?php endif; ?>
            <p class="form-text">Ketersediaan diperiksa ulang saat tiket disimpan.</p>
        </section>
        <section class="panel form-panel mt-3">
            <h2>Tarif saat ini</h2>
            <?php foreach ($tarif as $row): ?><div class="rate-summary">
                <strong><?= esc(ucfirst($row["jenis"])) ?></strong><span><?= rupiah($row["tarif_awal"]) ?>
                    jam pertama</span><small><?= rupiah($row["tarif_lanjutan"]) ?> setiap jam
                    berikutnya</small></div><?php endforeach; ?>
            <p class="form-text mb-0">Minimal 1 jam. Durasi berikutnya dibulatkan ke atas.</p>
        </section>
    </aside>
</div>
<?= $this->endSection() ?>
