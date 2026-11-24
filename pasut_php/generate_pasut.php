<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

/**
 * Menghasilkan prediksi data pasang surut harmonik (pola siklus lunar 29,5 hari)
 * secara dinamis mulai dari tanggal yang ditentukan, menggunakan Prepared Statements + Transaksi.
 */
function generate_tide_dataset(mysqli $conn, DateTimeImmutable $startDate, int $days): int
{
    $days = max(7, min(60, $days));

    $conn->begin_transaction();
    try {
        $conn->query('DELETE FROM data_pasut');

        $stmt = $conn->prepare(
            'INSERT INTO data_pasut (tanggal, jam_pasang, jam_surut, tinggi_maks, tinggi_min, amplitudo, intensitas_ikan)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $synodic = 29.53059;
        $knownNewMoon = new DateTimeImmutable('2000-01-06 18:14:00', new DateTimeZone('UTC'));

        for ($i = 0; $i < $days; $i++) {
            $current = $startDate->modify("+{$i} days");
            $tgl = $current->format('Y-m-d');

            // Hitung fase siklus lunar untuk menentukan pasang purnama (spring) vs perbani (neap)
            $diffDays = ($current->getTimestamp() - $knownNewMoon->getTimestamp()) / 86400.0;
            $lunarPhase = fmod(fmod($diffDays, $synodic) + $synodic, $synodic) / $synodic; // 0..1

            // Dua puncak spring tide per bulan lunar (Bulan Baru & Bulan Purnama -> cos(4*pi*phase))
            $springFactor = (cos(4.0 * M_PI * $lunarPhase) + 1.0) / 2.0; // 0..1

            $mean = 1.05;
            $halfRange = 0.14 + ($springFactor * 0.24); // amplitudo total ~0.28m s/d ~0.76m
            $tinggiMaks = round($mean + $halfRange, 2);
            $tinggiMin  = round(max(0.45, $mean - $halfRange), 2);
            $amplitudo  = round($tinggiMaks - $tinggiMin, 2);

            // Pergeseran jam pasang lunar ~50 menit lebih lambat setiap hari
            $pasangMinutes = (int)(240 + ($i * 50)) % 1440;
            $surutMinutes  = ($pasangMinutes + 375) % 1440; // ~6 jam 15 menit setelah pasang

            $jamPasang = sprintf('%02d:%02d', intdiv($pasangMinutes, 60), $pasangMinutes % 60);
            $jamSurut  = sprintf('%02d:%02d', intdiv($surutMinutes, 60), $surutMinutes % 60);

            if ($amplitudo >= 0.50) {
                $ikan = 'Aktif';
            } elseif ($amplitudo >= 0.35) {
                $ikan = 'Sedang';
            } else {
                $ikan = 'Rendah';
            }

            $stmt->bind_param('sssddds', $tgl, $jamPasang, $jamSurut, $tinggiMaks, $tinggiMin, $amplitudo, $ikan);
            $stmt->execute();
        }

        $stmt->close();
        $conn->commit();
        return $days;
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

// Mode CLI: `php pasut_php/generate_pasut.php [jumlah_hari]`
if (PHP_SAPI === 'cli') {
    $daysArg = isset($argv[1]) ? (int)$argv[1] : 30;
    $conn = get_db_connection();
    if (!$conn || !db_table_exists($conn, 'data_pasut')) {
        fwrite(STDERR, "Gagal: Database 'marisight' atau tabel 'data_pasut' belum siap. Impor database/schema.sql terlebih dahulu.\n");
        exit(1);
    }
    try {
        $count = generate_tide_dataset($conn, new DateTimeImmutable('today'), $daysArg);
        echo "Berhasil menghasilkan {$count} hari data pasang surut dinamis mulai hari ini (" . date('Y-m-d') . ").\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
        exit(1);
    }
}

// Mode Web (Terproteksi: GET hanya menampilkan UI, eksekusi wajib login + POST + CSRF)
start_secure_session();

$basePath   = '../';
$activePage = 'tabel';

$conn = get_db_connection();
$dbReady = $conn !== null && db_table_exists($conn, 'data_pasut');
$loggedIn = is_logged_in();

$successMessage = '';
$errorMessage = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!$loggedIn) {
        $errorMessage = 'Anda harus masuk (login) terlebih dahulu untuk memperbarui data pasang surut.';
    } elseif (!verify_csrf($_POST['_csrf_token'] ?? null)) {
        $errorMessage = 'Token keamanan CSRF tidak valid. Muat ulang halaman dan coba lagi.';
    } elseif (!$dbReady) {
        $errorMessage = 'Database marisight atau tabel data_pasut belum siap.';
    } else {
        $startRaw = trim((string)($_POST['start_date'] ?? date('Y-m-d')));
        $daysRaw  = (int)($_POST['days'] ?? 30);
        $startObj = DateTimeImmutable::createFromFormat('Y-m-d', $startRaw) ?: new DateTimeImmutable('today');

        try {
            $count = generate_tide_dataset($conn, $startObj, $daysRaw);
            $successMessage = "Berhasil memperbarui {$count} hari data pasang surut mulai " . $startObj->format('d F Y') . '.';
        } catch (Throwable $e) {
            error_log('[MARISIGHT Generate Pasut] ' . $e->getMessage());
            $errorMessage = 'Terjadi kesalahan saat menyimpan data pasang surut.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perbarui Data Pasang Surut - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>body { font-family: 'Space Grotesk', sans-serif; }</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-2xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div>
        <a href="tabel.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span>Kembali ke Tabel Pasang Surut</span>
        </a>
        <h1 class="mt-2 text-3xl font-bold text-[#0A2240]">Generator Data Pasang Surut Dinamis</h1>
        <p class="mt-1 text-sm text-gray-600">
            Menghasilkan prediksi pasang surut harian berbasis fase siklus lunar (Spring/Neap Tide) mulai tanggal pilihan.
        </p>
    </div>

    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        <?php if (!$dbReady): ?>
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                Database <code>marisight</code> atau tabel <code>data_pasut</code> belum siap. Impor <code>database/schema.sql</code> terlebih dahulu.
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 space-y-2">
                <p class="font-bold"><?= e($successMessage) ?></p>
                <div class="flex gap-3">
                    <a href="grafik.php" class="underline font-semibold">Lihat Grafik</a>
                    <a href="tabel.php" class="underline font-semibold">Lihat Tabel</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!$loggedIn): ?>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-5 text-sm text-[#0A2240] space-y-3">
                <p class="font-bold">Akses Terproteksi</p>
                <p>Untuk melindungi data pasang surut dari penghapusan tidak sengaja, pembaruan data melalui web memerlukan login akun MARISIGHT (atau jalankan melalui CLI: <code>php pasut_php/generate_pasut.php</code>).</p>
                <a href="../login.php?redirect=pasut_php/generate_pasut.php"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0A2240] text-white font-bold hover:bg-[#004D73] transition">
                    <span class="material-symbols-outlined text-base">login</span>
                    <span>Masuk untuk Memperbarui Data</span>
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="generate_pasut.php" class="space-y-4">
                <?= csrf_field() ?>

                <label class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-800 mb-1">Tanggal Mulai</span>
                    <input type="date" name="start_date" value="<?= e(date('Y-m-d')) ?>" required
                           class="form-input rounded-lg border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]">
                </label>

                <label class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-800 mb-1">Durasi Prediksi (7–60 hari)</span>
                    <input type="number" name="days" value="30" min="7" max="60" required
                           class="form-input rounded-lg border-gray-300 text-sm focus:border-[#00BFFF] focus:ring-[#00BFFF]">
                </label>

                <button type="submit"
                        class="w-full h-11 rounded-lg bg-[#0A2240] text-white font-bold text-sm hover:bg-[#004D73] transition">
                    Generate &amp; Perbarui Data Pasut
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

</body>
</html>
