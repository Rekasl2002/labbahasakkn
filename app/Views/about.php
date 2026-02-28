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
  <p class="muted tiny" style="margin:0 0 10px">
    Buka panduan sesuai peran melalui tombol berikut.
  </p>

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

<?= $this->endSection() ?>

