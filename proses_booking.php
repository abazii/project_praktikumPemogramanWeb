<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: booking.php");
    exit();
}

$id_customer      = (int) $_SESSION['id_customer'];
$id_services_post = isset($_POST['id_service']) && is_array($_POST['id_service']) ? $_POST['id_service'] : [];
$jumlah_dp        = (int) $_POST['jumlah_dp'];

if ($id_customer <= 0 || count($id_services_post) === 0) {
    header("Location: booking.php?status=gagal&pesan=Booking gagal! Pastikan minimal 1 service telah dipilih.");
    exit();
}

// 1. Ambil harga resmi setiap id_service yang dipilih dari tabel services
$total_harga  = 0;
$detail_items = [];
$nama_items   = [];

$stmt_srv = $koneksi->prepare("SELECT id_service, nama_service, harga FROM services WHERE id_service = ? AND is_active = 1");

foreach ($id_services_post as $id_raw) {
    $id_srv = (int) $id_raw;
    $stmt_srv->bind_param("i", $id_srv);
    $stmt_srv->execute();
    $res_srv = $stmt_srv->get_result();

    if ($row_srv = $res_srv->fetch_assoc()) {
        $total_harga   += (int) $row_srv['harga'];
        $detail_items[] = $row_srv;
        $nama_items[]   = $row_srv['nama_service'];
    }
}
$stmt_srv->close();

if (count($detail_items) === 0 || $total_harga <= 0) {
    header("Location: booking.php?status=gagal&pesan=Layanan yang dipilih tidak valid!");
    exit();
}

$gabungan_nama_service = implode(" + ", $nama_items);
$minimal_dp            = $total_harga * 0.5;

// 2. Validasi ketat DP minimal 50%
if ($jumlah_dp <= 0 || $jumlah_dp < $minimal_dp) {
    $pesan_err = "Booking GAGAL! Total layanan (" . $gabungan_nama_service . ") adalah Rp " . number_format($total_harga, 0, ',', '.') . ". Pembayaran QRIS wajib DP minimal 50% (Rp " . number_format($minimal_dp, 0, ',', '.') . ").";
    header("Location: booking.php?status=gagal&pesan=" . urlencode($pesan_err));
    exit();
}

// 3. Validasi Upload Foto Bukti Pembayaran (Hanya file foto)
if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! Wajib mengunggah foto screenshot bukti pembayaran.");
    exit();
}

$file_tmp   = $_FILES['bukti_pembayaran']['tmp_name'];
$file_name  = $_FILES['bukti_pembayaran']['name'];
$file_size  = $_FILES['bukti_pembayaran']['size'];
$ekstensi   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$cek_gambar = @getimagesize($file_tmp);

if (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp']) || $cek_gambar === false) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! File yang diunggah bukan foto. Hanya menerima format JPG, JPEG, PNG, atau WEBP.");
    exit();
}

if ($file_size > 5 * 1024 * 1024) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! Ukuran foto maksimal 5 MB.");
    exit();
}

$folder_upload = "uploads/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
}

$nama_file_baru = "bukti_" . time() . "_" . rand(100, 999) . "." . $ekstensi;
$tujuan_upload  = $folder_upload . $nama_file_baru;

if (!move_uploaded_file($file_tmp, $tujuan_upload)) {
    header("Location: booking.php?status=gagal&pesan=Gagal menyimpan file foto bukti pembayaran ke server.");
    exit();
}

// 4. Simpan transaksi ke tabel bookings dan rincian ke booking_details
$koneksi->begin_transaction();

try {
    $metode = "QRIS";
    $status = "Menunggu";

    $stmt_book = $koneksi->prepare("INSERT INTO bookings (id_customer, total_harga, jumlah_dp, metode_pembayaran, bukti_pembayaran, status_antrian) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_book->bind_param("iiisss", $id_customer, $total_harga, $jumlah_dp, $metode, $nama_file_baru, $status);
    $stmt_book->execute();
    $nomor_antrian = $stmt_book->insert_id;
    $stmt_book->close();

    $stmt_det = $koneksi->prepare("INSERT INTO booking_details (id_booking, id_service, harga_saat_booking) VALUES (?, ?, ?)");
    foreach ($detail_items as $item) {
        $stmt_det->bind_param("iii", $nomor_antrian, $item['id_service'], $item['harga']);
        $stmt_det->execute();
    }
    $stmt_det->close();

    $koneksi->commit();

    $pesan_ok = "Booking BERHASIL! Nomor antrian Anda #" . $nomor_antrian . " untuk layanan: " . $gabungan_nama_service . " (Total: Rp " . number_format($total_harga, 0, ',', '.') . " | DP QRIS: Rp " . number_format($jumlah_dp, 0, ',', '.') . ").";
    header("Location: booking.php?status=sukses&pesan=" . urlencode($pesan_ok));
    exit();
} catch (Exception $e) {
    $koneksi->rollback();
    if (file_exists($tujuan_upload)) {
        unlink($tujuan_upload);
    }
    header("Location: booking.php?status=gagal&pesan=Terjadi kesalahan sistem saat menyimpan booking.");
    exit();
}
?>