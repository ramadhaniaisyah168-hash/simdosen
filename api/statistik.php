<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
$s = db()->query("SELECT COUNT(*) total, SUM(status_aktivitas='Aktif') aktif, SUM(status_serdos='Sudah') sudah_serdos FROM dosen")->fetch();
$jab = db()->query('SELECT j.nama_jabatan nama, COUNT(d.id_dosen) jumlah FROM jabatan_fungsional j LEFT JOIN dosen d ON d.id_jabatan = j.id_jabatan GROUP BY j.id_jabatan, j.nama_jabatan ORDER BY j.id_jabatan')->fetchAll();
foreach ($jab as &$j) {
    $j['jumlah'] = (int) $j['jumlah'];
}
json_out(array('status' => 'ok', 'data' => array(
    'total_dosen' => (int) $s['total'], 'dosen_aktif' => (int) $s['aktif'], 'sudah_serdos' => (int) $s['sudah_serdos'], 'per_jabatan' => $jab,
)));
