<?php
/** Fungsi bersama untuk data dosen (dipakai halaman web dan API). */

function enum_serdos()    { return array('Sudah', 'Belum'); }
function enum_ikatan()    { return array('Dosen Tetap', 'Dosen Tidak Tetap'); }
function enum_aktivitas() { return array('Aktif', 'Cuti', 'Tugas Belajar', 'Izin Belajar', 'Pensiun'); }

/** Definisi relasi: kunci form => [tabel, pk, kolom nama] */
function dosen_relasi()
{
    return array(
        'id_jabatan'    => array('jabatan_fungsional', 'id_jabatan', 'nama_jabatan', 'Jabatan Fungsional'),
        'id_pangkat'    => array('kepangkatan', 'id_pangkat', 'nama_pangkat', 'Kepangkatan'),
        'id_pendidikan' => array('pendidikan', 'id_pendidikan', 'nama_pendidikan', 'Pendidikan Terakhir'),
        'id_rumpun'     => array('rumpun_ilmu', 'id_rumpun', 'nama_rumpun', 'Rumpun Ilmu'),
        'id_status'     => array('status_kepegawaian', 'id_status', 'nama_status', 'Status Pegawai'),
        'id_penempatan' => array('penempatan', 'id_penempatan', 'nama_penempatan', 'Penempatan'),
    );
}

/** Daftar pilihan untuk <select>: [kolom_fk => [ [id,nama,aktif], ... ]] */
function dosen_options()
{
    $out = array();
    foreach (dosen_relasi() as $col => $def) {
        $sql = 'SELECT ' . $def[1] . ' AS id, ' . $def[2] . ' AS nama, aktif FROM ' . $def[0] . ' ORDER BY ' . $def[1];
        $out[$col] = db()->query($sql)->fetchAll();
    }
    return $out;
}

/** Ambil & rapikan input (dari $_POST atau JSON). */
function dosen_clean($in)
{
    $f = array();
    $f['nomor_registrasi'] = isset($in['nomor_registrasi']) ? trim((string) $in['nomor_registrasi']) : '';
    $f['nama']             = isset($in['nama']) ? trim((string) $in['nama']) : '';
    foreach (array('nuptk', 'nip') as $k) {
        $v = isset($in[$k]) ? preg_replace('/\s+/', '', (string) $in[$k]) : '';
        $f[$k] = $v === '' ? null : $v;
    }
    foreach (array_keys(dosen_relasi()) as $k) {
        $v = isset($in[$k]) ? trim((string) $in[$k]) : '';
        $f[$k] = ($v === '' ? null : $v);
    }
    $f['status_serdos']    = isset($in['status_serdos']) ? trim((string) $in['status_serdos']) : 'Belum';
    $f['ikatan_kerja']     = isset($in['ikatan_kerja']) ? trim((string) $in['ikatan_kerja']) : 'Dosen Tetap';
    $f['status_aktivitas'] = isset($in['status_aktivitas']) ? trim((string) $in['status_aktivitas']) : 'Aktif';
    return $f;
}

/** Validasi. Mengembalikan array galat [field => pesan] (kosong = valid). */
function dosen_validate($f, $id = null)
{
    $err = array();
    if ($f['nomor_registrasi'] === '') {
        $err['nomor_registrasi'] = 'Nomor registrasi wajib diisi.';
    } elseif (strlen($f['nomor_registrasi']) > 30) {
        $err['nomor_registrasi'] = 'Maksimal 30 karakter.';
    } else {
        $st = db()->prepare('SELECT id_dosen FROM dosen WHERE nomor_registrasi = ? AND id_dosen <> ?');
        $st->execute(array($f['nomor_registrasi'], (int) $id));
        if ($st->fetch()) {
            $err['nomor_registrasi'] = 'Nomor registrasi sudah dipakai dosen lain.';
        }
    }
    if ($f['nama'] === '') {
        $err['nama'] = 'Nama wajib diisi.';
    } elseif (strlen($f['nama']) > 150) {
        $err['nama'] = 'Maksimal 150 karakter.';
    }
    if ($f['nuptk'] !== null && !preg_match('/^\d{16}$/', $f['nuptk'])) {
        $err['nuptk'] = 'NUPTK harus 16 digit angka.';
    }
    if ($f['nip'] !== null && !preg_match('/^\d{18}$/', $f['nip'])) {
        $err['nip'] = 'NIP harus 18 digit angka.';
    }
    foreach (dosen_relasi() as $col => $def) {
        if ($f[$col] !== null) {
            if (!ctype_digit((string) $f[$col])) {
                $err[$col] = 'Pilihan tidak valid.';
                continue;
            }
            $st = db()->prepare('SELECT 1 FROM ' . $def[0] . ' WHERE ' . $def[1] . ' = ?');
            $st->execute(array($f[$col]));
            if (!$st->fetch()) {
                $err[$col] = 'Pilihan tidak ditemukan.';
            }
        }
    }
    if (!in_array($f['status_serdos'], enum_serdos(), true)) {
        $err['status_serdos'] = 'Pilihan tidak valid.';
    }
    if (!in_array($f['ikatan_kerja'], enum_ikatan(), true)) {
        $err['ikatan_kerja'] = 'Pilihan tidak valid.';
    }
    if (!in_array($f['status_aktivitas'], enum_aktivitas(), true)) {
        $err['status_aktivitas'] = 'Pilihan tidak valid.';
    }
    return $err;
}

