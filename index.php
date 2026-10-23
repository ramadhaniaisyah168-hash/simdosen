<?php
require __DIR__ . '/includes/bootstrap.php';
$total = $aktif = $serdos = null;
try {
    $r = db()->query("SELECT COUNT(*) t, SUM(status_aktivitas='Aktif') a, SUM(status_serdos='Sudah') s FROM dosen")->fetch();
    $total = (int) $r['t'];
    $aktif = (int) $r['a'];
    $serdos = (int) $r['s'];
} catch (Exception $e) {
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> - <?= e(APP_INSTITUSI) ?></title><link rel="stylesheet" href="assets/style.css"></head>
<body>
<div class="land-nav">
  <div class="brand"><span class="logo">UMB</span><div><strong><?= e(APP_NAME) ?></strong><small><?= e(APP_INSTITUSI) ?></small></div></div>
  <a class="btn btn-primary" href="<?= logged_in() ? 'dashboard.php' : 'login.php' ?>"><?= logged_in() ? 'Dashboard' : 'Login' ?></a>
</div>
<section class="hero">
  <div>
    <div class="eyebrow">Sistem Informasi Manajemen</div>
    <h1>Data Dosen<br>Universitas</h1>
    <p>Sistem terintegrasi untuk pengelolaan dan penyajian data dosen aktif: jabatan fungsional, kepangkatan, pendidikan terakhir, rumpun ilmu, status serdos, status kepegawaian, hingga penempatan.</p>
    <div class="cta">
      <a class="btn btn-primary" href="login.php">Masuk ke Sistem</a>
      <a class="btn btn-outline" href="#tentang">Tentang Sistem</a>
    </div>
  </div>
  <div class="hero-card">
    <div style="font-size:34px">&#127891;</div>
    <h2>Daftar Dosen Aktif</h2>
    <p class="muted">Rekap data dosen yang dikelola sesuai kewenangan masing-masing role.</p>
    <?php if ($total !== null): ?>
    <div class="grid g3" style="margin-top:14px">
      <div><strong style="font-size:24px"><?= $total ?></strong><br><small class="muted">Total dosen</small></div>
      <div><strong style="font-size:24px"><?= $aktif ?></strong><br><small class="muted">Aktif</small></div>
      <div><strong style="font-size:24px"><?= $serdos ?></strong><br><small class="muted">Sudah serdos</small></div>
    </div>
    <?php endif; ?>
    <div class="chips"><?= badge('Admin', 'green') ?><?= badge('Operator', 'green') ?><?= badge('Pimpinan', 'green') ?><?= badge('Dosen', 'green') ?></div>
  </div>
</section>
<section class="section" id="tentang">
  <h2>Tentang Sistem</h2>
  <div class="grid g3" style="margin-top:12px">
    <div class="feature"><div class="ico">&#128221;</div><h3>CRUD Data Dosen</h3><p class="muted">Tambah, ubah, cari, filter, dan hapus data dosen dengan validasi.</p></div>
    <div class="feature"><div class="ico">&#128274;</div><h3>Hak Akses (RBAC)</h3><p class="muted">Menu dan aksi dibatasi sesuai role: Admin, Operator, Pimpinan, Dosen.</p></div>
    <div class="feature"><div class="ico">&#128202;</div><h3>Laporan &amp; API</h3><p class="muted">Rekap dosen siap cetak/CSV serta REST API JSON untuk integrasi.</p></div>
  </div>
</section>
</body></html>
