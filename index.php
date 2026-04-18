<?php
/**
 * index.php — Entry Point & Router Utama VoucherNet
 */

ini_set('expose_php', 'off');
$devMode = getenv('APP_ENV') !== 'production';
ini_set('display_errors', $devMode ? '1' : '0');
error_reporting($devMode ? E_ALL : 0);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Load konfigurasi & helpers
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mikrotik.php';
require_once __DIR__ . '/helpers/functions.php';

// Load Models
require_once __DIR__ . '/models/AdminModel.php';
require_once __DIR__ . '/models/VoucherModel.php';

// Load Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/VoucherController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/CaptivePortalController.php';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Routing
// [BUG FIX] sanitize() menggunakan htmlspecialchars — page name bisa rusak jika ada karakter khusus.
// Gunakan whitelist-only: ambil raw lalu validasi via $allowedPages, tidak perlu di-encode.
$page = trim($_GET['page'] ?? 'dashboard');

$allowedPages = [
    'login', 'logout',
    'dashboard',
    // Voucher
    'vouchers', 'generate', 'delete', 'print', 'sync', 'quick-print',
    // Users
    'users', 'add-user', 'delete-user',
    // Profiles
    'profiles', 'add-profile', 'delete-profile',
    // Captive Portal
    'captive-portal',
];

if (!in_array($page, $allowedPages, true)) {
    redirect('index.php?page=dashboard');
}

try {
    switch ($page) {

        // Auth
        case 'login':
            (new AuthController())->login();
            break;

        // [BUG FIX] Logout harus POST untuk mencegah CSRF.
        // Jika ada request GET ke logout (misal dari link lama), redirect ke dashboard.
        case 'logout':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('index.php?page=dashboard');
            }
            (new AuthController())->logout();
            break;

        // Dashboard
        case 'dashboard':
            (new DashboardController())->index();
            break;

        // Voucher
        case 'vouchers':
            (new VoucherController())->index();
            break;
        case 'generate':
            (new VoucherController())->generate();
            break;
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php?page=vouchers');
            (new VoucherController())->delete();
            break;
        case 'print':
            (new VoucherController())->printVouchers();
            break;
        case 'quick-print':
            (new VoucherController())->quickPrint();
            break;

        // [BUG FIX] Sync mengubah data di database — harus POST, bukan GET.
        // Akses GET sebelumnya memungkinkan CSRF: embed <img src="?page=sync"> bisa
        // mentrigger sinkronisasi tanpa sepengetahuan admin.
        case 'sync':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php?page=vouchers');
            (new VoucherController())->syncStatus();
            break;

        // Users
        case 'users':
            (new UserController())->index();
            break;
        case 'add-user':
            (new UserController())->addUser();
            break;
        case 'delete-user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php?page=users');
            (new UserController())->deleteUser();
            break;

        // Profiles
        case 'profiles':
            (new ProfileController())->index();
            break;
        case 'add-profile':
            (new ProfileController())->addProfile();
            break;
        case 'delete-profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php?page=profiles');
            (new ProfileController())->deleteProfile();
            break;

        // Captive Portal
        case 'captive-portal':
            (new CaptivePortalController())->index();
            break;
    }

} catch (Throwable $e) {
    error_log('Unhandled error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($devMode) {
        echo '<div style="font-family:monospace;background:#1e1e2e;color:#f38ba8;padding:24px;margin:20px;border-radius:8px;">';
        echo '<h3 style="color:#cba6f7">⚠ Unhandled Exception</h3>';
        echo '<p><strong>' . get_class($e) . ':</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p style="color:#a6e3a1;">File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre style="color:#cdd6f4;font-size:12px;overflow:auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        setFlash('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        redirect('index.php?page=dashboard');
    }
}
