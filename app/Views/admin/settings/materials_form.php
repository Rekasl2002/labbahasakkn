<?php
$mode = $mode ?? 'create';
$material = $material ?? null;
$file = $file ?? null;
$files = $files ?? [];
$embed = $embed ?? false;
$embedQuery = $embed ? '&embed=1' : '';
?>

<div class="row between wrap gap" style="align-items:flex-end">
  <div>
    <h2 style="margin:0"><?= $mode === 'edit' ? 'Edit Materi' : 'Tambah Materi' ?></h2>
    <div class="muted tiny" style="margin-top:4px">Materi akan tampil di halaman siswa saat dibroadcast.</div>
  </div>
  <?php if ($mode === 'edit'): ?>
    <a class="btn" href="/admin/settings?tab=materials&mat=add<?= $embedQuery ?>">+ Materi Baru</a>
  <?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data"
      action="<?= $mode === 'edit' ? '/admin/materials/update/' . (int)$material['id'] : '/admin/materials/store' ?>">
  <?php if ($embed): ?><input type="hidden" name="embed" value="1"><?php endif; ?>
  <label>Title</label>
  <input name="title" required value="<?= esc($material['title'] ?? '') ?>">

  <label>Type</label>
  <select name="type" id="matType">
    <option value="folder" <?= ($material['type'] ?? '') === 'folder' ? 'selected' : '' ?>>folder (multi file + daftar teks)</option>
    <option value="text" <?= ($material['type'] ?? '') === 'text' ? 'selected' : '' ?>>text (single)</option>
    <option value="file" <?= ($material['type'] ?? '') === 'file' ? 'selected' : '' ?>>file (single)</option>
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
      <label class="muted tiny" style="display:block;margin-top:6px">
        <input type="checkbox" name="delete_file" value="1"> Hapus file saat ini
      </label>
    <?php endif; ?>
  </div>

  <div id="folderBox" style="display:none">
    <label>Daftar teks (1 baris = 1 teks)</label>
    <textarea id="textItemsInput" name="text_items" rows="8" placeholder="Contoh:&#10;Apel&#10;Jeruk&#10;Mangga"><?= esc(($material['type'] ?? '') === 'folder' ? ($material['text_content'] ?? '') : '') ?></textarea>
    <div class="muted tiny" style="margin-top:6px">Urutkan teks dengan drag di bawah (tersimpan).</div>
    <ul id="textList" class="materialList sortable" style="margin-top:6px"></ul>

    <label style="margin-top:10px">Upload file (boleh lebih dari satu)</label>
    <input type="file" name="files[]" multiple>

    <?php if (!empty($files)): ?>
      <div class="muted tiny" style="margin-top:10px">Urutkan file dengan drag (tersimpan). Centang untuk hapus.</div>
      <ul id="fileList" class="materialList sortable" style="margin-top:6px">
        <?php foreach ($files as $f): ?>
          <li class="materialItem" draggable="true" data-file-id="<?= (int)$f['id'] ?>">
            <div class="label">
              <div><?= esc($f['filename']) ?></div>
              <div class="muted tiny"><?= esc($f['mime'] ?? '') ?></div>
            </div>
            <input type="hidden" name="file_order[]" value="<?= (int)$f['id'] ?>">
            <label class="muted tiny"><input type="checkbox" name="delete_files[]" value="<?= (int)$f['id'] ?>"> Hapus</label>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="row gap">
    <button type="submit">Simpan</button>
    <a class="btn" href="/admin/settings?tab=materials&mat=list<?= $embedQuery ?>">Batal</a>
  </div>
</form>

<script>
  const sel = document.getElementById('matType');
  const textBox = document.getElementById('textBox');
  const fileBox = document.getElementById('fileBox');
  const folderBox = document.getElementById('folderBox');
  const textItemsInput = document.getElementById('textItemsInput');
  const textList = document.getElementById('textList');
  const fileList = document.getElementById('fileList');

  function normalizeLines(text){
    return (text||'')
      .split(/\r\n|\n|\r/)
      .map(s=>s.trim())
      .filter(s=>s !== '');
  }

  function renderTextList(){
    if(!textList || !textItemsInput) return;
    const items = normalizeLines(textItemsInput.value);
    textList.innerHTML = '';
    items.forEach((t)=>{
      const li = document.createElement('li');
      li.className = 'materialItem';
      li.draggable = true;
      li.dataset.text = t;
      li.innerHTML = `<div class="label">${t}</div><span class="muted tiny">drag</span>`;
      textList.appendChild(li);
    });
  }

  function syncTextInputFromList(){
    if(!textList || !textItemsInput) return;
    const items = Array.from(textList.querySelectorAll('li')).map(li=> li.dataset.text || '').filter(Boolean);
    textItemsInput.value = items.join('\n');
  }

  function enableDrag(listEl, onReorder){
    if(!listEl) return;
    let dragging = null;
    listEl.addEventListener('dragstart', (e)=>{
      const li = e.target.closest('li');
      if(!li) return;
      dragging = li;
      li.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    listEl.addEventListener('dragend', ()=>{
      if(dragging) dragging.classList.remove('dragging');
      dragging = null;
      if(onReorder) onReorder();
    });
    listEl.addEventListener('dragover', (e)=>{
      e.preventDefault();
      const li = e.target.closest('li');
      if(!li || li === dragging) return;
      const rect = li.getBoundingClientRect();
      const after = (e.clientY - rect.top) > rect.height / 2;
      if(after){
        li.after(dragging);
      }else{
        li.before(dragging);
      }
    });
    listEl.addEventListener('drop', (e)=>{
      e.preventDefault();
      if(onReorder) onReorder();
    });
  }

  if(textItemsInput){
    renderTextList();
    textItemsInput.addEventListener('input', renderTextList);
    enableDrag(textList, syncTextInputFromList);
  }

  if(fileList){
    enableDrag(fileList, ()=>{
      const ordered = Array.from(fileList.querySelectorAll('li'));
      ordered.forEach((li, idx)=>{
        const hidden = li.querySelector('input[name="file_order[]"]');
        if(hidden) hidden.value = li.dataset.fileId || hidden.value;
        li.dataset.sortIndex = String(idx + 1);
      });
    });
  }

  function sync() {
    if (!sel) return;
    if (sel.value === 'file') {
      fileBox.style.display='block'; textBox.style.display='none'; folderBox.style.display='none';
    } else if (sel.value === 'folder') {
      fileBox.style.display='none'; textBox.style.display='none'; folderBox.style.display='block';
    } else {
      fileBox.style.display='none'; textBox.style.display='block'; folderBox.style.display='none';
    }
  }
  if(sel){
    sel.addEventListener('change', sync);
  }
  sync();
</script>
