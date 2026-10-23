<?php
/** Koneksi PDO (singleton). Mencoba beberapa port bila perlu. */
function db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $opts = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );
    $last = '';
    foreach (explode(',', DB_PORTS) as $port) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . trim($port) . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
            return $pdo;
        } catch (PDOException $e) {
            $last = $e->getMessage();
        }
    }
    db_fail($last);
}

function db_fail($msg)
{
    $json = (strpos(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '', '/api/') !== false);
    http_response_code(500);
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => 'error', 'message' => 'Koneksi basisdata gagal.'));
        exit;
    }
    echo '<!doctype html><meta charset="utf-8"><title>Koneksi gagal</title>';
    echo '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:60px auto;padding:0 16px;color:#1f2937">';
    echo '<h2>Koneksi basisdata gagal</h2>';
    echo '<p>Periksa pengaturan di <code>includes/config.php</code> (host, nama basisdata, user, password), ';
    echo 'pastikan MySQL menyala, dan berkas <code>database/sim_dosen.sql</code> sudah diimpor.</p>';
    echo '<p>Buka <a href="cek.php">cek.php</a> untuk diagnosis lengkap.</p>';
    if (IS_LOCAL) {
        echo '<pre style="background:#fef2f2;padding:12px;border-radius:8px;white-space:pre-wrap">' . htmlspecialchars($msg) . '</pre>';
    }
    exit;
}
