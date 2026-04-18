<?php
/**
 * captive-portal/aloha.php
 * Halaman status koneksi hotspot (aloha page)
 * MikroTik membuka halaman ini saat user sudah login untuk cek status
 *
 * Variabel GET dari MikroTik:
 *   ?username=...        — username aktif
 *   ?uptime=...          — durasi koneksi (format: HH:MM:SS)
 *   ?bytes-in=...        — total bytes diterima
 *   ?bytes-out=...       — total bytes dikirim
 *   ?session-time-left=  — sisa waktu sesi
 *   ?link-logout=...     — URL untuk logout
 */

function formatBytes(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
    return $bytes . ' B';
}

$username    = htmlspecialchars($_GET['username']          ?? '-',  ENT_QUOTES, 'UTF-8');
$uptime      = htmlspecialchars($_GET['uptime']            ?? '-',  ENT_QUOTES, 'UTF-8');
$bytesIn     = formatBytes((int)($_GET['bytes-in']         ?? 0));
$bytesOut    = formatBytes((int)($_GET['bytes-out']        ?? 0));
$timeLeft    = htmlspecialchars($_GET['session-time-left'] ?? '-',  ENT_QUOTES, 'UTF-8');
$linkLogout  = htmlspecialchars($_GET['link-logout']       ?? '',   ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Koneksi — VoucherNet WiFi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        :root { --bg:#0f0f1a; --card:#1a1a2e; --border:rgba(108,99,255,.25); --text:#e2e8f0; --muted:#94a3b8; --primary:#6c63ff; --accent:#00d4aa; --success:#4ade80; --danger:#f87171; }
        html,body { height:100%; font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 70% 60% at 30% 30%,rgba(108,99,255,.15) 0%,transparent 60%); pointer-events:none; }
        .wrapper { min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px 16px; }
        .card { background:var(--card); border:1px solid var(--border); border-radius:24px; width:100%; max-width:400px; padding:36px 28px; box-shadow:0 32px 80px rgba(0,0,0,.5); animation:up .4s ease both; }
        @keyframes up { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .status-dot { display:inline-block; width:10px; height:10px; background:var(--success); border-radius:50%; box-shadow:0 0 0 0 rgba(74,222,128,.5); animation:ripple 1.5s infinite; margin-right:8px; }
        @keyframes ripple { 0%{box-shadow:0 0 0 0 rgba(74,222,128,.5)} 100%{box-shadow:0 0 0 10px transparent} }
        .header { display:flex; align-items:center; margin-bottom:24px; }
        .header-text h2 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; }
        .header-text p { font-size:12px; color:var(--muted); margin-top:2px; }
        .user-badge { background:rgba(108,99,255,.15); border:1px solid rgba(108,99,255,.3); border-radius:8px; padding:6px 12px; font-size:13px; font-weight:600; color:#a78bfa; margin-bottom:20px; display:inline-block; }
        .stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px; }
        .stat-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:14px; }
        .stat-label { font-size:11px; color:var(--muted); margin-bottom:4px; }
        .stat-val { font-size:18px; font-weight:700; color:var(--text); }
        .stat-val.accent { color:var(--accent); }
        .stat-val.warning { color:#fbbf24; }
        .btn-logout { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; background:transparent; border:1px solid rgba(248,113,113,.3); border-radius:10px; color:var(--danger); font-family:inherit; font-size:14px; font-weight:600; cursor:pointer; margin-top:4px; text-decoration:none; transition:background .2s; }
        .btn-logout:hover { background:rgba(248,113,113,.08); }
        .footer { margin-top:20px; font-size:11px; color:rgba(148,163,184,.35); text-align:center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div>
                <div class="header-text">
                    <h2><span class="status-dot"></span>Terhubung</h2>
                    <p>Koneksi WiFi Anda aktif</p>
                </div>
            </div>
        </div>

        <div class="user-badge">🎫 <?= $username ?></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">⏱ Durasi</div>
                <div class="stat-val"><?= $uptime ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">⏳ Sisa Waktu</div>
                <div class="stat-val warning"><?= $timeLeft ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">⬇️ Download</div>
                <div class="stat-val accent"><?= $bytesIn ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">⬆️ Upload</div>
                <div class="stat-val accent"><?= $bytesOut ?></div>
            </div>
        </div>

        <?php if ($linkLogout): ?>
        <a href="<?= $linkLogout ?>" class="btn-logout"
           onclick="return confirm('Yakin ingin logout dari WiFi?')">
            🚪 Logout dari WiFi
        </a>
        <?php endif; ?>
    </div>
    <div class="footer">Powered by VoucherNet</div>
</div>
</body>
</html>
