<?php
declare(strict_types=1);

/**
 * Endpoint sinkronisasi data pasang surut eksternal (opsional/admin).
 * Terproteksi: hanya dapat dijalankan lewat PHP CLI atau POST dengan login + CSRF token.
 */

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (PHP_SAPI !== 'cli') {
    start_secure_session();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !is_logged_in() || !verify_csrf($_POST['_csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Akses ditolak. Endpoint ini memerlukan sesi login dengan metode POST + CSRF token (atau jalankan via CLI).',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$conn = get_db_connection();
if (!$conn || !db_table_exists($conn, 'data_pasut')) {
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database marisight atau tabel data_pasut belum tersedia.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Catatan: Endpoint publik resmi BMKG Maritim menggunakan dokumentasi terbaru di https://maritim.bmkg.go.id.
// Endpoint kustom dapat dikonfigurasi via parameter/env jika Anda memiliki feed JSON pasut resmi.
$url = getenv('BMKG_PASUT_URL') ?: 'https://maritim.bmkg.go.id/api/pasut?station=SERANG';
$json = http_get_json($url, 6);

if (!is_array($json)) {
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal mengambil data JSON dari endpoint BMKG. Gunakan pasut_php/generate_pasut.php untuk prediksi harmonik lokal atau perbarui BMKG_PASUT_URL sesuai API resmi https://maritim.bmkg.go.id.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$inserted = 0;

try {
    $cek = $conn->prepare('SELECT id FROM data_pasut WHERE tanggal = ? LIMIT 1');
    $insert = $conn->prepare(
        'INSERT INTO data_pasut (tanggal, jam_pasang, jam_surut, tinggi_maks, tinggi_min, amplitudo, intensitas_ikan)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($json as $row) {
        if (!is_array($row) || empty($row['tanggal']) || !isset($row['tinggi'])) {
            continue;
        }
        $tanggal = date('Y-m-d', strtotime((string)$row['tanggal']));
        $tinggi  = (float)$row['tinggi'];

        $jamPasang  = (string)($row['jam_pasang'] ?? '06:00');
        $jamSurut   = (string)($row['jam_surut'] ?? '12:15');
        $tinggiMaks = round($tinggi + 0.15, 2);
        $tinggiMin  = round(max(0.0, $tinggi - 0.15), 2);
        $amplitudo  = round($tinggiMaks - $tinggiMin, 2);

        if ($amplitudo >= 0.50) {
            $intensitas = 'Aktif';
        } elseif ($amplitudo >= 0.35) {
            $intensitas = 'Sedang';
        } else {
            $intensitas = 'Rendah';
        }

        $cek->bind_param('s', $tanggal);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows === 0) {
            $insert->bind_param('sssddds', $tanggal, $jamPasang, $jamSurut, $tinggiMaks, $tinggiMin, $amplitudo, $intensitas);
            $insert->execute();
            $inserted++;
        }
    }

    $cek->close();
    $insert->close();

    echo json_encode([
        'status'   => 'success',
        'inserted' => $inserted,
        'message'  => 'Sinkronisasi data selesai.',
    ], JSON_UNESCAPED_UNICODE);
} catch (mysqli_sql_exception $e) {
    error_log('[MARISIGHT API ambil_bmkg] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal menyimpan data ke database.',
    ], JSON_UNESCAPED_UNICODE);
}
