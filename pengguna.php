<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('pengguna.manage');
$page_title = 'Pengguna';
$active = 'pengguna';
$rows = db()->query('SELECT p.*, r.nama_role, r.kode_role, d.nama AS nama_dosen FROM pengguna p
    JOIN roles r ON r.id_role = p.id_role LEFT JOIN dosen d ON d.id_dosen = p.id_dosen ORDER BY r.id_role, p.username')->fetchAll();
$me = current_user();
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Pengaturan</div><h1>Pengguna</h1><p class="muted">Kelola akun pengguna dan hak akses (RBAC).</p></div>
  <a class="btn btn-primary" href="pengguna_form.php">+ Tambah Pengguna</a></div>
<div class="card"><div class="table-wrap"><table class="tbl">
  <thead><tr><th>No.</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Terhubung ke Dosen</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead><tbody>
  <?php $no = 0; foreach ($rows as $r): $no++; ?>
  <tr><td><?= $no ?></td><td><strong><?= e($r['username']) ?></strong></td>
    <td><?= e($r['nama_lengkap']) ?><span class="sub"><?= dash($r['email']) ?></span></td>
    <td><?= badge($r['nama_role'], $r['kode_role'] === 'admin' ? 'red' : 'green') ?></td>
    <td><?= dash($r['nama_dosen']) ?></td>
    <td><?= $r['aktif'] ? badge('Aktif', 'green') : badge('Nonaktif', 'gray') ?></td>
    <td><?= tgl($r['last_login']) ?></td>
    <td class="actions"><a class="btn btn-sm btn-outline" href="pengguna_form.php?id=<?= (int) $r['id_pengguna'] ?>">Edit</a>
      <?php if ((int) $r['id_pengguna'] !== $me['id']): ?>
      <form class="inline" method="post" action="pengguna_hapus.php" data-confirm="Hapus pengguna <?= e($r['username']) ?>?"><?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $r['id_pengguna'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
      <?php endif; ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
