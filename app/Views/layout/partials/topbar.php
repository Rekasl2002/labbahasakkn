<?php
helper('settings');
$branding = lab_app_branding();
$appName = $branding['app_name'] ?? 'Lab Bahasa';
$logoUrl = $branding['logo_url'] ?? base_url('favicon.ico');
?>
<header class="topbar">
  <div class="brand">
    <a href="/" class="brand-link">
      <img src="<?= esc($logoUrl) ?>" alt="Logo <?= esc($appName) ?>" class="brand-logo">
      <span><?= esc($appName) ?></span>
    </a>
  </div>
  <nav class="nav">
    <a href="/about">Tentang</a>
    <?php if (session('admin_id')): ?>
      <a href="/admin/settings?tab=auto-detect" class="js-open-settings">Pengaturan</a>
      <a href="/logout">Keluar</a>
    <?php elseif (session('participant_id')): ?>
      <a href="/student/settings?tab=general" class="js-open-settings">Pengaturan</a>
      <a href="/logout/student" onclick="return confirm('Keluar dari sesi siswa? Data siswa pada sesi aktif akan dihapus.');">Keluar Siswa</a>
    <?php elseif (session('student_waiting')): ?>
      <a href="/student/settings?tab=general" class="js-open-settings">Pengaturan</a>
      <a href="/logout/student" onclick="return confirm('Keluar dari mode menunggu sesi?');">Keluar</a>
    <?php endif; ?>
  </nav>
</header>

