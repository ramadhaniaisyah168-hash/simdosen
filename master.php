<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/master_lib.php';
require_login();

$cfgs = master_config();
$t = get('t', 'jabatan_fungsional');
if (!isset($cfgs[$t])) {
    redirect('master.php?t=jabatan_fungsional');
}
$cfg = $cfgs[$t];
require_perm($cfg['perm_view']);
$manage = can($cfg['perm_manage']);
$aksi = get('aksi');
$id = (int) get('id');
$self = 'master.php?t=' . $t;
$errors = array();

/* ---------- proses POST ---------- */
if (is_post()) {
    if (!$manage) {
        forbidden();
    }
    csrf_check();
    $op = post('op');
    $pid = (int) post('id');

    if ($op === 'hapus' && $cfg['can_delete']) {
        try {
            $st = db()->prepare('DELETE FROM ' . $cfg['table'] . ' WHERE ' . $cfg['pk'] . ' = ?');
            $st->execute(array($pid));
            flash('success', 'Data berhasil dihapus.');
        } catch (PDOException $e) {
            flash('error', 'Data tidak dapat dihapus karena masih dipakai data lain. Nonaktifkan saja bila tidak dipakai lagi.');
        }
        redirect($self);
    }

    if ($op === 'simpan') {
        $name = post('nama');
        $aktif = isset($_POST['aktif']) ? 1 : 0;
        $isEdit = $pid > 0;
        if (!$isEdit && !$cfg['can_add']) {
            forbidden();
        }
        $msg = master_validate_name($cfg, $name, $pid);
        $extraVals = array();
        if (isset($cfg['extra']['deskripsi'])) {
            $extraVals['deskripsi'] = ssub(post('deskripsi'), 0, 255);
        }
        if ($msg !== '') {
            $errors['nama'] = $msg;
            $aksi = $isEdit ? 'ubah' : 'tambah';
            $id = $pid;
            $row = array($cfg['name'] => $name, 'aktif' => $aktif, 'deskripsi' => isset($extraVals['deskripsi']) ? $extraVals['deskripsi'] : '');
        } else {
            try {
                if ($isEdit) {
                    $sets = array($cfg['name'] . ' = ?');
                    $vals = array($name);
                    if ($cfg['has_aktif']) {
                        $sets[] = 'aktif = ?';
                        $vals[] = $aktif;
                    }
                    foreach ($extraVals as $k => $v) {
                        $sets[] = "$k = ?";
                        $vals[] = $v;
                    }
                    $vals[] = $pid;
                    db()->prepare('UPDATE ' . $cfg['table'] . ' SET ' . implode(', ', $sets) . ' WHERE ' . $cfg['pk'] . ' = ?')->execute($vals);
                } else {
                    db()->prepare('INSERT INTO ' . $cfg['table'] . ' (' . $cfg['name'] . ', aktif) VALUES (?, ?)')->execute(array($name, $aktif));
                }
                flash('success', 'Data berhasil disimpan.');
                redirect($self);
            } catch (PDOException $e) {
                flash('error', 'Gagal menyimpan data.');
                redirect($self);
            }
        }
    }
}

$page_title = $cfg['label'];
$active = 'master:' . $t;
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow"><?= $t === 'roles' ? 'Pengaturan' : 'Data Master' ?></div><h1><?= e($cfg['label']) ?></h1></div>
  <div><?php if ($manage && $cfg['can_add'] && $aksi === ''): ?><a class="btn btn-primary" href="<?= e($self) ?>&aksi=tambah">+ Tambah Data</a><?php endif; ?></div></div>

<?php if ($t !== 'roles'): ?>
<div class="tabs"><?php foreach (master_list_tables() as $k => $c): ?>
  <a class="<?= $k === $t ? 'on' : '' ?>" href="master.php?t=<?= e($k) ?>"><?= e($c['label']) ?></a><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($manage && ($aksi === 'tambah' || $aksi === 'ubah')):
    if (!isset($row)) {
        $row = array($cfg['name'] => '', 'aktif' => 1, 'deskripsi' => '');
        if ($aksi === 'ubah') {
            $st = db()->prepare('SELECT * FROM ' . $cfg['table'] . ' WHERE ' . $cfg['pk'] . ' = ?');
            $st->execute(array($id));
            $row = $st->fetch();
            if (!$row) {
                flash('error', 'Data tidak ditemukan.');
                redirect($self);
            }
        }
    }
    if ($aksi === 'tambah' && !$cfg['can_add']) {
        redirect($self);
    }
