<?= $this->extend('layout/embed') ?>
<?= $this->section('content') ?>

<?php $tab = $tab ?? 'general'; ?>

<header class="pageHead">
  <div>
    <h1 style="margin:0">Pengaturan Siswa</h1>
    <p class="muted" style="margin:6px 0 0">
      Pengaturan untuk siswa akan ditambahkan bertahap.
    </p>
  </div>
</header>

<div class="settingsTabs">
  <a class="settingsTab <?= $tab === 'general' ? 'active' : '' ?>" href="/student/settings?tab=general&embed=1">Umum</a>
</div>

<?php if ($tab === 'general'): ?>
  <section class="card">
    <h2 style="margin:0 0 6px">Umum</h2>
    <p class="muted">MVP: nanti bisa isi preferensi suara, tampilan, dan aksesibilitas.</p>
  </section>
<?php endif; ?>

<?= $this->endSection() ?>
