<?php
/**
 * controllers/VoucherController.php
 * Logic untuk semua operasi voucher
 */

class VoucherController {
    private VoucherModel $voucherModel;

    public function __construct() {
        $this->voucherModel = new VoucherModel();
    }

    // =========================================================
    // List Voucher
    // =========================================================

    public function index(): void {
        requireLogin();

        $filters = [
            'status'  => sanitize($_GET['status']  ?? ''),
            'profile' => sanitize($_GET['profile'] ?? ''),
            'search'  => sanitize($_GET['search']  ?? ''),
        ];

        // Pagination
        $perPage = 20;
        $page    = max(1, sanitizeInt($_GET['p'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $vouchers = $this->voucherModel->getAll($filters, $perPage, $offset);
        $total    = $this->voucherModel->count($filters);
        $pages    = (int)ceil($total / $perPage);
        $profiles = defined('MT_PROFILES') ? MT_PROFILES : [];

        require __DIR__ . '/../views/vouchers/index.php';
    }

    // =========================================================
    // Generate Voucher (single)
    // =========================================================

    public function generate(): void {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $profiles = defined('MT_PROFILES') ? MT_PROFILES : [];
            require __DIR__ . '/../views/vouchers/generate.php';
            return;
        }

        // Validasi CSRF
        $this->validateCsrf();

        $profile  = sanitize($_POST['profile'] ?? '');
        $qty      = sanitizeInt($_POST['qty'] ?? 1, 1);
        $comment  = sanitize($_POST['comment'] ?? '');
        $qty      = min(max($qty, 1), 50); // Batasi 1-50 voucher

        // Validasi profile
        $validProfiles = defined('MT_PROFILES') ? array_keys(MT_PROFILES) : [];
        if (!in_array($profile, $validProfiles, true)) {
            setFlash('danger', 'Profile tidak valid.');
            redirect('index.php?page=generate');
        }

        // Generate voucher data
        $generated = [];
        $attempts  = 0;

        while (count($generated) < $qty && $attempts < $qty * 5) {
            $attempts++;
            $username = generateVoucherUsername();
            $password = generateVoucherPassword();

            // Pastikan username unik
            if ($this->voucherModel->usernameExists($username)) {
                continue;
            }

            $generated[] = [
                'username'   => $username,
                'password'   => $password,
                'profile'    => $profile,
                'comment'    => $comment,
                'created_by' => $_SESSION['admin_id'],
            ];
        }

        if (empty($generated)) {
            setFlash('danger', 'Gagal generate voucher. Coba lagi.');
            redirect('index.php?page=generate');
        }

        // Kirim ke MikroTik
        $mtErrors = [];
        try {
            $api = getMikrotikConnection();
            foreach ($generated as &$v) {
                $ok = $api->addHotspotUser($v['username'], $v['password'], $v['profile'], $v['comment']);
                if (!$ok) {
                    $mtErrors[] = "Gagal kirim '{$v['username']}' ke MikroTik: " . $api->getLastError();
                    // Tandai voucher ini gagal — jangan simpan ke DB
                    $v['_failed_mt'] = true;
                }
            }
            $api->disconnect();
        } catch (\RuntimeException $e) {
            // MikroTik tidak bisa diakses — simpan ke DB saja, tandai warning
            $mtErrors[] = 'Koneksi MikroTik gagal: ' . $e->getMessage()
                        . '. Voucher disimpan lokal, tambahkan manual ke router.';
        }

        // Filter yang tidak gagal di MikroTik
        $toSave = array_filter($generated, fn($v) => !isset($v['_failed_mt']));

        if (!empty($toSave)) {
            $this->voucherModel->bulkCreate(array_values($toSave));
            logActivity('GENERATE_VOUCHER', "Generate " . count($toSave) . " voucher profile=$profile");
        }

        if ($mtErrors) {
            setFlash('warning', implode('<br>', $mtErrors));
        } else {
            $count = count($toSave);
            setFlash('success', "$count voucher berhasil digenerate dan dikirim ke MikroTik!");
        }

        // Simpan generated list untuk halaman print
        $_SESSION['last_generated'] = array_values($toSave);

        redirect('index.php?page=vouchers');
    }

    // =========================================================
    // Delete Voucher
    // =========================================================

    public function delete(): void {
        requireLogin();
        $this->validateCsrf();

        $id = sanitizeInt($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlash('danger', 'ID tidak valid.');
            redirect('index.php?page=vouchers');
        }

        $voucher = $this->voucherModel->findById($id);
        if (!$voucher) {
            setFlash('danger', 'Voucher tidak ditemukan.');
            redirect('index.php?page=vouchers');
        }

        // Hapus dari MikroTik
        $mtError = null;
        try {
            $api = getMikrotikConnection();
            $ok  = $api->removeHotspotUser($voucher['username']);
            if (!$ok) {
                $mtError = 'Gagal hapus dari MikroTik: ' . $api->getLastError();
            }
            $api->disconnect();
        } catch (\RuntimeException $e) {
            $mtError = 'Koneksi MikroTik gagal: ' . $e->getMessage();
        }

        // Hapus dari database
        $this->voucherModel->delete($id);
        logActivity('DELETE_VOUCHER', "Hapus voucher ID=$id username={$voucher['username']}");

        if ($mtError) {
            setFlash('warning', "Voucher dihapus dari database. $mtError");
        } else {
            setFlash('success', "Voucher '{$voucher['username']}' berhasil dihapus.");
        }

        redirect('index.php?page=vouchers');
    }

    // =========================================================
    // Print Voucher
    // =========================================================

    public function printVouchers(): void {
        requireLogin();

        $vouchers = $_SESSION['last_generated'] ?? [];
        unset($_SESSION['last_generated']);

        if (empty($vouchers)) {
            // Ambil dari GET IDs jika ada
            $ids = array_map('intval', explode(',', $_GET['ids'] ?? ''));
            foreach ($ids as $id) {
                if ($id > 0) {
                    $v = $this->voucherModel->findById($id);
                    if ($v) $vouchers[] = $v;
                }
            }
        }

        require __DIR__ . '/../views/vouchers/print.php';
    }

    // =========================================================
    // Quick Print — cetak semua voucher unused
    // =========================================================
    public function quickPrint(): void {
        requireLogin();

        // Ambil semua voucher unused, max 200
        $vouchers = $this->voucherModel->getAll(['status' => 'unused'], 200);

        // Kumpulkan semua profile yang ada untuk filter
        $allProfiles = array_unique(array_column($vouchers, 'profile'));
        sort($allProfiles);

        require __DIR__ . '/../views/vouchers/quick-print.php';
    }

    // =========================================================
    // =========================================================

    /**
     * Sinkronisasi status voucher dengan data aktif di MikroTik
     */
    public function syncStatus(): void {
        requireLogin();
        // [BUG FIX] Sync sekarang hanya bisa dipanggil via POST (lihat index.php).
        // Validasi CSRF untuk keamanan penuh.
        $this->validateCsrf();

        // Inisialisasi default agar tidak undefined variable jika exception terjadi
        $actives = [];

        try {
            $api     = getMikrotikConnection();
            $actives = $api->getHotspotUsers();
            $api->disconnect();
        } catch (\RuntimeException $e) {
            setFlash('danger', 'Sync gagal: ' . $e->getMessage());
            redirect('index.php?page=vouchers');
        }

        // Jika $actives kosong setelah connect berhasil, beri peringatan
        // (bisa berarti tidak ada user hotspot sama sekali di router)
        if (empty($actives)) {
            logActivity('SYNC_STATUS', 'Sinkronisasi: tidak ada user hotspot di MikroTik.');
            setFlash('warning', 'Sinkronisasi selesai. Tidak ada user hotspot ditemukan di MikroTik. Status voucher tidak diubah.');
            redirect('index.php?page=vouchers');
        }

        // Buat set username yang ada di MikroTik (key = username, untuk O(1) lookup)
        $mtUsernames = array_column(
            array_filter($actives, fn($u) => isset($u['name'])),
            'name'
        );
        $mtSet = array_flip($mtUsernames);

        // Ambil semua voucher dari DB (max 1000 untuk keamanan memori)
        $vouchers = $this->voucherModel->getAll([], 1000);
        $updated  = 0;

        foreach ($vouchers as $v) {
            $inMt = isset($mtSet[$v['username']]);

            // Ada di MikroTik tapi DB bilang terpakai → kembalikan ke unused
            if ($inMt && $v['status'] === 'used') {
                $this->voucherModel->updateStatus((int)$v['id'], 'unused');
                $updated++;
            }
            // Tidak ada di MikroTik tapi DB bilang tersedia → tandai terpakai
            elseif (!$inMt && $v['status'] === 'unused') {
                $this->voucherModel->updateStatus((int)$v['id'], 'used');
                $updated++;
            }
        }

        logActivity('SYNC_STATUS', "Sinkronisasi status voucher: $updated diperbarui.");
        setFlash('success', "Sinkronisasi selesai. $updated voucher diperbarui statusnya.");
        redirect('index.php?page=vouchers');
    }

    // =========================================================
    // Private helpers
    // =========================================================

    private function validateCsrf(): void {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid request. Silakan coba lagi.');
            redirect('index.php?page=vouchers');
        }
    }
}
