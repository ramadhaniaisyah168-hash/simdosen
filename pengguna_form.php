<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('pengguna.manage');

$id = (int) get('id');
$edit = $id > 0;
$row = array('username' => '', 'nama_lengkap' => '', 'email' => '', 'id_role' => '', 'id_dosen' => '', 'aktif' => 1);
if ($edit) {
    $st = db()->prepare('SELECT * FROM pengguna WHERE id_pengguna = ?');
    $st->execute(array($id));
    $row = $st->fetch();
    if (!$row) {
        flash('error', 'Pengguna tidak ditemukan.');
        redirect('pengguna.php');
    }
}
$roles = db()->query('SELECT * FROM roles ORDER BY id_role')->fetchAll();
$dosen = db()->query('SELECT id_dosen, nama, nomor_registrasi FROM dosen ORDER BY nama')->fetchAll();
$me = current_user();
$errors = array();

if (is_post()) {
    csrf_check();
    $row['username'] = post('username');
    $row['nama_lengkap'] = post('nama_lengkap');
    $row['email'] = post('email');
    $row['id_role'] = post('id_role');
    $row['id_dosen'] = post('id_dosen');
    $row['aktif'] = isset($_POST['aktif']) ? 1 : 0;
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $row['username'])) {
        $errors['username'] = 'Username 3-50 karakter: huruf, angka, titik, minus, garis bawah.';
    } else {
        $st = db()->prepare('SELECT 1 FROM pengguna WHERE username = ? AND id_pengguna <> ?');
        $st->execute(array($row['username'], $id));
        if ($st->fetch()) {
            $errors['username'] = 'Username sudah dipakai.';
        }
    }
    if ($row['nama_lengkap'] === '' || slen($row['nama_lengkap']) > 150) {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi (maks. 150 karakter).';
    }
    if ($row['email'] !== '' && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    $roleKode = null;
    foreach ($roles as $r) {
        if ((string) $r['id_role'] === (string) $row['id_role']) {
            $roleKode = $r['kode_role'];
        }
    }
    if ($roleKode === null) {
        $errors['id_role'] = 'Pilih role.';
    }
    if ($roleKode === 'dosen' && $row['id_dosen'] === '') {
        $errors['id_dosen'] = 'Role Dosen harus dihubungkan ke data dosen.';
    }
    if (!$edit && strlen($pass) < 8) {
        $errors['password'] = 'Password minimal 8 karakter.';
    }
    if ($edit && $pass !== '' && strlen($pass) < 8) {
        $errors['password'] = 'Password minimal 8 karakter.';
    }
    if ($edit && $id === $me['id']) {
        if (!$row['aktif']) {
            $errors['aktif'] = 'Anda tidak dapat menonaktifkan akun sendiri.';
        }
        if ($roleKode !== null && $roleKode !== 'admin') {
            $errors['id_role'] = 'Anda tidak dapat mengubah role akun sendiri.';
        }
    }

    if (!$errors) {
        $dosenId = ($row['id_dosen'] === '') ? null : (int) $row['id_dosen'];
        $email = $row['email'] === '' ? null : $row['email'];
        try {
            if ($edit) {
                $sql = 'UPDATE pengguna SET username=?, nama_lengkap=?, email=?, id_role=?, id_dosen=?, aktif=?';
                $vals = array($row['username'], $row['nama_lengkap'], $email, (int) $row['id_role'], $dosenId, $row['aktif']);
                if ($pass !== '') {
                    $sql .= ', password=?';
                    $vals[] = password_hash($pass, PASSWORD_DEFAULT);
                }
                $vals[] = $id;
                db()->prepare($sql . ' WHERE id_pengguna=?')->execute($vals);
            } else {
                db()->prepare('INSERT INTO pengguna (username,password,nama_lengkap,email,id_role,id_dosen,aktif) VALUES (?,?,?,?,?,?,?)')
                    ->execute(array($row['username'], password_hash($pass, PASSWORD_DEFAULT), $row['nama_lengkap'], $email, (int) $row['id_role'], $dosenId, $row['aktif']));
            }
            flash('success', 'Pengguna berhasil disimpan.');
            redirect('pengguna.php');
        } catch (PDOException $e) {
            $errors['_'] = 'Gagal menyimpan pengguna.';
        }
    }
}

function perr($errors, $k)
{
    return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
}

$page_title = $edit ? 'Edit Pengguna' : 'Tambah Pengguna';
$active = 'pengguna';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Pengaturan</div><h1><?= e($page_title) ?></h1>
  <p class="muted">Buat akun pengguna dan tentukan role (hak akses).</p></div><a class="btn" href="pengguna.php">&larr; Kembali</a></div>
<?php if (isset($errors['_'])): ?><div class="alert alert-error"><?= e($errors['_']) ?></div><?php endif; ?>
<form method="post" class="card" autocomplete="off"><div class="card-body"><?= csrf_field() ?>
  <div class="form-grid">
    <div class="field"><label>Role <span class="req">*</span></label>
      <select name="id_role" required><option value="">-- Pilih Role --</option>
      <?php foreach ($roles as $r): ?><option value="<?= (int) $r['id_role'] ?>" <?= (string) $r['id_role'] === (string) $row['id_role'] ? 'selected' : '' ?>><?= e($r['nama_role']) ?></option><?php endforeach; ?></select><?= perr($errors, 'id_role') ?></div>
    <div class="field"><label>Username <span class="req">*</span></label><input type="text" name="username" maxlength="50" value="<?= e($row['username']) ?>" required autocomplete="off"><?= perr($errors, 'username') ?></div>
    <div class="field"><label>Password <?= $edit ? '' : '<span class="req">*</span>' ?></label>
      <input type="password" name="password" id="pw" autocomplete="new-password" <?= $edit ? '' : 'required' ?>>
      <small><?= $edit ? 'Kosongkan bila tidak ingin mengubah password. ' : '' ?>Minimal 8 karakter, disimpan dengan password_hash().</small><?= perr($errors, 'password') ?></div>
    <div class="field"><label>Nama Lengkap <span class="req">*</span></label><input type="text" name="nama_lengkap" maxlength="150" value="<?= e($row['nama_lengkap']) ?>" required><?= perr($errors, 'nama_lengkap') ?></div>
    <div class="field"><label>Email</label><input type="email" name="email" maxlength="120" value="<?= e($row['email']) ?>"><?= perr($errors, 'email') ?></div>
    <div class="field"><label>Hubungkan ke Data Dosen</label>
      <select name="id_dosen"><option value="">-- Tidak ada --</option>
      <?php foreach ($dosen as $d): ?><option value="<?= (int) $d['id_dosen'] ?>" <?= (string) $d['id_dosen'] === (string) $row['id_dosen'] ? 'selected' : '' ?>><?= e($d['nama']) ?> (<?= e($d['nomor_registrasi']) ?>)</option><?php endforeach; ?></select>
      <small>Wajib untuk role Dosen agar dapat melihat profilnya.</small><?= perr($errors, 'id_dosen') ?></div>
    <div class="field"><label>Status</label><label style="font-weight:400"><input type="checkbox" name="aktif" value="1" <?= !empty($row['aktif']) ? 'checked' : '' ?>> Akun aktif</label><?= perr($errors, 'aktif') ?></div>
  </div>
  <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan</button><a class="btn" href="pengguna.php">Batal</a></div>
</div></form>
<?php include __DIR__ . '/includes/footer.php'; ?>
