<?php
session_start();
require 'koneksi.php';

// Proteksi halaman agar hanya Owner yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php?error=Akses ditolak! Halaman ini khusus Owner Barberbor.");
    exit();
}

$notif_aksi = '';
$panggil_berikutnya = false;

// Proses update status atau hapus antrian ketika Owner menekan tombol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_booking'], $_POST['aksi_status'])) {
    $id_booking  = (int) $_POST['id_booking'];
    $aksi_status = $_POST['aksi_status'];

    if ($aksi_status === 'Hapus') {
        // Ambil nama file bukti pembayaran agar file ikut terhapus dari server
        $stmt_file = $koneksi->prepare("SELECT bukti_pembayaran FROM bookings WHERE id_booking = ?");
        $stmt_file->bind_param("i", $id_booking);
        $stmt_file->execute();
        $res_file = $stmt_file->get_result();
        if ($row_file = $res_file->fetch_assoc()) {
            $path_foto = "uploads/" . $row_file['bukti_pembayaran'];
            if (!empty($row_file['bukti_pembayaran']) && file_exists($path_foto)) {
                unlink($path_foto);
            }
        }
        $stmt_file->close();

        $stmt = $koneksi->prepare("DELETE FROM bookings WHERE id_booking = ?");
        $stmt->bind_param("i", $id_booking);
        $stmt->execute();
        $stmt->close();

        $notif_aksi = "Data Antrian #" . $id_booking . " berhasil dihapus dari daftar.";
    } else {
        $status_valid = ['Sedang Diproses', 'Selesai', 'Antrian Berikutnya'];
        if (in_array($aksi_status, $status_valid)) {
            $stmt = $koneksi->prepare("UPDATE bookings SET status_antrian = ? WHERE id_booking = ?");
            $stmt->bind_param("si", $aksi_status, $id_booking);
            $stmt->execute();
            $stmt->close();

            if ($aksi_status === 'Antrian Berikutnya') {
                $panggil_berikutnya = true;
                $notif_aksi = "Notifikasi terkirim ke Antrian #" . $id_booking . ": Anda antrian berikutnya, pastikan sudah berada di tempat!";
            } else {
                $notif_aksi = "Status Antrian #" . $id_booking . " berhasil diubah menjadi: " . $aksi_status;
            }
        }
    }
}

