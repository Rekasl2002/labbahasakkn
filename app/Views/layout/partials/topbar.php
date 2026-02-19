<header class="topbar">
  <div class="brand"><a href="/" class="brand-link">Lab Bahasa</a></div>
  <nav class="nav">
    <a href="/about">About</a>
    <?php if (session('admin_id')): ?>
      <a href="/admin/settings?tab=auto-detect" class="js-open-settings">Pengaturan</a>
      <a href="/logout">Logout</a>
    <?php elseif (session('participant_id')): ?>
      <a href="/student/settings?tab=general" class="js-open-settings">Pengaturan</a>
      <a href="/logout/student" onclick="return confirm('Keluar dari sesi siswa? Data siswa pada sesi aktif akan dihapus.');">Logout Siswa</a>
    <?php endif; ?>
  </nav>
</header>
