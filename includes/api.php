<?php
/** Pembantu REST API (JSON). Autentikasi memakai sesi (cookie) hasil api/login.php. */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_out($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_error($msg, $code = 400, $extra = array())
{
    json_out(array_merge(array('status' => 'error', 'message' => $msg), $extra), $code);
}

/** Metode HTTP efektif (mendukung override untuk hosting yang memblokir PUT/DELETE). */
function api_method()
{
    $m = strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    if ($m === 'POST') {
        $o = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : (isset($_GET['_method']) ? $_GET['_method'] : '');
        $o = strtoupper($o);
        if (in_array($o, array('PUT', 'PATCH', 'DELETE'), true)) {
            return $o;
        }
    }
    return $m;
}

/** Baca body JSON (atau form). */
function api_input()
{
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $j = json_decode($raw, true);
        if (is_array($j)) {
            return $j;
        }
        if (stripos(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '', 'json') !== false) {
            api_error('Body JSON tidak valid.', 400);
        }
    }
    return $_POST;
}

/** Perlindungan CSRF untuk permintaan yang mengubah data: wajib JSON atau header X-Requested-With. */
function api_guard_write()
{
    $ct = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
    $xr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
    if (strpos($ct, 'application/json') === false && $xr === '') {
        api_error('Gunakan Content-Type: application/json atau header X-Requested-With.', 415);
    }
}

function api_require($perm)
{
    if (!logged_in()) {
        api_error('Belum login. Panggil POST api/login.php terlebih dahulu.', 401);
    }
    if ($perm !== null && !can($perm)) {
        api_error('Anda tidak memiliki hak akses (role: ' . current_user()['role'] . ').', 403);
    }
}
