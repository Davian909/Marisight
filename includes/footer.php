<?php
declare(strict_types=1);

$basePath = isset($basePath) ? (string)$basePath : '';
?>
<footer class="relative w-full bg-gradient-to-b from-[#0A2240] via-[#042A53] to-[#004D73] text-white mt-auto pt-14 pb-10 overflow-hidden">
    <!-- 3 LAYER WAVE FULL WIDTH -->
    <div class="absolute bottom-0 left-0 right-0 w-full h-24 overflow-hidden select-none pointer-events-none opacity-80">
        <svg class="w-full h-full block" viewBox="0 0 1440 320" preserveAspectRatio="none" aria-hidden="true">
            <path fill="#00c6ff" fill-opacity="0.7"
                  d="M0,192L60,186.7C120,181,240,171,360,176C480,181,600,203,720,213.3C840,224,960,224,1080,197.3C1200,171,1320,117,1380,90.7L1440,64V320H0Z"/>
            <path fill="#009ee6" fill-opacity="0.75"
                  d="M0,256L60,240C120,224,240,192,360,165.3C480,139,600,117,720,138.7C840,160,960,224,1080,229.3C1200,235,1320,181,1380,154.7L1440,128V320H0Z"/>
            <path fill="#0066aa" fill-opacity="0.85"
                  d="M0,288L60,272C120,256,240,224,360,218.7C480,213,600,235,720,256C840,277,960,299,1080,298.7C1200,299,1320,277,1380,266.7L1440,256V320H0Z"/>
        </svg>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8 md:gap-10">
            <div>
                <h3 class="text-2xl font-bold tracking-wide mb-3">MARISIGHT</h3>
                <p class="text-gray-300 text-sm leading-relaxed">
                    Platform pemantauan kelautan Pulau Tunda, Banten — cuaca, gelombang, suhu permukaan laut,
                    pasang surut, batimetri, dan fenomena solunar.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-base mb-3 text-[#00BFFF]">Kondisi Perairan</h4>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li><a href="<?= e($basePath) ?>cuaca.php" class="hover:text-[#00BFFF] transition">Cuaca</a></li>
                    <li><a href="<?= e($basePath) ?>ombak.php" class="hover:text-[#00BFFF] transition">Ombak &amp; Gelombang</a></li>
                    <li><a href="<?= e($basePath) ?>spl.php" class="hover:text-[#00BFFF] transition">Suhu Permukaan Laut</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-base mb-3 text-[#00BFFF]">Pasang Surut</h4>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li><a href="<?= e($basePath) ?>pasut_php/grafik.php" class="hover:text-[#00BFFF] transition">Grafik Pasang Surut</a></li>
                    <li><a href="<?= e($basePath) ?>pasut_php/tabel.php" class="hover:text-[#00BFFF] transition">Tabel Pasang Surut</a></li>
                    <li><a href="<?= e($basePath) ?>bath.php" class="hover:text-[#00BFFF] transition">Batimetri</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-base mb-3 text-[#00BFFF]">Solunar</h4>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li><a href="<?= e($basePath) ?>waktu_bulan.php" class="hover:text-[#00BFFF] transition">Waktu Bulan</a></li>
                    <li><a href="<?= e($basePath) ?>fase_bulan.php" class="hover:text-[#00BFFF] transition">Fase-Fase Bulan</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-base mb-3 text-[#00BFFF]">Informasi</h4>
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li><a href="<?= e($basePath) ?>fitur.php" class="hover:text-[#00BFFF] transition">Detail Fitur</a></li>
                    <li>
                        <a href="mailto:marisight@gmail.com" class="hover:text-[#00BFFF] transition">
                            Hubungi Kami
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/20 mt-10 pt-5 text-center">
            <p class="text-gray-300 text-xs sm:text-sm">
                &copy; <?= date('Y') ?> MARISIGHT — Marine Information Dashboard (Pulau Tunda, Banten).
            </p>
        </div>
    </div>
</footer>
