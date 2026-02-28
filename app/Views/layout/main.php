<!DOCTYPE html>
<html lang="id">
<head>
  <?= view('layout/partials/head_css', ['title' => $title ?? null]) ?>
</head>
<body class="<?= esc($bodyClass ?? '') ?>">
  <?= $this->include('layout/partials/topbar') ?>

  <main class="container">
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('ok')): ?>
      <div class="alert alert-ok"><?= esc(session()->getFlashdata('ok')) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
  </main>

  <script>
    (function () {
      function syncSidebarBodyClass() {
        const body = document.body;
        if (!body) return;

        const hasLeft = !!document.querySelector('.lab-sidebar-left');
        const hasRight = !!document.querySelector('.lab-sidebar-right');

        body.classList.toggle('has-sidebars', hasLeft && hasRight);
        body.classList.toggle('has-left-sidebar', hasLeft && !hasRight);
      }

      syncSidebarBodyClass();
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncSidebarBodyClass, { once: true });
      }
    })();
  </script>

  <div id="settingsModal" class="modal" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-label="Pengaturan">
      <button type="button" class="modal-close" data-close-settings aria-label="Tutup">×</button>
      <iframe id="settingsModalFrame" title="Pengaturan" src="about:blank"></iframe>
    </div>
  </div>

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
      const modal = document.getElementById('settingsModal');
      const frame = document.getElementById('settingsModalFrame');
      if(!modal || !frame) return;

      function withEmbed(url){
        try{
          const u = new URL(url, window.location.origin);
          if(!u.searchParams.has('embed')){
            u.searchParams.set('embed','1');
          }
          return u.toString();
        }catch(e){
          return url;
        }
      }

      function openModal(url){
        frame.src = withEmbed(url);
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.classList.add('modal-open');
        modal.style.visibility = '';
        delete modal.dataset.previewSuspended;
      }

      function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
        frame.src = 'about:blank';
        modal.style.visibility = '';
        delete modal.dataset.previewSuspended;
      }

      document.addEventListener('click', function(e){
        const trigger = e.target.closest('a.js-open-settings');
        if(trigger){
          e.preventDefault();
          openModal(trigger.href);
          return;
        }
        if(e.target.closest('[data-close-settings]')){
          e.preventDefault();
          closeModal();
        }
        if(e.target === modal){
          closeModal();
        }
      });

      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && modal.classList.contains('open')){
          closeModal();
        }
      });
    })();
  </script>
  <script>
    (function(){
      const modal = document.getElementById('filePreviewModal');
      const frame = document.getElementById('filePreviewFrame');
      if(!modal || !frame) return;
      const titleEl = document.getElementById('filePreviewTitle');
      const openEl = document.getElementById('filePreviewOpen');
      const settingsModal = document.getElementById('settingsModal');

      function openPreview(url, title, options){
        const opts = options || {};
        if(
          opts.hideSettingsModal
          && settingsModal
          && settingsModal.classList.contains('open')
        ){
          settingsModal.dataset.previewSuspended = '1';
          settingsModal.style.visibility = 'hidden';
        }
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
        if(
          settingsModal
          && settingsModal.dataset.previewSuspended === '1'
        ){
          settingsModal.style.visibility = '';
          delete settingsModal.dataset.previewSuspended;
        }
      }

      window.__LAB_OPEN_FILE_PREVIEW__ = function(url, title, options){
        openPreview(url, title, options || {});
      };
      window.__LAB_CLOSE_FILE_PREVIEW__ = function(){
        closePreview();
      };

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

