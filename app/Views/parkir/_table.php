<?php if (!$rows): ?>
<div class="empty-state">
    <strong><?= $riwayat ? "Belum ada riwayat yang cocok" : "Tidak ada kendaraan yang cocok" ?></strong>
    <p><?= $riwayat ? "Transaksi yang telah dibayar akan muncul di sini." : "Catat kendaraan masuk atau ubah pencarian." ?>
    </p><a href="<?= url_to("parkir.masuk") ?>">Catat kendaraan masuk</a>
</div>
<?php else: ?>
<div class="table-responsive" tabindex="0" role="region"
    aria-label="Daftar kendaraan <?= $riwayat ? "selesai" : "aktif" ?>">
    <table class="table align-middle mb-0">
        <caption class="visually-hidden">Daftar tiket parkir <?= $riwayat ? "selesai" : "aktif" ?>
        </caption>
        <thead>
            <tr>
                <th scope="col">Kendaraan</th>
                <th scope="col">Tiket &amp; slot</th>
                <th scope="col">Waktu masuk</th><?php if ( $riwayat ): ?><th class="text-end"
                    scope="col">Total</th><?php endif; ?><th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><strong class="plate-text"><?= esc($row["plat_nomor"]) ?></strong><small
                        class="cell-sub"><?= esc(ucfirst($row["jenis"])) ?></small></td>
                <td><span class="ticket-code"><?= esc($row["kode_tiket"]) ?></span><small
                        class="cell-sub">Slot <?= esc($row["kode_slot"]) ?></small></td>
                <td><?= esc(waktu($row["waktu_masuk"])) ?></td><?php if ($riwayat): ?><td
                    class="text-end money"><?= rupiah($row["total"]) ?></td><?php endif; ?><td><a
                        class="btn btn-sm <?= $riwayat ? "btn-outline-secondary" : "btn-outline-primary" ?>"
                        href="<?= $riwayat ? url_to("parkir.tiket", $row["id"]) : url_to("parkir.keluar", $row["id"]) ?>"
                        aria-label="<?= $riwayat ? "Lihat bukti" : "Proses keluar" ?> <?= esc($row["plat_nomor"]) ?>"><?= $riwayat ? "Bukti" : "Proses keluar" ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
