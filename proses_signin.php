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
$email        = trim($_POST['email']);
$password     = trim($_POST['password']);

// 1. Validasi semua field wajib diisi
if ($nama_lengkap === '' || $no_hp === '' || $alamat === '' || $email === '' || $password === '') {
    header("Location: signin.php?error=Semua kolom wajib diisi!");
    exit();
}

// 2. Validasi format Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signin.php?error=Format email tidak valid! Contoh: nama@email.com");
    exit();
}

// 3. Validasi panjang Password minimal 6 karakter
if (strlen($password) < 6) {
    header("Location: signin.php?error=Password minimal harus 6 karakter!");
    exit();
}

// 4. Cek apakah email sudah terdaftar di tabel customers atau owners
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
    header("Location: signin.php?error=Email sudah terdaftar! Silakan gunakan email lain atau langsung Login.");
    exit();
}
$cek_cust->close();
$cek_owner->close();

// 5. Enkripsi password & simpan customer baru
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $koneksi->prepare("INSERT INTO customers (nama_lengkap, no_hp, alamat, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nama_lengkap, $no_hp, $alamat, $email, $password_hash);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: index.php?sukses=Sign In berhasil! Silakan login menggunakan Email dan Password Anda.");
    exit();
} else {
    $stmt->close();
    header("Location: signin.php?error=Terjadi kesalahan saat menyimpan akun.");
    exit();
}
