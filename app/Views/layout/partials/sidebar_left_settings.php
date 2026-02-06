<?php
$role = $role ?? '';
$roleLabel = $role === 'admin' ? 'Admin' : 'Siswa';
$userName = $role === 'admin'
    ? (session('admin_username') ?: 'Admin')
    : (session('student_name') ?: 'Siswa');
$userMeta = $role === 'student' ? (session('class_name') ?: '') : '';
$tab = $tab ?? ($role === 'admin' ? 'auto-detect' : 'general');

$links = [];
if ($role === 'admin') {
    $links = [
        'auto-detect' => 'Auto-Deteksi Komputer Siswa',
        'password' => 'Ganti Password Admin',
        'materials' => 'Manajemen Materi',
    ];
} elseif ($role === 'student') {
    $links = [
        'general' => 'Pengaturan Umum',
    ];
}
?>

<aside class="lab-sidebar lab-sidebar-left">
  <div class="lab-sidebar-inner">
    <div class="lab-sidebar-head">
      <div class="lab-sidebar-title">Pengaturan</div>
      <div class="lab-sidebar-sub">Sidebar Kiri • <?= esc($roleLabel) ?></div>
    </div>
    <div class="lab-sidebar-user">
      <div class="name"><?= esc($userName) ?></div>
      <?php if ($userMeta !== ''): ?>
        <div class="meta"><?= esc($userMeta) ?></div>
      <?php else: ?>
        <div class="meta">Menu Pengaturan</div>
      <?php endif; ?>
    </div>
    <section class="card">
      <h2 style="margin:0 0 8px">Pilih Pengaturan</h2>
      <nav class="lab-sidebar-nav">
        <?php foreach ($links as $key => $label): ?>
          <a
            class="lab-sidebar-link <?= $tab === $key ? 'active' : '' ?>"
            href="<?= $role === 'admin' ? '/admin/settings?tab=' . esc($key) : '/student/settings?tab=' . esc($key) ?>"
          >
            <?= esc($label) ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </section>
  </div>
</aside>
