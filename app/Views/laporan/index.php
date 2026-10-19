<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">ADMINISTRASI</p>
        <h1>Laporan pendapatan</h1>
        <p>Hanya pembayaran lunas, berdasarkan waktu bayar dalam WIB.</p>
    </div><a class="btn btn-outline-primary"
        href="<?= url_to("laporan.csv") . "?" . http_build_query($periode) ?>">Unduh CSV</a>
</div>
<section class="panel mb-4">
    <form class="filter-bar" method="get" action="<?= url_to("laporan") ?>">
        <div><label class="form-label" for="dari">Dari tanggal</label><input class="form-control"
                type="date" id="dari" name="dari" required value="<?= esc($periode["dari"]) ?>">
        </div>
        <div><label class="form-label" for="sampai">Sampai tanggal</label><input
                class="form-control" type="date" id="sampai" name="sampai" required
                value="<?= esc($periode["sampai"]) ?>"></div><button class="btn btn-primary"
            type="submit">Terapkan periode</button>
    </form>
    <div class="period-shortcuts"><a
            href="<?= url_to("laporan") . "?dari=" . date("Y-m-d") . "&sampai=" . date("Y-m-d") ?>">Hari
            ini</a><a href="<?= url_to("laporan") ?>">Bulan ini</a><a
            href="<?= url_to("laporan")
                . "?dari=" . date("Y-m-01", strtotime("first day of last month"))
                . "&sampai=" . date("Y-m-t", strtotime("last day of last month")) ?>">Bulan
            lalu</a></div>
</section>
<p class="period-label">Periode <?= esc(date("d/m/Y", strtotime($periode["dari"]))) ?> sampai
    <?= esc(date("d/m/Y", strtotime($periode["sampai"]))) ?> · IDR</p>
<div class="stats-grid report-stats">
    <article class="stat-card stat-accent"><span>Total
            pendapatan</span><strong><?= rupiah($summary["pendapatan"]) ?></strong><small>Jumlah
            tagihan lunas, tanpa kembalian</small></article>
    <article class="stat-card"><span>Transaksi
            selesai</span><strong><?= (int) $summary["transaksi"] ?></strong><small>Semua
            kendaraan dalam periode</small></article>
    <?php foreach ( ["motor", "mobil"] as $jenis ): $byJenis = array_column( $summary["jenis"], null, "jenis", ); ?>
    <article class="stat-card"><span>Pendapatan
            <?= $jenis ?></span><strong><?= rupiah($byJenis[$jenis]["pendapatan"] ?? 0) ?></strong><small><?= (int) ($byJenis[$jenis]["transaksi"] ?? 0) ?>
            transaksi <?= $jenis ?></small></article><?php endforeach; ?>
</div>
<div class="dashboard-columns">
    <section class="panel">
        <div class="panel-header">
            <h2>Rekap harian</h2><span class="small text-muted">Hari dengan pembayaran</span>
        </div>
        <div class="table-responsive" role="region" tabindex="0"
            aria-label="Rekap pendapatan harian">
            <table class="table mb-0">
                <caption class="visually-hidden">Pendapatan per tanggal dalam periode terpilih
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Transaksi</th>
                        <th scope="col" class="text-end">Pendapatan</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($harian as $day): ?><tr>
                        <td><?= esc(date("d/m/Y", strtotime($day["tanggal"]))) ?></td>
                        <td><?= (int) $day["transaksi"] ?></td>
                        <td class="text-end money"><?= rupiah($day["pendapatan"]) ?></td>
                    </tr><?php endforeach; if ( !$harian ): ?><tr>
                        <td colspan="3" class="empty-state">Belum ada pembayaran pada periode ini.
                        </td>
                    </tr><?php endif; ?></tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h2>Rekap bulanan</h2>
        </div>
        <div class="table-responsive" role="region" tabindex="0"
            aria-label="Rekap pendapatan bulanan">
            <table class="table mb-0">
                <caption class="visually-hidden">Pendapatan per bulan yang termasuk dalam periode
                    terpilih</caption>
                <thead>
                    <tr>
                        <th scope="col">Bulan</th>
                        <th scope="col">Transaksi</th>
                        <th scope="col" class="text-end">Pendapatan</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($bulanan as $data): ?><tr>
                        <td><?= esc($data["bulan"]) ?></td>
                        <td><?= (int) $data["transaksi"] ?></td>
                        <td class="text-end money"><?= rupiah($data["pendapatan"]) ?></td>
                    </tr><?php endforeach; if ( !$bulanan ): ?><tr>
                        <td colspan="3" class="empty-state">Belum ada pembayaran pada periode ini.
                        </td>
                    </tr><?php endif; ?></tbody>
            </table>
        </div>
        <p class="panel-note">Rekap mengikuti tanggal filter. Bulan yang terpotong dihitung hanya
            untuk tanggal yang dipilih.</p>
    </section>
</div>
<section class="panel mt-4">
    <div class="panel-header">
        <h2>Detail pembayaran</h2><span class="small text-muted">20 transaksi per halaman</span>
    </div>
    <div class="table-responsive" role="region" tabindex="0"
        aria-label="Detail pembayaran dalam periode">
        <table class="table align-middle mb-0">
            <caption class="visually-hidden">Transaksi lunas yang digunakan dalam laporan</caption>
            <thead>
                <tr>
                    <th scope="col">Kendaraan / tiket</th>
                    <th scope="col">Waktu bayar</th>
                    <th scope="col">Petugas</th>
                    <th scope="col" class="text-end">Pendapatan</th>
                    <th scope="col">Bukti</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $row): ?><tr>
                    <td><strong><?= esc($row["plat_nomor"]) ?></strong><small
                            class="cell-sub"><?= esc($row["kode_tiket"]) ?></small></td>
                    <td><?= esc(waktu($row["waktu_bayar"])) ?></td>
                    <td><?= esc($row["petugas"]) ?></td>
                    <td class="text-end money"><?= rupiah($row["total"]) ?></td>
                    <td><a class="btn btn-sm btn-outline-secondary"
                            href="<?= url_to("parkir.tiket", $row["tiket_id"]) ?>"
                            aria-label="Bukti pembayaran <?= esc($row["plat_nomor"]) ?>">Lihat</a>
                    </td>
                </tr><?php endforeach; if ( !$rows ): ?><tr>
                    <td colspan="5" class="empty-state">Tidak ada transaksi untuk periode ini.</td>
                </tr><?php endif; ?></tbody>
        </table>
    </div>
    <div class="pagination-wrap"><?= $pagination ?></div>
</section>
<?= $this->endSection() ?>
