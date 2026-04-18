<?php
/**
 * captive-portal/index.php
 * Halaman Captive Portal untuk user WiFi
 * 
 * Cara kerja:
 * 1. MikroTik redirect user ke halaman ini saat belum login
 * 2. User memasukkan kode voucher (username + password)
 * 3. Script ini mem-forward login ke MikroTik hotspot login URL
 * 
 * Variabel dari MikroTik (via GET):
 *   ?mac=xx:xx:xx:xx:xx:xx  — MAC address perangkat
 *   ?ip=192.168.x.x          — IP address perangkat
 *   ?username=               — username (jika sudah pernah login)
 *   ?link-login=...          — URL login MikroTik
 *   ?link-orig=...           — URL tujuan asal user
 *   ?error=...               — pesan error dari MikroTik
 *   ?chap-id=...             — CHAP id (untuk autentikasi CHAP)
 *   ?chap-challenge=...      — CHAP challenge
 */

ini_set('expose_php', 'off');
ini_set('display_errors', '0');
error_reporting(0);

// ── Ambil variabel dari MikroTik ──────────────────────────
$mac         = htmlspecialchars($_GET['mac']            ?? '', ENT_QUOTES, 'UTF-8');
$ip          = htmlspecialchars($_GET['ip']             ?? '', ENT_QUOTES, 'UTF-8');
$username    = htmlspecialchars($_GET['username']       ?? '', ENT_QUOTES, 'UTF-8');
$linkLogin   = $_GET['link-login']   ?? '';
$linkOrig    = $_GET['link-orig']    ?? 'http://google.com';
$errorMsg    = $_GET['error']        ?? '';
$chapId      = $_GET['chap-id']      ?? '';
$chapChall   = $_GET['chap-challenge'] ?? '';

// Mapping error MikroTik ke pesan Indonesia yang ramah
$errorMap = [
    'invalid username or password' => 'Username atau password salah. Periksa kembali kode voucher Anda.',
    'user session limit reached'   => 'Voucher ini sudah digunakan di perangkat lain.',
    'user time limit exceeded'     => 'Waktu akses voucher Anda telah habis.',
    'user data limit exceeded'     => 'Kuota data voucher Anda telah habis.',
    'cannot connect to radius'     => 'Sistem sedang bermasalah. Hubungi petugas.',
];

$friendlyError = '';
if ($errorMsg) {
    $lc = strtolower($errorMsg);
    foreach ($errorMap as $key => $msg) {
        if (str_contains($lc, $key)) {
            $friendlyError = $msg;
            break;
        }
    }
    if (!$friendlyError) {
        $friendlyError = 'Login gagal: ' . htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8');
    }
}

// Jika link-login tidak ada (akses langsung bukan dari MikroTik),
// gunakan action dummy agar form tetap bisa ditampilkan di preview
$loginAction = $linkLogin ?: '#';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Login — VoucherNet</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:   #6c63ff;
            --primary-d: #5a52d5;
            --accent:    #00d4aa;
            --bg:        #0f0f1a;
            --card:      #1a1a2e;
            --border:    rgba(108,99,255,.25);
            --text:      #e2e8f0;
            --muted:     #94a3b8;
            --danger:    #f87171;
            --success:   #4ade80;
            --radius:    16px;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ── Background animated ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(108,99,255,.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 80%, rgba(0,212,170,.12) 0%, transparent 60%);
            pointer-events: none;
        }

        /* ── Layout ── */
        .portal-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
        }

        /* ── Card ── */
        .portal-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 420px;
            padding: 40px 36px;
            box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(108,99,255,.1) inset;
            backdrop-filter: blur(20px);
            animation: slideUp .5s ease both;
        }

        @keyframes slideUp {
            from { opacity:0; transform: translateY(24px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ── Brand ── */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 30px;
            box-shadow: 0 8px 24px rgba(108,99,255,.4);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 8px 24px rgba(108,99,255,.4); }
            50%       { box-shadow: 0 8px 40px rgba(108,99,255,.7); }
        }

        .brand h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -.5px;
            background: linear-gradient(135deg, #fff 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /* ── Alert ── */
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }

        .alert-danger {
            background: rgba(248,113,113,.12);
            border: 1px solid rgba(248,113,113,.3);
            color: var(--danger);
        }

        .alert-success {
            background: rgba(74,222,128,.12);
            border: 1px solid rgba(74,222,128,.3);
            color: var(--success);
        }

        .alert-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        /* ── Form ── */
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 16px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 16px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            padding: 11px 14px 11px 40px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control::placeholder { color: var(--muted); }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108,99,255,.2);
        }

        /* toggle password */
        .btn-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            font-size: 15px;
            line-height: 1;
        }

        .btn-eye:hover { color: var(--text); }

        /* ── Submit Button ── */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary), var(--primary-d));
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            box-shadow: 0 4px 20px rgba(108,99,255,.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(108,99,255,.5); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: .7; cursor: not-allowed; transform: none; }

        /* spinner inside button */
        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Info bar ── */
        .info-bar {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .info-chip {
            flex: 1;
            min-width: 100px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 11px;
        }

        .info-chip .chip-label {
            color: var(--muted);
            margin-bottom: 2px;
        }

        .info-chip .chip-val {
            font-weight: 600;
            font-size: 12px;
            color: var(--text);
            word-break: break-all;
        }

        /* ── Help text ── */
        .help-text {
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            margin-top: 20px;
            line-height: 1.6;
        }

        .help-text strong { color: var(--accent); }

        /* ── Footer ── */
        .portal-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: rgba(148,163,184,.4);
        }
    </style>
