<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/dosen_lib.php';
require_login();

$id = (int) get('id');
$edit = $id > 0;
require_perm($edit ? 'dosen.edit' : 'dosen.create');

$old = $edit ? dosen_find($id) : null;
if ($edit && !$old) {
    flash('error', 'Data dosen tidak ditemukan.');
    redirect('dosen.php');
}
$errors = array();
$f = $edit ? $old : array(
    'nomor_registrasi' => '', 'nuptk' => '', 'nama' => '', 'nip' => '', 'id_jabatan' => '', 'id_pangkat' => '',
    'id_pendidikan' => '', 'id_rumpun' => '', 'status_serdos' => 'Belum', 'id_status' => '', 'ikatan_kerja' => 'Dosen Tetap',
    'status_aktivitas' => 'Aktif', 'id_penempatan' => '',
);

if (is_post()) {
    csrf_check();
    $f = dosen_clean($_POST);
    $errors = dosen_validate($f, $edit ? $id : null);
    if (!$errors) {
        try {
            dosen_save($f, $edit ? $id : null);
            flash('success', $edit ? 'Data dosen berhasil diperbarui.' : 'Data dosen berhasil ditambahkan.');
            redirect('dosen.php');
        } catch (PDOException $e) {
            $errors['_'] = 'Gagal menyimpan data. Periksa kembali isian Anda.';
        }
    }
}

$opt = dosen_options();
$page_title = $edit ? 'Edit Dosen' : 'Tambah Dosen';
$active = 'dosen';
include __DIR__ . '/includes/header.php';

function fld_err($errors, $k)
{
    return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
}
function val($f, $k)
{
    return isset($f[$k]) && $f[$k] !== null ? $f[$k] : '';
}
?>
<div class="page-head"><div><div class="eyebrow">Data Kepegawaian</div><h1><?= e($page_title) ?></h1>
  <p class="muted">Field bertanda <span style="color:var(--red)">*</span> wajib diisi.</p></div>
  <a class="btn" href="dosen.php">&larr; Kembali</a></div>

<?php if (isset($errors['_'])): ?><div class="alert alert-error"><?= e($errors['_']) ?></div><?php endif; ?>

<form method="post" class="card"><div class="card-body">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field"><label>Nomor Registrasi <span class="req">*</span></label>
      <input type="text" name="nomor_registrasi" maxlength="30" value="<?= e(val($f, 'nomor_registrasi')) ?>" required><?= fld_err($errors, 'nomor_registrasi') ?></div>
    <div class="field"><label>Nama Lengkap (beserta gelar) <span class="req">*</span></label>
      <input type="text" name="nama" maxlength="150" value="<?= e(val($f, 'nama')) ?>" required><?= fld_err($errors, 'nama') ?></div>
    <div class="field"><label>NUPTK</label>
      <input type="text" name="nuptk" inputmode="numeric" maxlength="16" value="<?= e(val($f, 'nuptk')) ?>"><small>16 digit angka (boleh kosong).</small><?= fld_err($errors, 'nuptk') ?></div>
    <div class="field"><label>NIP</label>
      <input type="text" name="nip" inputmode="numeric" maxlength="18" value="<?= e(val($f, 'nip')) ?>"><small>18 digit angka, khusus PNS (boleh kosong).</small><?= fld_err($errors, 'nip') ?></div>
    <div class="field"><label>Jabatan Fungsional</label>
      <select name="id_jabatan"><option value="">-- Pilih --</option><?= select_options($opt['id_jabatan'], val($f, 'id_jabatan')) ?></select><?= fld_err($errors, 'id_jabatan') ?></div>
    <div class="field"><label>Kepangkatan</label>
      <select name="id_pangkat"><option value="">-- Pilih --</option><?= select_options($opt['id_pangkat'], val($f, 'id_pangkat')) ?></select><?= fld_err($errors, 'id_pangkat') ?></div>
    <div class="field"><label>Pendidikan Terakhir</label>
      <select name="id_pendidikan"><option value="">-- Pilih --</option><?= select_options($opt['id_pendidikan'], val($f, 'id_pendidikan')) ?></select><?= fld_err($errors, 'id_pendidikan') ?></div>
    <div class="field"><label>Rumpun Ilmu</label>
      <select name="id_rumpun"><option value="">-- Pilih --</option><?= select_options($opt['id_rumpun'], val($f, 'id_rumpun')) ?></select><?= fld_err($errors, 'id_rumpun') ?></div>
    <div class="field"><label>Status Serdos</label>
      <select name="status_serdos"><?= simple_options(enum_serdos(), val($f, 'status_serdos')) ?></select><?= fld_err($errors, 'status_serdos') ?></div>
    <div class="field"><label>Status Pegawai</label>
      <select name="id_status"><option value="">-- Pilih --</option><?= select_options($opt['id_status'], val($f, 'id_status')) ?></select><?= fld_err($errors, 'id_status') ?></div>
    <div class="field"><label>Ikatan Kerja</label>
      <select name="ikatan_kerja"><?= simple_options(enum_ikatan(), val($f, 'ikatan_kerja')) ?></select><?= fld_err($errors, 'ikatan_kerja') ?></div>
    <div class="field"><label>Status Aktivitas</label>
      <select name="status_aktivitas"><?= simple_options(enum_aktivitas(), val($f, 'status_aktivitas')) ?></select><?= fld_err($errors, 'status_aktivitas') ?></div>
    <div class="field"><label>Penempatan</label>
      <select name="id_penempatan"><option value="">-- Pilih --</option><?= select_options($opt['id_penempatan'], val($f, 'id_penempatan')) ?></select><?= fld_err($errors, 'id_penempatan') ?></div>
  </div>
  <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan</button><a class="btn" href="dosen.php">Batal</a></div>
</div></form>
<?php include __DIR__ . '/includes/footer.php'; ?>
