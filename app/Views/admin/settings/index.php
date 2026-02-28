<?php $bodyClass = 'has-left-sidebar'; ?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$materials = $materials ?? [];
$mode = $mode ?? 'create';
$material = $material ?? null;
$file = $file ?? null;
$files = $files ?? [];
$tab = $tab ?? 'auto-detect';
$materialsTab = $materialsTab ?? 'list';
$warningSoundPath = $warningSoundPath ?? '';
$warningSoundUrl = $warningSoundUrl ?? '';
$tutorialTeacher = is_array($tutorialTeacher ?? null) ? $tutorialTeacher : [];
$tutorialStudent = is_array($tutorialStudent ?? null) ? $tutorialStudent : [];
?>

<?= view('layout/partials/sidebar_left_settings', ['role' => 'admin', 'tab' => $tab]) ?>

<div class="dashboard-center">
  <header class="pageHead">
    <div>
      <h1 style="margin:0">Pengaturan Guru</h1>
      <p class="muted" style="margin:6px 0 0">
        Kelola pengaturan sistem dan materi pembelajaran.
      </p>
    </div>
  </header>

  <?php if ($tab === 'auto-detect'): ?>
    <section class="card">
      <h2 style="margin:0 0 6px">Deteksi Otomatis Komputer Siswa</h2>
      <p class="muted tiny" style="margin:0 0 10px">
        Isi rentang IP dan format nama. Gunakan <code>{n}</code> untuk nomor urut.
      </p>
      <form method="post" action="/admin/settings">
        <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
        <label>IP awal</label>
        <input name="ip_range_start" placeholder="192.168.100.101" value="<?= esc($settings['ip_range_start'] ?? '') ?>">
        <label>IP akhir</label>
        <input name="ip_range_end" placeholder="192.168.100.140" value="<?= esc($settings['ip_range_end'] ?? '') ?>">
        <label>Format nama (pakai {n})</label>
        <input name="label_format" placeholder="Komputer {n}" value="<?= esc($settings['label_format'] ?? '') ?>">
        <label>Daftar nama komputer (opsional, satu per baris)</label>
        <textarea name="label_list" rows="6" placeholder="Komputer 1&#10;Komputer 2"><?= esc($settings['label_list'] ?? '') ?></textarea>
        <p class="muted tiny" style="margin:6px 0 0">
          Jika daftar diisi, akan dipakai berdasarkan urutan IP. Jika kosong, format nama digunakan.
        </p>
        <button type="submit" class="ok" style="margin-top:10px">Simpan</button>
      </form>
    </section>
  <?php elseif ($tab === 'branding'): ?>
    <?= view('admin/settings/branding_form', ['settings' => $settings]) ?>
  <?php elseif ($tab === 'warning-sound'): ?>
    <?= view('admin/settings/warning_sound_form', [
      'warningSoundPath' => $warningSoundPath,
      'warningSoundUrl' => $warningSoundUrl,
    ]) ?>
  <?php elseif ($tab === 'tutorial'): ?>
    <?= view('admin/settings/tutorial_form', [
      'tutorialTeacher' => $tutorialTeacher,
      'tutorialStudent' => $tutorialStudent,
    ]) ?>
  <?php elseif ($tab === 'password'): ?>
    <section class="card">
      <h2 style="margin:0 0 6px">Ubah Kata Sandi Guru</h2>
      <p class="muted tiny" style="margin:0 0 10px">
        Gunakan kata sandi yang kuat dan simpan dengan aman.
      </p>
      <form method="post" action="/admin/settings/password">
        <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
        <label>Kata sandi saat ini</label>
        <input name="current_password" type="password" autocomplete="current-password" required>
        <label>Kata sandi baru</label>
        <input name="new_password" type="password" autocomplete="new-password" required>
        <label>Konfirmasi kata sandi baru</label>
        <input name="confirm_password" type="password" autocomplete="new-password" required>
        <button type="submit" class="ok" style="margin-top:10px">Simpan Kata Sandi</button>
      </form>
    </section>
  <?php else: ?>
    <div class="settingsTabs">
      <a class="settingsTab <?= $materialsTab === 'list' ? 'active' : '' ?>" href="/admin/settings?tab=materials&mat=list">Manajemen Materi</a>
      <a class="settingsTab <?= $materialsTab === 'add' ? 'active' : '' ?>" href="/admin/settings?tab=materials&mat=add">Tambah Materi</a>
      <?php if ($materialsTab === 'edit' && !empty($material)): ?>
        <span class="settingsTab active">Ubah Materi</span>
      <?php endif; ?>
    </div>

    <?php if ($materialsTab === 'list'): ?>
      <section class="card">
        <?= view('admin/settings/materials_list', ['materials' => $materials]) ?>
      </section>
    <?php elseif ($materialsTab === 'add'): ?>
      <section class="card">
        <?= view('admin/settings/materials_form', [
          'mode' => 'create',
          'material' => $material,
          'file' => $file,
          'files' => $files,
        ]) ?>
      </section>
    <?php else: ?>
      <section class="card">
        <?= view('admin/settings/materials_form', [
          'mode' => $mode,
          'material' => $material,
          'file' => $file,
          'files' => $files,
        ]) ?>
      </section>
    <?php endif; ?>
  <?php endif; ?>

  <div style="margin-top:12px">
    <a href="/admin" class="btn">Kembali</a>
  </div>
</div>

<?= $this->endSection() ?>

