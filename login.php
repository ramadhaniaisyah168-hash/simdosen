<?php
require __DIR__ . '/includes/bootstrap.php';
if (logged_in()) {
    redirect('dashboard.php');
}
$error = '';
$username = '';
if (is_post()) {
    $t = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $t)) {
        $error = 'Sesi formulir kedaluwarsa, silakan coba lagi.';
    } else {
        $username = post('username');
        $error = attempt_login($username, isset($_POST['password']) ? $_POST['password'] : '');
        if ($error === null) {
            redirect('dashboard.php');
        }
    }
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?= e(APP_NAME) ?></title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login-page">
<div class="login-box">
  <div class="login-left">
    <div class="brand" style="border:0;padding:0"><span class="logo">UMB</span><div><strong><?= e(APP_NAME) ?></strong><small><?= e(APP_INSTITUSI) ?></small></div></div>
    <div><h2>Selamat Datang</h2><p>Silakan masuk menggunakan akun yang telah diberikan kepada Anda.</p></div>
    <a href="index.php">&larr; Kembali ke halaman utama</a>
  </div>
  <div class="login-right">
    <h1>Login</h1>
    <p class="muted">Masukkan username dan password.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="on">
      <?= csrf_field() ?>
      <div class="field"><label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus></div>
      <div class="field"><label for="password">Password</label>
        <input type="password" id="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
    <div class="demo"><strong>Akun demo:</strong><br>
      admin / admin123 &middot; operator / operator123<br>
      pimpinan / pimpinan123 &middot; dosen1 / dosen123</div>
  </div>
</div>
</body></html>
