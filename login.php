<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

start_secure_session();

$redirectTarget = sanitize_redirect($_GET['redirect'] ?? $_POST['redirect'] ?? null, 'index.php');

if (is_logged_in()) {
    header('Location: ' . $redirectTarget);
    exit;
}

$conn = get_db_connection();
$dbReady = $conn !== null && db_table_exists($conn, 'users');
$errorMessage = '';
$infoMessage = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $infoMessage = 'Registrasi berhasil! Silakan masuk menggunakan akun baru Anda.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf($_POST['_csrf_token'] ?? null)) {
        $errorMessage = 'Sesi formulir tidak valid atau kedaluwarsa. Silakan muat ulang halaman dan coba lagi.';
    } elseif (!$dbReady) {
        $errorMessage = 'Database belum siap. Pastikan MySQL aktif dan database/schema.sql sudah diimpor.';
    } else {
        $userInput = trim((string)($_POST['username'] ?? ''));
        $password  = (string)($_POST['password'] ?? '');

        if ($userInput === '' || $password === '') {
            $errorMessage = 'Nama pengguna/email dan kata sandi wajib diisi.';
        } else {
            try {
                $stmt = $conn->prepare(
                    'SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1'
                );
                $stmt->bind_param('ss', $userInput, $userInput);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result ? $result->fetch_assoc() : null;
                $stmt->close();

                if ($user && password_verify($password, (string)$user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id']   = (int)$user['id'];
                    $_SESSION['username']  = (string)$user['username'];

                    header('Location: ' . $redirectTarget);
                    exit;
                }

                $errorMessage = 'Nama pengguna/email atau kata sandi salah.';
            } catch (mysqli_sql_exception $e) {
                error_log('[MARISIGHT Login Error] ' . $e->getMessage());
                $errorMessage = 'Terjadi gangguan pada database saat memproses login.';
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
<title>Masuk - MARISIGHT</title>
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

<!-- Dekorasi gelombang latar lokal (tanpa ketergantungan URL gambar sementara) -->
<div class="fixed bottom-0 left-0 right-0 h-44 pointer-events-none opacity-35" aria-hidden="true">
    <svg class="w-full h-full block" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="#00BFFF" d="M0,224L80,208C160,192,320,160,480,170.7C640,181,800,235,960,245.3C1120,256,1280,224,1360,208L1440,192V320H0Z"/>
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
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0A2240]">Selamat Datang Kembali</h1>
            <p class="text-sm text-gray-600 mt-1">Masuk ke akun MARISIGHT Anda (opsional untuk personalisasi &amp; kelola data pasut).</p>
        </div>

        <?php if (!$dbReady): ?>
            <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-bold">Database MySQL belum terhubung / belum diimpor</p>
                <p class="mt-1">Jalankan MySQL di XAMPP lalu impor file <code class="bg-amber-100 px-1 rounded">database/schema.sql</code> agar fitur login dapat digunakan.</p>
            </div>
        <?php endif; ?>

        <?php if ($infoMessage !== ''): ?>
            <div class="mb-5 rounded-xl border border-emerald-300 bg-emerald-50 p-3.5 text-sm text-emerald-800">
                <?= e($infoMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="mb-5 rounded-xl border border-red-300 bg-red-50 p-3.5 text-sm text-red-700">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="flex flex-col gap-5">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1.5">Nama Pengguna atau Email</span>
                <input name="username"
                       type="text"
                       required
                       autocomplete="username"
                       value="<?= e($_POST['username'] ?? '') ?>"
                       placeholder="contoh@email.com"
                       class="form-input w-full rounded-lg border-gray-300 h-11 px-4 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <label class="flex flex-col">
                <span class="text-sm font-semibold text-gray-800 mb-1.5">Kata Sandi</span>
                <input name="password"
                       type="password"
                       required
                       autocomplete="current-password"
                       placeholder="Masukkan kata sandi Anda"
                       class="form-input w-full rounded-lg border-gray-300 h-11 px-4 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
            </label>

            <button type="submit"
                    class="w-full h-11 rounded-lg bg-[#0A2240] text-white font-bold text-sm hover:bg-[#004D73] transition">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="register.php" class="font-bold text-[#0A2240] hover:text-[#00BFFF] transition">Daftar sekarang</a>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-sky-200/80">&copy; <?= date('Y') ?> MARISIGHT. Semua hak dilindungi.</p>
</div>

</body>
</html>
