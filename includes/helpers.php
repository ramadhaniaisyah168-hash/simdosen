<?php
function e($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}

function post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function get($key, $default = '')
{
    return isset($_GET[$key]) ? trim((string) $_GET[$key]) : $default;
}

/* ---------- flash message ---------- */
function flash($type = null, $msg = null)
{
    if ($type !== null) {
        $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
        return null;
    }
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    $t = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $t)) {
        http_response_code(419);
        flash('error', 'Sesi formulir kedaluwarsa. Silakan ulangi.');
        redirect(isset($_SERVER['HTTP_REFERER']) ? 'dashboard.php' : 'dashboard.php');
    }
}

/* ---------- format ---------- */
function tgl($s)
{
    if (!$s) {
        return '-';
    }
    return date('d-m-Y H:i', strtotime($s));
}

function badge($text, $kind = 'gray')
{
    return '<span class="badge badge-' . e($kind) . '">' . e($text) . '</span>';
}

function badge_aktivitas($s)
{
    $map = array('Aktif' => 'green', 'Cuti' => 'amber', 'Tugas Belajar' => 'blue', 'Izin Belajar' => 'blue', 'Pensiun' => 'gray');
    return badge($s, isset($map[$s]) ? $map[$s] : 'gray');
}

function dash($v)
{
    return ($v === null || $v === '') ? '-' : e($v);
}

function qs_without($keys)
{
    $q = $_GET;
    foreach ((array) $keys as $k) {
        unset($q[$k]);
    }
    return http_build_query($q);
}

/* ---------- string multibyte (aman walau ekstensi mbstring tidak aktif) ---------- */
function slen($s)
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : preg_match_all('/./us', $s, $m);
}

function ssub($s, $start, $len)
{
    if (function_exists('mb_substr')) {
        return mb_substr($s, $start, $len, 'UTF-8');
    }
    return substr($s, $start, $len);
}
