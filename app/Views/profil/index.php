<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="profile-hero" aria-labelledby="hero-title">
    <div class="public-container hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">SISTEM INFORMASI MANAJEMEN PARKIR</p>
            <h1 id="hero-title">Parkir tertata,<br> dari masuk<br> sampai pulang.</h1>
            <p class="hero-description">Satu web untuk mencatat kendaraan, mengatur slot motor
                dan mobil, serta menghitung biaya parkir berdasarkan durasi.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#alur">Lihat alur parkir <span aria-hidden="true">↗</span></a>
                <a class="hero-secondary" href="#tentang">Kenali Parkir <span aria-hidden="true">↓</span></a>
            </div>
            <p class="hero-note">Tiket masuk tercatat. Biaya keluar jelas.</p>
        </div>
        <figure class="parking-visual">
            <div class="parking-scene" aria-hidden="true">
                <div class="scene-heading"><span class="scene-sign">P</span>
                    <span>AREA PARKIR<small>Motor &amp; mobil</small></span>
                    <span class="scene-direction">MASUK ↗</span>
                </div>
                <div class="scene-lot">
                    <div class="scene-bays">
                        <div class="scene-bay"><span>C01</span><i class="scene-car"></i></div>
                        <div class="scene-bay"><span>C02</span></div>
                        <div class="scene-bay"><span>C03</span><i class="scene-car car-light"></i></div>
                    </div>
                    <div class="scene-road"><span>→</span><i></i><span>→</span></div>
                    <div class="scene-bays scene-bays-bottom">
                        <div class="scene-bay"><span>M01</span></div>
                        <div class="scene-bay"><span>M02</span></div>
                        <div class="scene-bay"><span>M03</span></div>
                    </div>
                </div>
                <div class="scene-ticket"><span class="ticket-icon">P</span><span>Tiket masuk
                    <small>Kendaraan · slot · waktu</small></span><span class="ticket-check">✓</span></div>
            </div>
            <figcaption>Ilustrasi area parkir. Ketersediaan slot dicek oleh petugas.</figcaption>
        </figure>
    </div>
</section>

<section class="profile-section about-section" id="tentang" aria-labelledby="about-title">
    <div class="public-container about-grid">
        <div>
            <p class="eyebrow">TENTANG PARKIR</p>
            <h2 id="about-title">Kenali web<br> pengelolaan parkir.</h2>
        </div>
        <div class="about-copy">
            <p>Parkir adalah web untuk membantu pengelola menangani aktivitas parkir di satu
                lokasi. Admin mengatur tarif, slot, dan petugas. Petugas mencatat kendaraan
                masuk hingga pembayaran saat kendaraan keluar.</p>
            <p>Pengunjung dapat mempelajari layanan dan membuat akun pengguna. Setelah masuk,
                informasi akun serta tarif motor dan mobil tersedia di halaman Akun Saya.</p>
            <div class="service-tags" aria-label="Cakupan layanan">
                <span>Motor &amp; mobil</span><span>Tiket parkir</span><span>Pembayaran tunai</span>
            </div>
        </div>
    </div>
</section>

<section class="profile-section benefits-section" id="keunggulan" aria-labelledby="benefits-title">
    <div class="public-container">
        <div class="section-heading">
            <p class="eyebrow">KENAPA MEMAKAI PARKIR?</p>
            <h2 id="benefits-title">Setiap tahap punya catatan.</h2>
            <p>Pencatatan yang membantu aktivitas parkir lebih tertib dan mudah ditelusuri.</p>
        </div>
        <div class="benefits-grid">
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">01 / TIKET</span>
                <h3>Kendaraan masuk tercatat</h3>
                <p>Plat nomor, jenis kendaraan, slot, dan waktu masuk tercatat dalam tiket.
                    Simpan tiket untuk proses kendaraan keluar.</p>
            </article>
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">02 / SLOT</span>
                <h3>Tempat sesuai kendaraan</h3>
                <p>Sistem mengalokasikan slot aktif sesuai jenis motor atau mobil.
                    Petugas dapat memantau slot yang sedang terisi.</p>
            </article>
            <article class="benefit-item">
                <span class="benefit-number" aria-hidden="true">03 / BIAYA</span>
                <h3>Perhitungan tarif jelas</h3>
                <p>Biaya mengikuti durasi dan tarif saat kendaraan masuk. Pembayaran tunai
                    dilengkapi perhitungan kembalian serta bukti pembayaran.</p>
            </article>
        </div>
    </div>
