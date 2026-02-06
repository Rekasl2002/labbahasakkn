<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($title ?? 'Lab Bahasa') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
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
</body>
</html>
