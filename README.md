# 📶 VoucherNet — Sistem Manajemen Voucher WiFi MikroTik

Aplikasi web berbasis **PHP Native** untuk mengelola voucher hotspot MikroTik.  
Terintegrasi langsung dengan **MikroTik RouterOS API** (port 8728).

---

## 🗂 Struktur Proyek

```
wifi-voucher/
├── index.php                  ← Entry point & router utama
├── .htaccess                  ← Konfigurasi Apache (keamanan)
├── database.sql               ← Schema & data awal database
│
├── config/
│   ├── database.php           ← Konfigurasi PDO (host, dbname, dll)
│   └── mikrotik.php           ← Konfigurasi MikroTik (host, user, port)
│
├── controllers/
│   ├── AuthController.php     ← Login & logout admin
│   ├── VoucherController.php  ← CRUD voucher + kirim ke MikroTik
│   └── DashboardController.php← Data statistik dashboard
│
├── models/
│   ├── AdminModel.php         ← Query tabel admins
│   └── VoucherModel.php       ← Query tabel vouchers (CRUD + stats)
│
├── views/
│   ├── layout/
│   │   ├── header.php         ← Sidebar + topbar
│   │   └── footer.php         ← Closing tags + script
│   ├── auth/
│   │   └── login.php          ← Halaman login
│   ├── dashboard/
│   │   └── index.php          ← Halaman dashboard
│   └── vouchers/
│       ├── index.php          ← Daftar voucher + filter + paginasi
│       ├── generate.php       ← Form generate voucher
│       └── print.php          ← Layout cetak voucher (A4, 3 kolom)
│
├── mikrotik/
│   └── RouterosAPI.php        ← PHP class koneksi MikroTik API
│
├── helpers/
│   └── functions.php          ← Fungsi utilitas global
│
├── assets/
│   ├── css/style.css          ← Stylesheet utama (dark theme)
│   └── js/app.js              ← JavaScript interaksi UI
│
└── logs/
    └── activity.log           ← Log aktivitas admin (auto-generated)
```

---

## ⚡ Cara Instalasi

### 1. Persiapan Server
- **XAMPP** / **Laragon** / **WAMP** dengan PHP ≥ 8.1 & MySQL/MariaDB
- Apache dengan `mod_rewrite` aktif
- PHP extension: `pdo_mysql`, `sockets`

### 2. Letakkan Project
```bash
# Salin folder ke root web server
# XAMPP  → C:/xampp/htdocs/wifi-voucher/
# Laragon → C:/laragon/www/wifi-voucher/
```

### 3. Buat Database
```bash
# Buka phpMyAdmin atau jalankan via CLI:
mysql -u root -p < database.sql
```

### 4. Konfigurasi Koneksi

**`config/database.php`** — sesuaikan nilai default:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wifi_voucher');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**`config/mikrotik.php`** — sesuaikan dengan router Anda:
```php
define('MT_HOST', '192.168.1.1');   // IP Router MikroTik
define('MT_USER', 'admin');          // Username API
define('MT_PASS', '');               // Password API
define('MT_PORT', 8728);             // Port API RouterOS
```

> **Tip Produksi:** Gunakan environment variable agar kredensial tidak hardcode:
> ```bash
> # Di .env atau konfigurasi server
> export MT_HOST=192.168.1.1
> export MT_USER=admin
> export MT_PASS=rahasia123
> ```

### 5. Aktifkan API di MikroTik
```
/ip service enable api
# Pastikan port 8728 tidak diblokir firewall
```

### 6. Akses Aplikasi
```
http://localhost/wifi-voucher/
```

**Default login:**
- Username: `admin`
- Password: `password`

> ⚠️ **Segera ganti password** setelah login pertama!

---

## 🔧 Konfigurasi Profile MikroTik

Edit `config/mikrotik.php` sesuai profile hotspot di router Anda:

```php
define('MT_PROFILES', [
    '1jam'    => '1 Jam',
    '3jam'    => '3 Jam',
    '1hari'   => '1 Hari',
    '3hari'   => '3 Hari',
    '1minggu' => '1 Minggu',
]);
```

Nama key (misal `'1jam'`) harus **sama persis** dengan nama profile di MikroTik:
```
/ip hotspot user profile print
```

