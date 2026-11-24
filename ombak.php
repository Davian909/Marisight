<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'ombak';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ombak &amp; Gelombang Pulau Tunda - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>
body { font-family: 'Space Grotesk', sans-serif; }
.spinner {
    border: 3px solid rgba(10, 34, 64, 0.12);
    border-top: 3px solid #00BFFF;
    border-radius: 50%;
    width: 34px;
    height: 34px;
    margin: 24px auto;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-[#0A2240]">Informasi Ombak &amp; Gelombang Laut</h1>
            <p class="text-sm text-gray-600"><?= e(PULAU_TUNDA_NAME) ?> (<?= e(PULAU_TUNDA_LAT) ?>, <?= e(PULAU_TUNDA_LON) ?>) &bull; Diperbarui: <span id="waveUpdated">Memuat...</span></p>
        </div>
        <button type="button" id="refreshBtn"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0A2240] text-white text-sm font-semibold hover:bg-[#004D73] transition">
            <span class="material-symbols-outlined text-base">refresh</span>
            <span>Muat Ulang</span>
        </button>
    </div>

    <div id="waveError" class="hidden rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"></div>

    <!-- 6 METRIK SAAT INI -->
    <section class="bg-white shadow-sm rounded-2xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-[#0A2240] mb-4">Kondisi Jam Saat Ini</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">waves</span> Tinggi Gelombang
                </p>
                <p id="waveHeight" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>

            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">air</span> Kecepatan Angin
                </p>
                <p id="windSpeed" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>

            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">explore</span> Arah Angin
                </p>
                <p id="windDir" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>

            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">thermostat</span> Suhu Udara
                </p>
                <p id="temperature" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>

            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">navigation</span> Arah Gelombang
                </p>
                <p id="waveDirection" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>

            <div class="p-4 bg-sky-50 border border-sky-100 rounded-xl">
                <p class="font-semibold text-gray-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg text-[#0077b6]">timer</span> Periode Gelombang
                </p>
                <p id="wavePeriod" class="text-2xl font-bold text-[#0A2240] mt-1">-</p>
            </div>
        </div>
    </section>

    <!-- GRAFIK PRAKIRAAN 72 JAM -->
    <section class="bg-white shadow-sm rounded-2xl border border-gray-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-lg font-bold text-[#0A2240]">Grafik Prakiraan Tinggi Gelombang (72 Jam)</h2>
            <span class="text-xs text-gray-500">Sumber: Open-Meteo Marine API</span>
        </div>
        <div id="chartLoading" class="spinner"></div>
        <div class="relative h-[360px] w-full">
            <canvas id="waveChart" class="hidden"></canvas>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    const LAT = <?= json_encode(PULAU_TUNDA_LAT) ?>;
    const LON = <?= json_encode(PULAU_TUNDA_LON) ?>;

    // Parameter modern Open-Meteo: `current=` untuk kondisi jam saat ini, `hourly=` untuk grafik 3 hari
    const apiWave = `https://marine-api.open-meteo.com/v1/marine?latitude=${LAT}&longitude=${LON}&current=wave_height,wave_direction,wave_period&hourly=wave_height&forecast_days=3&timezone=Asia%2FJakarta`;
    const apiWeather = `https://api.open-meteo.com/v1/forecast?latitude=${LAT}&longitude=${LON}&current=temperature_2m,wind_speed_10m,wind_direction_10m&timezone=Asia%2FJakarta`;

    let chart = null;

    function formatLabel(iso) {
        const d = new Date(iso);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) + ' ' +
               d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
    }

    async function loadData() {
        const errBox = document.getElementById('waveError');
        errBox.classList.add('hidden');

        try {
            const [waveRes, weatherRes] = await Promise.all([fetch(apiWave), fetch(apiWeather)]);
            if (!waveRes.ok || !weatherRes.ok) {
                throw new Error('Gagal terhubung ke Open-Meteo API');
            }
            const wave = await waveRes.json();
            const weather = await weatherRes.json();

            const curWave = wave.current || {};
            const curWeather = weather.current || {};

            document.getElementById('waveHeight').textContent    = (curWave.wave_height ?? '-') + ' m';
            document.getElementById('waveDirection').textContent = (curWave.wave_direction ?? '-') + '°';
            document.getElementById('wavePeriod').textContent    = (curWave.wave_period ?? '-') + ' dtk';

            document.getElementById('temperature').textContent   = (curWeather.temperature_2m ?? '-') + ' °C';
            document.getElementById('windSpeed').textContent     = (curWeather.wind_speed_10m ?? '-') + ' km/jam';
            document.getElementById('windDir').textContent       = (curWeather.wind_direction_10m ?? '-') + '°';

            const updatedTime = curWave.time || curWeather.time;
            document.getElementById('waveUpdated').textContent = updatedTime ? formatLabel(updatedTime) + ' WIB' : '-';

            const waktu = (wave.hourly?.time || []).map(formatLabel);
            const tinggi = (wave.hourly?.wave_height || []).map(v => v ?? null);
            drawChart(waktu, tinggi);
        } catch (err) {
            document.getElementById('chartLoading').classList.add('hidden');
            errBox.textContent = 'Tidak dapat memuat data gelombang saat ini. Pastikan perangkat terhubung ke internet.';
            errBox.classList.remove('hidden');
        }
    }

    function drawChart(waktu, tinggi) {
        const loading = document.getElementById('chartLoading');
        const canvas = document.getElementById('waveChart');
        loading.classList.add('hidden');
        canvas.classList.remove('hidden');

        if (chart) chart.destroy();
        chart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: waktu,
                datasets: [{
                    label: 'Tinggi Gelombang (m)',
                    data: tinggi,
                    borderColor: '#0077b6',
                    backgroundColor: 'rgba(0, 191, 255, 0.18)',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Meter (m)' } },
                    x: { ticks: { maxTicksLimit: 12 } }
                }
            }
        });
    }

    document.getElementById('refreshBtn').addEventListener('click', loadData);
    loadData();
    setInterval(loadData, 300000);
})();
</script>

</body>
</html>
