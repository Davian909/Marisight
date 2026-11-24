<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Mengembalikan koneksi mysqli tunggal ke database 'marisight'.
 * Dilengkapi auto-init: bila MySQL aktif tetapi database/tabel belum ada,
 * PHP otomatis membuat database 'marisight', tabel `users` & `data_pasut`,
 * serta mengisi 15 hari data awal pasang surut mulai hari ini.
 *
 * Aman di PHP 8.1+ (menangkap mysqli_sql_exception agar tidak fatal crash
 * ketika service MySQL XAMPP belum dinyalakan).
 */
function get_db_connection(): ?mysqli
{
    static $initialized = false;
    static $connection = null;

    if ($initialized) {
        return $connection;
    }
    $initialized = true;
    $GLOBALS['marisight_db_error'] = null;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Validasi nama database agar aman saat auto-create
    $dbName = DB_NAME;
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
        $GLOBALS['marisight_db_error'] = 'Nama database pada konfigurasi tidak valid.';
        return null;
    }

    try {
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, $dbName, DB_PORT);
        } catch (mysqli_sql_exception $e) {
            // Error 1049 = Unknown database -> buat database otomatis
            if ((int)$e->getCode() !== 1049) {
                throw $e;
            }

            $bootstrap = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
            $bootstrap->set_charset('utf8mb4');
            $bootstrap->query(
                "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $bootstrap->select_db($dbName);
            $mysqli = $bootstrap;
        }

        $mysqli->set_charset('utf8mb4');
        ensure_marisight_schema($mysqli);

        $connection = $mysqli;
        return $connection;
    } catch (mysqli_sql_exception $e) {
        error_log('[MARISIGHT DB Error] ' . $e->getMessage());
        $GLOBALS['marisight_db_error'] = $e->getMessage();
        $connection = null;
        return null;
    }
}

/**
 * Memastikan tabel `users` dan `data_pasut` sudah ada, serta mengisi
 * seed awal 15 hari pasang surut secara otomatis bila `data_pasut` masih kosong.
 */
function ensure_marisight_schema(mysqli $conn): void
{
    static $schemaChecked = false;
    if ($schemaChecked) {
        return;
    }
    $schemaChecked = true;

    $hasUsers = db_table_exists($conn, 'users');
    $hasPasut = db_table_exists($conn, 'data_pasut');

    if (!$hasUsers) {
        $conn->query(
            "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(100) NOT NULL,
                `email` VARCHAR(150) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_users_username` (`username`),
                UNIQUE KEY `uq_users_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    if (!$hasPasut) {
        $conn->query(
            "CREATE TABLE IF NOT EXISTS `data_pasut` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tanggal` DATE NOT NULL,
                `jam_pasang` VARCHAR(10) NOT NULL,
                `jam_surut` VARCHAR(10) NOT NULL,
                `tinggi_maks` DECIMAL(5,2) NOT NULL,
                `tinggi_min` DECIMAL(5,2) NOT NULL,
                `amplitudo` DECIMAL(5,2) NOT NULL,
                `intensitas_ikan` VARCHAR(20) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_data_pasut_tanggal` (`tanggal`),
                KEY `idx_data_pasut_tanggal` (`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    // Jika tabel data_pasut baru dibuat atau masih kosong, isi otomatis 15 hari mulai hari ini
    $res = $conn->query('SELECT COUNT(*) AS total FROM `data_pasut`');
    $row = $res ? $res->fetch_assoc() : null;
    if ($res) {
        $res->free();
    }

    if ((int)($row['total'] ?? 0) === 0) {
        seed_initial_tide_data($conn, 15);
    }
}

/**
 * Mengisi data pasang surut awal berbasis fase lunar (Spring/Neap Tide) mulai hari ini.
 */
function seed_initial_tide_data(mysqli $conn, int $days = 15): void
{
    $stmt = $conn->prepare(
        'INSERT IGNORE INTO `data_pasut`
            (`tanggal`, `jam_pasang`, `jam_surut`, `tinggi_maks`, `tinggi_min`, `amplitudo`, `intensitas_ikan`)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $startDate = new DateTimeImmutable('today');
    $synodic = 29.53059;
    $knownNewMoon = new DateTimeImmutable('2000-01-06 18:14:00', new DateTimeZone('UTC'));

    for ($i = 0; $i < $days; $i++) {
        $current = $startDate->modify("+{$i} days");
        $tgl = $current->format('Y-m-d');

        $diffDays = ($current->getTimestamp() - $knownNewMoon->getTimestamp()) / 86400.0;
        $lunarPhase = fmod(fmod($diffDays, $synodic) + $synodic, $synodic) / $synodic;
        $springFactor = (cos(4.0 * M_PI * $lunarPhase) + 1.0) / 2.0;

        $mean = 1.05;
        $halfRange = 0.14 + ($springFactor * 0.24);
        $tinggiMaks = round($mean + $halfRange, 2);
        $tinggiMin  = round(max(0.45, $mean - $halfRange), 2);
        $amplitudo  = round($tinggiMaks - $tinggiMin, 2);

        $pasangMinutes = (int)(240 + ($i * 50)) % 1440;
        $surutMinutes  = ($pasangMinutes + 375) % 1440;
        $jamPasang = sprintf('%02d:%02d', intdiv($pasangMinutes, 60), $pasangMinutes % 60);
        $jamSurut  = sprintf('%02d:%02d', intdiv($surutMinutes, 60), $surutMinutes % 60);

        if ($amplitudo >= 0.50) {
            $ikan = 'Aktif';
        } elseif ($amplitudo >= 0.35) {
            $ikan = 'Sedang';
        } else {
            $ikan = 'Rendah';
        }

        $stmt->bind_param('sssddds', $tgl, $jamPasang, $jamSurut, $tinggiMaks, $tinggiMin, $amplitudo, $ikan);
        $stmt->execute();
    }

    $stmt->close();
}

function get_db_error(): ?string
{
    return isset($GLOBALS['marisight_db_error']) ? (string)$GLOBALS['marisight_db_error'] : null;
}

/**
 * Mengecek apakah tabel tertentu tersedia pada koneksi aktif.
 */
function db_table_exists(?mysqli $conn, string $table): bool
{
    if (!$conn) {
        return false;
    }
    try {
        $stmt = $conn->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1'
        );
        $dbName = DB_NAME;
        $stmt->bind_param('ss', $dbName, $table);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    } catch (mysqli_sql_exception $e) {
        error_log('[MARISIGHT DB Table Check] ' . $e->getMessage());
        return false;
    }
}
