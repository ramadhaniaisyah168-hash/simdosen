<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('pengguna.manage');
if (!is_post()) {
    redirect('pengguna.php');
}
csrf_check();
$id = (int) post('id');
if ($id === current_user()['id']) {
    flash('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
    redirect('pengguna.php');
}
$st = db()->prepare('DELETE FROM pengguna WHERE id_pengguna = ?');
$st->execute(array($id));
flash($st->rowCount() ? 'success' : 'error', $st->rowCount() ? 'Pengguna berhasil dihapus.' : 'Pengguna tidak ditemukan.');
redirect('pengguna.php');
