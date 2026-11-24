<?php
declare(strict_types=1);

/**
 * Konfigurasi terpusat MARISIGHT.
 * Membaca nilai dari file .env di root project jika tersedia,
 * dengan fallback default untuk lingkungan XAMPP lokal.
 */

if (!defined('MARISIGHT_CONFIG_LOADED')) {
    define('MARISIGHT_CONFIG_LOADED', true);
    define('MARISIGHT_ROOT', dirname(__DIR__));

    date_default_timezone_set('Asia/Jakarta');

    // Parser .env sederhana (tanpa dependensi eksternal)
    $envPath = MARISIGHT_ROOT . DIRECTORY_SEPARATOR . '.env';
    $envValues = [];
    if (is_file($envPath)) {
        $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }
                $pos = strpos($trimmed, '=');
                if ($pos === false) {
                    continue;
                }
                $key = trim(substr($trimmed, 0, $pos));
                $val = trim(substr($trimmed, $pos + 1));
                if (
                    (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))
                ) {
                    $val = substr($val, 1, -1);
                }
                $envValues[$key] = $val;
            }
        }
    }

    $getEnv = static function (string $key, string $default = '') use ($envValues): string {
        if (array_key_exists($key, $envValues)) {
            return $envValues[$key];
        }
        $sys = getenv($key);
        return ($sys !== false) ? (string)$sys : $default;
    };

    define('APP_NAME', 'MARISIGHT');
    define('APP_ENV', $getEnv('APP_ENV', 'local'));

    // Konfigurasi database tunggal (marisight)
    define('DB_HOST', $getEnv('DB_HOST', 'localhost'));
    define('DB_PORT', (int)$getEnv('DB_PORT', '3306'));
    define('DB_USER', $getEnv('DB_USER', 'root'));
    define('DB_PASS', $getEnv('DB_PASS', ''));
    define('DB_NAME', $getEnv('DB_NAME', 'marisight'));

    // Koordinat standar Pulau Tunda, Banten (5°48'44"S, 106°16'47"E)
    define('PULAU_TUNDA_LAT', -5.8125);
    define('PULAU_TUNDA_LON', 106.2799);
    define('PULAU_TUNDA_NAME', 'Pulau Tunda, Banten');
}
