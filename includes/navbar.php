<?php
declare(strict_types=1);

/**
 * Komponen Navbar Responsif Terpusat MARISIGHT.
 *
 * Variabel opsional sebelum include:
 *   $basePath   (string) '' untuk halaman root, '../' untuk subfolder seperti pasut_php/
 *   $activePage (string) penanda menu aktif ('home', 'cuaca', 'spl', 'ombak', 'grafik', 'tabel', 'bath', 'waktu_bulan', 'fase_bulan', 'fitur')
 */

$basePath   = isset($basePath) ? (string)$basePath : '';
$activePage = isset($activePage) ? (string)$activePage : '';

$loggedIn = is_logged_in();
$username = current_username();
$csrfHtml = csrf_field();
?>
<header class="bg-[#0A2240] text-white shadow-md sticky top-0 z-50">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8 py-3 gap-4">

        <!-- LOGO -->
        <a href="<?= e($basePath) ?>index.php" class="flex items-center gap-3 shrink-0 group">
            <img src="<?= e($basePath) ?>image2/Logo Marisight.png"
                 alt="Logo MARISIGHT"
                 class="h-10 w-auto object-contain transition-transform group-hover:scale-105">
            <span class="font-bold tracking-wider text-lg hidden sm:inline">MARISIGHT</span>
        </a>

        <!-- DESKTOP NAVIGATION (Publik, langsung dapat diakses tanpa wajib login) -->
        <nav class="hidden lg:flex flex-1 items-center justify-center gap-6" aria-label="Navigasi Utama">

            <a href="<?= e($basePath) ?>index.php"
               class="text-sm font-medium transition <?= $activePage === 'home' ? 'text-[#00BFFF]' : 'hover:text-[#00BFFF]' ?>">
                Beranda
            </a>

            <!-- Dropdown 1: Kondisi Perairan -->
            <div class="group relative py-1">
                <button type="button"
                        class="flex items-center gap-1 text-sm font-medium transition <?= in_array($activePage, ['cuaca', 'spl', 'ombak'], true) ? 'text-[#00BFFF]' : 'hover:text-[#00BFFF]' ?>">
                    <span>Kondisi Perairan</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
                </button>
                <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition-all duration-150 absolute left-0 top-full pt-2 z-50">
                    <div class="bg-[#0A2240] border border-white/10 rounded-xl shadow-xl py-2 w-52">
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'cuaca' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>cuaca.php">Cuaca</a>
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'spl' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>spl.php">Suhu Permukaan Laut</a>
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'ombak' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>ombak.php">Ombak &amp; Gelombang</a>
                    </div>
                </div>
            </div>

            <!-- Dropdown 2: Pasang Surut & Batimetri -->
            <div class="group relative py-1">
                <button type="button"
                        class="flex items-center gap-1 text-sm font-medium transition <?= in_array($activePage, ['grafik', 'tabel', 'bath'], true) ? 'text-[#00BFFF]' : 'hover:text-[#00BFFF]' ?>">
                    <span>Pasang Surut</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
                </button>
                <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition-all duration-150 absolute left-0 top-full pt-2 z-50">
                    <div class="bg-[#0A2240] border border-white/10 rounded-xl shadow-xl py-2 w-52">
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'grafik' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>pasut_php/grafik.php">Grafik Pasang Surut</a>
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'tabel' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>pasut_php/tabel.php">Tabel Pasang Surut</a>
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'bath' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>bath.php">Batimetri</a>
                    </div>
                </div>
            </div>

            <!-- Dropdown 3: Solunar -->
            <div class="group relative py-1">
                <button type="button"
                        class="flex items-center gap-1 text-sm font-medium transition <?= in_array($activePage, ['waktu_bulan', 'fase_bulan'], true) ? 'text-[#00BFFF]' : 'hover:text-[#00BFFF]' ?>">
                    <span>Solunar</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
                </button>
                <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition-all duration-150 absolute left-0 top-full pt-2 z-50">
                    <div class="bg-[#0A2240] border border-white/10 rounded-xl shadow-xl py-2 w-52">
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'waktu_bulan' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>waktu_bulan.php">Waktu Bulan</a>
                        <a class="block px-4 py-2 text-sm hover:bg-[#00BFFF]/20 <?= $activePage === 'fase_bulan' ? 'text-[#00BFFF] font-semibold' : '' ?>"
                           href="<?= e($basePath) ?>fase_bulan.php">Fase Bulan</a>
                    </div>
                </div>
            </div>

            <a href="<?= e($basePath) ?>fitur.php"
               class="text-sm font-medium transition <?= $activePage === 'fitur' ? 'text-[#00BFFF]' : 'hover:text-[#00BFFF]' ?>">
                Fitur
            </a>
        </nav>

        <!-- RIGHT WIDGETS + AUTH + MOBILE BUTTON -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Weather realtime mini widget -->
            <div class="hidden sm:flex items-center gap-2 text-right">
                <span id="navWeatherIcon" class="material-symbols-outlined text-2xl text-yellow-300">light_mode</span>
                <div class="leading-tight">
                    <div id="navWeatherText" class="text-xs font-bold">Memuat...</div>
                    <div id="navWeatherTemp" class="text-xs text-gray-300">--°C</div>
                </div>
            </div>

            <div class="hidden sm:block h-7 w-px bg-white/20"></div>

            <!-- Realtime Clock -->
            <div class="hidden md:block text-right leading-tight">
                <div id="navTime" class="text-sm font-bold tabular-nums">--:--:--</div>
                <div id="navDate" class="text-[11px] text-gray-300"></div>
            </div>

            <!-- Auth Opsional -->
            <?php if ($loggedIn): ?>
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-xs text-sky-200 font-medium max-w-[110px] truncate" title="<?= e($username) ?>">
                        <?= e($username) ?>
                    </span>
                    <form method="POST" action="<?= e($basePath) ?>logout.php" class="inline">
                        <?= $csrfHtml ?>
                        <button type="submit"
                                class="flex items-center gap-1.5 rounded-lg h-9 px-3 bg-[#00BFFF]/20 hover:bg-[#00BFFF]/40 text-sm font-medium transition">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <a href="<?= e($basePath) ?>login.php"
                   class="hidden sm:flex items-center gap-1.5 rounded-lg h-9 px-3.5 bg-[#00BFFF]/20 hover:bg-[#00BFFF]/40 text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">login</span>
                    <span>Masuk</span>
                </a>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button type="button"
                    id="mobileMenuBtn"
                    aria-label="Buka menu navigasi"
                    aria-expanded="false"
                    class="lg:hidden flex items-center justify-center rounded-lg p-2 bg-white/10 hover:bg-white/20 transition">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
        </div>
    </div>

    <!-- MOBILE DRAWER MENU -->
    <div id="mobileMenu" class="hidden lg:hidden border-t border-white/10 bg-[#081b34] px-4 py-4 space-y-4 text-sm">
        <div class="space-y-1">
            <a href="<?= e($basePath) ?>index.php" class="block px-3 py-2 rounded-lg hover:bg-white/10 font-medium">Beranda</a>
            <a href="<?= e($basePath) ?>fitur.php" class="block px-3 py-2 rounded-lg hover:bg-white/10 font-medium">Detail Fitur</a>
        </div>

        <div>
            <p class="px-3 text-xs uppercase tracking-wider text-sky-300 font-bold mb-1">Kondisi Perairan</p>
            <a href="<?= e($basePath) ?>cuaca.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Cuaca</a>
            <a href="<?= e($basePath) ?>spl.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Suhu Permukaan Laut</a>
            <a href="<?= e($basePath) ?>ombak.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Ombak &amp; Gelombang</a>
        </div>

        <div>
            <p class="px-3 text-xs uppercase tracking-wider text-sky-300 font-bold mb-1">Pasang Surut &amp; Batimetri</p>
            <a href="<?= e($basePath) ?>pasut_php/grafik.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Grafik Pasang Surut</a>
            <a href="<?= e($basePath) ?>pasut_php/tabel.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Tabel Pasang Surut</a>
            <a href="<?= e($basePath) ?>bath.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Batimetri</a>
        </div>

        <div>
            <p class="px-3 text-xs uppercase tracking-wider text-sky-300 font-bold mb-1">Solunar</p>
            <a href="<?= e($basePath) ?>waktu_bulan.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Waktu Bulan</a>
            <a href="<?= e($basePath) ?>fase_bulan.php" class="block px-3 py-1.5 rounded-lg hover:bg-white/10">Fase Bulan</a>
        </div>

        <div class="pt-3 border-t border-white/10 flex items-center justify-between px-3">
            <?php if ($loggedIn): ?>
                <span class="text-sky-200 font-medium">Halo, <?= e($username) ?></span>
                <form method="POST" action="<?= e($basePath) ?>logout.php">
                    <?= $csrfHtml ?>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#00BFFF]/25 hover:bg-[#00BFFF]/40 font-semibold">Keluar</button>
                </form>
            <?php else: ?>
                <a href="<?= e($basePath) ?>login.php" class="w-full text-center px-4 py-2 rounded-lg bg-[#00BFFF] text-[#0A2240] font-bold hover:bg-sky-300">
                    Masuk / Daftar (Opsional)
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
(function () {
    // Toggle mobile menu
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    if (btn && menu) {
        btn.addEventListener('click', function () {
            const isHidden = menu.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!isHidden));
        });
    }

    // Jam & tanggal navbar realtime
    function tickNavbarClock() {
        const now = new Date();
        const tEl = document.getElementById('navTime');
        const dEl = document.getElementById('navDate');
        if (tEl) tEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        if (dEl) dEl.textContent = now.toLocaleDateString('id-ID', {
            weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
        });
    }
    tickNavbarClock();
    setInterval(tickNavbarClock, 1000);

    // Peta kode cuaca WMO standar (juga dipakai global oleh index.php)
    window.MARISIGHT_WEATHER_MAP = {
        0:  { text: 'Cerah',              icon: 'light_mode' },
        1:  { text: 'Cerah Berawan',      icon: 'partly_cloudy_day' },
        2:  { text: 'Berawan Sebagian',   icon: 'cloud' },
        3:  { text: 'Mendung',            icon: 'cloud_queue' },
        45: { text: 'Berkabut',           icon: 'foggy' },
        48: { text: 'Kabut Tebal',        icon: 'foggy' },
        51: { text: 'Gerimis Ringan',     icon: 'grain' },
        53: { text: 'Gerimis Sedang',     icon: 'grain' },
        55: { text: 'Gerimis Lebat',      icon: 'grain' },
        61: { text: 'Hujan Ringan',       icon: 'rainy' },
        63: { text: 'Hujan Sedang',       icon: 'rainy' },
        65: { text: 'Hujan Lebat',        icon: 'rainy' },
        80: { text: 'Hujan Lokal Ringan', icon: 'rainy' },
        81: { text: 'Hujan Lokal Sedang', icon: 'rainy' },
        82: { text: 'Hujan Lokal Lebat',  icon: 'rainy' },
        95: { text: 'Badai Petir',        icon: 'thunderstorm' },
        96: { text: 'Badai Hujan Ringan', icon: 'thunderstorm' },
        99: { text: 'Badai Hujan Lebat',  icon: 'thunderstorm' }
    };

    const defaultLat = <?= json_encode(PULAU_TUNDA_LAT) ?>;
    const defaultLon = <?= json_encode(PULAU_TUNDA_LON) ?>;

    async function refreshNavWeather(lat, lon) {
        try {
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&timezone=Asia%2FJakarta`;
            const res = await fetch(url);
            if (!res.ok) return;
            const data = await res.json();
            const cur = data && data.current;
            if (!cur) return;

            const code = Number(cur.weather_code);
            const mapped = window.MARISIGHT_WEATHER_MAP[code] || window.MARISIGHT_WEATHER_MAP[2];
            const iconEl = document.getElementById('navWeatherIcon');
            const textEl = document.getElementById('navWeatherText');
            const tempEl = document.getElementById('navWeatherTemp');
            if (iconEl) iconEl.textContent = mapped.icon;
            if (textEl) textEl.textContent = mapped.text;
            if (tempEl) tempEl.textContent = Math.round(cur.temperature_2m) + '°C';

            if (typeof window.onMarisightWeatherUpdate === 'function') {
                window.onMarisightWeatherUpdate(cur, mapped);
            }
        } catch (err) {
            const textEl = document.getElementById('navWeatherText');
            if (textEl && textEl.textContent === 'Memuat...') {
                textEl.textContent = 'Offline';
            }
        }
    }

    function initNavWeather() {
        const startLoop = (lat, lon) => {
            refreshNavWeather(lat, lon);
            setInterval(() => refreshNavWeather(lat, lon), 180000);
        };
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => startLoop(pos.coords.latitude, pos.coords.longitude),
                () => startLoop(defaultLat, defaultLon),
                { timeout: 4000 }
            );
        } else {
            startLoop(defaultLat, defaultLon);
        }
    }

    document.addEventListener('DOMContentLoaded', initNavWeather);
})();
</script>
