<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
require __DIR__ . '/includes/dosen_lib.php';

$page_title = 'Dashboard';
$active = 'dashboard';
$u = current_user();

function bars($rows)
{
    $max = 1;
    foreach ($rows as $r) {
        $max = max($max, (int) $r['c']);
    }
    $h = '';
    foreach ($rows as $r) {
        $w = round(((int) $r['c'] / $max) * 100);
        $h .= '<div class="bar-row"><span>' . e($r['n']) . '</span><div class="bar"><i style="width:' . $w . '%"></i></div><strong>' . (int) $r['c'] . '</strong></div>';
    }
    return $h ?: '<p class="muted">Belum ada data.</p>';
}

include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Dashboard</div>
  <h1>Selamat datang, <?= e($u['nama']) ?></h1>
  <p class="muted"><?= e($u['role_nama']) ?> &middot; <?= e(APP_INSTITUSI) ?></p></div></div>

<?php if (can('dosen.view')):
    $s = db()->query("SELECT COUNT(*) t, SUM(status_aktivitas='Aktif') a, SUM(status_serdos='Sudah') s, SUM(ikatan_kerja='Dosen Tetap') tt FROM dosen")->fetch();
    $byJab = db()->query('SELECT j.nama_jabatan n, COUNT(d.id_dosen) c FROM jabatan_fungsional j LEFT JOIN dosen d ON d.id_jabatan = j.id_jabatan GROUP BY j.id_jabatan, j.nama_jabatan ORDER BY j.id_jabatan')->fetchAll();
    $byPen = db()->query('SELECT p.nama_penempatan n, COUNT(d.id_dosen) c FROM penempatan p LEFT JOIN dosen d ON d.id_penempatan = p.id_penempatan GROUP BY p.id_penempatan, p.nama_penempatan ORDER BY c DESC, p.nama_penempatan')->fetchAll();
    list($recent) = dosen_search(array(), 1, 0);
    usort($recent, function ($a, $b) { return $b['id_dosen'] - $a['id_dosen']; });
    $recent = array_slice($recent, 0, 5);
?>
<div class="grid g4" style="margin-bottom:18px">
  <div class="stat"><div class="ico">&#128101;</div><div><small>Total Dosen</small><strong><?= (int) $s['t'] ?></strong></div></div>
  <div class="stat"><div class="ico">&#9989;</div><div><small>Dosen Aktif</small><strong><?= (int) $s['a'] ?></strong></div></div>
  <div class="stat"><div class="ico">&#127891;</div><div><small>Sudah Serdos</small><strong><?= (int) $s['s'] ?></strong></div></div>
  <div class="stat"><div class="ico">&#128188;</div><div><small>Dosen Tetap</small><strong><?= (int) $s['tt'] ?></strong></div></div>
</div>
<div class="grid g2">
  <div class="card"><div class="card-head"><h3>Dosen per Jabatan Fungsional</h3></div><div class="card-body"><?= bars($byJab) ?></div></div>
  <div class="card"><div class="card-head"><h3>Dosen per Penempatan</h3></div><div class="card-body"><?= bars($byPen) ?></div></div>
</div>
<div class="card"><div class="card-head"><h3>Data Dosen Terbaru</h3><a class="btn btn-sm" href="dosen.php">Lihat semua</a></div>
  <div class="table-wrap"><table class="tbl"><thead><tr><th>Nama</th><th>Jabatan</th><th>Penempatan</th><th>Aktivitas</th></tr></thead><tbody>
  <?php foreach ($recent as $d): ?>
    <tr><td><a href="dosen_detail.php?id=<?= (int) $d['id_dosen'] ?>"><?= e($d['nama']) ?></a><span class="sub"><?= e($d['nomor_registrasi']) ?></span></td>
    <td><?= dash($d['nama_jabatan']) ?></td><td><?= dash($d['nama_penempatan']) ?></td><td><?= badge_aktivitas($d['status_aktivitas']) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<?php else:
    $me = $u['id_dosen'] ? dosen_find($u['id_dosen']) : null; ?>
<div class="card"><div class="card-body">
  <div class="eyebrow">Portal Dosen</div><h2>Data kepegawaian Anda</h2>
  <?php if ($me): ?>
    <p class="muted">Anda dapat melihat data dosen yang tercatat atas nama Anda.</p>
    <p><strong><?= e($me['nama']) ?></strong> &middot; <?= dash($me['nama_jabatan']) ?> &middot; <?= badge_aktivitas($me['status_aktivitas']) ?></p>
    <a class="btn btn-primary" href="profil.php">Lihat Profil</a>
  <?php else: ?>
    <p class="muted">Akun Anda belum dihubungkan dengan data dosen. Hubungi administrator.</p>
  <?php endif; ?>
</div></div>
<?php endif; ?>

<div class="card"><div class="card-head"><h3>Informasi Sistem</h3></div><div class="card-body">
  <dl class="dl"><dt>Universitas</dt><dd><?= e(APP_INSTITUSI) ?></dd>
  <dt>Username</dt><dd><?= e($u['username']) ?></dd>
  <dt>Role</dt><dd><?= e($u['role_nama']) ?></dd>
  <dt>Lingkungan</dt><dd><?= IS_LOCAL ? 'Lokal (XAMPP)' : 'Hosting' ?></dd></dl>
</div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
