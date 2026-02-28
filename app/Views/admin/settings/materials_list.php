<?php
$materials = $materials ?? [];
$embed = $embed ?? false;
$embedQuery = $embed ? '&embed=1' : '';
?>

<div class="row between wrap gap" style="align-items:flex-end">
  <div>
    <h2 style="margin:0">Manajemen Materi</h2>
    <div class="muted tiny" style="margin-top:4px">Pilih materi untuk ditampilkan, diubah, atau dihapus.</div>
  </div>
  <a class="btn" href="/admin/settings?tab=materials&mat=add<?= $embedQuery ?>">+ Tambah Materi</a>
</div>

<?php if (empty($materials)): ?>
  <p class="muted" style="margin-top:10px">Belum ada materi.</p>
<?php else: ?>
  <div class="tableWrap" style="margin-top:10px">
    <table class="table">
      <thead><tr><th>ID</th><th>Judul</th><th>Jenis</th><th>Diperbarui</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($materials as $m): ?>
        <tr>
          <td><?= (int)$m['id'] ?></td>
          <td><?= esc($m['title']) ?></td>
          <td><?= esc($m['type']) ?></td>
          <td><?= esc($m['updated_at'] ?? '-') ?></td>
          <td class="row gap">
            <form method="post" action="/admin/materials/broadcast/<?= (int)$m['id'] ?>">
              <?php if ($embed): ?><input type="hidden" name="embed" value="1"><?php endif; ?>
              <button type="submit" class="btn">Pilih</button>
            </form>
            <a class="btn" href="/admin/settings?tab=materials&mat=edit&edit_id=<?= (int)$m['id'] ?><?= $embedQuery ?>">Ubah</a>
            <form method="post" action="/admin/materials/delete/<?= (int)$m['id'] ?>" onsubmit="return confirm('Hapus materi?')">
              <?php if ($embed): ?><input type="hidden" name="embed" value="1"><?php endif; ?>
              <button class="btn danger" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

