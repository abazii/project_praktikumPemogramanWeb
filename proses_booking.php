<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: booking.php");
    exit();
}

$nama_customer   = trim($_POST['nama_customer']);
$no_hp           = trim($_POST['no_hp']);
$email_customer  = $_SESSION['email'];
$services_dipilih = isset($_POST['nama_service']) && is_array($_POST['nama_service']) ? $_POST['nama_service'] : [];
$jumlah_dp       = (int) $_POST['jumlah_dp'];

// Daftar harga resmi server-side sesuai pilihan di booking.php
$daftar_harga = [
    "Haircuts Pro"                  => 30000,
    "Haircut Kids"                  => 25000,
    "Shaves, Beard & Mustache Trim" => 20000,
    "Hair Coloring"                 => 100000,
    "Hair Coloring Basic"           => 45000,
    "Hair Coloring Fashion"         => 65000,
    "Haircare Treatment"            => 60000
];

if ($nama_customer === '' || $no_hp === '' || count($services_dipilih) === 0) {
    header("Location: booking.php?status=gagal&pesan=Booking gagal! Pastikan nama, nomor HP, dan minimal 1 service telah dipilih.");
    exit();
}

// Hitung total harga resmi & gabungkan nama seluruh service yang valid
$harga_resmi_total = 0;
$list_nama_valid   = [];

foreach ($services_dipilih as $srv) {
    $srv_bersih = trim($srv);
    if (isset($daftar_harga[$srv_bersih])) {
        $harga_resmi_total += $daftar_harga[$srv_bersih];
        $list_nama_valid[]  = $srv_bersih;
    }
}

if (count($list_nama_valid) === 0 || $harga_resmi_total <= 0) {
    header("Location: booking.php?status=gagal&pesan=Layanan yang dipilih tidak valid!");
    exit();
}

$gabungan_nama_service = implode(" + ", $list_nama_valid);
$minimal_dp            = $harga_resmi_total * 0.5;

// 1. Validasi ketat DP minimal 50% dari total harga gabungan
if ($jumlah_dp <= 0 || $jumlah_dp < $minimal_dp) {
    $pesan_err = "Booking GAGAL! Total layanan (" . $gabungan_nama_service . ") adalah Rp " . number_format($harga_resmi_total, 0, ',', '.') . ". Pembayaran QRIS wajib DP minimal 50% (Rp " . number_format($minimal_dp, 0, ',', '.') . ").";
    header("Location: booking.php?status=gagal&pesan=" . urlencode($pesan_err));
    exit();
}

// 2. Validasi Upload Foto Bukti Pembayaran (Hanya menerima file foto)
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

// 3. Simpan booking gabungan beserta nama file bukti pembayaran ke database
$metode = "QRIS";
$status = "Menunggu";

$stmt = $koneksi->prepare("INSERT INTO bookings (nama_customer, no_hp, email_customer, nama_service, harga_service, jumlah_dp, metode_pembayaran, bukti_pembayaran, status_antrian) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiisss", $nama_customer, $no_hp, $email_customer, $gabungan_nama_service, $harga_resmi_total, $jumlah_dp, $metode, $nama_file_baru, $status);

if ($stmt->execute()) {
    $nomor_antrian = $stmt->insert_id;
    $stmt->close();
    $pesan_ok = "Booking BERHASIL! Nomor antrian Anda #" . $nomor_antrian . " untuk layanan: " . $gabungan_nama_service . " (Total: Rp " . number_format($harga_resmi_total, 0, ',', '.') . " | DP QRIS: Rp " . number_format($jumlah_dp, 0, ',', '.') . ").";
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
