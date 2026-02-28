<?= $this->extend('layout/embed') ?>
<?= $this->section('content') ?>

<?php
$tab = $tab ?? 'general';
$profile = $profile ?? [];
$modeLabel = $mode_label ?? 'Menunggu sesi';
?>

<header class="pageHead">
  <div>
    <h1 style="margin:0">Pengaturan Siswa</h1>
    <p class="muted" style="margin:6px 0 0">
      Ubah nama lengkap, kelas, dan nama komputer kapan saja.
    </p>
  </div>
</header>

<div class="settingsTabs">
  <a class="settingsTab <?= $tab === 'general' ? 'active' : '' ?>" href="/student/settings?tab=general&embed=1">Identitas</a>
</div>

<?php if ($tab === 'general'): ?>
  <section class="card">
    <h2 style="margin:0 0 6px">Identitas Siswa</h2>
    <p class="muted tiny" style="margin:0 0 10px">
      Status saat ini: <b><?= esc($modeLabel) ?></b>
    </p>

    <form method="post" action="/student/settings">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <input type="hidden" name="embed" value="1">

      <label>Nama lengkap</label>
      <input
        name="student_name"
        maxlength="60"
        required
        placeholder="Nama lengkap"
        value="<?= esc((string) ($profile['student_name'] ?? '')) ?>"
      >

      <label>Kelas</label>
      <input
        name="class_name"
        maxlength="60"
        required
        placeholder="X IPA / XII IPS"
        value="<?= esc((string) ($profile['class_name'] ?? '')) ?>"
      >

      <label>Nama komputer (opsional)</label>
      <input
        name="device_label"
        maxlength="60"
        placeholder="Komputer-01"
        value="<?= esc((string) ($profile['device_label'] ?? '')) ?>"
      >

      <button type="submit" class="ok" style="margin-top:10px">Simpan Perubahan</button>
    </form>
  </section>
<?php endif; ?>

<?= $this->endSection() ?>