?>
<form method="post" class="card"><div class="card-body">
  <?= csrf_field() ?><input type="hidden" name="op" value="simpan"><input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $id : 0 ?>">
  <div class="form-grid">
    <?php if (isset($cfg['extra']['kode_role'])): ?>
    <div class="field"><label>Kode Role</label><input type="text" value="<?= e(isset($row['kode_role']) ? $row['kode_role'] : '') ?>" readonly><small>Kode role tetap (dipakai pada aturan hak akses).</small></div>
    <?php endif; ?>
    <div class="field"><label>Nama <span class="req">*</span></label>
      <input type="text" name="nama" maxlength="<?= (int) $cfg['max'] ?>" value="<?= e($row[$cfg['name']]) ?>" required autofocus>
      <?= isset($errors['nama']) ? '<span class="err">' . e($errors['nama']) . '</span>' : '' ?></div>
    <?php if (isset($cfg['extra']['deskripsi'])): ?>
    <div class="field" style="grid-column:1/-1"><label>Deskripsi</label><input type="text" name="deskripsi" maxlength="255" value="<?= e(isset($row['deskripsi']) ? $row['deskripsi'] : '') ?>"></div>
    <?php endif; ?>
    <?php if ($cfg['has_aktif']): ?>
    <div class="field"><label>Status</label><label style="font-weight:400"><input type="checkbox" name="aktif" value="1" <?= !empty($row['aktif']) ? 'checked' : '' ?>> Aktif (muncul di pilihan form dosen)</label></div>
    <?php endif; ?>
  </div>
  <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan</button><a class="btn" href="<?= e($self) ?>">Batal</a></div>
</div></form>
<?php endif; ?>

<?php
$rows = db()->query('SELECT * FROM ' . $cfg['table'] . ' ORDER BY ' . $cfg['pk'])->fetchAll();
$used = array();
if ($cfg['used_col']) {
    foreach (db()->query('SELECT ' . $cfg['used_col'] . ' k, COUNT(*) c FROM dosen WHERE ' . $cfg['used_col'] . ' IS NOT NULL GROUP BY ' . $cfg['used_col'])->fetchAll() as $u) {
        $used[$u['k']] = (int) $u['c'];
    }
}
if ($t === 'roles') {
    foreach (db()->query('SELECT id_role k, COUNT(*) c FROM pengguna GROUP BY id_role')->fetchAll() as $u) {
        $used[$u['k']] = (int) $u['c'];
    }
}
?>
<div class="card"><div class="table-wrap"><table class="tbl">
  <thead><tr><th>ID</th><?php if ($t === 'roles'): ?><th>Kode</th><?php endif; ?><th>Nama</th><?php if ($t === 'roles'): ?><th>Deskripsi</th><?php endif; ?>
    <?php if ($cfg['has_aktif']): ?><th>Status</th><?php endif; ?><th><?= $t === 'roles' ? 'Jumlah Pengguna' : 'Dipakai Dosen' ?></th><?php if ($manage): ?><th>Aksi</th><?php endif; ?></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">Belum ada data.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): $rid = (int) $r[$cfg['pk']]; $n = isset($used[$rid]) ? $used[$rid] : 0; ?>
    <tr><td><?= $rid ?></td>
      <?php if ($t === 'roles'): ?><td><code><?= e($r['kode_role']) ?></code></td><?php endif; ?>
      <td><strong><?= e($r[$cfg['name']]) ?></strong></td>
      <?php if ($t === 'roles'): ?><td class="muted"><?= dash($r['deskripsi']) ?></td><?php endif; ?>
      <?php if ($cfg['has_aktif']): ?><td><?= $r['aktif'] ? badge('Aktif', 'green') : badge('Nonaktif', 'gray') ?></td><?php endif; ?>
      <td><?= $n ?></td>
      <?php if ($manage): ?><td class="actions">
        <a class="btn btn-sm btn-outline" href="<?= e($self) ?>&aksi=ubah&id=<?= $rid ?>">Edit</a>
        <?php if ($cfg['can_delete']): ?>
        <form class="inline" method="post" data-confirm="Hapus data ini?"><?= csrf_field() ?>
          <input type="hidden" name="op" value="hapus"><input type="hidden" name="id" value="<?= $rid ?>">
          <button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
        <?php endif; ?></td><?php endif; ?></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
