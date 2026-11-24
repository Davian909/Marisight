<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Memulai PHP session dengan konfigurasi cookie yang lebih aman.
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    @session_start();
}

/**
 * Escape string aman untuk HTML output (anti-XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Status autentikasi pengguna saat ini.
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function current_username(): ?string
{
    return is_logged_in() ? (string)$_SESSION['username'] : null;
}

function current_user_id(): ?int
{
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Token CSRF per session.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    if (!is_string($token) || $token === '' || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    return hash_equals((string)$_SESSION['_csrf_token'], $token);
}

/**
 * Whitelist halaman tujuan redirect internal (mencegah Open Redirect).
 */
function sanitize_redirect(?string $target, string $fallback = 'index.php'): string
{
    if (!is_string($target)) {
        return $fallback;
    }
    $clean = ltrim(trim($target), '/');

    $allowed = [
        'index.php',
        'fitur.php',
        'cuaca.php',
        'spl.php',
        'ombak.php',
        'bath.php',
        'contour.php',
        'profile.php',
        'surface.php',
        'waktu_bulan.php',
        'fase_bulan.php',
        'pasut_php/grafik.php',
        'pasut_php/tabel.php',
        'pasut_php/generate_pasut.php',
    ];

    // Mapping kompatibilitas alias lama yang sempat salah di versi sebelumnya
    $aliases = [
        'suhu.php' => 'spl.php',
        'grafik'   => 'pasut_php/grafik.php',
        'tabel'    => 'pasut_php/tabel.php',
    ];

    if (isset($aliases[$clean])) {
        $clean = $aliases[$clean];
    }

    return in_array($clean, $allowed, true) ? $clean : $fallback;
}

/**
 * Mengambil JSON dari URL eksternal dengan timeout aman (cURL + fallback stream).
 */
