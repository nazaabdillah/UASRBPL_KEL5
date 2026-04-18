<?php
/**
 * controllers/AuthController.php
 * Menangani login dan logout admin
 */

class AuthController {
    private AdminModel $adminModel;

    public function __construct() {
        $this->adminModel = new AdminModel();
    }

    /**
     * Proses login — validasi kredensial lalu buat session
     */
    public function login(): void {
        // Jika sudah login, langsung ke dashboard
        if (isLoggedIn()) {
            redirect('index.php?page=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // [BUG FIX] Repopulate username dari session (disimpan saat login gagal)
            $lastUsername = $_SESSION['login_last_username'] ?? '';
            unset($_SESSION['login_last_username']);
            // Tampilkan halaman login
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        // Validasi CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token. Silakan coba lagi.');
            redirect('index.php?page=login');
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input dasar
        if (empty($username) || empty($password)) {
            setFlash('danger', 'Username dan password wajib diisi.');
            redirect('index.php?page=login');
        }

        // Cari admin di database
        $admin = $this->adminModel->findByUsername($username);

        if (!$admin || !$this->adminModel->verifyPassword($password, $admin['password'])) {
            // Delay kecil untuk mencegah brute-force
            sleep(1);
            setFlash('danger', 'Username atau password salah.');
            // [BUG FIX] Simpan username di session sementara agar form bisa repopulate setelah redirect.
            // Sebelumnya value="" kosong karena POST data tidak tersedia setelah redirect (PRG pattern).
            $_SESSION['login_last_username'] = $username;
            redirect('index.php?page=login');
        }

        // Buat session baru (regenerate ID untuk keamanan)
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name']     = $admin['full_name'] ?: $admin['username'];

        logActivity('LOGIN', "Admin '{$admin['username']}' berhasil login.");

        redirect('index.php?page=dashboard');
    }

    /**
     * Logout — hancurkan session
     * [BUG FIX] Validasi CSRF token sebelum logout untuk mencegah CSRF attack.
     * Sebelumnya logout bisa dipicu via GET request dari halaman pihak ketiga.
     */
    public function logout(): void {
        // Validasi CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid request.');
            redirect('index.php?page=dashboard');
        }

        if (isLoggedIn()) {
            logActivity('LOGOUT', "Admin '{$_SESSION['admin_username']}' logout.");
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        redirect('index.php?page=login');
    }
}
