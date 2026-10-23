<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('laporan.view');
require __DIR__ . '/includes/dosen_lib.php';

list($rows) = dosen_search(dosen_filters_from_get(), 1, 0);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="rekap_dosen_' . date('Ymd_His') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
fputcsv($out, array('No', 'Nomor Registrasi', 'NUPTK', 'Nama', 'NIP', 'Jabatan Fungsional', 'Kepangkatan', 'Pendidikan Terakhir',
    'Rumpun Ilmu', 'Status Serdos', 'Status Pegawai', 'Ikatan Kerja', 'Status Aktivitas', 'Penempatan'), ';');
$no = 0;
foreach ($rows as $d) {
    $no++;
    // format ="angka" agar NIP/NUPTK tetap utuh (tidak jadi notasi ilmiah) di Excel
    fputcsv($out, array($no, $d['nomor_registrasi'], $d['nuptk'] ? '="' . $d['nuptk'] . '"' : '', $d['nama'], $d['nip'] ? '="' . $d['nip'] . '"' : '',
        $d['nama_jabatan'], $d['nama_pangkat'], $d['nama_pendidikan'], $d['nama_rumpun'], $d['status_serdos'],
        $d['nama_status'], $d['ikatan_kerja'], $d['status_aktivitas'], $d['nama_penempatan']), ';');
}
fclose($out);
