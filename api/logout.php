<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
if (api_method() !== 'POST') {
    api_error('Gunakan metode POST.', 405);
}
api_guard_write();
do_logout();
json_out(array('status' => 'ok', 'message' => 'Logout berhasil.'));
