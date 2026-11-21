<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: booking.php");
    exit();
}

$nama_customer  = trim($_POST['nama_customer']);
$no_hp          = trim($_POST['no_hp']);
$email_customer = $_SESSION['email'];
$nama_service   = trim($_POST['nama_service']);
$harga_service  = (int) $_POST['harga_service'];
$jumlah_dp      = (int) $_POST['jumlah_dp'];

// Daftar harga resmi server-side sesuai halaman Services
$daftar_harga = [
    "Haircuts Pro"                  => 30000,
    "Haircut Kids"                  => 25000,
    "Shaves, Beard & Mustache Trim" => 20000,
    "Shoescare Deep Cleaning"       => 25000,
    "Hair Coloring Basic"           => 45000,
    "Hair Coloring Fashion"         => 65000,
    "Haircare Treatment"            => 60000
];

if ($nama_customer === '' || $no_hp === '' || !isset($daftar_harga[$nama_service])) {
    header("Location: booking.php?status=gagal&pesan=Booking gagal! Pastikan nama, nomor HP, dan service dipilih dengan benar.");
    exit();
}

$harga_resmi = $daftar_harga[$nama_service];
$minimal_dp  = $harga_resmi * 0.5;

// 1. Validasi ketat DP minimal 50%
if ($jumlah_dp <= 0 || $jumlah_dp < $minimal_dp) {
    $pesan_err = "Booking GAGAL! Pembayaran QRIS wajib DP minimal 50% (Rp " . number_format($minimal_dp, 0, ',', '.') . ") dari harga " . $nama_service . ".";
    header("Location: booking.php?status=gagal&pesan=" . urlencode($pesan_err));
    exit();
}

// 2. Validasi Upload Foto Bukti Pembayaran (Hanya menerima file gambar/foto)
if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! Wajib mengunggah foto screenshot bukti pembayaran.");
    exit();
}

$file_tmp  = $_FILES['bukti_pembayaran']['tmp_name'];
$file_name = $_FILES['bukti_pembayaran']['name'];
$file_size = $_FILES['bukti_pembayaran']['size'];
$ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

$ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
$cek_gambar = @getimagesize($file_tmp);

if (!in_array($ekstensi, $ekstensi_diizinkan) || $cek_gambar === false) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! File yang diunggah bukan foto. Hanya menerima format JPG, JPEG, PNG, atau WEBP.");
    exit();
}

if ($file_size > 5 * 1024 * 1024) {
    header("Location: booking.php?status=gagal&pesan=Booking GAGAL! Ukuran foto maksimal 5 MB.");
    exit();
}

// Buat folder uploads jika belum ada
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

// 3. Simpan booking beserta nama file bukti pembayaran ke database
$metode = "QRIS";
$status = "Menunggu";

$stmt = $koneksi->prepare("INSERT INTO bookings (nama_customer, no_hp, email_customer, nama_service, harga_service, jumlah_dp, metode_pembayaran, bukti_pembayaran, status_antrian) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiisss", $nama_customer, $no_hp, $email_customer, $nama_service, $harga_resmi, $jumlah_dp, $metode, $nama_file_baru, $status);

if ($stmt->execute()) {
    $nomor_antrian = $stmt->insert_id;
    $stmt->close();
    $pesan_ok = "Booking BERHASIL! Nomor antrian Anda #" . $nomor_antrian . " untuk layanan " . $nama_service . " (DP QRIS Rp " . number_format($jumlah_dp, 0, ',', '.') . ").";
    header("Location: booking.php?status=sukses&pesan=" . urlencode($pesan_ok));
    exit();
} else {
    $stmt->close();
    if (file_exists($tujuan_upload)) {
        unlink($tujuan_upload);
    }
    header("Location: booking.php?status=gagal&pesan=Terjadi kesalahan sistem saat menyimpan booking.");
    exit();
}
