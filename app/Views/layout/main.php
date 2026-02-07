<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($title ?? 'Lab Bahasa') ?></title>
<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
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

  <div id="settingsModal" class="modal" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-label="Pengaturan">
      <button type="button" class="modal-close" data-close-settings aria-label="Tutup">×</button>
      <iframe id="settingsModalFrame" title="Pengaturan" src="about:blank"></iframe>
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
      }

      function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
        frame.src = 'about:blank';
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
</body>
</html>
