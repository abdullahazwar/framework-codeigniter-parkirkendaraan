<article class="member-visit">
    <div class="visit-heading">
        <div>
            <p class="member-caption"><?= esc(ucfirst($row['jenis'])) ?></p>
            <h3 class="member-plate"><?= esc($row['plat_nomor']) ?></h3>
        </div>
        <span class="member-status <?= warna_status_pengguna($row['status']) ?>">
            <?= esc(status_pengguna($row['status'])) ?>
        </span>
    </div>
    <dl class="visit-details">
        <div><dt><?= $row['tiket_id'] ? 'Tiket parkir' : 'Kode permintaan' ?></dt>
            <dd class="member-code"><?= esc($row['kode_tiket'] ?? $row['kode_permintaan']) ?></dd></div>
        <div><dt><?= $row['waktu_masuk'] ? 'Waktu masuk' : 'Diajukan' ?></dt>
            <dd><?= esc(waktu($row['waktu_masuk'] ?? $row['created_at'])) ?></dd></div>
        <?php if ($row['kode_slot']): ?>
        <div><dt>Lokasi parkir</dt><dd><?= esc($row['area'] . ' · ' . $row['kode_slot']) ?></dd></div>
        <?php endif; ?>
    </dl>
    <a class="btn btn-outline-primary" href="<?= url_to('pengguna.tiket', $row['id']) ?>">
        <?= $row['tiket_id'] ? 'Lihat tiket & tagihan' : 'Lihat permintaan' ?>
    </a>
</article>
