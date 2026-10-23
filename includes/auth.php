<?php
/** Autentikasi + RBAC */

/* Matriks hak akses: kode_role => daftar permission */
function role_perms()
{
    return array(
        'admin' => array('dosen.view', 'dosen.create', 'dosen.edit', 'dosen.delete', 'master.view', 'master.manage',
            'pengguna.manage', 'roles.manage', 'laporan.view', 'profil.self'),
        'operator' => array('dosen.view', 'dosen.create', 'dosen.edit', 'dosen.delete', 'master.view', 'master.manage',
            'laporan.view', 'profil.self'),
        'pimpinan' => array('dosen.view', 'master.view', 'laporan.view', 'profil.self'),
        'dosen' => array('profil.self'),
    );
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function logged_in()
{
    return isset($_SESSION['user']);
}

function can($perm)
{
    $u = current_user();
    if (!$u) {
        return false;
    }
    $all = role_perms();
    return isset($all[$u['role']]) && in_array($perm, $all[$u['role']], true);
}

function require_login()
{
    if (!logged_in()) {
        redirect('login.php');
    }
}

function forbidden($msg = 'Anda tidak memiliki hak akses untuk halaman ini.')
{
    http_response_code(403);
    $page_title = 'Akses Ditolak';
    $active = '';
    include __DIR__ . '/header.php';
    echo '<div class="card"><div class="card-body center"><h2>403 - Akses Ditolak</h2><p class="muted">' . e($msg) . '</p>';
    echo '<a class="btn btn-primary" href="dashboard.php">Kembali ke Dashboard</a></div></div>';
    include __DIR__ . '/footer.php';
    exit;
}

function require_perm($perm)
{
    require_login();
    if (!can($perm)) {
        forbidden();
    }
}

/** Coba login. Mengembalikan null bila sukses, atau string pesan galat. */
function attempt_login($username, $password)
{
    $now = time();
    if (isset($_SESSION['lock_until']) && $_SESSION['lock_until'] > $now) {
        return 'Terlalu banyak percobaan. Coba lagi dalam ' . ($_SESSION['lock_until'] - $now) . ' detik.';
    }
    $st = db()->prepare('SELECT p.*, r.kode_role, r.nama_role FROM pengguna p JOIN roles r ON r.id_role = p.id_role WHERE p.username = ? LIMIT 1');
    $st->execute(array($username));
    $u = $st->fetch();

    if (!$u || !password_verify($password, $u['password'])) {
        $_SESSION['fail'] = (isset($_SESSION['fail']) ? $_SESSION['fail'] : 0) + 1;
        if ($_SESSION['fail'] >= 5) {
            $_SESSION['lock_until'] = $now + 30;
            $_SESSION['fail'] = 0;
        }
        return 'Username atau password salah.';
    }
    if ((int) $u['aktif'] !== 1) {
        return 'Akun Anda dinonaktifkan. Hubungi administrator.';
    }

    session_regenerate_id(true);
    unset($_SESSION['fail'], $_SESSION['lock_until']);
    $_SESSION['user'] = array(
        'id'       => (int) $u['id_pengguna'],
        'username' => $u['username'],
        'nama'     => $u['nama_lengkap'],
        'role'     => $u['kode_role'],
        'role_nama' => $u['nama_role'],
        'id_dosen' => $u['id_dosen'] !== null ? (int) $u['id_dosen'] : null,
    );
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE pengguna SET last_login = NOW() WHERE id_pengguna = ?')->execute(array($u['id_pengguna']));
    return null;
}

function do_logout()
{
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
