<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

start_secure_session();

$basePath   = '../';
$activePage = 'tabel';

$conn = get_db_connection();
$dbReady = $conn !== null && db_table_exists($conn, 'data_pasut');
$rows = [];

if ($dbReady) {
    try {
        $res = $conn->query('SELECT tanggal, jam_pasang, jam_surut, tinggi_maks, tinggi_min, amplitudo, intensitas_ikan FROM data_pasut ORDER BY tanggal ASC');
        if ($res) {
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
        }
    } catch (mysqli_sql_exception $e) {
        error_log('[MARISIGHT Tabel Pasut] ' . $e->getMessage());
        $dbReady = false;
    }
}

function render_intensitas_badge(string $status): array
{
    $norm = strtolower(trim($status));
    if (str_contains($norm, 'aktif')) {
        return ['label' => 'Aktif', 'class' => 'bg-emerald-100 text-emerald-800'];
    }
    if (str_contains($norm, 'sedang')) {
        return ['label' => 'Sedang', 'class' => 'bg-sky-100 text-sky-800'];
    }
    if (str_contains($norm, 'rendah')) {
        return ['label' => 'Rendah', 'class' => 'bg-slate-100 text-slate-700'];
    }
    return ['label' => $status !== '' ? $status : '-', 'class' => 'bg-gray-100 text-gray-700'];
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tabel Pasang Surut Pulau Tunda - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<link rel="stylesheet" href="assets/style.css">
<style>body { font-family: 'Space Grotesk', sans-serif; }</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="../index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-2 text-3xl font-bold text-[#0A2240] flex items-center gap-2">
                <span class="material-symbols-outlined text-3xl text-[#0077b6]">table_chart</span>
                <span>Tabel Pasang Surut Pulau Tunda</span>
            </h1>
            <p class="mt-1 text-sm text-gray-600">Jadwal jam pasang, jam surut, amplitudo, serta estimasi intensitas aktivitas ikan.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="grafik.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-gray-200 text-sm font-semibold text-[#0A2240] hover:border-[#00BFFF] transition">
                <span class="material-symbols-outlined text-base">monitoring</span>
                <span>Lihat Grafik</span>
            </a>
            <a href="generate_pasut.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0A2240] text-white text-sm font-semibold hover:bg-[#004D73] transition">
                <span class="material-symbols-outlined text-base">autorenew</span>
                <span>Perbarui Data</span>
            </a>
        </div>
    </div>

    <?php if (!$dbReady): ?>
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-6 text-amber-900 text-sm space-y-2">
            <p class="font-bold text-base">Database <code>marisight</code> belum terhubung atau tabel <code>data_pasut</code> belum dibuat.</p>
            <p>Jalankan MySQL di XAMPP lalu impor file <code class="bg-amber-100 px-1.5 py-0.5 rounded">database/schema.sql</code> melalui phpMyAdmin.</p>
        </div>
    <?php elseif (empty($rows)): ?>
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 text-[#0A2240] text-sm">
            <p class="font-bold">Data pasang surut masih kosong.</p>
            <p class="mt-1">Gunakan <a href="generate_pasut.php" class="underline font-bold">Generator Data Pasut</a> atau impor ulang <code>database/schema.sql</code>.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full text-sm text-left">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Pasang</th>
                            <th>Jam Surut</th>
                            <th>Tinggi Maks (m)</th>
                            <th>Tinggi Min (m)</th>
                            <th>Amplitudo (m)</th>
                            <th>Intensitas Ikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php $badge = render_intensitas_badge((string)$row['intensitas_ikan']); ?>
                            <tr>
                                <td class="font-semibold text-[#0A2240] whitespace-nowrap"><?= e($row['tanggal']) ?></td>
                                <td class="whitespace-nowrap"><?= e($row['jam_pasang']) ?> WIB</td>
                                <td class="whitespace-nowrap"><?= e($row['jam_surut']) ?> WIB</td>
                                <td class="font-semibold text-[#0077b6]"><?= e(number_format((float)$row['tinggi_maks'], 2)) ?></td>
                                <td><?= e(number_format((float)$row['tinggi_min'], 2)) ?></td>
                                <td><?= e(number_format((float)$row['amplitudo'], 2)) ?></td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= e($badge['class']) ?>">
                                        <?= e($badge['label']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

</body>
</html>
