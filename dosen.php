<?php
require __DIR__ . '/includes/bootstrap.php';
require_perm('dosen.view');
require __DIR__ . '/includes/dosen_lib.php';

$page_title = 'Data Dosen';
$active = 'dosen';
$flt = dosen_filters_from_get();
$per = 10;
$page = max(1, (int) get('page', 1));
list($rows, $total) = dosen_search($flt, $page, $per);
$pages = max(1, (int) ceil($total / $per));
$opt = dosen_options();
$base = qs_without('page');

include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><div class="eyebrow">Data Kepegawaian</div><h1>Daftar Dosen Aktif</h1>
  <p class="muted">Kelola data dosen universitas.</p></div>
  <div class="no-print"><?php if (can('dosen.create')): ?><a class="btn btn-primary" href="dosen_form.php">+ Tambah Dosen</a><?php endif; ?></div></div>

<div class="card"><div class="card-body">
  <form method="get" class="filters">
    <div class="field" style="flex:2"><label>Cari</label><input type="text" name="q" value="<?= e($flt['q']) ?>" placeholder="Nama, NIP, NUPTK, no. registrasi"></div>
    <div class="field"><label>Jabatan</label><select name="jabatan"><option value="">Semua</option><?= select_options($opt['id_jabatan'], $flt['jabatan'], false) ?></select></div>
    <div class="field"><label>Penempatan</label><select name="penempatan"><option value="">Semua</option><?= select_options($opt['id_penempatan'], $flt['penempatan'], false) ?></select></div>
    <div class="field"><label>Aktivitas</label><select name="aktivitas"><option value="">Semua</option><?= simple_options(enum_aktivitas(), $flt['aktivitas']) ?></select></div>
    <div class="field"><label>Serdos</label><select name="serdos"><option value="">Semua</option><?= simple_options(enum_serdos(), $flt['serdos']) ?></select></div>
    <div><button class="btn btn-primary" type="submit">Terapkan</button> <a class="btn" href="dosen.php">Reset</a></div>
  </form>
</div></div>

<div class="card">
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>No.</th><th>No. Registrasi</th><th>Nama / NIP / NUPTK</th><th>Jabatan / Pangkat</th><th>Pendidikan</th><th>Rumpun Ilmu</th>
      <th>Serdos</th><th>Status Pegawai</th><th>Aktivitas</th><th>Penempatan</th><th class="no-print">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="11" class="empty">Tidak ada data dosen yang cocok.</td></tr><?php endif; ?>
    <?php $no = ($page - 1) * $per; foreach ($rows as $d): $no++; ?>
      <tr>
        <td><?= $no ?></td>
        <td><?= e($d['nomor_registrasi']) ?></td>
        <td><a href="dosen_detail.php?id=<?= (int) $d['id_dosen'] ?>"><strong><?= e($d['nama']) ?></strong></a>
          <span class="sub">NIP: <?= dash($d['nip']) ?></span><span class="sub">NUPTK: <?= dash($d['nuptk']) ?></span></td>
        <td><?= dash($d['nama_jabatan']) ?><span class="sub"><?= dash($d['nama_pangkat']) ?></span></td>
        <td><?= dash($d['nama_pendidikan']) ?></td>
        <td><?= dash($d['nama_rumpun']) ?></td>
        <td><?= badge($d['status_serdos'], $d['status_serdos'] === 'Sudah' ? 'green' : 'gray') ?></td>
        <td><?= dash($d['nama_status']) ?><span class="sub"><?= e($d['ikatan_kerja']) ?></span></td>
        <td><?= badge_aktivitas($d['status_aktivitas']) ?></td>
        <td><?= dash($d['nama_penempatan']) ?></td>
        <td class="actions no-print">
          <a class="btn btn-sm" href="dosen_detail.php?id=<?= (int) $d['id_dosen'] ?>">Detail</a>
          <?php if (can('dosen.edit')): ?><a class="btn btn-sm btn-outline" href="dosen_form.php?id=<?= (int) $d['id_dosen'] ?>">Edit</a><?php endif; ?>
          <?php if (can('dosen.delete')): ?>
          <form class="inline" method="post" action="dosen_hapus.php" data-confirm="Hapus data dosen <?= e($d['nama']) ?>?">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $d['id_dosen'] ?>">
            <button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <div class="pager"><span class="muted">Menampilkan <?= count($rows) ?> dari <?= $total ?> dosen</span>
    <div class="pg">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i === $page): ?><span class="cur"><?= $i ?></span><?php else: ?><a href="?<?= e($base . ($base ? '&' : '') . 'page=' . $i) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
