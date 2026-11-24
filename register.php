<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

start_secure_session();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn = get_db_connection();
$dbReady = $conn !== null && db_table_exists($conn, 'users');
$errorMessage = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf($_POST['_csrf_token'] ?? null)) {
        $errorMessage = 'Sesi formulir tidak valid atau kedaluwarsa. Silakan muat ulang halaman.';
    } elseif (!$dbReady) {
        $errorMessage = 'Database belum siap. Pastikan MySQL aktif dan database/schema.sql sudah diimpor.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['password_confirm'] ?? '');

        if (strlen($username) < 3 || strlen($username) > 50 || !preg_match('/^[a-zA-Z0-9_.\- ]+$/', $username)) {
            $errorMessage = 'Nama pengguna harus 3–50 karakter dan hanya boleh huruf, angka, spasi, titik, tanda hubung, atau garis bawah.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Format alamat email tidak valid.';
        } elseif (strlen($password) < 6) {
            $errorMessage = 'Kata sandi minimal 6 karakter.';
        } elseif ($password !== $confirm) {
            $errorMessage = 'Konfirmasi kata sandi tidak cocok.';
        } else {
            try {
                $check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                $check->bind_param('ss', $username, $email);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $errorMessage = 'Nama pengguna atau email sudah terdaftar.';
                    $check->close();
                } else {
                    $check->close();
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                    $insert->bind_param('sss', $username, $email, $hash);
                    $insert->execute();
                    $insert->close();

                    header('Location: login.php?registered=1');
                    exit;
                }
            } catch (mysqli_sql_exception $e) {
                error_log('[MARISIGHT Register Error] ' . $e->getMessage());
                $errorMessage = 'Terjadi kesalahan saat menyimpan akun baru.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Daftar - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>
body { font-family: 'Space Grotesk', sans-serif; }
</style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#061830] via-[#0A2B4C] to-[#005A8D] flex flex-col items-center justify-center p-4 relative overflow-x-hidden">

<div class="fixed bottom-0 left-0 right-0 h-44 pointer-events-none opacity-35" aria-hidden="true">
    <svg class="w-full h-full block" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="#00BFFF" d="M0,256L80,234.7C160,213,320,171,480,176C640,181,800,235,960,234.7C1120,235,1280,181,1360,154.7L1440,128V320H0Z"/>
    </svg>
</div>

<div class="relative z-10 w-full max-w-md">
    <div class="mb-6 flex flex-col items-center text-white">
        <a href="index.php" class="flex items-center gap-3 mb-2">
            <img src="image2/Logo Marisight.png" alt="Logo MARISIGHT" class="h-12 w-auto object-contain">
            <span class="text-2xl font-bold tracking-wider">MARISIGHT</span>
        </a>
        <a href="index.php" class="inline-flex items-center gap-1 text-xs text-sky-200 hover:text-white transition">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <div class="w-full rounded-2xl bg-white p-6 sm:p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0A2240]">Buat Akun MARISIGHT</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar untuk personalisasi dashboard dan akses pembaruan data.</p>
        </div>

        <?php if (!$dbReady): ?>
            <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-bold">Database MySQL belum terhubung / belum diimpor</p>
                <p class="mt-1">Jalankan MySQL di XAMPP lalu impor file <code class="bg-amber-100 px-1 rounded">database/schema.sql</code>.</p>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="mb-5 rounded-xl border border-red-300 bg-red-50 p-3.5 text-sm text-red-700">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="flex flex-col gap-4">
            <?= csrf_field() ?>

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1">Nama Pengguna</span>
                <input name="username"
                       type="text"
                       required
                       minlength="3"
                       maxlength="50"
                       autocomplete="username"
                       value="<?= e($_POST['username'] ?? '') ?>"
                       placeholder="Masukkan nama pengguna"
                       class="form-input rounded-lg h-11 px-4 border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1">Alamat Email</span>
                <input name="email"
                       type="email"
                       required
                       autocomplete="email"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       placeholder="nama@contoh.com"
                       class="form-input rounded-lg h-11 px-4 border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1">Kata Sandi</span>
                <input name="password"
                       type="password"
                       required
                       minlength="6"
                       autocomplete="new-password"
                       placeholder="Minimal 6 karakter"
                       class="form-input rounded-lg h-11 px-4 border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1">Konfirmasi Kata Sandi</span>
                <input name="password_confirm"
                       type="password"
                       required
                       minlength="6"
                       autocomplete="new-password"
                       placeholder="Ulangi kata sandi"
                       class="form-input rounded-lg h-11 px-4 border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <button type="submit"
                    class="mt-2 w-full h-11 rounded-lg bg-[#0A2240] text-white font-bold text-sm hover:bg-[#004D73] transition">
                Buat Akun
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="login.php" class="font-bold text-[#0A2240] hover:text-[#00BFFF] transition">Masuk di sini</a>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-sky-200/80">&copy; <?= date('Y') ?> MARISIGHT. Semua hak dilindungi.</p>
</div>

</body>
</html>
