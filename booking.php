<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php?error=Silakan login terlebih dahulu!");
    exit();
}

$id_customer = (int) $_SESSION['id_customer'];

// Ambil antrian aktif milik customer beserta gabungan layanan dari booking_details
$sql_riwayat = "SELECT b.*, c.nama_lengkap,
                GROUP_CONCAT(s.nama_service SEPARATOR ' + ') AS daftar_service
                FROM bookings b
                JOIN customers c ON b.id_customer = c.id_customer
                JOIN booking_details bd ON b.id_booking = bd.id_booking
                JOIN services s ON bd.id_service = s.id_service
                WHERE b.id_customer = ? AND b.status_antrian NOT IN ('Selesai', 'Dibatalkan')
                GROUP BY b.id_booking
                ORDER BY b.id_booking DESC";

$stmt_riwayat = $koneksi->prepare($sql_riwayat);
$stmt_riwayat->bind_param("i", $id_customer);
$stmt_riwayat->execute();
$riwayat_booking = $stmt_riwayat->get_result();

// Endpoint AJAX ringan agar antrian yang sudah 'Selesai' langsung hilang secara real-time
if (isset($_GET['ajax_antrian']) && $_GET['ajax_antrian'] == '1') {
    if ($riwayat_booking->num_rows > 0) {
        while ($row = $riwayat_booking->fetch_assoc()) {
            if ($row['status_antrian'] === 'Antrian Berikutnya') {
                echo '<div class="alert retro-alert-call mb-3 fw-bold" data-panggil="1">';
                echo '🔔 PERHATIAN (Antrian #' . $row['id_booking'] . '):<br>';
                echo '"Anda antrian berikutnya, pastikan sudah berada di tempat!"';
                echo '</div>';
            }
            echo '<div class="retro-queue-ticket p-3 mb-3">';
            echo '<div class="d-flex justify-content-between align-items-center mb-2">';
            echo '<span class="badge retro-price-badge">NO. ANTRIAN #' . $row['id_booking'] . '</span>';
            echo '<span class="badge bg-dark text-warning border border-warning">' . strtoupper(htmlspecialchars($row['status_antrian'])) . '</span>';
            echo '</div>';
            echo '<h5 class="mb-1 text-cream">' . htmlspecialchars($row['daftar_service']) . '</h5>';
            echo '<p class="small mb-1 retro-text">Atas Nama: <strong>' . htmlspecialchars($row['nama_lengkap']) . '</strong></p>';
            echo '<p class="small mb-0 retro-text">Total Harga: Rp ' . number_format($row['total_harga'], 0, ',', '.') . ' | DP QRIS: <span class="text-mustard fw-bold">Rp ' . number_format($row['jumlah_dp'], 0, ',', '.') . '</span></p>';
            echo '</div>';
        }
    } else {
        echo '<p class="retro-text mb-0">Tidak ada antrian aktif saat ini. Silakan isi form di samping untuk memesan kursi barber Anda.</p>';
    }
    exit();
}

// Ambil daftar layanan aktif dari tabel services untuk dropdown
$query_services = $koneksi->query("SELECT id_service, nama_service, harga FROM services WHERE is_active = 1 ORDER BY id_service ASC");

$status_pesan = isset($_GET['status']) ? $_GET['status'] : '';
$pesan_teks   = isset($_GET['pesan']) ? $_GET['pesan'] : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - BARBERBOR Retro Barbershop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=7">
</head>