---

## 📋 Fitur Aplikasi

| Fitur | Deskripsi |
|-------|-----------|
| 🔐 Login Admin | Autentikasi dengan bcrypt + proteksi CSRF |
| 📊 Dashboard | Statistik voucher + status koneksi MikroTik |
| ⚡ Generate Voucher | Single/bulk (1–50), kirim otomatis ke MikroTik |
| 📋 List Voucher | Tabel dengan filter status, profile, search & paginasi |
| 🗑 Hapus Voucher | Hapus dari DB + hapus dari MikroTik API |
| 🖨 Cetak Voucher | Layout print A4 (3 kolom per baris), siap cetak gunting |
| 🔄 Sync MikroTik | Sinkronisasi status voucher dengan data router |
| 📝 Activity Log | Log aktivitas admin ke DB & file `logs/activity.log` |

---

## 🗄 Skema Database

### Tabel `admins`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT UNSIGNED AUTO_INCREMENT | Primary key |
| username | VARCHAR(50) UNIQUE | Username login |
| password | VARCHAR(255) | Hash bcrypt |
| full_name | VARCHAR(100) | Nama lengkap |
| created_at | TIMESTAMP | Waktu dibuat |

### Tabel `vouchers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT UNSIGNED AUTO_INCREMENT | Primary key |
| username | VARCHAR(50) UNIQUE | Username voucher (format: WFxxxxxx) |
| password | VARCHAR(50) | Password voucher |
| profile | VARCHAR(50) | Nama profile MikroTik |
| status | ENUM('unused','used') | Status voucher |
| comment | VARCHAR(255) | Keterangan opsional |
| created_by | INT UNSIGNED (FK) | ID admin yang membuat |
| created_at | TIMESTAMP | Waktu dibuat |
| used_at | TIMESTAMP NULL | Waktu dipakai |

### Tabel `activity_logs`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT UNSIGNED AUTO_INCREMENT | Primary key |
| admin_id | INT UNSIGNED (FK) | ID admin |
| action | VARCHAR(100) | Nama aksi |
| description | TEXT | Detail aksi |
| ip_address | VARCHAR(45) | IP admin |
| created_at | TIMESTAMP | Waktu log |

---

## 🛡 Keamanan

- ✅ Password admin di-hash dengan **bcrypt** (PASSWORD_BCRYPT)
- ✅ **CSRF token** pada setiap form POST
- ✅ **PDO Prepared Statements** — bebas SQL Injection
- ✅ **session_regenerate_id()** saat login
- ✅ `htmlspecialchars()` / fungsi `e()` pada semua output
- ✅ Whitelist routing — tidak ada path traversal
- ✅ `.htaccess` memblokir akses ke `config/` dan `logs/`
- ✅ Session flag: `httponly`, `strict_mode`, `samesite=Strict`

---

## 🐛 Troubleshooting

**Tidak bisa konek ke MikroTik?**
1. Pastikan API service aktif: `/ip service print`
2. Cek firewall MikroTik tidak blokir port 8728 dari IP server
3. Cek `MT_HOST`, `MT_USER`, `MT_PASS` di `config/mikrotik.php`
4. Coba ping dari server ke router

**Error database?**
1. Pastikan service MySQL berjalan
2. Cek kredensial di `config/database.php`
3. Pastikan database `wifi_voucher` sudah dibuat (`database.sql`)

**Halaman 500/blank?**
1. Aktifkan `display_errors` di `index.php` baris `$devMode`
2. Cek `logs/php_error.log` atau error log Apache

---

## 🚀 Pengembangan Selanjutnya

- [ ] Halaman manajemen admin (tambah/edit admin)
- [ ] Export voucher ke PDF/Excel
- [ ] Fitur import voucher dari CSV
- [ ] Notifikasi WhatsApp/Telegram saat voucher dipakai
- [ ] Multi-router (kelola beberapa MikroTik sekaligus)
- [ ] Laporan penjualan harian/bulanan
- [ ] REST API endpoint untuk integrasi aplikasi lain

---

## 📄 Lisensi

Open source untuk penggunaan pribadi dan komersial. Modifikasi bebas sesuai kebutuhan.
