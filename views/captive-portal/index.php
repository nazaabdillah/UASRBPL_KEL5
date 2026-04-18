<?php
/**
 * views/captive-portal/index.php
 * Halaman manajemen & panduan setup Captive Portal
 */
require __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid px-4 py-3">

    <!-- Status Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:linear-gradient(135deg,#6c63ff,#5a52d5)">
                    <i class="bi bi-globe2"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Status Portal</div>
                    <div class="stat-value" style="font-size:18px;color:#4ade80">
                        <?= $portalExists ? '✅ File Tersedia' : '⚠️ Belum Dipasang' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:linear-gradient(135deg,#00d4aa,#00a87f)">
                    <i class="bi bi-router"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">MikroTik Router</div>
                    <div class="stat-value" style="font-size:16px"><?= e($mtHost) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Portal URL</div>
                    <div class="stat-value" style="font-size:12px;word-break:break-all"><?= e($portalUrl) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Kiri: Preview + Quick Actions -->
        <div class="col-lg-5">

            <!-- Preview Card -->
            <div class="card-modern mb-4">
                <div class="card-modern-header">
                    <i class="bi bi-eye me-2"></i>Preview Captive Portal
                </div>
                <div class="card-modern-body p-0" style="border-radius:0 0 16px 16px;overflow:hidden">
                    <div style="background:#0f0f1a;padding:20px;text-align:center">
                        <!-- Mini preview iframe feel -->
                        <div style="
                            background:#1a1a2e;
                            border:1px solid rgba(108,99,255,.3);
                            border-radius:16px;
                            padding:24px 20px;
                            max-width:300px;
                            margin:0 auto;
                            font-family:'Inter',sans-serif;
                        ">
                            <div style="width:48px;height:48px;background:linear-gradient(135deg,#6c63ff,#00d4aa);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px">📶</div>
                            <div style="font-weight:800;font-size:18px;color:#e2e8f0;margin-bottom:4px">VoucherNet WiFi</div>
                            <div style="font-size:11px;color:#94a3b8;margin-bottom:16px">Masukkan kode voucher untuk akses internet</div>
                            <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;margin-bottom:8px;text-align:left;font-size:11px;color:#94a3b8">🎫 Kode Voucher...</div>
                            <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:8px 12px;margin-bottom:12px;text-align:left;font-size:11px;color:#94a3b8">🔑 Password...</div>
                            <div style="background:linear-gradient(135deg,#6c63ff,#5a52d5);border-radius:8px;padding:8px;font-size:12px;color:#fff;font-weight:600">🚀 Sambungkan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File List -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="bi bi-folder2-open me-2"></i>File Captive Portal
                </div>
                <div class="card-modern-body">
                    <?php
                    $files = [
                        ['captive-portal/index.php',   'Login Page',        'Halaman voucher login untuk user WiFi',    'bi-door-open'],
                        ['captive-portal/success.php',  'Success Page',      'Halaman setelah login berhasil',           'bi-check-circle'],
                        ['captive-portal/error.php',    'Error Page',        'Halaman gagal koneksi',                    'bi-x-circle'],
                        ['captive-portal/aloha.php',    'Status Page',       'Status koneksi (uptime, bytes, sisa waktu)','bi-speedometer2'],
                    ];
                    foreach ($files as [$path, $label, $desc, $icon]):
                        $exists = file_exists(__DIR__ . '/../../' . $path);
                    ?>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="width:36px;height:36px;background:rgba(108,99,255,.15);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi <?= $icon ?>" style="color:#a78bfa"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="font-weight:600;font-size:13px"><?= $label ?></span>
                                <span class="badge <?= $exists ? 'badge-unused' : 'badge-used' ?>">
                                    <?= $exists ? 'Ada' : 'Tidak Ada' ?>
                                </span>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= $desc ?></div>
                            <code style="font-size:10px;color:#6c63ff"><?= $path ?></code>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Kanan: Setup Guide -->
        <div class="col-lg-7">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="bi bi-journal-code me-2"></i>Panduan Setup MikroTik
                </div>
                <div class="card-modern-body">

                    <!-- Step 1 -->
                    <div class="setup-step mb-4">
                        <div class="step-header">
                            <div class="step-num">1</div>
                            <div class="step-title">Upload File Captive Portal ke Server Web</div>
                        </div>
                        <div class="step-body">
                            <p class="mb-2" style="font-size:13px;color:var(--text-muted)">
                                Pastikan folder <code>captive-portal/</code> sudah di-upload dan bisa diakses via browser.
                                Test dengan buka URL berikut di browser Anda:
                            </p>
                            <div class="code-block">
                                <code><?= e($portalUrl) ?></code>
                                <button class="btn-copy" onclick="copyText('<?= e($portalUrl) ?>', this)" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="setup-step mb-4">
                        <div class="step-header">
                            <div class="step-num">2</div>
                            <div class="step-title">Konfigurasi Hotspot Profile di MikroTik</div>
                        </div>
                        <div class="step-body">
                            <p class="mb-2" style="font-size:13px;color:var(--text-muted)">
                                Di Winbox atau Terminal MikroTik, jalankan perintah berikut.
                                Ganti <code>default</code> dengan nama profile Anda:
                            </p>
                            <div class="code-block">
                                <code id="cmd-profile">/ip hotspot profile set [find name=default] \
  login-by=http-chap \
  html-directory=flash/hotspot \
  http-cookie-lifetime=3d \
  use-radius=no</code>
                                <button class="btn-copy" onclick="copyText(document.getElementById('cmd-profile').textContent, this)" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="alert-tip mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Jika Anda host captive portal di web server eksternal (bukan di router),
                                gunakan opsi <code>login-page</code> di bawah.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="setup-step mb-4">
                        <div class="step-header">
                            <div class="step-num">3</div>
                            <div class="step-title">Arahkan Login Page ke URL Server Anda</div>
                        </div>
                        <div class="step-body">
                            <p class="mb-2" style="font-size:13px;color:var(--text-muted)">
                                Jika captive portal di-host di server web (bukan di flash router),
                                set <code>login-page</code> di hotspot profile:
                            </p>
                            <div class="code-block">
                                <code id="cmd-loginpage">/ip hotspot profile set [find name=default] \
  login-page=<?= e($portalUrl) ?></code>
                                <button class="btn-copy" onclick="copyText(document.getElementById('cmd-loginpage').textContent, this)" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="setup-step mb-4">
                        <div class="step-header">
                            <div class="step-num">4</div>
                            <div class="step-title">Konfigurasi Walled Garden (Akses Tanpa Login)</div>
                        </div>
                        <div class="step-body">
                            <p class="mb-2" style="font-size:13px;color:var(--text-muted)">
                                Izinkan akses ke server web Anda sebelum user login
                                (agar halaman portal bisa dimuat):
                            </p>
                            <div class="code-block">
                                <code id="cmd-walled">/ip hotspot walled-garden add dst-host=<?= e(parse_url($portalUrl, PHP_URL_HOST) ?: 'your-server.com') ?> action=allow</code>
                                <button class="btn-copy" onclick="copyText(document.getElementById('cmd-walled').textContent, this)" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="setup-step">
                        <div class="step-header">
                            <div class="step-num">5</div>
                            <div class="step-title">Test Alur Login</div>
                        </div>
                        <div class="step-body">
                            <p class="mb-2" style="font-size:13px;color:var(--text-muted)">Langkah verifikasi:</p>
                            <ol style="font-size:13px;color:var(--text-muted);padding-left:18px;line-height:2">
                                <li>Hubungkan perangkat ke SSID hotspot MikroTik</li>
                                <li>Buka browser → coba akses situs mana saja (misal <code>http://example.com</code>)</li>
                                <li>MikroTik seharusnya redirect ke halaman login VoucherNet</li>
                                <li>Masukkan username & password dari voucher yang sudah digenerate</li>
                                <li>Setelah login berhasil → halaman <code>success.php</code> tampil</li>
                            </ol>
                            <div class="alert-tip mt-2">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>Tips:</strong> Gunakan HTTP (bukan HTTPS) saat test pertama kali
                                karena MikroTik redirect HTTP request. HTTPS tidak bisa di-redirect oleh hotspot.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.setup-step { }
.step-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}
.step-num {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #6c63ff, #5a52d5);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.step-title { font-weight: 600; font-size: 14px; }
.step-body { padding-left: 40px; }

.code-block {
    background: #0f0f1a;
    border: 1px solid rgba(108,99,255,.2);
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 12px;
    color: #a6e3a1;
    position: relative;
    overflow-x: auto;
    white-space: pre;
}
.btn-copy {
    position: absolute;
    top: 8px; right: 8px;
    background: rgba(108,99,255,.2);
    border: 1px solid rgba(108,99,255,.3);
    border-radius: 6px;
    color: #a78bfa;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: background .2s;
}
.btn-copy:hover { background: rgba(108,99,255,.4); }
.btn-copy.copied { color: #4ade80; border-color: rgba(74,222,128,.4); background: rgba(74,222,128,.1); }

.alert-tip {
    background: rgba(251,191,36,.08);
    border: 1px solid rgba(251,191,36,.2);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    color: #fbbf24;
}
</style>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text.trim()).then(() => {
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 2000);
    });
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
