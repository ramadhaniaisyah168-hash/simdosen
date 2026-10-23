<?php
/**
 * KONFIGURASI SIM DOSEN
 * Aplikasi otomatis mengenali lingkungan:
 *  - dibuka lewat localhost / 127.0.0.1  -> memakai pengaturan LOKAL (XAMPP)
 *  - dibuka lewat domain hosting         -> memakai pengaturan HOSTING (InfinityFree)
 */
date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'SIM Dosen');
define('APP_INSTITUSI', 'Universitas Muhammadiyah Bengkulu');

$__host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'localhost';
$__host = preg_replace('/:\d+$/', '', $__host);
define('IS_LOCAL', PHP_SAPI === 'cli' || in_array($__host, array('localhost', '127.0.0.1', '::1', '[::1]'), true));

if (IS_LOCAL) {
    // ===== LOKAL (XAMPP) =====
    define('DB_HOST', '127.0.0.1');
    define('DB_PORTS', '3307,3306');   // port dicoba berurutan (XAMPP Anda memakai 3307)
    define('DB_NAME', 'sim_dosen');    // nama basisdata lokal (sesuai phpMyAdmin Anda)
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // ===== HOSTING (InfinityFree) =====
    // Salin 3 nilai ini dari panel InfinityFree > MySQL Databases
    // (sama dengan yang dipakai di proyek SIM Mahasiswa).
    define('DB_HOST', 'sql309.infinityfree.com');   // <-- GANTI: MySQL Hostname
    define('DB_PORTS', '3306');
    define('DB_NAME', 'if0_43191408_sim_dosen');    // nama basisdata di hosting
    define('DB_USER', 'if0_43191408');              // <-- MySQL Username
    define('DB_PASS', 'zBoRM5kYV4yvDDu');      // <-- GANTI: password akun hosting / vPanel
}