<body class="retro-body">

    <?php include 'navbar.php'; ?>

    <!-- Banner Header Halaman -->
    <header class="retro-page-header">
        <div class="container">
            <div class="retro-kicker mb-2">
                <span class="retro-line"></span> RESERVE YOUR CHAIR NOW
            </div>
            <h1 class="retro-page-title">BOOKING</h1>
        </div>
    </header>

    <section class="retro-section retro-section-dark">
        <div class="container">

            <!-- Notifikasi Berhasil / Gagal Booking -->
            <?php if ($pesan_teks != ''): ?>
                <div class="alert <?= ($status_pesan === 'sukses') ? 'retro-alert-success' : 'retro-alert-danger'; ?> mb-4">
                    <?= htmlspecialchars($pesan_teks); ?>
                </div>
            <?php endif; ?>

            <div class="row gy-5">
                <!-- Kolom Kiri: Form Booking -->
                <div class="col-lg-7">
                    <div class="retro-card-box p-4 p-md-5">
                        <div class="retro-section-num">/ 01</div>
                        <h3 class="retro-section-title fs-2 mb-1">FORM BOOKING</h3>
                        <p class="retro-text mb-4">Isi data di bawah, lakukan pembayaran <strong>QRIS DP minimal 50%</strong>, dan unggah screenshot bukti pembayaran.</p>

                        <form id="formBooking" action="proses_booking.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label retro-label">NAMA LENGKAP *</label>
                                <input type="text" name="nama_customer" class="form-control retro-input" value="<?= htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label retro-label">NOMOR HANDPHONE *</label>
                                <input type="tel" name="no_hp" class="form-control retro-input" value="<?= htmlspecialchars($_SESSION['no_hp']); ?>" readonly>
                            </div>

                            <!-- Dropdown Services Dinamis dari Tabel services -->
                            <div class="mb-3">
                                <label for="selectService" class="form-label retro-label">PILIH SERVICES (BISA PILIH LEBIH DARI 1) *</label>
                                <select id="selectService" class="form-select retro-input">
                                    <option value="" data-harga="0">-- Pilih / Tambah Layanan Barberbor --</option>
                                    <?php while ($srv = $query_services->fetch_assoc()): ?>
                                        <option value="<?= $srv['id_service']; ?>" data-nama="<?= htmlspecialchars($srv['nama_service']); ?>" data-harga="<?= $srv['harga']; ?>">
                                            <?= htmlspecialchars($srv['nama_service']); ?> - Rp <?= number_format($srv['harga'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-warning d-block mt-1">
                                    * Klik dropdown untuk memilih layanan. Setelah 1 layanan masuk ke daftar di bawah, klik dropdown lagi jika ingin menambah layanan berikutnya.
                                </small>

                                <!-- Daftar Layanan yang Dipilih Customer -->
                                <div id="listServiceDipilih" class="mt-2 d-flex flex-column gap-2"></div>

                                <input type="hidden" id="inputHargaService" name="total_harga" value="0">
                            </div>

                            <!-- Rincian Harga & Minimal DP 50% -->
                            <div class="retro-dp-summary p-3 mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Harga Service:</span>
                                    <strong id="labelHargaService">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between text-mustard">
                                    <span>Wajib Minimal DP (50%):</span>
                                    <strong id="labelMinimalDp">Rp 0</strong>
                                </div>
                            </div>

                            <!-- Metode Pembayaran QRIS -->
                            <div class="mb-3">
                                <label class="form-label retro-label">METODE PEMBAYARAN: QRIS BARBERBOR *</label>
                                <div class="retro-qris-box p-3 text-center mb-3">
                                    <span class="badge retro-price-badge mb-2">SCAN QRIS OFFICIAL BARBERBOR</span>
                                    <div class="qris-svg-wrapper mx-auto my-2">
                                        <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                                            <img src="uploads/QRIS_BARBERBORshop.jpeg" alt="qris">
                                        </svg>
                                    </div>
                                    <small class="d-block text-dark fw-bold">NMID: ID102026BARBERBOR • A/N BARBERBOR STUDIO</small>
                                </div>

                                <label class="form-label retro-label">NOMINAL TRANSFER DP QRIS (RP) *</label>
                                <input type="number" id="inputJumlahDp" name="jumlah_dp" class="form-control retro-input" placeholder="Masukkan nominal DP (minimal 50% total harga)" min="0" required>
                                <small class="text-warning d-block mt-1">
                                    * Jika tidak membayar DP atau nominal kurang dari 50%, booking tidak akan berhasil.
                                </small>
                            </div>

                            <!-- Input Upload Foto Screenshot Bukti Pembayaran (Tanpa Preview) -->
                            <div class="mb-4">
                                <label for="inputBuktiBayar" class="form-label retro-label">SCREENSHOT BUKTI PEMBAYARAN (KHUSUS FOTO) *</label>
                                <input type="file" id="inputBuktiBayar" name="bukti_pembayaran" class="form-control retro-input" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                                <small class="text-warning d-block mt-1">
                                    * Hanya bisa upload file foto (JPG, JPEG, PNG, WEBP).
                                </small>
                            </div>

                            <button type="submit" class="btn retro-btn-primary w-100">
                                KONFIRMASI & KIRIM BOOKING →
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kolom Kanan: Status Antrian Aktif Customer -->
                <div class="col-lg-5">
                    <div class="retro-card-box p-4">
                        <div class="retro-section-num">/ 02</div>
                        <h3 class="retro-section-title fs-3 mb-3">ANTRIAN ANDA</h3>

                        <div id="containerAntrianCustomer">
                            <?php if ($riwayat_booking->num_rows > 0): ?>
                                <?php while ($row = $riwayat_booking->fetch_assoc()): ?>
                                    <?php if ($row['status_antrian'] === 'Antrian Berikutnya'): ?>
                                        <div class="alert retro-alert-call mb-3 fw-bold" data-panggil="1">
                                            🔔 PERHATIAN (Antrian #<?= $row['id_booking']; ?>):<br>
                                            "Anda antrian berikutnya, pastikan sudah berada di tempat!"
                                        </div>
                                        <script>
                                            alert("Anda antrian berikutnya, pastikan sudah berada di tempat!");
                                        </script>
                                    <?php endif; ?>

                                    <div class="retro-queue-ticket p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge retro-price-badge">NO. ANTRIAN #<?= $row['id_booking']; ?></span>
                                            <span class="badge bg-dark text-warning border border-warning">
                                                <?= strtoupper($row['status_antrian']); ?>
                                            </span>
                                        </div>
                                        <h5 class="mb-1 text-cream"><?= htmlspecialchars($row['daftar_service']); ?></h5>
                                        <p class="small mb-1 retro-text">Atas Nama: <strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></p>
                                        <p class="small mb-0 retro-text">
                                            Total Harga: Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?> |
                                            DP QRIS: <span class="text-mustard fw-bold">Rp <?= number_format($row['jumlah_dp'], 0, ',', '.'); ?></span>
                                        </p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="retro-text mb-0">Tidak ada antrian aktif saat ini. Silakan isi form di samping untuk memesan kursi barber Anda.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>

</html>