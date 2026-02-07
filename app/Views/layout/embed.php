<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($title ?? 'Lab Bahasa') ?></title>
<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
</head>
<body class="embed-modal">
  <main class="container">
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('ok')): ?>
      <div class="alert alert-ok"><?= esc(session()->getFlashdata('ok')) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
  </main>

  <div id="filePreviewModal" class="modal" aria-hidden="true">
    <div class="modal-dialog modal-preview" role="dialog" aria-modal="true" aria-label="Preview File">
      <button type="button" class="modal-close" data-close-preview aria-label="Tutup">×</button>
      <div class="modal-preview-head">
        <div id="filePreviewTitle" class="modal-title">Preview File</div>
        <a id="filePreviewOpen" class="btn tiny" href="#" target="_blank" rel="noopener">Buka Tab</a>
      </div>
      <iframe id="filePreviewFrame" title="Preview File" src="about:blank"></iframe>
    </div>
  </div>

  <script>
    (function(){
      const modal = document.getElementById('filePreviewModal');
      const frame = document.getElementById('filePreviewFrame');
      if(!modal || !frame) return;
      const titleEl = document.getElementById('filePreviewTitle');
      const openEl = document.getElementById('filePreviewOpen');

      function openPreview(url, title){
        frame.src = url || 'about:blank';
        if(titleEl) titleEl.textContent = title || 'Preview File';
        if(openEl){
          if(url){
            openEl.href = url;
            openEl.style.display = '';
          }else{
            openEl.href = '#';
            openEl.style.display = 'none';
          }
        }
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.classList.add('modal-open');
      }

      function closePreview(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
        frame.src = 'about:blank';
      }

      document.addEventListener('click', function(e){
        const trigger = e.target.closest('[data-preview-url]');
        if(trigger){
          e.preventDefault();
          openPreview(trigger.dataset.previewUrl || '', trigger.dataset.previewTitle || '');
          return;
        }
        if(e.target.closest('[data-close-preview]')){
          e.preventDefault();
          closePreview();
        }
        if(e.target === modal){
          closePreview();
        }
      });

      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && modal.classList.contains('open')){
          closePreview();
        }
      });
    })();
  </script>
</body>
</html>