function http_get_json(string $url, int $timeoutSeconds = 5): ?array
{
    $raw = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
                CURLOPT_TIMEOUT        => $timeoutSeconds,
                CURLOPT_USERAGENT      => 'MARISIGHT/2.0',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $res = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (is_string($res) && $httpCode >= 200 && $httpCode < 300) {
                $raw = $res;
            }
        }
    } elseif (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => $timeoutSeconds,
                'header'  => "User-Agent: MARISIGHT/2.0\r\n",
            ],
        ]);
        $res = @file_get_contents($url, false, $ctx);
        if (is_string($res)) {
            $raw = $res;
        }
    }

    if ($raw === null) {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Pemetaan resmi kode cuaca WMO Open-Meteo ke label Indonesia & ikon Material Symbols.
 */
function weather_code_info(int $code): array
{
    $map = [
        0  => ['text' => 'Cerah',               'icon' => 'light_mode',   'theme' => 'sunny'],
        1  => ['text' => 'Cerah Berawan',       'icon' => 'partly_cloudy_day', 'theme' => 'cloudy'],
        2  => ['text' => 'Berawan Sebagian',    'icon' => 'cloud',        'theme' => 'cloudy'],
        3  => ['text' => 'Mendung',             'icon' => 'cloud_queue',  'theme' => 'cloudy'],
        45 => ['text' => 'Berkabut',            'icon' => 'foggy',        'theme' => 'cloudy'],
        48 => ['text' => 'Kabut Tebal',         'icon' => 'foggy',        'theme' => 'cloudy'],
        51 => ['text' => 'Gerimis Ringan',      'icon' => 'grain',        'theme' => 'rainy'],
        53 => ['text' => 'Gerimis Sedang',      'icon' => 'grain',        'theme' => 'rainy'],
        55 => ['text' => 'Gerimis Lebat',       'icon' => 'grain',        'theme' => 'rainy'],
        61 => ['text' => 'Hujan Ringan',        'icon' => 'rainy',        'theme' => 'rainy'],
        63 => ['text' => 'Hujan Sedang',        'icon' => 'rainy',        'theme' => 'rainy'],
        65 => ['text' => 'Hujan Lebat',         'icon' => 'rainy',        'theme' => 'rainy'],
        80 => ['text' => 'Hujan Lokal Ringan',  'icon' => 'rainy',        'theme' => 'rainy'],
        81 => ['text' => 'Hujan Lokal Sedang',  'icon' => 'rainy',        'theme' => 'rainy'],
        82 => ['text' => 'Hujan Lokal Lebat',   'icon' => 'rainy',        'theme' => 'rainy'],
        95 => ['text' => 'Badai Petir',         'icon' => 'thunderstorm', 'theme' => 'storm'],
        96 => ['text' => 'Badai Hujan Ringan',  'icon' => 'thunderstorm', 'theme' => 'storm'],
        99 => ['text' => 'Badai Hujan Lebat',   'icon' => 'thunderstorm', 'theme' => 'storm'],
    ];

    return $map[$code] ?? ['text' => 'Berawan', 'icon' => 'cloud', 'theme' => 'cloudy'];
}

/**
 * Mengonversi derajat arah angin (0-360) ke arah mata angin Indonesia.
 */
function wind_direction_label(float $deg): string
{
    $dirs = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'];
    $idx = (int)round(fmod($deg, 360.0) / 45.0) % 8;
    if ($idx < 0) {
        $idx += 8;
    }
    return $dirs[$idx];
}

/**
 * Membuat file ZIP secara aman:
 * - Memakai ZipArchive jika tersedia.
 * - Fallback otomatis ke Pure-PHP ZIP (metode STORE, ideal untuk PNG) bila ekstensi zip tidak aktif di XAMPP.
 *
 * @param array<string, string> $entries Map [nama_di_dalam_zip => path_file_absolut]
 */
function build_zip_archive(array $entries, string $destPath): bool
{
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($destPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        foreach ($entries as $archiveName => $filePath) {
            if (is_file($filePath)) {
                $zip->addFile($filePath, $archiveName);
            }
        }
        return $zip->close();
    }

    // Fallback Pure-PHP ZIP (STORE method - tanpa butuh extension=zip)
    $fp = @fopen($destPath, 'wb');
    if ($fp === false) {
        return false;
    }

    $centralDir = '';
    $offset = 0;
    $entryCount = 0;

    foreach ($entries as $archiveName => $filePath) {
        if (!is_file($filePath)) {
            continue;
        }
        $data = @file_get_contents($filePath);
        if ($data === false) {
            continue;
        }

        $name = str_replace('\\', '/', $archiveName);
        $nameLen = strlen($name);
        $size = strlen($data);
        $crc = crc32($data);
        $mtime = filemtime($filePath) ?: time();
        $dt = getdate($mtime);
        $dosTime = (($dt['hours'] << 11) | ($dt['minutes'] << 5) | ($dt['seconds'] >> 1));
        $dosDate = ((max(1980, $dt['year']) - 1980) << 9) | ($dt['mon'] << 5) | $dt['mday'];

        // Local File Header
        $localHeader = pack(
            'VvvvvvVVVvv',
            0x04034b50, // signature
            20,         // version needed
            0,          // general purpose bit flag
            0,          // compression method (0 = store)
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            $nameLen,
            0           // extra field length
        );

        fwrite($fp, $localHeader . $name . $data);

        // Central Directory File Header
        $centralDir .= pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50, // signature
            20,         // version made by
            20,         // version needed
            0,          // flag
            0,          // method (store)
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            $nameLen,
            0,          // extra length
            0,          // comment length
            0,          // disk number
            0,          // internal attr
            32,         // external attr
            $offset     // local header offset
        ) . $name;

        $offset += strlen($localHeader) + $nameLen + $size;
        $entryCount++;
    }

    $cdSize = strlen($centralDir);
    $eocd = pack(
        'VvvvvVVv',
        0x06054b50,  // end of central dir signature
        0,           // disk number
        0,           // start disk
        $entryCount, // entries on disk
        $entryCount, // total entries
        $cdSize,
        $offset,
        0            // comment length
    );

    fwrite($fp, $centralDir . $eocd);
    fclose($fp);

    return $entryCount > 0;
}
