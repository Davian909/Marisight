<?php
declare(strict_types=1);

/**
 * JSON REST API: Mengembalikan seluruh data pasang surut dari tabel `data_pasut`.
 * Endpoint: GET /pasut_php/api/ambil_pasut.php
 */

require_once dirname(__DIR__, 2) . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$conn = get_db_connection();
if (!$conn || !db_table_exists($conn, 'data_pasut')) {
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database marisight atau tabel data_pasut belum tersedia. Impor database/schema.sql terlebih dahulu.',
        'data'    => [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $limit = isset($_GET['limit']) ? max(1, min(365, (int)$_GET['limit'])) : 90;
    $stmt = $conn->prepare(
        'SELECT tanggal, jam_pasang, jam_surut, tinggi_maks, tinggi_min, amplitudo, intensitas_ikan
         FROM data_pasut
         ORDER BY tanggal ASC
         LIMIT ?'
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $maks = (float)$row['tinggi_maks'];
            $min  = (float)$row['tinggi_min'];
            $amp  = (float)$row['amplitudo'];
            $items[] = [
                'tanggal'         => (string)$row['tanggal'],
                'jam_pasang'      => (string)$row['jam_pasang'],
                'jam_surut'       => (string)$row['jam_surut'],
                'tinggi'          => $maks, // alias kompatibilitas lama
                'tinggi_maks'     => $maks,
                'tinggi_min'      => $min,
                'amplitudo'       => $amp,
                'intensitas_ikan' => (string)$row['intensitas_ikan'],
            ];
        }
    }
    $stmt->close();

    echo json_encode([
        'status'   => 'success',
        'location' => PULAU_TUNDA_NAME,
        'count'    => count($items),
        'data'     => $items,
    ], JSON_UNESCAPED_UNICODE);
} catch (mysqli_sql_exception $e) {
    error_log('[MARISIGHT API ambil_pasut] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal membaca data pasang surut.',
        'data'    => [],
    ], JSON_UNESCAPED_UNICODE);
}
