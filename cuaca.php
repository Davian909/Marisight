<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'cuaca';

// Ambil cuaca Pulau Tunda dari Open-Meteo (parameter modern `current=`)
$lat = PULAU_TUNDA_LAT;
$lon = PULAU_TUNDA_LON;
$apiUrl = sprintf(
    'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m,surface_pressure&timezone=Asia%%2FJakarta',
    rawurlencode((string)$lat),
    rawurlencode((string)$lon)
);

$data = http_get_json($apiUrl, 5);
$isLive = is_array($data) && isset($data['current']);

if ($isLive) {
    $cur = $data['current'];
    $code = (int)($cur['weather_code'] ?? 2);
    $info = weather_code_info($code);
    $windDeg = (float)($cur['wind_direction_10m'] ?? 0);
    $pulauTunda = [
        'lokasi'         => PULAU_TUNDA_NAME,
        'suhu'           => round((float)($cur['temperature_2m'] ?? 28), 1),
        'terasa'         => round((float)($cur['apparent_temperature'] ?? 30), 1),
        'kelembaban'     => (int)($cur['relative_humidity_2m'] ?? 78),
        'tekanan'        => round((float)($cur['surface_pressure'] ?? 1010), 1),
        'kecepatanAngin' => round((float)($cur['wind_speed_10m'] ?? 10), 1),
        'arahAngin'      => round($windDeg),
        'arahLabel'      => wind_direction_label($windDeg),
        'waktu'          => date('d F Y H:i', strtotime((string)($cur['time'] ?? 'now'))) . ' WIB',
        'kondisi'        => $info['text'],
        'ikon'           => $info['icon'],
    ];
} else {
    $pulauTunda = [
        'lokasi'         => PULAU_TUNDA_NAME,
        'suhu'           => 28.0,
        'terasa'         => 31.0,
        'kelembaban'     => 78,
        'tekanan'        => 1010.0,
        'kecepatanAngin' => 10.0,
        'arahAngin'      => 250,
        'arahLabel'      => wind_direction_label(250),
        'waktu'          => date('d F Y H:i') . ' WIB (Mode Fallback Offline)',
        'kondisi'        => 'Data API Tidak Terjangkau',
        'ikon'           => 'cloud_off',
    ];
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cuaca Pulau Tunda - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>
body { font-family: 'Space Grotesk', sans-serif; }
</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full <?= $isLive ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
            <span class="material-symbols-outlined text-sm"><?= $isLive ? 'check_circle' : 'warning' ?></span>
            <span><?= $isLive ? 'Live Open-Meteo API' : 'Fallback Lokal (Periksa Koneksi Internet)' ?></span>
        </span>
    </div>

    <!-- KARTU UTAMA PULAU TUNDA -->
    <section class="rounded-2xl bg-white shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-[#0A2240] to-[#005A8D] text-white p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-wider text-sky-300 font-bold">Kondisi Cuaca Saat Ini</p>
                <h1 class="text-2xl sm:text-3xl font-bold mt-1"><?= e($pulauTunda['lokasi']) ?></h1>
                <p class="text-xs sm:text-sm text-sky-100 mt-1">
                    Koordinat: <?= e($lat) ?>, <?= e($lon) ?> &bull; Diperbarui: <?= e($pulauTunda['waktu']) ?>
                </p>
            </div>
            <div class="flex items-center gap-4 text-center sm:text-right">
                <span class="material-symbols-outlined text-6xl text-yellow-300"><?= e($pulauTunda['ikon']) ?></span>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold"><?= e($pulauTunda['suhu']) ?>°C</div>
                    <div class="text-sm text-sky-100 font-medium"><?= e($pulauTunda['kondisi']) ?></div>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 text-center">
                <span class="material-symbols-outlined text-2xl text-[#0077b6]">air</span>
                <p class="text-xs text-gray-500 font-medium mt-1">Kecepatan Angin</p>
                <p class="text-xl font-bold text-[#0A2240] mt-0.5"><?= e($pulauTunda['kecepatanAngin']) ?> km/jam</p>
            </div>

            <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 text-center">
                <span class="material-symbols-outlined text-2xl text-[#0077b6]">explore</span>
                <p class="text-xs text-gray-500 font-medium mt-1">Arah Angin</p>
                <p class="text-xl font-bold text-[#0A2240] mt-0.5"><?= e($pulauTunda['arahAngin']) ?>° (<?= e($pulauTunda['arahLabel']) ?>)</p>
            </div>

            <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 text-center">
                <span class="material-symbols-outlined text-2xl text-[#0077b6]">humidity_percentage</span>
                <p class="text-xs text-gray-500 font-medium mt-1">Kelembaban</p>
                <p class="text-xl font-bold text-[#0A2240] mt-0.5"><?= e($pulauTunda['kelembaban']) ?>%</p>
            </div>

            <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 text-center">
                <span class="material-symbols-outlined text-2xl text-[#0077b6]">speed</span>
                <p class="text-xs text-gray-500 font-medium mt-1">Tekanan / Terasa</p>
                <p class="text-xl font-bold text-[#0A2240] mt-0.5"><?= e($pulauTunda['tekanan']) ?> hPa</p>
                <p class="text-xs text-gray-500">Terasa <?= e($pulauTunda['terasa']) ?>°C</p>
            </div>
        </div>

        <!-- CUACA LOKASI PENGGUNA -->
        <div class="border-t border-gray-200 px-6 sm:px-8 py-6 bg-slate-50 text-center">
            <h2 class="text-lg font-bold text-[#0A2240] flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[#0077b6]">my_location</span>
                <span>Kondisi Cuaca di Lokasi Anda Saat Ini</span>
            </h2>
            <p id="local-weather" class="mt-2 text-sm text-gray-600">Mengambil data geolokasi perangkat Anda...</p>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    const out = document.getElementById('local-weather');
    if (!navigator.geolocation) {
        out.textContent = 'Browser Anda tidak mendukung fitur geolokasi.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            const lat = pos.coords.latitude.toFixed(4);
            const lon = pos.coords.longitude.toFixed(4);
            try {
                const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code,wind_speed_10m&timezone=Asia%2FJakarta`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                const cur = data.current;
                const mapped = (window.MARISIGHT_WEATHER_MAP && window.MARISIGHT_WEATHER_MAP[cur.weather_code])
                    || { text: 'Berawan', icon: 'cloud' };

                out.innerHTML = '';
                const line1 = document.createElement('div');
                line1.className = 'font-semibold text-[#0A2240]';
                line1.textContent = `Koordinat Anda: ${lat}, ${lon}`;
                const line2 = document.createElement('div');
                line2.className = 'mt-1 text-gray-700';
                line2.textContent = `Suhu: ${cur.temperature_2m}°C • Kondisi: ${mapped.text} • Angin: ${cur.wind_speed_10m} km/jam`;
                out.appendChild(line1);
                out.appendChild(line2);
            } catch (err) {
                out.textContent = 'Gagal memuat data cuaca untuk lokasi Anda (periksa koneksi internet).';
            }
        },
        () => {
            out.textContent = 'Izin lokasi tidak diberikan. Menampilkan data utama Pulau Tunda di atas.';
        },
        { timeout: 6000 }
    );
})();
</script>

</body>
</html>