// Ambil seluruh list booking customer
$query_bookings = $koneksi->query("SELECT * FROM bookings ORDER BY id_booking ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - BARBERBOR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="retro-body">

    <!-- Navbar Khusus Owner -->
    <nav class="navbar retro-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand retro-nav-logo" href="dashboard_owner.php">
                <span class="logo-top">BARBERBOR OWNER</span>
                <span class="logo-bottom">PANEL KONTROL ANTRIAN RETRO</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-cream small d-none d-md-inline">Login sebagai: <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="logout.php" class="btn retro-btn-logout btn-sm">LOGOUT</a>
            </div>
        </div>
    </nav>

    <section class="retro-section retro-section-dark min-vh-100">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <div class="retro-section-num">/ OWNER PANEL</div>
                    <h1 class="retro-section-title mb-1">DASHBOARD ANTRIAN BOOKING</h1>
                    <p class="retro-text mb-0">Kelola antrian customer Barberbor, cek bukti DP QRIS, dan export laporan booking.</p>
                </div>

                <!-- Tombol Export Rekapan Booking ke XLSX -->
                <div>
                    <a href="export_xlsx.php" class="btn retro-btn-export d-inline-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        EXPORT REKAPAN (.XLSX)
                    </a>
                </div>
            </div>

            <!-- Alert / Notifikasi Aksi Owner -->
            <?php if ($notif_aksi !== ''): ?>
                <div class="alert <?= $panggil_berikutnya ? 'retro-alert-call' : 'retro-alert-success'; ?> mb-4 fw-bold">
                    <?= htmlspecialchars($notif_aksi); ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Daftar Antrian Booking Customer -->
            <div class="retro-card-box p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-dark retro-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>NAMA CUSTOMER</th>
                                <th>NO. HP</th>
                                <th>SERVICES</th>
                                <th>DP QRIS</th>
                                <th class="text-center">BUKTI DP</th>
                                <th>STATUS</th>
                                <th class="text-center" style="min-width: 230px;">AKSI ANTRIAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query_bookings->num_rows > 0): ?>
                                <?php while ($b = $query_bookings->fetch_assoc()): ?>
                                    <?php
                                    $file_bukti = !empty($b['bukti_pembayaran']) ? 'uploads/' . $b['bukti_pembayaran'] : '';
                                    $ada_foto   = ($file_bukti !== '' && file_exists($file_bukti));
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-mustard">#<?= $b['id_booking']; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($b['nama_customer']); ?></strong><br>
                                            <small class="text-secondary"><?= htmlspecialchars($b['email_customer']); ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($b['no_hp']); ?></td>
                                        <td>
                                            <?= htmlspecialchars($b['nama_service']); ?><br>
                                            <small class="text-secondary">Harga: Rp <?= number_format($b['harga_service'], 0, ',', '.'); ?></small>
                                        </td>
                                        <td class="text-success fw-bold">Rp <?= number_format($b['jumlah_dp'], 0, ',', '.'); ?></td>

                                        <!-- Kolom Bukti Pembayaran Ringkas (Thumbnail + Tombol Lihat) -->
                                        <td class="text-center">
                                            <?php if ($ada_foto): ?>
                                                <button type="button"
                                                    class="btn btn-bukti-view btn-lihat-bukti d-inline-flex align-items-center gap-2"
                                                    data-foto="<?= htmlspecialchars($file_bukti); ?>"
                                                    data-nama="<?= htmlspecialchars($b['nama_customer']); ?>"
                                                    data-service="<?= htmlspecialchars($b['nama_service']); ?>"
                                                    data-dp="Rp <?= number_format($b['jumlah_dp'], 0, ',', '.'); ?>"
                                                    data-antrian="#<?= $b['id_booking']; ?>">
                                                    <img src="<?= htmlspecialchars($file_bukti); ?>" alt="Bukti" class="retro-bukti-thumb">
                                                    <span>Lihat</span>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php
                                            $badge_class = 'bg-secondary';
                                            if ($b['status_antrian'] === 'Antrian Berikutnya') $badge_class = 'bg-warning text-dark';
                                            elseif ($b['status_antrian'] === 'Sedang Diproses') $badge_class = 'bg-info text-dark';
                                            elseif ($b['status_antrian'] === 'Selesai') $badge_class = 'bg-success';
                                            ?>
                                            <span class="badge <?= $badge_class; ?> px-2 py-1">
                                                <?= strtoupper($b['status_antrian']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- ICON GRID 2x2: Antrian Berikutnya, Sedang Diproses, Hapus, Selesai -->
                                            <div class="retro-action-grid">

                                                <!-- 1. Antrian Berikutnya -->
                                                <form method="POST" onsubmit="alert('Anda antrian berikutnya, pastikan sudah berada di tempat!');">
                                                    <input type="hidden" name="id_booking" value="<?= $b['id_booking']; ?>">
                                                    <input type="hidden" name="aksi_status" value="Antrian Berikutnya">
                                                    <button type="submit" class="btn retro-grid-btn btn-grid-next" title="Panggil Antrian Berikutnya">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                                        </svg>
                                                        <span>Antrian Berikutnya</span>
                                                    </button>
                                                </form>

                                                <!-- 2. Sedang Diproses -->
                                                <form method="POST">
                                                    <input type="hidden" name="id_booking" value="<?= $b['id_booking']; ?>">
                                                    <input type="hidden" name="aksi_status" value="Sedang Diproses">
                                                    <button type="submit" class="btn retro-grid-btn btn-grid-process" title="Tandai Sedang Diproses">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                            <circle cx="6" cy="6" r="3" />
                                                            <circle cx="6" cy="18" r="3" />
                                                            <line x1="20" y1="4" x2="8.12" y2="15.88" />
                                                            <line x1="14.47" y1="14.48" x2="20" y2="20" />
                                                            <line x1="8.12" y1="8.12" x2="12" y2="12" />
                                                        </svg>
                                                        <span>Sedang Diproses</span>
                                                    </button>
                                                </form>

                                                <!-- 3. Hapus -->
                                                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus antrian #<?= $b['id_booking']; ?> milik <?= htmlspecialchars($b['nama_customer']); ?>?');">
                                                    <input type="hidden" name="id_booking" value="<?= $b['id_booking']; ?>">
                                                    <input type="hidden" name="aksi_status" value="Hapus">
                                                    <button type="submit" class="btn retro-grid-btn btn-grid-delete" title="Hapus Data Antrian">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                            <line x1="10" y1="11" x2="10" y2="17" />
                                                            <line x1="14" y1="11" x2="14" y2="17" />
                                                        </svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>

                                                <!-- 4. Selesai -->
                                                <form method="POST">
                                                    <input type="hidden" name="id_booking" value="<?= $b['id_booking']; ?>">
                                                    <input type="hidden" name="aksi_status" value="Selesai">
                                                    <button type="submit" class="btn retro-grid-btn btn-grid-done" title="Tandai Selesai">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <polyline points="20 6 9 17 4 12" />
                                                        </svg>
                                                        <span>Selesai</span>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 retro-text">
                                        Belum ada antrian booking dari customer.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Preview Foto Bukti Pembayaran -->
    <div class="modal fade" id="modalBuktiBayar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content retro-modal-box">
                <div class="modal-header border-bottom border-warning">
                    <h5 class="modal-title text-mustard fw-bold" id="modalBuktiJudul">BUKTI PEMBAYARAN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalBuktiImg" src="" alt="Screenshot Bukti Pembayaran" class="img-fluid border border-2 border-warning mb-3" style="max-height: 420px; object-fit: contain;">
                    <div class="retro-dp-summary p-2 text-start small">
                        <div class="d-flex justify-content-between">
                            <span class="retro-text">Customer:</span>
                            <strong id="modalBuktiNama" class="text-cream"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="retro-text">Service:</span>
                            <strong id="modalBuktiService" class="text-cream"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="retro-text">Nominal DP QRIS:</span>
                            <strong id="modalBuktiDp" class="text-mustard"></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>