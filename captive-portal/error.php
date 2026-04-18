<?php
/**
 * captive-portal/error.php
 * Halaman error captive portal — ditampilkan saat login gagal atau timeout
 */

$errorMsg  = htmlspecialchars($_GET['error']       ?? '', ENT_QUOTES, 'UTF-8');
$linkLogin = htmlspecialchars($_GET['link-login']  ?? 'index.php', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gagal Terhubung — VoucherNet WiFi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg:#0f0f1a; --card:#1a1a2e; --danger:#f87171; --text:#e2e8f0; --muted:#94a3b8; --primary:#6c63ff; }
        html, body { height:100%; font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); }
        .wrapper { min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px 16px; }
        .card { background:var(--card); border:1px solid rgba(248,113,113,.25); border-radius:24px; width:100%; max-width:380px; padding:40px 32px; text-align:center; box-shadow:0 32px 80px rgba(0,0,0,.5); animation:pop .4s ease both; }
        @keyframes pop { from{opacity:0;transform:scale(.9)} to{opacity:1;transform:scale(1)} }
        .err-icon { width:72px; height:72px; background:rgba(248,113,113,.15); border:2px solid rgba(248,113,113,.4); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px; }
        h1 { font-family:'Syne',sans-serif; font-size:24px; color:var(--danger); margin-bottom:10px; }
        .msg { color:var(--muted); font-size:14px; line-height:1.6; margin-bottom:28px; }
        .err-detail { background:rgba(248,113,113,.08); border:1px solid rgba(248,113,113,.2); border-radius:10px; padding:12px 14px; font-size:13px; color:var(--danger); margin-bottom:24px; text-align:left; }
        .btn { display:flex; align-items:center; justify-content:center; gap:8px; padding:13px; background:linear-gradient(135deg,var(--primary),#5a52d5); color:#fff; border:none; border-radius:10px; font-family:inherit; font-size:15px; font-weight:600; cursor:pointer; text-decoration:none; box-shadow:0 4px 16px rgba(108,99,255,.4); transition:transform .15s; }
        .btn:hover { transform:translateY(-1px); }
        .footer { margin-top:20px; font-size:11px; color:rgba(148,163,184,.35); }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="err-icon">❌</div>
        <h1>Gagal Terhubung</h1>
        <p class="msg">Tidak dapat menyambungkan ke jaringan WiFi.<br>Silakan coba kembali.</p>
        <?php if ($errorMsg): ?>
        <div class="err-detail">⚠️ <?= $errorMsg ?></div>
        <?php endif; ?>
        <a href="<?= $linkLogin ?>" class="btn">🔄 Coba Lagi</a>
    </div>
    <div class="footer">Powered by VoucherNet</div>
</div>
</body>
</html>
