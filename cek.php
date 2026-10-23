<?php
/**
 * ALAT DIAGNOSIS - buka  http://localhost/simdosen/cek.php
 * Memeriksa PHP, koneksi basisdata, tabel, dan akun demo.
 * HAPUS berkas ini setelah aplikasi berjalan di hosting.
 */
require __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_name('SIMDOSENSESS');
    session_start();
}

function row($ok, $label, $detail = '')
{
    $c = $ok ? '#166534' : '#b91c1c';
    echo '<tr><td style="color:' . $c . ';font-weight:700;width:70px">' . ($ok ? 'OK' : 'GAGAL') . '</td><td><strong>'
        . htmlspecialchars($label) . '</strong><br><span style="color:#6b7280">' . $detail . '</span></td></tr>';
}

$demo = array('admin' => 'admin123', 'operator' => 'operator123', 'pimpinan' => 'pimpinan123', 'dosen1' => 'dosen123', 'dosen2' => 'dosen123');
$pdo = null;
$msgFix = '';
$tables = array('roles', 'jabatan_fungsional', 'kepangkatan', 'pendidikan', 'rumpun_ilmu', 'status_kepegawaian', 'penempatan', 'dosen', 'pengguna');
?><!doctype html><html lang="id"><head><meta charset="utf-8"><title>Cek SIM Dosen</title>
<style>body{font-family:system-ui,sans-serif;max-width:760px;margin:30px auto;padding:0 16px;color:#1f2937}
table{width:100%;border-collapse:collapse}td{padding:9px 6px;border-bottom:1px solid #e5e7eb;vertical-align:top}
.warn{background:#fffbeb;border:1px solid #fde68a;padding:10px 12px;border-radius:8px;margin:12px 0}
button,a.b{background:#166534;color:#fff;border:0;padding:9px 14px;border-radius:8px;cursor:pointer;text-decoration:none;font-size:14px}</style></head><body>
<h2>Diagnosis SIM Dosen</h2>
<div class="warn"><strong>Penting:</strong> hapus berkas <code>cek.php</code> dari hosting setelah semuanya berjalan.</div>
<table>
<?php
row(version_compare(PHP_VERSION, '7.3.0', '>='), 'Versi PHP', PHP_VERSION . ' (minimal 7.3)');
row(extension_loaded('pdo_mysql'), 'Ekstensi pdo_mysql', 'Dibutuhkan untuk koneksi MySQL.');
row(true, 'Lingkungan terdeteksi', IS_LOCAL ? 'LOKAL (localhost)' : 'HOSTING');
echo '<tr><td></td><td><strong>Pengaturan koneksi yang dipakai</strong><br><span style="color:#6b7280">host=' . htmlspecialchars(DB_HOST)
    . ' &middot; port=' . htmlspecialchars(DB_PORTS) . ' &middot; db=' . htmlspecialchars(DB_NAME) . ' &middot; user=' . htmlspecialchars(DB_USER) . '</span></td></tr>';

if (!IS_LOCAL && (strpos(DB_HOST, 'XXX') !== false || DB_PASS === 'ISI_PASSWORD_HOSTING')) {
    row(false, 'config.php belum diisi', 'Ganti host MySQL dan password hosting di includes/config.php (bagian HOSTING).');
}

if (extension_loaded('pdo_mysql')) {
    $err = '';
    foreach (explode(',', DB_PORTS) as $port) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . trim($port) . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
            row(true, 'Koneksi basisdata', 'Tersambung lewat port ' . htmlspecialchars(trim($port)) . '.');
            break;
        } catch (PDOException $e) {
            $err = $e->getMessage();
        }
    }
    if (!$pdo) {
        $hint = 'Pastikan MySQL menyala di XAMPP, nama basisdata benar, dan user/password sesuai.';
        if (stripos($err, 'Unknown database') !== false) {
            $hint = 'Basisdata "' . htmlspecialchars(DB_NAME) . '" belum ada. Buat di phpMyAdmin lalu impor database/sim_dosen.sql.';
        } elseif (stripos($err, 'Access denied') !== false) {
            $hint = 'User/password MySQL salah. Periksa DB_USER dan DB_PASS di includes/config.php.';
        } elseif (stripos($err, 'getaddrinfo') !== false || stripos($err, 'php_network') !== false) {
            $hint = 'Host MySQL tidak ditemukan. Periksa DB_HOST di includes/config.php.';
        }
        row(false, 'Koneksi basisdata', $hint . (IS_LOCAL ? '<br>Detail: ' . htmlspecialchars($err) : ''));
    }
}

if ($pdo) {
    $missing = array();
    foreach ($tables as $t) {
        try {
            $n = $pdo->query('SELECT COUNT(*) FROM `' . $t . '`')->fetchColumn();
        } catch (PDOException $e) {
            $missing[] = $t;
        }
    }
    row(!$missing, 'Tabel basisdata', $missing ? 'Belum ada: ' . htmlspecialchars(implode(', ', $missing)) . '. Impor database/sim_dosen.sql.' : 'Semua 9 tabel tersedia.');

    if (!$missing) {
        if (IS_LOCAL && isset($_POST['fix']) && isset($_POST['csrf']) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            $up = $pdo->prepare('UPDATE pengguna SET password = ? WHERE username = ?');
            foreach ($demo as $u => $p) {
                $up->execute(array(password_hash($p, PASSWORD_DEFAULT), $u));
            }
            $msgFix = 'Password akun demo sudah dikembalikan ke nilai awal.';
        }
        $bad = array();
        $st = $pdo->prepare('SELECT password, aktif FROM pengguna WHERE username = ?');
        foreach ($demo as $u => $p) {
            $st->execute(array($u));
            $r = $st->fetch();
            if (!$r || !password_verify($p, $r['password']) || !(int) $r['aktif']) {
                $bad[] = $u;
            }
        }
        row(!$bad, 'Akun demo (admin, operator, pimpinan, dosen1, dosen2)',
            $bad ? 'Bermasalah / password sudah diubah: ' . htmlspecialchars(implode(', ', $bad)) : 'Semua dapat login dengan password bawaan.');
    }
}
?>
</table>
<?php
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
if ($msgFix) {
    echo '<div class="warn" style="background:#f0fdf4;border-color:#bbf7d0">' . htmlspecialchars($msgFix) . ' <a href="cek.php">Muat ulang</a></div>';
}
if (IS_LOCAL && $pdo): ?>
<form method="post" style="margin-top:16px"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
  <button name="fix" value="1" type="submit">Kembalikan password akun demo</button>
  <span style="color:#6b7280;font-size:13px"> (hanya tersedia di localhost)</span></form>
<?php endif; ?>
<p><a class="b" href="index.php">Ke halaman utama</a> <a class="b" href="login.php">Ke halaman login</a></p>
</body></html>
