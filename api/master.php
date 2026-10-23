<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
require __DIR__ . '/../includes/master_lib.php';

$cfgs = master_list_tables();
$t = isset($_GET['t']) ? $_GET['t'] : '';
if (!isset($cfgs[$t])) {
    api_error('Parameter t tidak valid. Pilihan: ' . implode(', ', array_keys($cfgs)), 400);
}
$cfg = $cfgs[$t];
$method = api_method();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($method === 'GET') {
    api_require('master.view');
    $rows = db()->query('SELECT ' . $cfg['pk'] . ' AS id, ' . $cfg['name'] . ' AS nama, aktif FROM ' . $cfg['table'] . ' ORDER BY ' . $cfg['pk'])->fetchAll();
    json_out(array('status' => 'ok', 'tabel' => $t, 'total' => count($rows), 'data' => $rows));
}

api_require('master.manage');
api_guard_write();

if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
    $in = api_input();
    $name = isset($in['nama']) ? trim((string) $in['nama']) : '';
    $aktif = (isset($in['aktif']) && !$in['aktif']) ? 0 : 1;
    $msg = master_validate_name($cfg, $name, $id);
    if ($msg !== '') {
        api_error('Validasi gagal.', 422, array('errors' => array('nama' => $msg)));
    }
    if ($method === 'POST') {
        db()->prepare('INSERT INTO ' . $cfg['table'] . ' (' . $cfg['name'] . ', aktif) VALUES (?, ?)')->execute(array($name, $aktif));
        json_out(array('status' => 'ok', 'message' => 'Data ditambahkan.', 'id' => (int) db()->lastInsertId()), 201);
    }
    if (!$id) {
        api_error('Parameter id wajib.', 400);
    }
    $st = db()->prepare('UPDATE ' . $cfg['table'] . ' SET ' . $cfg['name'] . ' = ?, aktif = ? WHERE ' . $cfg['pk'] . ' = ?');
    $st->execute(array($name, $aktif, $id));
    json_out(array('status' => 'ok', 'message' => 'Data diperbarui.'));
}

if ($method === 'DELETE') {
    if (!$id) {
        api_error('Parameter id wajib.', 400);
    }
    try {
        $st = db()->prepare('DELETE FROM ' . $cfg['table'] . ' WHERE ' . $cfg['pk'] . ' = ?');
        $st->execute(array($id));
        if (!$st->rowCount()) {
            api_error('Data tidak ditemukan.', 404);
        }
        json_out(array('status' => 'ok', 'message' => 'Data dihapus.'));
    } catch (PDOException $e) {
        api_error('Data masih dipakai dosen, tidak dapat dihapus.', 409);
    }
}
api_error('Metode tidak didukung.', 405);
