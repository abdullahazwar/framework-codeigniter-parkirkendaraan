<?= $this->extend('layouts/pengguna') ?>
<?= $this->section('content') ?>
<header class="member-heading">
    <div><p class="eyebrow">KENDARAAN SAYA</p><h1>Kendaraan untuk kunjungan berikutnya.</h1>
        <p>Simpan plat sekali, lalu pilih kendaraan saat mengajukan parkir.</p></div>
</header>
<div class="member-columns">
    <section class="member-surface" aria-labelledby="vehicle-list-title">
        <h2 id="vehicle-list-title">Daftar kendaraan <span class="member-count"><?= count($kendaraan) ?></span></h2>
        <?php if (!$kendaraan): ?>
        <div class="member-empty compact-empty"><p>Belum ada kendaraan. Isi formulir tambah kendaraan untuk mulai.</p></div>
        <?php endif; ?>
        <?php foreach ($kendaraan as $row): ?>
        <article class="saved-vehicle">
            <div><p class="member-caption"><?= esc(ucfirst($row['jenis'])) ?></p>
                <h3 class="member-plate"><?= esc($row['plat_nomor']) ?></h3>
                <p><?= $row['status_parkir'] ? esc(status_pengguna($row['status_parkir'])) : 'Siap untuk kunjungan baru' ?></p></div>
            <?php if ($row['permintaan_id']): ?>
            <a class="btn btn-outline-primary" href="<?= url_to('pengguna.tiket', $row['permintaan_id']) ?>">Lihat parkir</a>
            <?php else: ?>
            <div class="saved-vehicle-actions">
                <a class="btn btn-outline-primary" href="<?= url_to('pengguna.parkir') ?>">Gunakan untuk parkir</a>
                <form action="<?= url_to('pengguna.kendaraan.hapus', $row['id']) ?>" method="post"
                    data-confirm="Lepas kendaraan <?= esc($row['plat_nomor']) ?> dari daftar? Riwayat parkir tetap tersimpan.">
                    <?= csrf_field() ?><button class="member-text-button" type="submit">Lepas kendaraan</button>
                </form>
            </div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </section>
    <section class="member-surface" aria-labelledby="add-title">
        <p class="eyebrow">KENDARAAN BARU</p><h2 id="add-title">Tambah kendaraan</h2>
        <form action="<?= url_to('pengguna.kendaraan.tambah') ?>" method="post" data-submit-lock>
            <?= csrf_field() ?>
            <div class="mb-4"><label class="form-label" for="plat_nomor">Plat nomor</label>
                <input class="form-control plate-input" id="plat_nomor" name="plat_nomor" required
                    maxlength="16" autocomplete="off" placeholder="BE 1234 AB"
                    value="<?= old('plat_nomor', '') ?>" aria-describedby="plat-help">
                <p class="form-text" id="plat-help">Gunakan plat kendaraan yang akan dibawa ke lokasi.</p></div>
            <div class="mb-4"><label class="form-label" for="jenis">Jenis kendaraan</label>
                <select class="form-select" id="jenis" name="jenis" required>
                    <option value="motor" <?= old('jenis', 'motor') === 'motor' ? 'selected' : '' ?>>Motor</option>
                    <option value="mobil" <?= old('jenis') === 'mobil' ? 'selected' : '' ?>>Mobil</option>
                </select></div>
            <button class="btn btn-primary w-100" type="submit">Simpan kendaraan</button>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
