<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
helper('settings');

$role = 'guest';
if (session('admin_id')) {
    $role = 'admin';
} elseif (session('participant_id') || session('student_waiting')) {
    $role = 'student';
}

$tutorialItems = lab_tutorial_items_for_role($role);
?>

<h1>Tentang</h1>
<p>Aplikasi Lab Bahasa ini dibuat untuk membantu guru mengelola sesi, peserta, suara, pesan, dan materi dengan mudah.</p>

<section class="card" style="margin-top:12px">
  <h2 style="margin:0 0 8px">Panduan Penggunaan</h2>

  <?php if (empty($tutorialItems)): ?>
    <p class="muted" style="margin:0">File tutorial belum tersedia.</p>
  <?php else: ?>
    <div class="row wrap gap">
      <?php foreach ($tutorialItems as $item): ?>
        <a
          href="#"
          class="btn"
          data-preview-url="<?= esc($item['url'] ?? '') ?>"
          data-preview-title="<?= esc($item['label'] ?? 'Panduan') ?>"
        >
          <?= esc($item['label'] ?? 'Panduan') ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<footer class="card" style="margin-top:12px">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap">
    <div>
      <p style="margin:0 0 8px">&copy; 2026 KKN STT Pratama Adi 2025-2026 PPI31 Banjaran</p>
      <p style="margin:0 0 4px"><strong>Anggota Tim:</strong></p>
      <p class="muted" style="margin:0 0 8px">Reka Shakiralhamdi Latief, Adhitia Budi Prasetyo, Khabbab Abdurrasyid, Sri Sukmawati, Andini Faradilla Putri</p>
      <a href="https://github.com/Rekasl2002/labbahasakkn" target="_blank" rel="noopener">GitHub Repository</a>
    </div>

    <button
      type="button"
      class="btn"
      onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '/'; }"
      aria-label="Kembali ke halaman sebelumnya"
    >
      Kembali
    </button>
  </div>
</footer>

<?= $this->endSection() ?>
