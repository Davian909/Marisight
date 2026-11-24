<?php
declare(strict_types=1);

/**
 * Wrapper kompatibilitas koneksi modul Pasang Surut.
 * Kini terhubung ke database tunggal 'marisight' (tabel data_pasut).
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

start_secure_session();
$conn = get_db_connection();
