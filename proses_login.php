<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$email    = strtolower(trim($_POST['email']));
$password = trim($_POST['password']);

if ($email === '' || $password === '') {
    header("Location: index.php?error=Email dan password wajib diisi!");
    exit();
}

// 1. Cek akun OWNER terlebih dahulu
$stmt_owner = $koneksi->prepare("SELECT id_owner, username, email, password FROM owners WHERE email = ?");
$stmt_owner->bind_param("s", $email);
$stmt_owner->execute();
$hasil_owner = $stmt_owner->get_result();

if ($hasil_owner->num_rows === 1) {
    $owner = $hasil_owner->fetch_assoc();
    if (password_verify($password, $owner['password']) || $password === $owner['password']) {
        $_SESSION['role']     = 'owner';
        $_SESSION['username'] = $owner['username'];
        $_SESSION['email']    = $owner['email'];

        $stmt_owner->close();
        header("Location: dashboard_owner.php");
        exit();
    } else {
        $stmt_owner->close();
        header("Location: index.php?error=Password Owner salah!");
        exit();
    }
}
$stmt_owner->close();

// 2. Cek akun CUSTOMER
$stmt_cust = $koneksi->prepare("SELECT id_customer, nama_lengkap, no_hp, alamat, email, password FROM customers WHERE email = ?");
$stmt_cust->bind_param("s", $email);
$stmt_cust->execute();
$hasil_cust = $stmt_cust->get_result();

if ($hasil_cust->num_rows === 1) {
    $cust = $hasil_cust->fetch_assoc();

    // Mendukung password ter-hash maupun plain text
    if (password_verify($password, $cust['password']) || $password === $cust['password']) {
        $_SESSION['role']         = 'customer';
        $_SESSION['id_customer']  = $cust['id_customer'];
        $_SESSION['nama_lengkap'] = $cust['nama_lengkap'];
        $_SESSION['no_hp']        = $cust['no_hp'];
        $_SESSION['alamat']       = $cust['alamat'];
        $_SESSION['email']        = $cust['email'];

        $stmt_cust->close();
        header("Location: home.php");
        exit();
    } else {
        $stmt_cust->close();
        header("Location: index.php?error=Password Customer salah!");
        exit();
    }
}
$stmt_cust->close();

header("Location: index.php?error=Email tidak ditemukan! Silakan Sign In terlebih dahulu.");
exit();
?>