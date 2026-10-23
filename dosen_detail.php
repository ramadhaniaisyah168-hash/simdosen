<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
require __DIR__ . '/includes/dosen_lib.php';

$id = (int) get('id');
$u = current_user();
// RBAC: yang boleh melihat semua dosen = dosen.view; role dosen hanya data miliknya sendiri
if (!can('dosen.view') && !($u['id_dosen'] && $u['id_dosen'] === $id)) {
    forbidden();
}
$d = dosen_find($id);
$page_title = 'Detail Dosen';
$active = can('dosen.view') ? 'dosen' : 'profil';
include __DIR__ . '/includes/header.php';
if (!$d) {
    echo '<div class="card"><div class="card-body center"><h2>Data tidak ditemukan</h2><a class="btn" href="dosen.php">Kembali</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>
<div class="page-head"><div><div class="eyebrow">Detail Dosen</div><h1><?= e($d['nama']) ?></h1></div>
  <div class="no-print">
    <?php if (can('dosen.view')): ?><a class="btn" href="dosen.php">&larr; Kembali</a><?php endif; ?>
    <?php if (can('dosen.edit')): ?><a class="btn btn-primary" href="dosen_form.php?id=<?= $id ?>">Edit</a><?php endif; ?>
  </div></div>
<div class="card"><div class="card-body"><dl class="dl">
  <dt>Nomor Registrasi</dt><dd><?= dash($d['nomor_registrasi']) ?></dd>
  <dt>NUPTK</dt><dd><?= dash($d['nuptk']) ?></dd>
  <dt>NIP</dt><dd><?= dash($d['nip']) ?></dd>
  <dt>Jabatan Fungsional</dt><dd><?= dash($d['nama_jabatan']) ?></dd>
  <dt>Kepangkatan</dt><dd><?= dash($d['nama_pangkat']) ?></dd>
  <dt>Pendidikan Terakhir</dt><dd><?= dash($d['nama_pendidikan']) ?></dd>
  <dt>Rumpun Ilmu</dt><dd><?= dash($d['nama_rumpun']) ?></dd>
  <dt>Status Serdos</dt><dd><?= e($d['status_serdos']) ?></dd>
  <dt>Status Pegawai</dt><dd><?= dash($d['nama_status']) ?></dd>
  <dt>Ikatan Kerja</dt><dd><?= e($d['ikatan_kerja']) ?></dd>
  <dt>Status Aktivitas</dt><dd><?= badge_aktivitas($d['status_aktivitas']) ?></dd>
  <dt>Penempatan</dt><dd><?= dash($d['nama_penempatan']) ?></dd>
  <dt>Terakhir diperbarui</dt><dd><?= tgl($d['updated_at']) ?></dd>
</dl></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
