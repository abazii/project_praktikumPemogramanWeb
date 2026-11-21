<?php
session_start();

// Jika sudah login, langsung arahkan sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'customer') {
        header("Location: home.php");
        exit();
    } elseif ($_SESSION['role'] === 'owner') {
        header("Location: dashboard_owner.php");
        exit();
    }
}

$error  = isset($_GET['error']) ? $_GET['error'] : '';
$sukses = isset($_GET['sukses']) ? $_GET['sukses'] : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BARBERBOR Retro Barbershop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body class="retro-body login-page-bg">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="retro-login-box w-100" style="max-width: 480px;">

            <!-- Header Logo Retro -->
            <div class="text-center mb-4">
                <div class="retro-badge-logo mx-auto mb-2">
                    <span class="badge-sub">EST. 1998 • GOOD BARBER FOR GOOD PEOPLE</span>
                    <h1 class="retro-brand-title mb-0">BARBERBOR</h1>
                    <span class="badge-sub">BARBERSHOP & SHOESCARE</span>
                </div>
                <p class="retro-subtitle mt-2 mb-0">Silakan login menggunakan Email dan Password Anda</p>
            </div>

            <!-- Notifikasi Error / Sukses -->
            <?php if ($error != ''): ?>
                <div class="alert retro-alert-danger mb-3" role="alert">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($sukses != ''): ?>
                <div class="alert retro-alert-success mb-3" role="alert">
                    <?= htmlspecialchars($sukses); ?>
                </div>
            <?php endif; ?>

            <!-- Form Login (Hanya Email & Password) -->
            <form id="formLogin" action="proses_login.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="loginEmail" class="form-label retro-label">EMAIL *</label>
                    <input type="email" id="loginEmail" name="email" class="form-control retro-input" placeholder="Masukkan email Anda" required>
                </div>

                <div class="mb-4">
                    <label for="loginPassword" class="form-label retro-label">PASSWORD *</label>
                    <input type="password" id="loginPassword" name="password" class="form-control retro-input" placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" class="btn retro-btn-primary w-100 mb-3">
                    LOGIN MASUK →
                </button>
            </form>

            <hr class="retro-divider my-3">

            <!-- Link ke Halaman Sign In -->
            <div class="text-center">
                <p class="retro-text small mb-2">Belum punya akun customer Barberbor?</p>
                <a href="signin.php" class="btn retro-btn-outline w-100">
                    DAFTAR AKUN BARU (SIGN IN)
                </a>
            </div>

        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>