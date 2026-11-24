<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

start_secure_session();

$basePath   = '../';
$activePage = 'grafik';

$conn = get_db_connection();
$dbReady = $conn !== null && db_table_exists($conn, 'data_pasut');
$rows = [];

if ($dbReady) {
    try {
        $res = $conn->query('SELECT tanggal, tinggi_maks, tinggi_min, amplitudo FROM data_pasut ORDER BY tanggal ASC');
        if ($res) {
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
        }
    } catch (mysqli_sql_exception $e) {
        error_log('[MARISIGHT Grafik Pasut] ' . $e->getMessage());
        $dbReady = false;
    }
}

$labels = [];
$tinggiMaks = [];
$tinggiMin = [];

foreach ($rows as $row) {
    $labels[]     = (string)$row['tanggal'];
    $tinggiMaks[] = (float)$row['tinggi_maks'];
    $tinggiMin[]  = (float)$row['tinggi_min'];
}

$jumlah = count($tinggiMaks);
$rataMaks = $jumlah > 0 ? array_sum($tinggiMaks) / $jumlah : 0.0;
$maksTertinggi = $jumlah > 0 ? max($tinggiMaks) : 0.0;
$terakhir = $jumlah > 0 ? end($labels) : '-';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Grafik Pasang Surut Pulau Tunda - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
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
                <span class="material-symbols-outlined text-3xl text-[#0077b6]">monitoring</span>
                <span>Grafik Pasang Surut Pulau Tunda</span>
            </h1>
            <p class="mt-1 text-sm text-gray-600">Visualisasi tinggi air pasang maksimum dan surut minimum harian.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="tabel.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-gray-200 text-sm font-semibold text-[#0A2240] hover:border-[#00BFFF] transition">
                <span class="material-symbols-outlined text-base">table_chart</span>
                <span>Lihat Tabel</span>
            </a>
            <a href="api/ambil_pasut.php" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white border border-gray-200 text-sm font-semibold text-[#0A2240] hover:border-[#00BFFF] transition">
                <span class="material-symbols-outlined text-base">data_object</span>
                <span>JSON API</span>
            </a>
            <a href="generate_pasut.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0A2240] text-white text-sm font-semibold hover:bg-[#004D73] transition">
                <span class="material-symbols-outlined text-base">autorenew</span>
                <span>Perbarui Data</span>
            </a>
        </div>
    </div>

    <?php if (!$dbReady): ?>
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-6 text-amber-900 text-sm space-y-2">
            <p class="font-bold text-base flex items-center gap-2">
                <span class="material-symbols-outlined">database</span>
                <span>Database <code class="bg-amber-100 px-1.5 py-0.5 rounded">marisight</code> belum terhubung atau tabel <code class="bg-amber-100 px-1.5 py-0.5 rounded">data_pasut</code> belum dibuat</span>
            </p>
            <p>Aktifkan MySQL di XAMPP, lalu impor file <code class="bg-amber-100 px-1.5 py-0.5 rounded">database/schema.sql</code> melalui phpMyAdmin untuk menampilkan grafik pasang surut.</p>
        </div>
    <?php elseif ($jumlah === 0): ?>
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 text-[#0A2240] text-sm">
            <p class="font-bold">Belum ada data pada tabel <code>data_pasut</code>.</p>
            <p class="mt-1">Silakan jalankan <a href="generate_pasut.php" class="underline font-bold">Generator Data Pasut</a> (butuh login) atau impor ulang <code>database/schema.sql</code>.</p>
        </div>
    <?php else: ?>
        <!-- RINGKASAN STATISTIK -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Jumlah Data Harian</p>
                <p class="text-2xl font-bold text-[#0A2240] mt-1"><?= e($jumlah) ?> hari</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Rata-rata / Puncak Pasang</p>
                <p class="text-2xl font-bold text-[#0077b6] mt-1"><?= e(number_format($rataMaks, 2)) ?> m <span class="text-sm font-medium text-gray-500">(maks <?= e(number_format($maksTertinggi, 2)) ?> m)</span></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase">Tanggal Data Terakhir</p>
                <p class="text-2xl font-bold text-[#0A2240] mt-1"><?= e($terakhir) ?></p>
            </div>
        </section>

        <!-- GRAFIK GARIS & BATANG -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-[#0A2240] text-center mb-4">Grafik Garis (Pasang Maks &amp; Surut Min)</h2>
                <div class="relative h-[360px]">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-[#0A2240] text-center mb-4">Grafik Batang (Tinggi Maksimum)</h2>
                <div class="relative h-[360px]">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

<?php if ($dbReady && $jumlah > 0): ?>
<script>
(function () {
    const labels = <?= json_encode($labels) ?>;
    const tinggiMaks = <?= json_encode($tinggiMaks) ?>;
    const tinggiMin = <?= json_encode($tinggiMin) ?>;

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Tinggi Pasang Maks (m)',
                    data: tinggiMaks,
                    borderColor: '#0077b6',
                    backgroundColor: 'rgba(0, 119, 182, 0.16)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Tinggi Surut Min (m)',
                    data: tinggiMin,
                    borderColor: '#00b4d8',
                    backgroundColor: 'rgba(0, 180, 216, 0.08)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 2.5,
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Meter (m)' } }
            }
        }
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Tinggi Maksimum (m)',
                data: tinggiMaks,
                backgroundColor: '#0096c7',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Meter (m)' } }
            }
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>
