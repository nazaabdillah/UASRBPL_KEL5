<?php
/**
 * captive-portal/success.php
 * Halaman setelah user berhasil login ke hotspot MikroTik
 *
 * MikroTik dapat dikonfigurasi untuk redirect ke halaman ini setelah login berhasil.
 * Di MikroTik: /ip hotspot profile set [profile] login-by=http-chap
 *   kemudian set html-directory ke direktori ini, atau set login-page URL ke file ini.
 *
 * Variabel dari MikroTik (via GET):
 *   ?username=...     — username yang berhasil login
 *   ?link-status=...  — URL untuk cek status (refresh / logout)
 *   ?link-logout=...  — URL untuk logout dari hotspot
 */

$username   = htmlspecialchars($_GET['username']    ?? '', ENT_QUOTES, 'UTF-8');
$linkStatus = htmlspecialchars($_GET['link-status'] ?? '', ENT_QUOTES, 'UTF-8');
$linkLogout = htmlspecialchars($_GET['link-logout'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terhubung! — VoucherNet WiFi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #6c63ff;
            --accent:  #00d4aa;
            --bg:      #0f0f1a;
            --card:    #1a1a2e;
            --border:  rgba(108,99,255,.25);
            --text:    #e2e8f0;
            --muted:   #94a3b8;
            --success: #4ade80;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(74,222,128,.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 80%, rgba(0,212,170,.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 32px 80px rgba(0,0,0,.5);
            animation: pop .5s cubic-bezier(.34,1.56,.64,1) both;
        }

        @keyframes pop {
            from { opacity:0; transform: scale(.9); }
            to   { opacity:1; transform: scale(1); }
        }

        .success-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--success), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            box-shadow: 0 8px 32px rgba(74,222,128,.35);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 8px 32px rgba(74,222,128,.35); }
            50%       { box-shadow: 0 8px 48px rgba(74,222,128,.6); }
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--success);
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .username-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(108,99,255,.15);
            border: 1px solid rgba(108,99,255,.3);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            color: #a78bfa;
            margin-bottom: 28px;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: transform .15s, box-shadow .15s;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #5a52d5);
            color: #fff;
            box-shadow: 0 4px 16px rgba(108,99,255,.4);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(248,113,113,.3);
            color: #f87171;
        }

        .btn-outline:hover { background: rgba(248,113,113,.08); }

        .tip {
            margin-top: 24px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
        }

        .footer {
            margin-top: 24px;
            font-size: 11px;
            color: rgba(148,163,184,.35);
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <div class="success-icon">✅</div>

        <h1>Terhubung!</h1>
        <p class="subtitle">
            Anda berhasil terhubung ke jaringan WiFi.<br>
            Selamat menikmati internet!
        </p>

        <?php if ($username): ?>
        <div class="username-badge">
            🎫 <?= $username ?>
        </div>
        <?php endif; ?>

        <div class="actions">
            <a href="http://google.com" class="btn btn-primary" target="_blank">
                🌐 Mulai Browsing
            </a>

            <?php if ($linkLogout): ?>
            <a href="<?= $linkLogout ?>" class="btn btn-outline"
               onclick="return confirm('Yakin ingin logout dari WiFi?')">
                🚪 Logout dari WiFi
            </a>
            <?php endif; ?>
        </div>

        <p class="tip">
            Jangan tutup tab ini agar koneksi tetap aktif.<br>
            Waktu sesi sesuai dengan paket voucher Anda.
        </p>
    </div>

    <div class="footer">
        Powered by VoucherNet &mdash; WiFi Voucher Management System
    </div>
</div>

<?php if ($linkStatus): ?>
<script>
// Auto-refresh status setiap 5 menit agar koneksi tidak terputus karena idle
setInterval(function() {
    const img = new Image();
    img.src = '<?= $linkStatus ?>' + '&t=' + Date.now();
}, 5 * 60 * 1000);
</script>
<?php endif; ?>

</body>
</html>
