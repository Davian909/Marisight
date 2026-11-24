<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'bath';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Kedalaman Pulau Tunda - MARISIGHT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>body { font-family: 'Space Grotesk', sans-serif; }</style>
</head>
<body class="bg-[#F5F7FA] text-[#333333] flex flex-col min-h-screen">

<?php require __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div>
        <a href="bath.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span>Kembali ke Batimetri</span>
        </a>
        <h1 class="mt-2 text-3xl font-bold text-[#0A2240]">Profil Kedalaman Batimetri</h1>
        <p class="mt-1 text-sm text-gray-600">Penampang melintang elevasi dasar laut Pulau Tunda terhadap jarak (Dataset 1–15).</p>
    </div>

    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <a href="bath.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Galeri Gabungan (15)</a>
        <a href="contour.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Peta Kontur (15)</a>
        <a href="profile.php" class="px-4 py-2 rounded-lg text-sm font-bold bg-[#0A2240] text-white">Profil Kedalaman (15)</a>
        <a href="surface.php" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border border-gray-200 text-[#0A2240] hover:border-[#00BFFF] transition">Surface 3D (15)</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php for ($i = 1; $i <= 15; $i++): ?>
            <article class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-[#0A2240]">Profil Kedalaman Dataset <?= $i ?></h2>
                    <a href="assets/profile<?= $i ?>.png" download="profile_pulau_tunda_<?= $i ?>.png"
                       class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900">
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Unduh</span>
                    </a>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-center">
                    <img src="assets/profile<?= $i ?>.png" alt="Profil Kedalaman Dataset <?= $i ?>" loading="lazy"
                         class="max-h-60 w-auto object-contain rounded">
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Profil kedalaman dataset <?= $i ?> memperlihatkan perubahan topografi dasar laut dari area dangkal
                    menuju perairan lebih dalam, termasuk variasi cekungan sedimen dan tonjolan dasar laut.
                </p>
            </article>
        <?php endfor; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
