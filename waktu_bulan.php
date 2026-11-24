<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'waktu_bulan';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Waktu Terbit &amp; Terbenam Bulan - MARISIGHT</title>
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
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-[#0A2240]">Informasi Terbit &amp; Terbenam Bulan</h1>
            <p class="mt-1 text-sm sm:text-base text-gray-600">
                Perhitungan astronomi realtime untuk <?= e(PULAU_TUNDA_NAME) ?> (<?= e(PULAU_TUNDA_LAT) ?>, <?= e(PULAU_TUNDA_LON) ?>).
            </p>
        </div>

        <label class="flex flex-col text-xs font-semibold text-gray-600">
            <span class="mb-1">Pilih Tanggal</span>
            <input id="datePicker" type="date" class="form-input rounded-lg border-gray-300 text-sm font-medium text-[#0A2240] focus:border-[#00BFFF] focus:ring-[#00BFFF]"/>
        </label>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- KARTU FASE & VISUAL BULAN DINAMIS -->
        <section class="lg:col-span-3 rounded-2xl overflow-hidden shadow-sm bg-white border border-gray-200 flex flex-col">
            <div class="relative bg-gradient-to-b from-[#041024] via-[#0A2240] to-[#0E3A63] p-8 flex flex-col items-center justify-center text-white">
                <img id="moonPhaseImg" src="image2/5.jpg" alt="Visual Fase Bulan"
                     class="w-36 h-36 rounded-full object-cover ring-4 ring-sky-300/40 shadow-2xl mb-4">
                <span id="moonPhaseBadge" class="px-3 py-1 rounded-full bg-white/15 text-sky-200 text-xs font-semibold">
                    Memuat fase...
                </span>
                <h2 id="moonPhaseTitle" class="mt-2 text-2xl font-bold text-center">-</h2>
                <p id="selectedDateText" class="text-xs text-sky-200 mt-1">-</p>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-[#0A2240] mb-1">Deskripsi &amp; Pengaruh Kelautan</h3>
                    <p id="moonPhaseDesc" class="text-sm text-gray-600 leading-relaxed">Menghitung fase bulan...</p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <span class="text-xs text-gray-500 block">Iluminasi Cahaya</span>
                        <span id="moonIllum" class="text-lg font-bold text-[#0A2240]">-</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <span class="text-xs text-gray-500 block">Usia Siklus Lunar</span>
                        <span id="moonAge" class="text-lg font-bold text-[#0A2240]">-</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- KARTU WAKTU TERBIT, TERBENAM & SOLUNAR -->
        <section class="lg:col-span-2 flex flex-col gap-5">
            <div class="rounded-2xl p-6 border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 text-gray-700">
                    <span class="flex items-center justify-center size-10 rounded-lg bg-sky-100 text-[#0A2240]">
                        <span class="material-symbols-outlined">nights_stay</span>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-600">Waktu Terbit Bulan (Moonrise)</p>
                        <p id="moonriseVal" class="text-3xl font-bold text-[#0A2240] mt-0.5 tabular-nums">--:-- WIB</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 text-gray-700">
                    <span class="flex items-center justify-center size-10 rounded-lg bg-sky-100 text-[#0A2240]">
                        <span class="material-symbols-outlined">wb_twilight</span>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-600">Waktu Terbenam Bulan (Moonset)</p>
                        <p id="moonsetVal" class="text-3xl font-bold text-[#0A2240] mt-0.5 tabular-nums">--:-- WIB</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-6 border border-sky-200 bg-sky-50/70 shadow-sm flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 text-[#0A2240] font-bold mb-2">
                        <span class="material-symbols-outlined text-[#0077b6]">phishing</span>
                        <span>Indeks Aktivitas Solunar</span>
                    </div>
                    <p id="solunarRating" class="text-lg font-bold text-[#0077b6]">-</p>
                    <p id="solunarNote" class="text-xs sm:text-sm text-gray-600 mt-1 leading-relaxed">-</p>
                </div>
                <a href="fase_bulan.php" class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-[#0A2240] hover:text-[#00BFFF] transition">
                    <span>Pelajari 8 Fase Bulan</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
// Perhitungan Astronomi Bulan berbasis algoritma SunCalc (Mourner / Jean Meeus),
// berjalan 100% lokal di browser tanpa API key dan tetap akurat secara offline.
(function () {
    const LAT = <?= json_encode(PULAU_TUNDA_LAT) ?>;
    const LON = <?= json_encode(PULAU_TUNDA_LON) ?>;
    const rad = Math.PI / 180;
    const dayMs = 86400000;
    const J1970 = 2440588;
    const J2000 = 2451545;
    const e = rad * 23.4397;

    function toDays(date) { return date.valueOf() / dayMs - 0.5 + J1970 - J2000; }
    function rightAscension(l, b) { return Math.atan2(Math.sin(l) * Math.cos(e) - Math.tan(b) * Math.sin(e), Math.cos(l)); }
    function declination(l, b) { return Math.asin(Math.sin(b) * Math.cos(e) + Math.cos(b) * Math.sin(e) * Math.sin(l)); }
    function altitude(H, phi, dec) { return Math.asin(Math.sin(phi) * Math.sin(dec) + Math.cos(phi) * Math.cos(dec) * Math.cos(H)); }
    function siderealTime(d, lw) { return rad * (280.16 + 360.9856235 * d) - lw; }
    function solarMeanAnomaly(d) { return rad * (357.5291 + 0.98560028 * d); }
    function eclipticLongitude(M) {
        const C = rad * (1.9148 * Math.sin(M) + 0.02 * Math.sin(2 * M) + 0.0003 * Math.sin(3 * M));
        return M + C + rad * 102.9372 + Math.PI;
    }
    function sunCoords(d) {
        const M = solarMeanAnomaly(d), L = eclipticLongitude(M);
        return { dec: declination(L, 0), ra: rightAscension(L, 0) };
    }
    function moonCoords(d) {
        const L = rad * (218.316 + 13.176396 * d);
        const M = rad * (134.963 + 13.064993 * d);
        const F = rad * (93.272 + 13.229350 * d);
        const l = L + rad * 6.289 * Math.sin(M);
        const b = rad * 5.128 * Math.sin(F);
        const dt = 385001 - 20905 * Math.cos(M);
        return { ra: rightAscension(l, b), dec: declination(l, b), dist: dt };
    }
    function getMoonPosition(date, lat, lng) {
        const lw = rad * -lng, phi = rad * lat, d = toDays(date);
        const c = moonCoords(d);
        const H = siderealTime(d, lw) - c.ra;
        let h = altitude(H, phi, c.dec);
        h += rad * 0.017 / Math.tan(h + rad * 10.26 / (h + rad * 5.10));
        return { altitude: h };
    }
    function getMoonIllumination(date) {
        const d = toDays(date), s = sunCoords(d), m = moonCoords(d);
        const sdist = 149598000;
        const phi = Math.acos(Math.sin(s.dec) * Math.sin(m.dec) + Math.cos(s.dec) * Math.cos(m.dec) * Math.cos(s.ra - m.ra));
        const inc = Math.atan2(sdist * Math.sin(phi), m.dist - sdist * Math.cos(phi));
        const angle = Math.atan2(
            Math.cos(s.dec) * Math.sin(s.ra - m.ra),
            Math.sin(s.dec) * Math.cos(m.dec) - Math.cos(s.dec) * Math.sin(m.dec) * Math.cos(s.ra - m.ra)
        );
        return {
            fraction: (1 + Math.cos(inc)) / 2,
            phase: 0.5 + 0.5 * inc * (angle < 0 ? -1 : 1) / Math.PI
        };
    }
    function hoursLater(date, h) { return new Date(date.valueOf() + h * 3600000); }
    function getMoonTimes(date, lat, lng) {
        const t = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0, 0);
        const hc = 0.133 * rad;
        let h0 = getMoonPosition(t, lat, lng).altitude - hc;
        let rise = null, set = null;

        for (let i = 1; i <= 24; i += 2) {
            const h1 = getMoonPosition(hoursLater(t, i), lat, lng).altitude - hc;
            const h2 = getMoonPosition(hoursLater(t, i + 1), lat, lng).altitude - hc;
            const a = (h0 + h2) / 2 - h1;
            const b = (h2 - h0) / 2;
            const xe = -b / (2 * a);
            const ye = (a * xe + b) * xe + h1;
            const d = b * b - 4 * a * h1;
            let roots = 0, x1 = 0, x2 = 0;

            if (d >= 0) {
                const dx = Math.sqrt(d) / (Math.abs(a) * 2);
                x1 = xe - dx;
                x2 = xe + dx;
                if (Math.abs(x1) <= 1) roots++;
                if (Math.abs(x2) <= 1) roots++;
                if (x1 < -1) x1 = x2;
            }
            if (roots === 1) {
                if (h0 < 0) rise = i + x1;
                else set = i + x1;
            } else if (roots === 2) {
                rise = i + (ye < 0 ? x2 : x1);
                set = i + (ye < 0 ? x1 : x2);
            }
            if (rise !== null && set !== null) break;
            h0 = h2;
        }
        return {
            rise: rise !== null ? hoursLater(t, rise) : null,
            set: set !== null ? hoursLater(t, set) : null
        };
    }

    const PHASES = [
        { max: 0.0625, name: 'Bulan Baru (New Moon)',           img: 'image2/1.jpg', solunar: 'Sangat Tinggi (Pasang Purnama/Spring Tide)', desc: 'Bulan berada di antara Bumi dan Matahari sehingga sisi yang menghadap Bumi gelap. Gaya tarik gravitasi Bulan dan Matahari sejajar, menghasilkan rentang pasang surut besar dan aktivitas ikan tinggi.' },
        { max: 0.1875, name: 'Sabit Awal (Waxing Crescent)',    img: 'image2/2.jpg', solunar: 'Sedang – Baik', desc: 'Bulan mulai bergerak menjauh dari Matahari dan memperlihatkan sabit tipis bercahaya di langit barat setelah matahari terbenam.' },
        { max: 0.3125, name: 'Kuartal Pertama (First Quarter)', img: 'image2/3.jpg', solunar: 'Sedang (Pasang Perbani/Neap Tide)', desc: 'Separuh permukaan bulan diterangi Matahari pada sudut 90°. Rentang pasang surut cenderung lebih kecil (pasang perbani).' },
        { max: 0.4375, name: 'Cembung Awal (Waxing Gibbous)',   img: 'image2/4.jpg', solunar: 'Baik – Meningkat', desc: 'Lebih dari separuh piringan bulan bersinar terang dan terus bertambah menjelang purnama; arus pasang mulai menguat.' },
        { max: 0.5625, name: 'Bulan Purnama (Full Moon)',       img: 'image2/5.jpg', solunar: 'Sangat Tinggi (Pasang Purnama/Spring Tide)', desc: 'Bumi berada di antara Matahari dan Bulan sehingga seluruh sisi Bulan tampak bulat dan terang. Gaya gravitasi gabungan menghasilkan pasang purnama dengan aktivitas biota laut sangat aktif.' },
        { max: 0.6875, name: 'Cembung Akhir (Waning Gibbous)',  img: 'image2/6.jpg', solunar: 'Baik', desc: 'Setelah purnama, cahaya bulan perlahan berkurang namun masih mendominasi langit malam; kondisi perairan masih cukup dinamis.' },
        { max: 0.8125, name: 'Kuartal Ketiga (Third Quarter)',  img: 'image2/7.jpg', solunar: 'Sedang (Pasang Perbani/Neap Tide)', desc: 'Separuh sisi lain bulan tampak terang saat bergerak kembali menuju bulan baru, disertai rentang pasang surut yang lebih landai.' },
        { max: 0.9375, name: 'Sabit Akhir (Waning Crescent)',   img: 'image2/8.jpg', solunar: 'Sedang – Meningkat', desc: 'Fase sabit tipis terakhir sebelum siklus kembali ke Bulan Baru; arus pasang mulai meningkat kembali menjelang fase bulan baru.' },
        { max: 1.01,   name: 'Bulan Baru (New Moon)',           img: 'image2/1.jpg', solunar: 'Sangat Tinggi (Pasang Purnama/Spring Tide)', desc: 'Bulan kembali sejajar antara Bumi dan Matahari, mengawali siklus lunar baru dengan rentang pasang surut besar.' }
    ];

    function fmtWib(dt) {
        if (!dt) return 'Tidak terjadi hari ini';
        return dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }) + ' WIB';
    }

    function renderForDate(dateObj) {
        const midday = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), 12, 0, 0);
        const illum = getMoonIllumination(midday);
        const times = getMoonTimes(dateObj, LAT, LON);
        const phaseInfo = PHASES.find(p => illum.phase < p.max) || PHASES[0];
        const lunarAge = (illum.phase * 29.53059).toFixed(1);

        document.getElementById('moonPhaseImg').src = phaseInfo.img;
        document.getElementById('moonPhaseTitle').textContent = phaseInfo.name;
        document.getElementById('moonPhaseBadge').textContent = `Iluminasi ${(illum.fraction * 100).toFixed(1)}%`;
        document.getElementById('moonPhaseDesc').textContent = phaseInfo.desc;
        document.getElementById('moonIllum').textContent = `${(illum.fraction * 100).toFixed(1)}%`;
        document.getElementById('moonAge').textContent = `${lunarAge} hari / 29,5 hari`;

        document.getElementById('moonriseVal').textContent = fmtWib(times.rise);
        document.getElementById('moonsetVal').textContent = fmtWib(times.set);
        document.getElementById('solunarRating').textContent = phaseInfo.solunar;
        document.getElementById('solunarNote').textContent =
            'Periode paling aktif umumnya terjadi sekitar 1 jam sebelum hingga 1 jam sesudah bulan terbit, bulan terbenam, dan kulminasi bulan.';
        document.getElementById('selectedDateText').textContent = dateObj.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }

    const picker = document.getElementById('datePicker');
    const today = new Date();
    const pad = n => String(n).padStart(2, '0');
    picker.value = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;

    picker.addEventListener('change', function () {
        if (!this.value) return;
        const [y, m, d] = this.value.split('-').map(Number);
        renderForDate(new Date(y, m - 1, d));
    });

    renderForDate(today);
})();
</script>

</body>
</html>
