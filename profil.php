<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('profil.self');
require __DIR__ . '/includes/dosen_lib.php';

$u = current_user();
$errors = array();
if (is_post()) {
    csrf_check();
    $lama = isset($_POST['lama']) ? $_POST['lama'] : '';
    $baru = isset($_POST['baru']) ? $_POST['baru'] : '';
    $konf = isset($_POST['konfirmasi']) ? $_POST['konfirmasi'] : '';
    $st = db()->prepare('SELECT password FROM pengguna WHERE id_pengguna = ?');
    $st->execute(array($u['id']));
    $hash = $st->fetchColumn();
    if (!$hash || !password_verify($lama, $hash)) {
        $errors['lama'] = 'Password lama salah.';
    }
    if (strlen($baru) < 8) {
        $errors['baru'] = 'Password baru minimal 8 karakter.';
    }
    if ($baru !== $konf) {
        $errors['konfirmasi'] = 'Konfirmasi tidak sama.';
    }
    if (!$errors) {
        db()->prepare('UPDATE pengguna SET password = ? WHERE id_pengguna = ?')->execute(array(password_hash($baru, PASSWORD_DEFAULT), $u['id']));
        flash('success', 'Password berhasil diganti.');
        redirect('profil.php');
    }
}
$st = db()->prepare('SELECT * FROM pengguna WHERE id_pengguna = ?');
$st->execute(array($u['id']));
$akun = $st->fetch();
$d = $u['id_dosen'] ? dosen_find($u['id_dosen']) : null;
$page_title = 'Profil Saya';
$active = 'profil';
function pe($errors, $k)
{
    return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
}
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Akun Saya</div><h1>Profil Saya</h1></div></div>
<div class="grid g2">
  <div class="card"><div class="card-head"><h3>Informasi Akun</h3></div><div class="card-body"><dl class="dl">
    <dt>Username</dt><dd><?= e($akun['username']) ?></dd><dt>Nama</dt><dd><?= e($akun['nama_lengkap']) ?></dd>
    <dt>Email</dt><dd><?= dash($akun['email']) ?></dd><dt>Role</dt><dd><?= e($u['role_nama']) ?></dd>
    <dt>Login terakhir</dt><dd><?= tgl($akun['last_login']) ?></dd></dl></div></div>
  <div class="card"><div class="card-head"><h3>Ganti Password</h3></div><div class="card-body">
    <form method="post" autocomplete="off"><?= csrf_field() ?>
      <div class="field" style="margin-bottom:12px"><label>Password lama</label><input type="password" name="lama" required><?= pe($errors, 'lama') ?></div>
      <div class="field" style="margin-bottom:12px"><label>Password baru</label><input type="password" name="baru" required autocomplete="new-password"><?= pe($errors, 'baru') ?></div>
      <div class="field" style="margin-bottom:12px"><label>Ulangi password baru</label><input type="password" name="konfirmasi" required autocomplete="new-password"><?= pe($errors, 'konfirmasi') ?></div>
      <button class="btn btn-primary" type="submit">Simpan Password</button></form></div></div>
</div>
<?php if ($d): ?>
<div class="card"><div class="card-head"><h3>Data Dosen Saya</h3><a class="btn btn-sm" href="dosen_detail.php?id=<?= (int) $d['id_dosen'] ?>">Lihat lengkap</a></div><div class="card-body"><dl class="dl">
  <dt>Nama</dt><dd><?= e($d['nama']) ?></dd><dt>NIP / NUPTK</dt><dd><?= dash($d['nip']) ?> / <?= dash($d['nuptk']) ?></dd>
  <dt>Jabatan / Pangkat</dt><dd><?= dash($d['nama_jabatan']) ?> / <?= dash($d['nama_pangkat']) ?></dd>
  <dt>Pendidikan</dt><dd><?= dash($d['nama_pendidikan']) ?></dd><dt>Penempatan</dt><dd><?= dash($d['nama_penempatan']) ?></dd>
  <dt>Status Aktivitas</dt><dd><?= badge_aktivitas($d['status_aktivitas']) ?></dd></dl></div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
