<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

// Unduh semua gambar batimetri sebagai ZIP (aman dari race condition & berjalan walau extension=zip belum aktif)
if (isset($_GET['zip'])) {
    $entries = [];
    for ($i = 1; $i <= 15; $i++) {
        $path = __DIR__ . '/assets/data' . $i . '.png';
        if (is_file($path)) {
            $entries['data' . $i . '.png'] = $path;
        }
    }

    if (empty($entries)) {
        http_response_code(404);
        echo 'File gambar batimetri tidak ditemukan.';
        exit;
    }

    $tmpBase = tempnam(sys_get_temp_dir(), 'marisight_bath_');
    if ($tmpBase === false) {
        http_response_code(500);
        echo 'Gagal menyiapkan file sementara ZIP.';
        exit;
    }
    $zipPath = $tmpBase . '.zip';
    @unlink($tmpBase);

    if (!build_zip_archive($entries, $zipPath) || !is_file($zipPath)) {
        @unlink($zipPath);
        http_response_code(500);
        echo 'Gagal membuat arsip ZIP batimetri.';
        exit;
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="Bathymetry_Pulau_Tunda.zip"');
    header('Content-Length: ' . (string)filesize($zipPath));
    header('Cache-Control: no-store');
    readfile($zipPath);
    @unlink($zipPath);
    exit;
}

$basePath   = '';
$activePage = 'bath';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Batimetri Pulau Tunda - MARISIGHT</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
body { font-family: 'Space Grotesk', sans-serif; }
#mapid { height: 380px; z-index: 1; }
</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- HEADER + SUB-NAV -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-[#0A2240]">Batimetri Pulau Tunda</h1>
            <p class="mt-1 text-sm sm:text-base text-gray-600">
                Peta lokasi serta 15 dataset visualisasi kontur, profil kedalaman, dan permukaan 3D dasar laut Pulau Tunda.
            </p>
        </div>

        <a href="bath.php?zip=1"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-3 text-sm shadow transition">
            <span class="material-symbols-outlined text-xl">folder_zip</span>
            <span>Unduh Semua Gambar (ZIP)</span>
        </a>
    </div>

    <!-- TAB NAVIGASI SUB-HALAMAN BATIMETRI -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <a href="bath.php" class="px-4 py-2 rounded-lg text-sm font-bold bg-[#0A2240] text-white">Galeri Gabungan (15)</a>
        <a href="contour.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Peta Kontur (15)</a>
        <a href="profile.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Profil Kedalaman (15)</a>
        <a href="surface.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Surface 3D (15)</a>
    </div>

    <!-- PETA INTERAKTIF -->
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 class="text-lg font-bold text-[#0A2240]">Peta Lokasi Survei — <?= e(PULAU_TUNDA_NAME) ?></h2>
            <p class="text-xs font-mono bg-slate-100 px-3 py-1 rounded-lg text-slate-700">
                Lat: <span id="lat_val"><?= e(PULAU_TUNDA_LAT) ?></span> | Lng: <span id="lng_val"><?= e(PULAU_TUNDA_LON) ?></span>
            </p>
        </div>
        <div id="mapid" class="w-full rounded-xl border border-gray-200 overflow-hidden"></div>
    </section>

    <!-- GALERI 15 DATASET GABUNGAN -->
    <section>
        <h2 class="text-2xl font-bold text-[#0A2240] mb-5">15 Dataset Lengkap Batimetri (Kontur + Profil + 3D)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php for ($i = 1; $i <= 15; $i++): ?>
                <article class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <button type="button"
                            data-img="assets/data<?= $i ?>.png"
                            data-title="Dataset Batimetri <?= $i ?>"
                            class="zoom-trigger group relative bg-white p-3 h-56 flex items-center justify-center overflow-hidden cursor-zoom-in">
                        <img src="assets/data<?= $i ?>.png"
                             alt="Batimetri Pulau Tunda Dataset <?= $i ?>"
                             loading="lazy"
                             class="max-h-full max-w-full object-contain transition-transform group-hover:scale-105">
                    </button>
                    <div class="p-4 border-t border-gray-100 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-[#0A2240]">Dataset <?= $i ?></h3>
                            <p class="text-xs text-gray-500">Kontur, Profil &amp; Surface 3D</p>
                        </div>
                        <a href="assets/data<?= $i ?>.png" download="batimetri_pulau_tunda_<?= $i ?>.png"
                           class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            <span class="material-symbols-outlined text-base">download</span>
                            <span>Unduh</span>
                        </a>
                    </div>
                </article>
            <?php endfor; ?>
        </div>
    </section>
</main>

<!-- MODAL ZOOM -->
<div id="imgModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="relative bg-white rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
            <h3 id="modalTitle" class="font-bold text-[#0A2240]">Pratinjau Gambar</h3>
            <button type="button" id="closeModal" class="p-1 rounded-lg hover:bg-gray-100 text-gray-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-4 overflow-auto flex items-center justify-center bg-slate-50">
            <img id="modalImg" src="" alt="Pratinjau" class="max-h-[75vh] w-auto object-contain">
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    const lat = <?= json_encode(PULAU_TUNDA_LAT) ?>;
    const lng = <?= json_encode(PULAU_TUNDA_LON) ?>;
    const map = L.map('mapid').setView([lat, lng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup('<b>Pulau Tunda, Banten</b><br>Lokasi Batimetri').openPopup();

    map.on('mousemove', (e) => {
        document.getElementById('lat_val').textContent = e.latlng.lat.toFixed(6);
        document.getElementById('lng_val').textContent = e.latlng.lng.toFixed(6);
    });

    // Modal Zoom
    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('modalImg');
    const modalTitle = document.getElementById('modalTitle');
    const closeBtn = document.getElementById('closeModal');

    document.querySelectorAll('.zoom-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            modalImg.src = btn.dataset.img;
            modalTitle.textContent = btn.dataset.title || 'Pratinjau';
            modal.classList.remove('hidden');
        });
    });

    const closeModal = () => modal.classList.add('hidden');
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
})();
</script>

</body>
</html>
