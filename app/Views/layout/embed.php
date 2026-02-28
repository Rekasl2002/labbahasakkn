<!DOCTYPE html>
<html lang="id">
<head>
  <?= view('layout/partials/head_css', ['title' => $title ?? null]) ?>
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
    <div class="modal-dialog modal-preview" role="dialog" aria-modal="true" aria-label="Pratinjau Berkas">
      <button type="button" class="modal-close" data-close-preview aria-label="Tutup">×</button>
      <div class="modal-preview-head">
        <div id="filePreviewTitle" class="modal-title">Pratinjau Berkas</div>
        <a id="filePreviewOpen" class="btn tiny" href="#" target="_blank" rel="noopener">Buka Halaman Baru</a>
      </div>
      <iframe id="filePreviewFrame" title="Pratinjau Berkas" src="about:blank"></iframe>
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
        if(titleEl) titleEl.textContent = title || 'Pratinjau Berkas';
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
          const url = trigger.dataset.previewUrl || '';
          const title = trigger.dataset.previewTitle || '';
          const canUseParentPreview = window.parent
            && window.parent !== window
            && typeof window.parent.__LAB_OPEN_FILE_PREVIEW__ === 'function';
          if (canUseParentPreview) {
            try {
              window.parent.__LAB_OPEN_FILE_PREVIEW__(url, title, {
                hideSettingsModal: true,
              });
              return;
            } catch (err) {
              // fallback ke modal lokal jika parent tidak bisa diakses
            }
          }
          openPreview(url, title);
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
  <?php if (session('participant_id') && session('session_id')): ?>
    <script>
      window.__LAB_STUDENT_PRESENCE__ = {
        enabled: true,
        participant_id: <?= (int) session('participant_id') ?>,
        session_id: <?= (int) session('session_id') ?>,
      };
    </script>
    <script src="/assets/js/student-presence.js"></script>
  <?php endif; ?>
</body>
</html>

