<?php
session_start();

if (isset($_SESSION['role'])) {
    header("Location: home.php");
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BARBERBOR Retro Barbershop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="retro-body login-page-bg">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="retro-login-box w-100" style="max-width: 540px;">

            <!-- Header Logo Retro -->
            <div class="text-center mb-4">
                <div class="retro-badge-logo mx-auto mb-2">
                    <span class="badge-sub">JOIN THE CLASSIC CLUB</span>
                    <h1 class="retro-brand-title mb-0">SIGN IN BARBERBOR</h1>
                    <span class="badge-sub">BUAT AKUN CUSTOMER BARU</span>
                </div>
                <p class="retro-subtitle mt-2 mb-0">Lengkapi data diri Anda untuk membuat akun</p>
            </div>

            <!-- Notifikasi Error Validasi -->
            <?php if ($error != ''): ?>
                <div class="alert retro-alert-danger mb-3" role="alert">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Form Sign In -->
            <form id="formSignIn" action="proses_signin.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="signinNama" class="form-label retro-label">NAMA LENGKAP *</label>
                    <input type="text" id="signinNama" name="nama_lengkap" class="form-control retro-input" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="mb-3">
                    <label for="signinHp" class="form-label retro-label">NOMOR HANDPHONE *</label>
                    <input type="tel" id="signinHp" name="no_hp" class="form-control retro-input" placeholder="Contoh: 081234567890" required>
                </div>

                <div class="mb-3">
                    <label for="signinAlamat" class="form-label retro-label">ALAMAT LENGKAP *</label>
                    <textarea id="signinAlamat" name="alamat" rows="2" class="form-control retro-input" placeholder="Masukkan alamat lengkap tempat tinggal" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="signinEmail" class="form-label retro-label">EMAIL *</label>
                    <input type="email" id="signinEmail" name="email" class="form-control retro-input" placeholder="nama@email.com" required>
                </div>

                <div class="mb-4">
                    <label for="signinPassword" class="form-label retro-label">PASSWORD * (Minimal 6 Karakter)</label>
                    <input type="password" id="signinPassword" name="password" class="form-control retro-input" placeholder="Buat password minimal 6 karakter" minlength="6" required>
                </div>

                <button type="submit" class="btn retro-btn-primary w-100 mb-3">
                    DAFTAR & BUAT AKUN →
                </button>
            </form>

            <hr class="retro-divider my-3">

            <div class="text-center">
                <p class="retro-text small mb-2">Sudah memiliki akun Barberbor?</p>
                <a href="index.php" class="btn retro-btn-outline w-100">
                    ← KEMBALI KE HALAMAN LOGIN
                </a>
            </div>

        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>