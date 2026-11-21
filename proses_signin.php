<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signin.php");
    exit();
}

$nama_lengkap = trim($_POST['nama_lengkap']);
$no_hp        = trim($_POST['no_hp']);
$alamat       = trim($_POST['alamat']);
$email        = strtolower(trim($_POST['email']));
$password     = trim($_POST['password']);

// 1. Validasi kolom kosong
if ($nama_lengkap === '' || $no_hp === '' || $alamat === '' || $email === '' || $password === '') {
    header("Location: signin.php?error=Semua kolom wajib diisi!");
    exit();
}

// 2. Validasi format email & panjang password
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signin.php?error=Format email tidak valid!");
    exit();
}

if (strlen($password) < 6) {
    header("Location: signin.php?error=Password minimal harus 6 karakter!");
    exit();
}

// 3. Cek email ganda di tabel customers dan owners
$cek_cust = $koneksi->prepare("SELECT id_customer FROM customers WHERE email = ?");
$cek_cust->bind_param("s", $email);
$cek_cust->execute();
$cek_cust->store_result();

$cek_owner = $koneksi->prepare("SELECT id_owner FROM owners WHERE email = ?");
$cek_owner->bind_param("s", $email);
$cek_owner->execute();
$cek_owner->store_result();

if ($cek_cust->num_rows > 0 || $cek_owner->num_rows > 0) {
    $cek_cust->close();
    $cek_owner->close();
    header("Location: signin.php?error=Email sudah terdaftar! Silakan langsung Login.");
    exit();
}
$cek_cust->close();
$cek_owner->close();

// 4. Enkripsi password & simpan ke tabel customers
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $koneksi->prepare("INSERT INTO customers (nama_lengkap, no_hp, alamat, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nama_lengkap, $no_hp, $alamat, $email, $password_hash);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: index.php?sukses=Sign In berhasil! Silakan login menggunakan Email dan Password Anda.");
    exit();
} else {
    $err_db = $stmt->error;
    $stmt->close();
    header("Location: signin.php?error=" . urlencode("Gagal menyimpan akun: " . $err_db));
    exit();
}