</section>

<section class="profile-section process-section" id="alur" aria-labelledby="process-title">
    <div class="public-container process-grid">
        <div class="process-intro">
            <p class="eyebrow">ALUR PARKIR</p>
            <h2 id="process-title">Datang, parkir,<br>lanjutkan aktivitas.</h2>
            <p>Pencatatan dan pembayaran dilayani petugas di lokasi.</p>
            <a class="text-link" href="#faq">Baca pertanyaan umum <span aria-hidden="true">↗</span></a>
        </div>
        <ol class="process-list">
            <li>
                <span class="step-number" aria-hidden="true">1</span>
                <div><h3>Catat kendaraan saat masuk</h3>
                    <p>Petugas memasukkan plat nomor dan jenis kendaraan. Sistem menentukan
                        slot yang tersedia lalu menerbitkan tiket masuk.</p></div>
            </li>
            <li>
                <span class="step-number" aria-hidden="true">2</span>
                <div><h3>Parkir dan simpan tiket</h3>
                    <p>Tempatkan kendaraan sesuai arahan petugas. Simpan tiket sebagai
                        acuan untuk mencari catatan parkir saat keluar.</p></div>
            </li>
            <li>
                <span class="step-number" aria-hidden="true">3</span>
                <div><h3>Bayar saat kendaraan keluar</h3>
                    <p>Petugas memeriksa tiket dan tagihan. Setelah pembayaran tunai
                        dikonfirmasi, bukti pembayaran dapat dicetak.</p></div>
            </li>
        </ol>
    </div>
</section>

<section class="profile-section faq-section" id="faq" aria-labelledby="faq-title">
    <div class="public-container faq-grid">
        <div>
            <p class="eyebrow">PERTANYAAN UMUM</p>
            <h2 id="faq-title">Sebelum mulai parkir.</h2>
        </div>
        <div class="faq-list">
            <details open>
                <summary>Apa fungsi akun pengguna?</summary>
                <p>Akun pengguna memberi akses ke profil akun dan informasi tarif yang
                    ditetapkan pengelola. Pendaftaran cukup memakai nama, username, dan password.</p>
            </details>
            <details>
                <summary>Apakah akun wajib untuk parkir?</summary>
                <p>Petugas tetap mencatat kendaraan dan menerbitkan tiket di lokasi.
                    Pendaftaran akun tidak diperlukan untuk proses pencatatan kendaraan.</p>
            </details>
            <details>
                <summary>Bagaimana biaya parkir dihitung?</summary>
                <p>Motor memakai tarif per jam. Mobil memakai tarif jam pertama dan jam lanjutan.
                    Durasi dibulatkan ke atas dengan minimal satu jam. Tarif saat masuk
                    tersimpan pada tiket, sehingga perubahan tarif berikutnya tidak mengubah tiket tersebut.</p>
            </details>
            <details>
                <summary>Apakah bisa memesan slot dari web?</summary>
                <p>Alokasi slot dilakukan saat petugas mencatat kendaraan masuk di lokasi.
                    Akun pengguna belum menyediakan pemesanan slot secara daring.</p>
            </details>
            <details>
                <summary>Siapa yang mengelola tiket dan pembayaran?</summary>
                <p>Admin dan petugas yang memiliki akses operasional. Akun yang dibuat lewat
                    halaman Daftar terdaftar sebagai pengguna.</p>
            </details>
        </div>
    </div>
</section>

<section class="account-invite" aria-labelledby="invite-title">
    <div class="public-container invite-inner">
        <div><p class="eyebrow">AKUN PENGGUNA</p>
            <h2 id="invite-title">Informasi Parkir dalam satu akun.</h2>
            <p>Lihat profil akun dan tarif parkir yang berlaku.</p></div>
        <?php $accountRoute = session('user.role') === 'pengguna' ? 'akun' : 'dashboard'; ?>
        <a class="btn btn-primary" href="<?= url_to(session('user') ? $accountRoute : 'daftar') ?>">
            <?= session('user') ? 'Buka akun saya' : 'Buat akun pengguna' ?>
            <span aria-hidden="true">↗</span>
        </a>
    </div>
</section>
<?= $this->endSection() ?>
