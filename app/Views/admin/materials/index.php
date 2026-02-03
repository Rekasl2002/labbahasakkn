<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Manajemen Materi</h1>
<div class="row gap">
  <a class="btn" href="/admin/materials/create">+ Tambah Materi</a>
  <a class="btn" href="/admin">Kembali</a>
</div>

<div class="card">
  <div class="tableWrap">
  <table class="table">
    <thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Updated</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($materials as $m): ?>
      <tr>
        <td><?= (int)$m['id'] ?></td>
        <td><?= esc($m['title']) ?></td>
        <td><?= esc($m['type']) ?></td>
        <td><?= esc($m['updated_at'] ?? '-') ?></td>
        <td class="row gap">
          <form method="post" action="/admin/materials/broadcast/<?= (int)$m['id'] ?>">
            <button type="submit">Broadcast</button>
          </form>
          <a class="btn" href="/admin/materials/edit/<?= (int)$m['id'] ?>">Edit</a>
          <form method="post" action="/admin/materials/delete/<?= (int)$m['id'] ?>" onsubmit="return confirm('Hapus materi?')">
            <button class="danger" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?= $this->endSection() ?>
