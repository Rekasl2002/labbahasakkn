<?php $bodyClass = 'has-left-sidebar'; ?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $tab = $tab ?? 'general'; ?>

<?= view('layout/partials/sidebar_left_settings', ['role' => 'student', 'tab' => $tab]) ?>

<div class="dashboard-center">
  <header class="pageHead">
    <div>
      <h1 style="margin:0">Pengaturan Siswa</h1>
      <p class="muted" style="margin:6px 0 0">
        Pengaturan untuk siswa akan ditambahkan bertahap.
      </p>
    </div>
  </header>

  <?php if ($tab === 'general'): ?>
    <section class="card">
      <h2 style="margin:0 0 6px">Umum</h2>
      <p class="muted">MVP: nanti bisa isi preferensi suara, tampilan, dan aksesibilitas.</p>
    </section>
  <?php endif; ?>

  <div style="margin-top:12px">
    <a href="/student" class="btn">Kembali</a>
  </div>
</div>

<?= $this->endSection() ?>
