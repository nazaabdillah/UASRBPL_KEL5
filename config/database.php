<?php
/**
 * config/database.php
 * Konfigurasi koneksi database menggunakan PDO
 * Ubah nilai sesuai environment Anda
 */

define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_NAME',     getenv('DB_NAME')     ?: 'wifi_voucher');
define('DB_USER',     getenv('DB_USER')     ?: 'app_voucher');
define('DB_PASS',     getenv('DB_PASS')     ?: 'dev123');
define('DB_CHARSET',  'utf8mb4');

/**
 * Mendapatkan koneksi PDO (Singleton pattern ringan)
 * Menggunakan static variable agar koneksi hanya dibuat sekali
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // [BUG FIX] die(json_encode()) mengirim JSON tanpa header Content-Type yang benar,
            // dan tidak mengirim HTTP 500 status code — browser/client bisa interpret salah.
            // Juga: response JSON bisa bocor ke AJAX caller dengan info structure internal.
            // Fix: log detail, kirim HTTP 503, tampilkan pesan generik.
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(503);
            // Jika request AJAX/API, kirim JSON; jika browser, tampilkan HTML generik
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Service temporarily unavailable.']);
            } else {
                echo '<!DOCTYPE html><html><head><title>503</title></head><body>'
                   . '<h2>Layanan Sementara Tidak Tersedia</h2>'
                   . '<p>Silakan coba beberapa saat lagi.</p>'
                   . '</body></html>';
            }
            exit;
        }
    }

    return $pdo;
}
