<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/api.php';
json_out(array(
    'status' => 'ok',
    'nama' => APP_NAME . ' REST API',
    'catatan' => 'Autentikasi berbasis sesi (cookie). Untuk POST/PUT/DELETE kirim Content-Type: application/json. Jika PUT/DELETE diblokir hosting, kirim POST dengan header X-HTTP-Method-Override atau ?_method=PUT.',
    'endpoint' => array(
        array('POST', 'api/login.php', 'Body JSON: {"username":"admin","password":"admin123"}'),
        array('POST', 'api/logout.php', 'Keluar'),
        array('GET', 'api/statistik.php', 'Statistik ringkas (publik, tanpa data pribadi)'),
        array('GET', 'api/dosen.php?q=&jabatan=&penempatan=&aktivitas=&serdos=&page=1&per_page=10', 'Daftar dosen (dosen.view)'),
        array('GET', 'api/dosen.php?id=1', 'Detail dosen (dosen.view)'),
        array('POST', 'api/dosen.php', 'Tambah dosen (dosen.create)'),
        array('PUT', 'api/dosen.php?id=1', 'Ubah dosen (dosen.edit)'),
        array('DELETE', 'api/dosen.php?id=1', 'Hapus dosen (dosen.delete)'),
        array('GET', 'api/master.php?t=jabatan_fungsional', 'Daftar data master: jabatan_fungsional, kepangkatan, pendidikan, rumpun_ilmu, status_kepegawaian, penempatan'),
        array('POST/PUT/DELETE', 'api/master.php?t=...&id=...', 'Kelola data master (master.manage). Body: {"nama":"...","aktif":1}'),
    ),
));
