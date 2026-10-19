<?= $this->extend("layouts/app") ?>
<?= $this->section("content") ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">ADMINISTRASI</p>
        <h1>Slot parkir</h1>
        <p>Atur kapasitas motor dan mobil di lokasi parkir.</p>
    </div><span class="badge-soft"><?= count($rows) ?> slot terdaftar</span>
</div>
<div class="admin-columns">
    <section class="panel form-panel">
        <h2><?= $edit ? "Edit slot " . esc($edit["kode_slot"]) : "Tambah slot" ?></h2>
        <form method="post"
            action="<?= $edit ? url_to("slot.update", $edit["id"]) : url_to("slot.create") ?>"
            data-submit-lock><?= csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="kode_slot">Kode slot</label><input
                    class="form-control" id="kode_slot" name="kode_slot" required maxlength="20"
                    pattern="[A-Za-z0-9-]+" placeholder="M21"
                    value="<?= esc(old("kode_slot") ?? ($edit["kode_slot"] ?? "")) ?>"></div>
            <div class="mb-3"><label class="form-label" for="area">Area</label><input
                    class="form-control" id="area" name="area" required maxlength="50"
                    value="<?= esc(old("area") ?? ($edit["area"] ?? "Utama")) ?>"></div>
            <div class="mb-3"><label class="form-label" for="jenis">Jenis kendaraan</label><select
                    class="form-select" id="jenis" name="jenis"
                    required><?php foreach ( ["motor", "mobil"] as $jenis ): ?><option
                        value="<?= $jenis ?>"
                        <?= (old("jenis") ?? ($edit["jenis"] ?? "motor")) === $jenis ? "selected" : "" ?>>
                        <?= ucfirst($jenis) ?></option><?php endforeach; ?></select></div>
            <div class="mb-4"><label class="form-label" for="aktif">Status slot</label><select
                    class="form-select" id="aktif" name="aktif">
                    <option value="1"
                        <?= (string) (old("aktif") ?? ($edit["aktif"] ?? "1")) === "1" ? "selected" : "" ?>>
                        Aktif</option>
                    <option value="0"
                        <?= (string) (old("aktif") ?? ($edit["aktif"] ?? "1")) === "0" ? "selected" : "" ?>>
                        Nonaktif</option>
                </select></div>
            <div class="button-row"><button class="btn btn-primary" type="submit">Simpan
                    slot</button><?php if ( $edit ): ?><a class="btn btn-outline-secondary"
                    href="<?= url_to("slot") ?>">Batal edit</a><?php endif; ?></div>
        </form>
        <p class="form-text mt-3 mb-0">Slot yang terisi tidak dapat diubah. Nonaktifkan slot lama
            untuk mempertahankan riwayat.</p>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h2>Daftar slot</h2>
        </div>
        <div class="table-responsive" role="region" tabindex="0" aria-label="Daftar slot parkir">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Status dan pengaturan slot parkir</caption>
                <thead>
                    <tr>
                        <th scope="col">Slot</th>
                        <th scope="col">Jenis</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?><tr>
                        <td><strong><?= esc($row["kode_slot"]) ?></strong><small
                                class="cell-sub"><?= esc($row["area"]) ?></small></td>
                        <td><?= esc(ucfirst($row["jenis"])) ?></td>
                        <td><span
                                class="badge-soft <?= !$row["aktif"]
                                    ? "neutral"
                                    : ($row["plat_nomor"] ? "warning" : "success") ?>"><?= !$row["aktif"]
                                    ? "Nonaktif"
                                    : ($row["plat_nomor"] ? "Terisi" : "Kosong") ?></span><?php if ($row["plat_nomor"]): ?><small
                                class="cell-sub"><?= esc($row["plat_nomor"]) ?></small><?php endif; ?>
                        </td>
                        <td>
                            <div class="button-row"><a class="btn btn-sm btn-outline-secondary"
                                    href="<?= url_to("slot") . "?edit=" . (int) $row["id"] ?>"
                                    aria-label="Edit slot <?= esc($row["kode_slot"]) ?>">Edit</a>
                                <form method="post"
                                    action="<?= url_to("slot.delete", $row["id"]) ?>"
                                    data-confirm="Hapus slot <?= esc($row["kode_slot"], "attr") ?>? Slot yang memiliki riwayat akan tetap disimpan.">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"
                                        type="submit"
                                        aria-label="Hapus slot <?= esc($row["kode_slot"]) ?>">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>
                    <?php if ( !$rows ): ?><tr>
                        <td colspan="4" class="empty-state">Belum ada slot. Tambahkan slot pertama
                            melalui formulir.</td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
