<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
helper('settings');

$settings = lab_load_settings();
$branding = lab_app_branding($settings);
$appName = trim((string) ($branding['app_name'] ?? 'Lab Bahasa'));
if ($appName === '') {
    $appName = 'Lab Bahasa';
}

$role = 'guest';
if (session('admin_id')) {
    $role = 'admin';
} elseif (session('participant_id') || session('student_waiting')) {
    $role = 'student';
}

$tutorialItems = lab_tutorial_items_for_role($role);

$teamMembers = [
    'Reka Shakiralhamdi Latief',
    'Adhitia Budi Prasetyo',
    'Khabbab Abdurrasyid',
    'Sri Sukmawati',
    'Andini Faradilla Putri',
];
?>

<section class="pageHead">
  <h1>Tentang <?= esc($appName) ?></h1>
</section>

<div class="grid2">
  <section class="card">
    <div class="row wrap gap" style="align-items:flex-start">
      <img
        src="<?= esc($branding['logo_url']) ?>"
        alt="Logo <?= esc($appName) ?>"
        style="width:64px; height:64px; border-radius:12px; border:1px solid var(--line); background:#fff; object-fit:cover"
      >
      <div style="flex:1; min-width:220px">
        <h2 style="margin:0 0 6px">Ringkasan Aplikasi</h2>
      </div>
    </div>

    <hr>
    <p style="margin:0 0 6px">
      <strong>Tujuan utama:</strong> memudahkan guru dalam mengelola aktivitas pembelajaran bahasa secara real-time.
    </p>
    <p class="muted tiny" style="margin:0 0 6px">
      <?= esc($appName) ?> mendukung pengelolaan sesi, komunikasi kelas, kontrol audio peserta,
      serta distribusi materi dalam satu panel terintegrasi.
    </p>
    <p class="muted tiny" style="margin:0 0 6px">
      Proyek ini dikembangkan sebagai bagian dari kegiatan KKN STT Pratama Adi 2025-2026 PPI 31 Banjaran.
    </p>
  </section>

  <section class="card">
    <h2 style="margin:0 0 8px">Anggota Kelompok KKN STT Pratama Adi</h2>
    <ul style="margin:0; padding-left:18px">
      <?php foreach ($teamMembers as $member): ?>
        <li><?= esc($member) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>

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

<div class="row wrap gap" style="justify-content:flex-end; margin-top:12px">
  <button
    type="button"
    class="btn"
    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '/'; }"
    aria-label="Kembali ke halaman sebelumnya"
  >
    Kembali
  </button>
</div>

<?= $this->endSection() ?>
