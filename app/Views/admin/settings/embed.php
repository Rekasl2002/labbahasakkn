<?= $this->extend('layout/embed') ?>
<?= $this->section('content') ?>

<?php
$materials = $materials ?? [];
$mode = $mode ?? 'create';
$material = $material ?? null;
$file = $file ?? null;
$files = $files ?? [];
$tab = $tab ?? 'auto-detect';
$materialsTab = $materialsTab ?? 'list';
$embed = true;
$warningSoundPath = $warningSoundPath ?? '';
$warningSoundUrl = $warningSoundUrl ?? '';
$tutorialTeacher = is_array($tutorialTeacher ?? null) ? $tutorialTeacher : [];
$tutorialStudent = is_array($tutorialStudent ?? null) ? $tutorialStudent : [];
?>

<header class="pageHead">
  <div>
    <h1 style="margin:0">Pengaturan Guru</h1>
    <p class="muted" style="margin:6px 0 0">
      Kelola pengaturan sistem dan materi pembelajaran.
    </p>
  </div>
</header>

<div class="settingsTabs">
  <a class="settingsTab <?= $tab === 'branding' ? 'active' : '' ?>" href="/admin/settings?tab=branding&embed=1">Tampilan Aplikasi</a>
  <a class="settingsTab <?= $tab === 'warning-sound' ? 'active' : '' ?>" href="/admin/settings?tab=warning-sound&embed=1">Suara Peringatan</a>
  <a class="settingsTab <?= $tab === 'tutorial' ? 'active' : '' ?>" href="/admin/settings?tab=tutorial&embed=1">Panduan Tutorial</a>
  <a class="settingsTab <?= $tab === 'auto-detect' ? 'active' : '' ?>" href="/admin/settings?tab=auto-detect&embed=1">Deteksi Otomatis</a>
  <a class="settingsTab <?= $tab === 'password' ? 'active' : '' ?>" href="/admin/settings?tab=password&embed=1">Kata Sandi</a>
  <a class="settingsTab <?= $tab === 'materials' ? 'active' : '' ?>" href="/admin/settings?tab=materials&mat=list&embed=1">Materi</a>
</div>

<?php if ($tab === 'branding'): ?>
  <?= view('admin/settings/branding_form', ['settings' => $settings, 'embed' => true]) ?>
<?php elseif ($tab === 'warning-sound'): ?>
  <?= view('admin/settings/warning_sound_form', [
    'warningSoundPath' => $warningSoundPath,
    'warningSoundUrl' => $warningSoundUrl,
    'embed' => true,
  ]) ?>
<?php elseif ($tab === 'tutorial'): ?>
  <?= view('admin/settings/tutorial_form', [
    'tutorialTeacher' => $tutorialTeacher,
    'tutorialStudent' => $tutorialStudent,
    'embed' => true,
  ]) ?>
<?php elseif ($tab === 'auto-detect'): ?>
  <section class="card">
    <h2 style="margin:0 0 6px">Deteksi Otomatis Komputer Siswa</h2>
    <p class="muted tiny" style="margin:0 0 10px">
      Isi rentang IP dan format nama. Gunakan <code>{n}</code> untuk nomor urut.
    </p>
    <form method="post" action="/admin/settings">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <input type="hidden" name="embed" value="1">
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
<?php elseif ($tab === 'password'): ?>
  <section class="card">
    <h2 style="margin:0 0 6px">Ubah Kata Sandi Guru</h2>
    <p class="muted tiny" style="margin:0 0 10px">
      Gunakan kata sandi yang kuat dan simpan dengan aman.
    </p>
    <form method="post" action="/admin/settings/password">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <input type="hidden" name="embed" value="1">
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
    <a class="settingsTab <?= $materialsTab === 'list' ? 'active' : '' ?>" href="/admin/settings?tab=materials&mat=list&embed=1">Manajemen Materi</a>
    <a class="settingsTab <?= $materialsTab === 'add' ? 'active' : '' ?>" href="/admin/settings?tab=materials&mat=add&embed=1">Tambah Materi</a>
    <?php if ($materialsTab === 'edit' && !empty($material)): ?>
      <span class="settingsTab active">Ubah Materi</span>
    <?php endif; ?>
  </div>

  <?php if ($materialsTab === 'list'): ?>
    <section class="card">
      <?= view('admin/settings/materials_list', ['materials' => $materials, 'embed' => $embed]) ?>
    </section>
  <?php elseif ($materialsTab === 'add'): ?>
    <section class="card">
      <?= view('admin/settings/materials_form', [
        'mode' => 'create',
        'material' => $material,
        'file' => $file,
        'files' => $files,
        'embed' => $embed,
      ]) ?>
    </section>
  <?php else: ?>
    <section class="card">
      <?= view('admin/settings/materials_form', [
        'mode' => $mode,
        'material' => $material,
        'file' => $file,
        'files' => $files,
        'embed' => $embed,
      ]) ?>
    </section>
  <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>

