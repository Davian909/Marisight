<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$isLoggedIn = is_logged_in();
$username   = current_username();
$basePath   = '';
$activePage = 'home';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MARISIGHT - Marine Information Dashboard</title>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#00BFFF",
                "background-light": "#F5F7FA",
                "background-dark": "#101922",
                "navbar-dark": "#0A2240",
                "text-dark": "#333333",
                "text-light": "#FFFFFF"
            },
            fontFamily: {
                "display": ["Space Grotesk", "sans-serif"]
            },
            backgroundImage: {
                'weather-sunny': "url('https://images.unsplash.com/photo-1541848566252-789e9095fb82?q=80&w=2070&auto=format&fit=crop')",
                'weather-cloudy': "url('https://images.unsplash.com/photo-1499956827185-0d63ee78a910?q=80&w=2070&auto=format&fit=crop')",
                'weather-rainy': "url('https://images.unsplash.com/photo-1519692933481-e162a57d6721?q=80&w=2070&auto=format&fit=crop')",
            }
        }
    }
};
</script>

<style>
body {
  font-family: 'Space Grotesk', sans-serif;
}

#weatherAnimation {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 5;
}

.rain-drop {
  position: absolute;
  width: 2.5px;
  height: 18px;
  background: rgba(255, 255, 255, 0.85);
  border-radius: 2px;
  filter: blur(0.4px);
  animation: rainFall linear infinite;
}

@keyframes rainFall {
  0%   { transform: translateY(-10vh) translateX(-3px); opacity: 0; }
  10%  { opacity: 0.9; }
  100% { transform: translateY(115vh) translateX(4px); opacity: 0; }
}

.mist {
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 38%;
  background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.22));
  animation: mistFloat 7s ease-in-out infinite;
  filter: blur(3px);
}

@keyframes mistFloat {
  0%, 100% { transform: translateY(3%); opacity: 0.6; }
  50%      { transform: translateY(-3%); opacity: 0.95; }
}

.lightning {
  position: absolute;
  width: 5px;
  height: 145px;
  background: linear-gradient(180deg, #fff, #ffe47a, #ffb300);
  border-radius: 4px;
  filter: blur(1px);
  opacity: 0;
  animation: lightningFlash 3.2s infinite;
}

@keyframes lightningFlash {
  0%, 88%, 100% { opacity: 0; transform: scaleY(0.75); }
  90%           { opacity: 1; transform: scaleY(1.15); }
  93%           { opacity: 0.75; transform: scaleY(1); }
}

.ocean-bg {
  background: linear-gradient(180deg, #e0f4ff 0%, #c8ecff 50%, #a5dcfb 100%);
  background-size: 100% 200%;
  animation: oceanShift 10s ease-in-out infinite;
}

@keyframes oceanShift {
  0%, 100% { background-position-y: 0%; }
  50%      { background-position-y: 100%; }
}

#heroSection {
  min-height: 76vh;
  position: relative;
  z-index: 1;
  background-color: #06284c;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 2;
  background: linear-gradient(180deg, rgba(3, 37, 65, 0.55), rgba(3, 37, 65, 0.35));
}

#heroContentWrapper {
  position: relative;
  z-index: 3;
}
</style>
</head>

<body class="bg-background-light font-display text-text-dark flex flex-col min-h-screen">

<!-- GLOBAL CUACA ANIMATION LAYER -->
<div id="weatherAnimation" aria-hidden="true"></div>

