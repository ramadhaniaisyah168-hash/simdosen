<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
require __DIR__ . '/../includes/dosen_lib.php';

$method = api_method();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($method === 'GET') {
    api_require('dosen.view');
    if ($id) {
        $d = dosen_find($id);
        if (!$d) {
            api_error('Data dosen tidak ditemukan.', 404);
        }
        json_out(array('status' => 'ok', 'data' => $d));
    }
    $page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
    $per = min(100, max(1, (int) (isset($_GET['per_page']) ? $_GET['per_page'] : 10)));
    list($rows, $total) = dosen_search(dosen_filters_from_get(), $page, $per);
    json_out(array('status' => 'ok', 'total' => $total, 'page' => $page, 'per_page' => $per,
        'total_pages' => max(1, (int) ceil($total / $per)), 'data' => $rows));
}

if ($method === 'POST') {
    api_require('dosen.create');
    api_guard_write();
    $f = dosen_clean(api_input());
    $err = dosen_validate($f);
    if ($err) {
        api_error('Validasi gagal.', 422, array('errors' => $err));
    }
    $newId = dosen_save($f);
    json_out(array('status' => 'ok', 'message' => 'Dosen berhasil ditambahkan.', 'data' => dosen_find($newId)), 201);
}

if ($method === 'PUT' || $method === 'PATCH') {
    api_require('dosen.edit');
    api_guard_write();
    $old = $id ? dosen_find($id) : null;
    if (!$old) {
        api_error('Data dosen tidak ditemukan.', 404);
    }
    // PATCH/PUT: field yang tidak dikirim tetap memakai nilai lama
    $in = array_merge($old, api_input());
    $f = dosen_clean($in);
    $err = dosen_validate($f, $id);
    if ($err) {
        api_error('Validasi gagal.', 422, array('errors' => $err));
    }
    dosen_save($f, $id);
    json_out(array('status' => 'ok', 'message' => 'Dosen berhasil diperbarui.', 'data' => dosen_find($id)));
}

if ($method === 'DELETE') {
    api_require('dosen.delete');
    api_guard_write();
    if (!$id) {
        api_error('Parameter id wajib.', 400);
    }
    $st = db()->prepare('DELETE FROM dosen WHERE id_dosen = ?');
    $st->execute(array($id));
    if (!$st->rowCount()) {
        api_error('Data dosen tidak ditemukan.', 404);
    }
    json_out(array('status' => 'ok', 'message' => 'Dosen berhasil dihapus.'));
}

api_error('Metode tidak didukung.', 405);
