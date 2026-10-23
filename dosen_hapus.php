<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('dosen.delete');
if (!is_post()) {
    redirect('dosen.php');
}
csrf_check();
$id = (int) post('id');
try {
    $st = db()->prepare('DELETE FROM dosen WHERE id_dosen = ?');
    $st->execute(array($id));
    flash($st->rowCount() ? 'success' : 'error', $st->rowCount() ? 'Data dosen berhasil dihapus.' : 'Data dosen tidak ditemukan.');
} catch (PDOException $e) {
    flash('error', 'Data dosen tidak dapat dihapus.');
}
redirect('dosen.php');