<?php require __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-1 flex flex-col">
    <!-- ====================== HERO SECTION ====================== -->
    <section id="heroSection" class="relative bg-weather-sunny bg-cover bg-center flex items-center">
        <div class="hero-overlay"></div>

        <div id="heroContentWrapper" class="relative w-full py-20 md:py-28 px-6 text-center text-white">
            <div class="max-w-4xl mx-auto">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur-sm px-4 py-1 text-xs sm:text-sm font-medium mb-5 border border-white/25">
                    <span class="material-symbols-outlined text-base text-primary">location_on</span>
                    <span>Pemantauan Kelautan <?= e(PULAU_TUNDA_NAME) ?> (<?= e(PULAU_TUNDA_LAT) ?>, <?= e(PULAU_TUNDA_LON) ?>)</span>
                </span>

                <h1 class="text-4xl md:text-5xl font-bold tracking-tight">
                    Selamat Datang di MARISIGHT<?= $isLoggedIn ? ', ' . e($username) : '' ?>
                </h1>

                <p class="mt-5 text-base md:text-lg max-w-3xl mx-auto text-sky-50 leading-relaxed">
                    Platform informasi kelautan terpadu untuk memantau cuaca, gelombang, suhu permukaan laut,
                    pasang surut, batimetri, dan fenomena solunar di perairan Pulau Tunda.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="cuaca.php" class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm sm:text-base font-bold text-[#0A2240] shadow-lg hover:bg-sky-300 transition">
                        <span class="material-symbols-outlined text-xl">cloud</span>
                        <span>Cek Cuaca Sekarang</span>
                    </a>
                    <a href="spl.php" class="inline-flex items-center gap-2 rounded-lg bg-white/15 backdrop-blur-sm border border-white/30 px-6 py-3 text-sm sm:text-base font-bold text-white hover:bg-white/25 transition">
                        <span class="material-symbols-outlined text-xl">map</span>
                        <span>Peta Suhu Laut</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================== AKSES CEPAT MODUL ====================== -->
    <section class="px-4 sm:px-6 lg:px-8 py-12 bg-background-light">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-navbar-dark">Akses Cepat Informasi Maritim</h2>
                <p class="mt-2 text-gray-600 text-sm sm:text-base">Pilih modul pemantauan yang ingin Anda lihat secara langsung.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="cuaca.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">partly_cloudy_day</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Cuaca</h3>
                    <p class="mt-1 text-sm text-gray-600">Suhu udara, kondisi atmosfer, kecepatan &amp; arah angin realtime.</p>
                </a>

                <a href="ombak.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">waves</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Ombak &amp; Gelombang</h3>
                    <p class="mt-1 text-sm text-gray-600">Tinggi, periode, arah gelombang, dan grafik prakiraan per jam.</p>
                </a>

                <a href="spl.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">thermostat</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Suhu Permukaan Laut</h3>
                    <p class="mt-1 text-sm text-gray-600">Peta interaktif distribusi suhu permukaan laut Pulau Tunda.</p>
                </a>

                <a href="pasut_php/grafik.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">monitoring</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Pasang Surut</h3>
                    <p class="mt-1 text-sm text-gray-600">Grafik &amp; tabel pasang surut harian beserta intensitas ikan.</p>
                </a>

                <a href="bath.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">terrain</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Batimetri</h3>
                    <p class="mt-1 text-sm text-gray-600">Kontur, profil kedalaman, model 3D dasar laut, dan unduh ZIP.</p>
                </a>

                <a href="waktu_bulan.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">nights_stay</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Waktu Bulan</h3>
                    <p class="mt-1 text-sm text-gray-600">Jadwal terbit, terbenam, iluminasi, dan fase bulan realtime.</p>
                </a>

                <a href="fase_bulan.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">dark_mode</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Fase Bulan</h3>
                    <p class="mt-1 text-sm text-gray-600">Panduan 8 siklus fase bulan dan pengaruhnya terhadap laut.</p>
                </a>

                <a href="fitur.php" class="group rounded-xl bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-primary transition">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-sky-100 text-[#0A2240] group-hover:bg-primary group-hover:text-white transition mb-4">
                        <span class="material-symbols-outlined text-2xl">category</span>
                    </div>
                    <h3 class="font-bold text-lg text-navbar-dark">Semua Fitur</h3>
                    <p class="mt-1 text-sm text-gray-600">Penjelasan lengkap seluruh modul analisis kelautan MARISIGHT.</p>
                </a>
            </div>

            <!-- CTA BANNER -->
            <div class="mt-10 flex flex-col items-center text-center p-6 md:p-8 rounded-xl ocean-bg shadow-sm border border-sky-200">
                <div class="flex items-center justify-center size-14 mb-4 rounded-full bg-white/80 text-navbar-dark shadow-sm">
                    <span class="material-symbols-outlined text-3xl">explore</span>
                </div>
                <h3 class="text-2xl font-bold text-navbar-dark">Jelajahi Fitur Lengkap Website Kami</h3>
                <p class="mt-2 max-w-2xl text-sm sm:text-base text-slate-700">
                    Temukan semua yang Anda butuhkan untuk analisis kelautan — mulai dari cuaca,
                    pasang surut, batimetri, hingga fenomena solunar dalam satu platform terpadu.
                </p>
                <a href="fitur.php" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-navbar-dark px-6 py-3 text-base font-bold text-white shadow-md hover:bg-[#004D73] transition">
                    <span>Lihat Detail Fitur</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function () {
    function clearWeatherAnimation() {
        const box = document.getElementById('weatherAnimation');
        if (box) box.innerHTML = '';
    }

    function makeRain(count) {
        const box = document.getElementById('weatherAnimation');
        if (!box) return;
        const frag = document.createDocumentFragment();
        for (let i = 0; i < count; i++) {
            const drop = document.createElement('div');
            drop.className = 'rain-drop';
            drop.style.left = `${Math.random() * 100}vw`;
            drop.style.animationDuration = `${0.3 + Math.random() * 0.55}s`;
            drop.style.animationDelay = `${Math.random() * 1.2}s`;
            drop.style.height = `${14 + Math.random() * 16}px`;
            drop.style.opacity = String(0.45 + Math.random() * 0.5);
            frag.appendChild(drop);
        }
        box.appendChild(frag);
    }

    function makeMist() {
        const box = document.getElementById('weatherAnimation');
        if (!box) return;
        const fog = document.createElement('div');
        fog.className = 'mist';
        box.appendChild(fog);
    }

    function makeLightning(count) {
        const box = document.getElementById('weatherAnimation');
        if (!box) return;
        for (let i = 0; i < count; i++) {
            const bolt = document.createElement('div');
            bolt.className = 'lightning';
            bolt.style.left = `${10 + Math.random() * 80}vw`;
            bolt.style.animationDelay = `${Math.random() * 3}s`;
            box.appendChild(bolt);
        }
    }

    // Dipanggil otomatis oleh includes/navbar.php setiap data cuaca diperbarui
    window.onMarisightWeatherUpdate = function (currentWeather) {
        const code = Number(currentWeather.weather_code);
        const hero = document.getElementById('heroSection');
        if (!hero) return;

        clearWeatherAnimation();
        hero.classList.remove('bg-weather-sunny', 'bg-weather-cloudy', 'bg-weather-rainy');

        if (code === 0) {
            hero.classList.add('bg-weather-sunny');
        } else if ([1, 2, 3, 45, 48].includes(code)) {
            hero.classList.add('bg-weather-cloudy');
            makeMist();
        } else if ((code >= 51 && code <= 67) || (code >= 80 && code <= 82)) {
            hero.classList.add('bg-weather-rainy');
            makeMist();
            makeRain(140);
        } else if (code >= 95) {
            hero.classList.add('bg-weather-rainy');
            makeMist();
            makeRain(180);
            makeLightning(3);
        } else {
            hero.classList.add('bg-weather-cloudy');
        }
    };
})();
</script>

</body>
</html>
