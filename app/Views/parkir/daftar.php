<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">OPERASIONAL</p>
        <h1><?= esc($title) ?></h1>
        <p><?= $riwayat ? "Telusuri kunjungan yang sudah selesai dan bukti pembayaran." : "Cari tiket atau plat untuk melanjutkan ke pembayaran." ?>
        </p>
    </div><a class="btn btn-primary" href="<?= url_to("parkir.masuk") ?>">+ Kendaraan masuk</a>
</div>
<section class="panel">
    <form class="filter-bar" method="get"
        action="<?= $riwayat ? url_to("riwayat") : url_to("parkir.aktif") ?>">
        <div class="filter-search"><label class="form-label" for="q">Cari tiket atau
                plat</label><input class="form-control" type="search" id="q" name="q"
                maxlength="100" value="<?= esc($q) ?>" placeholder="Kode tiket atau BE1234AB">
        </div>
        <div><label class="form-label" for="jenis">Jenis kendaraan</label><select
                class="form-select" id="jenis" name="jenis">
                <option value="">Semua jenis</option value="motor"
                    <?= $jenis === "motor" ? "selected" : "" ?>>Motor</option>
                <option value="mobil" <?= $jenis === "mobil" ? "selected" : "" ?>>Mobil</option>
            </select></div>
        <button class="btn btn-primary" type="submit">Cari</button><a
            class="btn btn-outline-secondary"
            href="<?= $riwayat ? url_to("riwayat") : url_to("parkir.aktif") ?>">Reset</a>
    </form>
    <div class="result-count"><?= (int) $total ?> tiket ditemukan</div>
    <?= $this->include("parkir/_table") ?>
    <div class="pagination-wrap"><?= $pagination ?></div>
</section>
<?= $this->endSection() ?>