</head>
<body>

<div class="portal-wrapper">

    <div class="portal-card">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">📶</div>
            <h1>VoucherNet WiFi</h1>
            <p>Masukkan kode voucher untuk mengakses internet</p>
        </div>

        <!-- Error Alert -->
        <?php if ($friendlyError): ?>
        <div class="alert alert-danger" role="alert">
            <span class="alert-icon">⚠️</span>
            <span><?= $friendlyError ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form — dikirim ke MikroTik hotspot login URL -->
        <form method="POST" action="<?= htmlspecialchars($loginAction, ENT_QUOTES, 'UTF-8') ?>" id="loginForm">

            <!-- Field tersembunyi yang dibutuhkan MikroTik -->
            <input type="hidden" name="dst"     value="<?= htmlspecialchars($linkOrig, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="popup"   value="true">
            <?php if ($chapId && $chapChall): ?>
            <input type="hidden" name="chap-id"        value="<?= htmlspecialchars($chapId,   ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="chap-challenge" value="<?= htmlspecialchars($chapChall, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <!-- Username (Kode Voucher) -->
            <div>
                <label class="form-label" for="username">Kode Voucher (Username)</label>
                <div class="input-wrap">
                    <span class="input-icon">🎫</span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Contoh: WFAB1234"
                        value="<?= $username ?>"
                        required
                        autofocus
                        autocomplete="username"
                        spellcheck="false"
                        autocapitalize="characters"
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Password dari voucher"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="btn-eye" id="togglePass" title="Tampilkan password">
                        👁
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText">🚀 Sambungkan</span>
            </button>

        </form>

        <!-- Device Info -->
        <?php if ($ip || $mac): ?>
        <div class="info-bar">
            <?php if ($ip): ?>
            <div class="info-chip">
                <div class="chip-label">IP Anda</div>
                <div class="chip-val"><?= $ip ?></div>
            </div>
            <?php endif; ?>
            <?php if ($mac): ?>
            <div class="info-chip">
                <div class="chip-label">MAC Address</div>
                <div class="chip-val"><?= $mac ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Help -->
        <p class="help-text">
            Voucher tersedia di kasir atau petugas.<br>
            Butuh bantuan? Hubungi <strong>petugas setempat</strong>.
        </p>

    </div>

    <div class="portal-footer">
        Powered by VoucherNet &mdash; WiFi Voucher Management System
    </div>

</div>

<script>
// Toggle tampilkan/sembunyikan password
document.getElementById('togglePass').addEventListener('click', function() {
    const pw = document.getElementById('password');
    const showing = pw.type === 'text';
    pw.type = showing ? 'password' : 'text';
    this.textContent = showing ? '👁' : '🙈';
    this.title = showing ? 'Tampilkan password' : 'Sembunyikan password';
});

// Auto-uppercase username
document.getElementById('username').addEventListener('input', function() {
    const pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});

// Loading state saat submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn    = document.getElementById('submitBtn');
    const spin   = document.getElementById('spinner');
    const text   = document.getElementById('btnText');
    btn.disabled = true;
    spin.style.display = 'block';
    text.textContent   = 'Menghubungkan...';
});
</script>

</body>
</html>
