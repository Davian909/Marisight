<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'fitur';

$features = [
    [
        'title' => 'Cuaca',
        'icon'  => 'partly_cloudy_day',
        'href'  => 'cuaca.php',
        'desc'  => 'Menyediakan informasi kondisi atmosfer saat ini di Pulau Tunda dan lokasi Anda — meliputi suhu udara, kondisi cuaca berdasarkan kode WMO, serta kecepatan dan arah angin.',
    ],
    [
        'title' => 'Suhu Permukaan Laut',
        'icon'  => 'thermostat',
        'href'  => 'spl.php',
        'desc'  => 'Menampilkan peta sebaran suhu air laut di permukaan yang mempengaruhi distribusi organisme laut, sangat membantu nelayan dan peneliti dalam memetakan kondisi perairan.',
    ],
    [
        'title' => 'Ombak & Gelombang',
        'icon'  => 'waves',
        'href'  => 'ombak.php',
        'desc'  => 'Memberikan data tinggi, periode, dan arah gelombang laut beserta grafik prakiraan per jam untuk mendukung keselamatan pelayaran, olahraga air, dan aktivitas pesisir.',
    ],
    [
        'title' => 'Grafik Pasang Surut',
        'icon'  => 'monitoring',
        'href'  => 'pasut_php/grafik.php',
        'desc'  => 'Menyajikan visualisasi grafik garis dan batang tinggi pasang surut harian yang dipengaruhi gaya gravitasi bulan dan matahari untuk perencanaan aktivitas pelabuhan dan perikanan.',
    ],
    [
        'title' => 'Tabel Pasang Surut',
        'icon'  => 'table_chart',
        'href'  => 'pasut_php/tabel.php',
        'desc'  => 'Representasi data pasang surut harian dalam format tabel terperinci lengkap dengan jam pasang, jam surut, amplitudo, serta estimasi intensitas aktivitas ikan.',
    ],
    [
        'title' => 'Batimetri (Kontur, Profil & 3D)',
        'icon'  => 'terrain',
        'href'  => 'bath.php',
        'desc'  => 'Menampilkan topografi dasar laut Pulau Tunda dalam bentuk peta interaktif, kontur kedalaman, profil kedalaman, serta permukaan 3D yang dapat diperbesar dan diunduh dalam paket ZIP.',
    ],
    [
        'title' => 'Waktu Terbit & Terbenam Bulan',
        'icon'  => 'nights_stay',
        'href'  => 'waktu_bulan.php',
        'desc'  => 'Menghitung secara otomatis dan realtime jadwal bulan terbit, bulan terbenam, persentase iluminasi, dan fase bulan aktif hari ini di perairan Pulau Tunda.',
    ],
    [
        'title' => 'Fase-Fase Bulan',
        'icon'  => 'dark_mode',
        'href'  => 'fase_bulan.php',
        'desc'  => 'Menampilkan edukasi siklus lengkap 8 fase bulan mulai dari Bulan Baru hingga Sabit Akhir yang berkaitan langsung dengan siklus pasang surut purnama dan perbani.',
    ],
];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Fitur MARISIGHT - Platform Informasi Kelautan</title>
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

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col gap-6">
        <div>
            <a class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition" href="index.php">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold text-[#0A2240]">Fitur MARISIGHT</h1>
            <p class="mt-2 text-gray-600 text-base">Penjelasan detail setiap modul informasi kelautan yang tersedia di platform MARISIGHT.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($features as $feat): ?>
                <div class="flex flex-col justify-between gap-4 p-6 rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md hover:border-[#00BFFF] transition">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="flex items-center justify-center size-10 rounded-lg bg-sky-100 text-[#0A2240]">
                                <span class="material-symbols-outlined"><?= e($feat['icon']) ?></span>
                            </span>
                            <h2 class="text-lg font-bold text-[#0A2240]"><?= e($feat['title']) ?></h2>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= e($feat['desc']) ?></p>
                    </div>
                    <a href="<?= e($feat['href']) ?>"
                       class="inline-flex items-center gap-1.5 text-sm font-bold text-[#0077b6] hover:text-[#00BFFF] transition w-fit">
                        <span>Buka Fitur</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