function dosen_save($f, $id = null)
{
    $cols = array('nomor_registrasi', 'nuptk', 'nama', 'nip', 'id_jabatan', 'id_pangkat', 'id_pendidikan', 'id_rumpun',
        'status_serdos', 'id_status', 'ikatan_kerja', 'status_aktivitas', 'id_penempatan');
    $vals = array();
    foreach ($cols as $c) {
        $vals[] = $f[$c];
    }
    if ($id) {
        $set = implode(', ', array_map(function ($c) { return "$c = ?"; }, $cols));
        $vals[] = (int) $id;
        db()->prepare("UPDATE dosen SET $set WHERE id_dosen = ?")->execute($vals);
        return (int) $id;
    }
    $ph = implode(', ', array_fill(0, count($cols), '?'));
    db()->prepare('INSERT INTO dosen (' . implode(', ', $cols) . ") VALUES ($ph)")->execute($vals);
    return (int) db()->lastInsertId();
}

function dosen_select_base()
{
    return 'SELECT d.*, j.nama_jabatan, k.nama_pangkat, pd.nama_pendidikan, r.nama_rumpun, s.nama_status, pn.nama_penempatan
            FROM dosen d
            LEFT JOIN jabatan_fungsional j ON j.id_jabatan = d.id_jabatan
            LEFT JOIN kepangkatan k ON k.id_pangkat = d.id_pangkat
            LEFT JOIN pendidikan pd ON pd.id_pendidikan = d.id_pendidikan
            LEFT JOIN rumpun_ilmu r ON r.id_rumpun = d.id_rumpun
            LEFT JOIN status_kepegawaian s ON s.id_status = d.id_status
            LEFT JOIN penempatan pn ON pn.id_penempatan = d.id_penempatan';
}

function dosen_find($id)
{
    $st = db()->prepare(dosen_select_base() . ' WHERE d.id_dosen = ?');
    $st->execute(array((int) $id));
    $r = $st->fetch();
    return $r ? $r : null;
}

/** Filter: q, jabatan, penempatan, aktivitas, serdos. Mengembalikan [where, params]. */
function dosen_where($flt)
{
    $w = array();
    $p = array();
    if (!empty($flt['q'])) {
        $w[] = '(d.nama LIKE ? OR d.nip LIKE ? OR d.nuptk LIKE ? OR d.nomor_registrasi LIKE ?)';
        $like = '%' . $flt['q'] . '%';
        array_push($p, $like, $like, $like, $like);
    }
    if (!empty($flt['jabatan']) && ctype_digit((string) $flt['jabatan'])) {
        $w[] = 'd.id_jabatan = ?';
        $p[] = (int) $flt['jabatan'];
    }
    if (!empty($flt['penempatan']) && ctype_digit((string) $flt['penempatan'])) {
        $w[] = 'd.id_penempatan = ?';
        $p[] = (int) $flt['penempatan'];
    }
    if (!empty($flt['aktivitas']) && in_array($flt['aktivitas'], enum_aktivitas(), true)) {
        $w[] = 'd.status_aktivitas = ?';
        $p[] = $flt['aktivitas'];
    }
    if (!empty($flt['serdos']) && in_array($flt['serdos'], enum_serdos(), true)) {
        $w[] = 'd.status_serdos = ?';
        $p[] = $flt['serdos'];
    }
    return array($w ? ' WHERE ' . implode(' AND ', $w) : '', $p);
}

/** Cari dosen dengan paginasi. Mengembalikan [rows, total]. $per = 0 -> tanpa batas. */
function dosen_search($flt, $page = 1, $per = 10)
{
    list($where, $p) = dosen_where($flt);
    $st = db()->prepare('SELECT COUNT(*) FROM dosen d' . $where);
    $st->execute($p);
    $total = (int) $st->fetchColumn();

    $sql = dosen_select_base() . $where . ' ORDER BY d.nama ASC';
    if ($per > 0) {
        $sql .= ' LIMIT ' . (int) $per . ' OFFSET ' . (int) max(0, ($page - 1) * $per);
    }
    $st = db()->prepare($sql);
    $st->execute($p);
    return array($st->fetchAll(), $total);
}

function dosen_filters_from_get()
{
    return array(
        'q'          => get('q'),
        'jabatan'    => get('jabatan'),
        'penempatan' => get('penempatan'),
        'aktivitas'  => get('aktivitas'),
        'serdos'     => get('serdos'),
    );
}

function select_options($rows, $selected, $only_active = true)
{
    $h = '';
    foreach ($rows as $r) {
        if ($only_active && !(int) $r['aktif'] && (string) $r['id'] !== (string) $selected) {
            continue;
        }
        $h .= '<option value="' . e($r['id']) . '"' . ((string) $r['id'] === (string) $selected ? ' selected' : '') . '>' . e($r['nama']) . '</option>';
    }
    return $h;
}

function simple_options($list, $selected)
{
    $h = '';
    foreach ($list as $v) {
        $h .= '<option value="' . e($v) . '"' . ((string) $v === (string) $selected ? ' selected' : '') . '>' . e($v) . '</option>';
    }
    return $h;
}
