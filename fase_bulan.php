<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
start_secure_session();

$basePath   = '';
$activePage = 'fase_bulan';

$phases = [
    ['idx' => 0, 'img' => 'image2/1.jpg', 'title' => 'Bulan Baru (New Moon)', 'desc' => 'Bulan berada di antara Bumi dan Matahari, sisi yang menghadap Bumi tidak mendapat cahaya langsung.'],
    ['idx' => 1, 'img' => 'image2/2.jpg', 'title' => 'Sabit Awal (Waxing Crescent)', 'desc' => 'Sebagian kecil bulan mulai terlihat sebagai sabit tipis setelah fase bulan baru.'],
    ['idx' => 2, 'img' => 'image2/3.jpg', 'title' => 'Kuartal Pertama (First Quarter)', 'desc' => 'Separuh bulan terlihat terang, membentuk sudut 90 derajat terhadap Matahari dan Bumi.'],
    ['idx' => 3, 'img' => 'image2/4.jpg', 'title' => 'Cembung Awal (Waxing Gibbous)', 'desc' => 'Lebih dari separuh bulan terlihat terang dan terus bertambah besar menuju bulan purnama.'],
    ['idx' => 4, 'img' => 'image2/5.jpg', 'title' => 'Bulan Purnama (Full Moon)', 'desc' => 'Bumi berada di antara Matahari dan Bulan, membuat seluruh sisi bulan yang menghadap Bumi terlihat terang penuh.'],
    ['idx' => 5, 'img' => 'image2/6.jpg', 'title' => 'Cembung Akhir (Waning Gibbous)', 'desc' => 'Bulan mulai terlihat mengecil setelah fase purnama, namun masih lebih dari separuh permukaannya terang.'],
    ['idx' => 6, 'img' => 'image2/7.jpg', 'title' => 'Kuartal Ketiga (Third Quarter)', 'desc' => 'Separuh bagian lain dari bulan terlihat terang saat bergerak menuju fase bulan baru.'],
    ['idx' => 7, 'img' => 'image2/8.jpg', 'title' => 'Sabit Akhir (Waning Crescent)', 'desc' => 'Fase sabit tipis terakhir sebelum bulan kembali ke fase bulan baru dan siklus 29,5 hari dimulai lagi.'],
];
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Fase Bulan - MARISIGHT</title>
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

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0A2240] hover:text-[#00BFFF] transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-[#0A2240]">Memahami Siklus &amp; Fase Bulan</h1>
            <p class="mt-1 text-sm sm:text-base text-gray-600">
                Siklus 8 fase bulan yang mempengaruhi pasang surut dan aktivitas biota laut. Fase aktif hari ini ditandai otomatis.
            </p>
        </div>

        <a href="waktu_bulan.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0A2240] text-white text-sm font-semibold hover:bg-[#004D73] transition">
            <span class="material-symbols-outlined text-base">nights_stay</span>
            <span>Lihat Waktu Bulan Hari Ini</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($phases as $p): ?>
            <article data-phase-idx="<?= (int)$p['idx'] ?>"
                     class="phase-card flex flex-col gap-3 bg-white border border-gray-200 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center">
                    <img src="<?= e($p['img']) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover">
                    <span class="active-badge hidden absolute top-2 right-2 px-2.5 py-0.5 rounded-full bg-[#00BFFF] text-[#0A2240] text-xs font-bold shadow">
                        Fase Hari Ini
                    </span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#0A2240]"><?= e($p['title']) ?></h2>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed"><?= e($p['desc']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
// Tandai fase bulan aktif hari ini berdasarkan siklus sinodik (referensi Bulan Baru 6 Jan 2000 18:14 UTC)
(function () {
    const synodicMonth = 29.53058867;
    const knownNewMoon = Date.UTC(2000, 0, 6, 18, 14, 0);
    const daysSince = (Date.now() - knownNewMoon) / 86400000;
    const cycle = ((daysSince % synodicMonth) + synodicMonth) % synodicMonth;
    const idx = Math.floor(((cycle / synodicMonth) * 8) + 0.5) % 8;

    const card = document.querySelector(`.phase-card[data-phase-idx="${idx}"]`);
    if (card) {
        card.classList.add('ring-2', 'ring-[#00BFFF]', 'border-[#00BFFF]');
        const badge = card.querySelector('.active-badge');
        if (badge) badge.classList.remove('hidden');
    }
})();
</script>

</body>
</html>
