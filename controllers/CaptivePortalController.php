<?php
/**
 * controllers/CaptivePortalController.php
 * Manajemen konfigurasi Captive Portal
 * Menampilkan panduan setup dan preview captive portal
 */

class CaptivePortalController {

    public function index(): void {
        requireLogin();

        $pageTitle        = 'Captive Portal';
        $currentPage      = 'captive-portal';
        $breadcrumbParent = 'Sistem';
        $breadcrumb       = 'Captive Portal';

        // Cek apakah folder captive-portal ada
        $portalPath   = __DIR__ . '/../captive-portal/';
        $portalExists = is_dir($portalPath);

        // Baca konfigurasi MikroTik
        $mtHost = defined('MT_HOST') ? MT_HOST : '192.168.1.1';

        // URL captive portal (asumsi webserver di IP yang sama dengan admin panel)
        $serverHost  = $_SERVER['HTTP_HOST'] ?? 'your-server.com';
        $serverProto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $basePath    = dirname($_SERVER['SCRIPT_NAME'] ?? '/wifi-voucher/index.php');
        $portalUrl   = "$serverProto://$serverHost$basePath/captive-portal/index.php";

        require __DIR__ . '/../views/captive-portal/index.php';
    }
}
