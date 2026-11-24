<?php
declare(strict_types=1);

/**
 * Wrapper kompatibilitas koneksi database utama MARISIGHT.
 * Seluruh modul kini memakai database tunggal 'marisight' lewat config/database.php.
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

start_secure_session();
$conn = get_db_connection();
