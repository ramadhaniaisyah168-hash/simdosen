<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('laporan.view');
require __DIR__ . '/includes/dosen_lib.php';

$page_title = 'Rekap Dosen';
$active = 'laporan';
$flt = dosen_filters_from_get();
list($rows, $total) = dosen_search($flt, 1, 0);
$opt = dosen_options();

// ringkasan per kelompok (dari hasil filter)
function rekap($rows, $key)
{
    $o = array();
    foreach ($rows as $r) {
        $k = ($r[$key] === null || $r[$key] === '') ? '(Belum diisi)' : $r[$key];
        $o[$k] = isset($o[$k]) ? $o[$k] + 1 : 1;
    }
    arsort($o);
    return $o;
}
$groups = array(
    'Jabatan Fungsional' => rekap($rows, 'nama_jabatan'),
    'Pendidikan Terakhir' => rekap($rows, 'nama_pendidikan'),
    'Status Aktivitas' => rekap($rows, 'status_aktivitas'),
    'Status Serdos' => rekap($rows, 'status_serdos'),
    'Penempatan' => rekap($rows, 'nama_penempatan'),
    'Status Pegawai' => rekap($rows, 'nama_status'),
);
$csv = 'laporan_csv.php?' . http_build_query(array_filter($flt, 'strlen'));
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Laporan</div><h1>Rekap Dosen</h1>
  <p class="muted">Dicetak <?= date('d-m-Y H:i') ?> &middot; <?= e(APP_INSTITUSI) ?> &middot; <?= $total ?> dosen</p></div>
  <div class="no-print"><a class="btn" href="<?= e($csv) ?>">Unduh CSV</a> <button class="btn btn-primary" type="button" onclick="window.print()">Cetak</button></div></div>

<div class="card no-print"><div class="card-body">
  <form method="get" class="filters">
    <div class="field" style="flex:2"><label>Cari</label><input type="text" name="q" value="<?= e($flt['q']) ?>"></div>
    <div class="field"><label>Jabatan</label><select name="jabatan"><option value="">Semua</option><?= select_options($opt['id_jabatan'], $flt['jabatan'], false) ?></select></div>
    <div class="field"><label>Penempatan</label><select name="penempatan"><option value="">Semua</option><?= select_options($opt['id_penempatan'], $flt['penempatan'], false) ?></select></div>
    <div class="field"><label>Aktivitas</label><select name="aktivitas"><option value="">Semua</option><?= simple_options(enum_aktivitas(), $flt['aktivitas']) ?></select></div>
    <div class="field"><label>Serdos</label><select name="serdos"><option value="">Semua</option><?= simple_options(enum_serdos(), $flt['serdos']) ?></select></div>
    <div><button class="btn btn-primary" type="submit">Terapkan</button> <a class="btn" href="laporan.php">Reset</a></div>
  </form></div></div>

<div class="grid g3">
<?php foreach ($groups as $title => $g): ?>
  <div class="card"><div class="card-head"><h3><?= e($title) ?></h3></div>
    <table class="tbl"><tbody>
    <?php if (!$g): ?><tr><td class="muted">Tidak ada data.</td></tr><?php endif; ?>
    <?php foreach ($g as $k => $n): ?><tr><td><?= e($k) ?></td><td class="right"><strong><?= (int) $n ?></strong></td></tr><?php endforeach; ?>
    </tbody></table></div>
<?php endforeach; ?>
</div>

<div class="card"><div class="card-head"><h3>Daftar Dosen</h3></div><div class="table-wrap"><table class="tbl">
  <thead><tr><th>No.</th><th>No. Reg</th><th>Nama</th><th>NIP</th><th>NUPTK</th><th>Jabatan</th><th>Pangkat</th><th>Pend.</th><th>Rumpun Ilmu</th><th>Serdos</th><th>Status Pegawai</th><th>Ikatan</th><th>Aktivitas</th><th>Penempatan</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="14" class="empty">Tidak ada data.</td></tr><?php endif; ?>
  <?php $no = 0; foreach ($rows as $d): $no++; ?>
  <tr><td><?= $no ?></td><td><?= e($d['nomor_registrasi']) ?></td><td><?= e($d['nama']) ?></td><td><?= dash($d['nip']) ?></td><td><?= dash($d['nuptk']) ?></td>
    <td><?= dash($d['nama_jabatan']) ?></td><td><?= dash($d['nama_pangkat']) ?></td><td><?= dash($d['nama_pendidikan']) ?></td><td><?= dash($d['nama_rumpun']) ?></td>
    <td><?= e($d['status_serdos']) ?></td><td><?= dash($d['nama_status']) ?></td><td><?= e($d['ikatan_kerja']) ?></td><td><?= e($d['status_aktivitas']) ?></td><td><?= dash($d['nama_penempatan']) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
