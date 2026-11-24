<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'spl';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Suhu Permukaan Laut Pulau Tunda - MARISIGHT</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-compass/dist/leaflet-compass.css" />
<script src="https://unpkg.com/leaflet-compass/dist/leaflet-compass.min.js"></script>

<style>
body { font-family: 'Space Grotesk', sans-serif; }
#map { height: calc(100vh - 150px); min-height: 460px; z-index: 1; }
.legend { background: rgba(255,255,255,0.96); padding: 12px 14px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.18); font-size: 13px; line-height: 1.6; }
.sw { width: 16px; height: 12px; display: inline-block; border-radius: 3px; margin-right: 7px; vertical-align: middle; }
.glow { filter: drop-shadow(0 0 8px rgba(0,0,0,0.35)); transition: 0.2s; }
</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require __DIR__ . '/includes/navbar.php'; ?>

<div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-3">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-3">
            <a href="index.php" class="inline-flex items-center gap-1 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Beranda</span>
            </a>
            <span class="text-gray-300">|</span>
            <h1 class="font-bold text-base sm:text-lg text-[#0A2240]">Peta Suhu Permukaan Laut (SPL) — <?= e(PULAU_TUNDA_NAME) ?></h1>
        </div>
        <span class="text-xs text-gray-500">Dataset spasial lokal: <code class="bg-gray-100 px-1.5 py-0.5 rounded">data/SPLL.geojson</code> (302 zona grid)</span>
    </div>
</div>

<div id="mapError" class="hidden max-w-4xl mx-auto my-4 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700"></div>
<div id="map" class="w-full flex-1"></div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    const map = L.map('map', { zoomControl: true }).setView(
        [<?= json_encode(PULAU_TUNDA_LAT) ?>, <?= json_encode(PULAU_TUNDA_LON) ?>],
        12
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.control.scale({ imperial: false }).addTo(map);

    if (L.Control.Compass) {
        map.addControl(new L.Control.Compass({ autoActive: true, showDigit: false }));
    }

    const hoverPopup = L.popup({ closeButton: false, offset: [12, 0] });

    // Pemetaan 5 kelas gridcode yang tersedia di data/SPLL.geojson
    const SST = {
        '1': { suhu: 24.0, label: 'Dingin (24,0°C)',    color: '#003f7f' },
        '2': { suhu: 25.5, label: 'Sejuk (25,5°C)',     color: '#0077b6' },
        '3': { suhu: 27.0, label: 'Normal (27,0°C)',    color: '#00b4d8' },
        '4': { suhu: 28.2, label: 'Hangat (28,2°C)',    color: '#ffd500' },
        '5': { suhu: 29.2, label: 'Sangat Hangat (29,2°C)', color: '#f77f00' }
    };

    fetch('data/SPLL.geojson')
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat data/SPLL.geojson');
            return res.json();
        })
        .then(data => {
            const layer = L.geoJSON(data, {
                style: feature => {
                    const gc = String(feature.properties.gridcode);
                    return {
                        color: '#222',
                        weight: 0.8,
                        fillColor: SST[gc]?.color || '#666',
                        fillOpacity: 0.75
                    };
                },
                onEachFeature: (feature, featureLayer) => {
                    const gc = String(feature.properties.gridcode);
                    const info = SST[gc] || { suhu: 'N/A', label: 'Tidak diketahui', color: '#666' };
                    const fid = feature.properties.FID;

                    featureLayer.on('mouseover', function (e) {
                        featureLayer.setStyle({ weight: 2, fillOpacity: 0.92 });
                        if (featureLayer.getElement()) featureLayer.getElement().classList.add('glow');

                        hoverPopup
                            .setLatLng(e.latlng)
                            .setContent(
                                `<div class="p-1">
                                    <div class="font-bold" style="color:${info.color}">Zona FID ${fid}</div>
                                    <div class="text-sm"><b>Estimasi Suhu:</b> ${info.suhu} °C</div>
                                    <div class="text-xs text-gray-600 mt-0.5">Klasifikasi Grid ${gc}</div>
                                </div>`
                            )
                            .openOn(map);
                    });

                    featureLayer.on('mouseout', function () {
                        featureLayer.setStyle({ weight: 0.8, fillOpacity: 0.75 });
                        if (featureLayer.getElement()) featureLayer.getElement().classList.remove('glow');
                        map.closePopup(hoverPopup);
                    });

                    featureLayer.on('click', function (e) {
                        L.popup()
                            .setLatLng(e.latlng)
                            .setContent(
                                `<b>Detail Zona SPL</b><br>
                                 FID: ${fid}<br>
                                 Gridcode: ${gc}<br>
                                 Estimasi Suhu: ${info.suhu} °C`
                            )
                            .openOn(map);
                    });
                }
            }).addTo(map);

            map.fitBounds(layer.getBounds(), { padding: [20, 20] });

            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function () {
                const div = L.DomUtil.create('div', 'legend');
                div.innerHTML =
                    `<b>Suhu Permukaan Laut</b><div class="text-[11px] text-gray-500 mb-2">Klasifikasi Grid SPLL</div>` +
                    Object.keys(SST).map(k =>
                        `<div><span class="sw" style="background:${SST[k].color}"></span>${SST[k].label}</div>`
                    ).join('');
                return div;
            };
            legend.addTo(map);
        })
        .catch(err => {
            const box = document.getElementById('mapError');
            box.textContent = err.message;
            box.classList.remove('hidden');
        });
})();
</script>

</body>
</html>
