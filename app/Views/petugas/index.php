<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">ADMINISTRASI</p>
        <h1>Akun petugas</h1>
        <p>Petugas dapat mencatat kendaraan, menerima pembayaran, dan melihat riwayat.</p>
    </div>
</div>
<div class="admin-columns">
    <section class="panel form-panel">
        <h2><?= $edit ? "Edit akun petugas" : "Tambah petugas" ?></h2>
        <form method="post"
            action="<?= $edit ? url_to("petugas.update", $edit["id"]) : url_to("petugas.create") ?>"
            data-submit-lock><?= csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="nama">Nama petugas</label><input
                    class="form-control" id="nama" name="nama" required maxlength="100"
                    value="<?= esc(old("nama") ?? ($edit["nama"] ?? "")) ?>"></div>
            <div class="mb-3"><label class="form-label" for="username">Username</label><input
                    class="form-control" id="username" name="username" autocomplete="off" required
                    minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+"
                    value="<?= esc(old("username") ?? ($edit["username"] ?? "")) ?>">
                <div class="form-text">3 sampai 50 huruf, angka, atau underscore.</div>
            </div>
            <div class="mb-3"><label class="form-label"
                    for="password"><?= $edit ? "Password baru (opsional)" : "Password" ?></label><input
                    class="form-control" id="password" name="password" type="password"
                    autocomplete="new-password" minlength="12" maxlength="72"
                    <?= $edit ? "" : "required" ?>>
                <div class="form-text">12 sampai 72 karakter.
                    <?= $edit ? "Kosongkan untuk mempertahankan password." : "" ?></div>
            </div>
            <div class="mb-4"><label class="form-label" for="aktif">Status akun</label><select
                    class="form-select" id="aktif" name="aktif">
                    <option value="1"
                        <?= (string) (old("aktif") ?? ($edit["aktif"] ?? "1")) === "1" ? "selected" : "" ?>>
                        Aktif</option>
                    <option value="0"
                        <?= (string) (old("aktif") ?? ($edit["aktif"] ?? "1")) === "0" ? "selected" : "" ?>>
                        Nonaktif</option>
                </select></div>
            <div class="button-row"><button class="btn btn-primary" type="submit">Simpan
                    akun</button><?php if ( $edit ): ?><a class="btn btn-outline-secondary"
                    href="<?= url_to("petugas") ?>">Batal edit</a><?php endif; ?></div>
        </form>
        <p class="form-text mt-3 mb-0">Perubahan akun mengakhiri sesi login petugas. Riwayat tetap
            tersimpan saat akun dinonaktifkan.</p>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h2>Petugas terdaftar</h2><span class="small text-muted"><?= count($rows) ?>
                akun</span>
        </div>
        <div class="table-responsive" role="region" tabindex="0" aria-label="Daftar akun petugas">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Akun dan status petugas</caption>
                <thead>
                    <tr>
                        <th scope="col">Petugas</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?><tr>
                        <td><strong><?= esc($row["nama"]) ?></strong><small
                                class="cell-sub"><?= esc($row["username"]) ?></small></td>
                        <td><span
                                class="badge-soft <?= $row["aktif"] ? "success" : "neutral" ?>"><?= $row["aktif"] ? "Aktif" : "Nonaktif" ?></span>
                        </td>
                        <td><a class="btn btn-sm btn-outline-secondary"
                                href="<?= url_to("petugas") . "?edit=" . (int) $row["id"] ?>"
                                aria-label="Edit akun <?= esc($row["nama"]) ?>">Edit</a></td>
                    </tr><?php endforeach; ?>
                    <?php if ( !$rows ): ?><tr>
                        <td colspan="3" class="empty-state">Belum ada akun petugas. Buat akun
                            melalui formulir.</td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
