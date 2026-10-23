<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
if (api_method() !== 'POST') {
    api_error('Gunakan metode POST.', 405);
}
api_guard_write();
$in = api_input();
$err = attempt_login(isset($in['username']) ? trim((string) $in['username']) : '', isset($in['password']) ? (string) $in['password'] : '');
if ($err !== null) {
    api_error($err, 401);
}
$u = current_user();
json_out(array('status' => 'ok', 'message' => 'Login berhasil.', 'user' => array(
    'username' => $u['username'], 'nama' => $u['nama'], 'role' => $u['role'], 'id_dosen' => $u['id_dosen'],
)));
