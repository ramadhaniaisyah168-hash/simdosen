<?php
/** Konfigurasi CRUD generik untuk tabel master + roles. */
function master_config()
{
    $m = array();
    $def = array(
        'jabatan_fungsional' => array('Jabatan Fungsional', 'id_jabatan', 'nama_jabatan', 'id_jabatan'),
        'kepangkatan'        => array('Kepangkatan', 'id_pangkat', 'nama_pangkat', 'id_pangkat'),
        'pendidikan'         => array('Pendidikan Terakhir', 'id_pendidikan', 'nama_pendidikan', 'id_pendidikan'),
        'rumpun_ilmu'        => array('Rumpun Ilmu', 'id_rumpun', 'nama_rumpun', 'id_rumpun'),
        'status_kepegawaian' => array('Status Kepegawaian', 'id_status', 'nama_status', 'id_status'),
        'penempatan'         => array('Penempatan', 'id_penempatan', 'nama_penempatan', 'id_penempatan'),
    );
    foreach ($def as $t => $d) {
        $m[$t] = array(
            'label' => $d[0], 'table' => $t, 'pk' => $d[1], 'name' => $d[2], 'used_col' => $d[3],
            'max' => 120, 'has_aktif' => true, 'can_add' => true, 'can_delete' => true,
            'perm_view' => 'master.view', 'perm_manage' => 'master.manage',
        );
    }
    $m['roles'] = array(
        'label' => 'Role', 'table' => 'roles', 'pk' => 'id_role', 'name' => 'nama_role', 'used_col' => null,
        'max' => 60, 'has_aktif' => false, 'can_add' => false, 'can_delete' => false,
        'perm_view' => 'roles.manage', 'perm_manage' => 'roles.manage',
        'extra' => array('kode_role' => 'Kode Role', 'deskripsi' => 'Deskripsi'),
    );
    return $m;
}

function master_list_tables()
{
    $out = master_config();
    unset($out['roles']);
    return $out;
}

/** Validasi nama master (wajib, unik). Mengembalikan pesan galat atau ''. */
function master_validate_name($cfg, $name, $id = 0)
{
    if ($name === '') {
        return 'Nama wajib diisi.';
    }
    if (slen($name) > $cfg['max']) {
        return 'Maksimal ' . $cfg['max'] . ' karakter.';
    }
    $st = db()->prepare('SELECT 1 FROM ' . $cfg['table'] . ' WHERE ' . $cfg['name'] . ' = ? AND ' . $cfg['pk'] . ' <> ?');
    $st->execute(array($name, (int) $id));
    if ($st->fetch()) {
        return 'Nama sudah ada.';
    }
    return '';
}
