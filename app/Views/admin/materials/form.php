<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1><?= $mode === 'edit' ? 'Edit Materi' : 'Tambah Materi' ?></h1>

<form class="card" method="post" enctype="multipart/form-data"
      action="<?= $mode === 'edit' ? '/admin/materials/update/' . (int)$material['id'] : '/admin/materials/store' ?>">
  <label>Title</label>
  <input name="title" required value="<?= esc($material['title'] ?? '') ?>">

  <label>Type</label>
  <select name="type" id="matType">
    <option value="text" <?= ($material['type'] ?? '') === 'text' ? 'selected' : '' ?>>text</option>
    <option value="file" <?= ($material['type'] ?? '') === 'file' ? 'selected' : '' ?>>file</option>
  </select>

  <div id="textBox">
    <label>Text content</label>
    <textarea name="text_content" rows="8"><?= esc($material['text_content'] ?? '') ?></textarea>
  </div>

  <div id="fileBox" style="display:none">
    <label>Upload file (audio/video/pdf/image)</label>
    <input type="file" name="file">
    <?php if (!empty($file['url_path'])): ?>
      <p class="muted">File saat ini: <a href="<?= esc($file['url_path']) ?>" target="_blank"><?= esc($file['filename']) ?></a></p>
    <?php endif; ?>
  </div>

  <div class="row gap">
    <button type="submit">Simpan</button>
    <a class="btn" href="/admin/materials">Batal</a>
  </div>
</form>

<script>
  const sel = document.getElementById('matType');
  const textBox = document.getElementById('textBox');
  const fileBox = document.getElementById('fileBox');
  function sync() {
    if (sel.value === 'file') { fileBox.style.display='block'; textBox.style.display='none'; }
    else { fileBox.style.display='none'; textBox.style.display='block'; }
  }
  sel.addEventListener('change', sync);
  sync();
</script>
<?= $this->endSection() ?>
