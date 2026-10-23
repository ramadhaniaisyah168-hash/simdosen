<?php
/** Kerangka halaman (sidebar + topbar). Variabel: $page_title, $active */
$u = current_user();
$page_title = isset($page_title) ? $page_title : APP_NAME;
$active = isset($active) ? $active : '';
$flash = flash();

function menu_item($href, $label, $key, $active, $icon)
{
    $cls = ($key === $active) ? 'nav-item active' : 'nav-item';
    return '<a class="' . $cls . '" href="' . e($href) . '"><span class="ico">' . $icon . '</span>' . e($label) . '</a>';
}
$masters = array(
    'jabatan_fungsional' => 'Jabatan Fungsional', 'kepangkatan' => 'Kepangkatan', 'pendidikan' => 'Pendidikan',
    'rumpun_ilmu' => 'Rumpun Ilmu', 'status_kepegawaian' => 'Status Kepegawaian', 'penempatan' => 'Penempatan',
);
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="app">
<aside class="sidebar" id="sidebar">
  <div class="brand"><span class="logo">UMB</span><div><strong><?= e(APP_NAME) ?></strong><small><?= e(APP_INSTITUSI) ?></small></div></div>
  <nav>
    <div class="nav-title">Menu Utama</div>
    <?= menu_item('dashboard.php', 'Dashboard', 'dashboard', $active, '&#9638;') ?>
    <?php if (can('dosen.view')): ?>
      <div class="nav-title">Data Kepegawaian</div>
      <?= menu_item('dosen.php', 'Data Dosen', 'dosen', $active, '&#128100;') ?>
    <?php endif; ?>
    <?php if (can('master.view')): ?>
      <div class="nav-title">Data Master</div>
      <?php foreach ($masters as $k => $lbl): ?>
        <?= menu_item('master.php?t=' . $k, $lbl, 'master:' . $k, $active, '&#9679;') ?>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if (can('pengguna.manage') || can('roles.manage')): ?>
      <div class="nav-title">Pengaturan</div>
      <?php if (can('pengguna.manage')) echo menu_item('pengguna.php', 'Pengguna', 'pengguna', $active, '&#128101;'); ?>
      <?php if (can('roles.manage')) echo menu_item('master.php?t=roles', 'Role', 'master:roles', $active, '&#128274;'); ?>
    <?php endif; ?>
    <?php if (can('laporan.view')): ?>
      <div class="nav-title">Laporan</div>
      <?= menu_item('laporan.php', 'Rekap Dosen', 'laporan', $active, '&#128202;') ?>
    <?php endif; ?>
    <div class="nav-title">Akun Saya</div>
    <?= menu_item('profil.php', 'Profil Saya', 'profil', $active, '&#128100;') ?>
  </nav>
</aside>
<div class="main">
  <header class="topbar">
    <button class="burger" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">&#9776;</button>
    <div class="top-title"><strong><?= e(APP_NAME) ?></strong><small><?= e(APP_INSTITUSI) ?></small></div>
    <div class="top-user">
      <span class="avatar"><?= e(strtoupper(substr($u['nama'], 0, 1))) ?></span>
      <div class="who"><strong><?= e($u['nama']) ?></strong><small><?= e($u['role_nama']) ?></small></div>
      <a class="logout" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar</a>
    </div>
  </header>
  <main class="content">
  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
  <?php endif; ?>
