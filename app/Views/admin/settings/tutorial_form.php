<?php
$embed = !empty($embed);

$tutorialTeacher = is_array($tutorialTeacher ?? null) ? $tutorialTeacher : [];
$tutorialStudent = is_array($tutorialStudent ?? null) ? $tutorialStudent : [];

$teacherUrl = trim((string) ($tutorialTeacher['url'] ?? ''));
$teacherPath = trim((string) ($tutorialTeacher['path'] ?? ''));
$teacherConfiguredPath = trim((string) ($tutorialTeacher['configured_path'] ?? ''));
$teacherIsDefault = !empty($tutorialTeacher['is_default']);

$studentUrl = trim((string) ($tutorialStudent['url'] ?? ''));
$studentPath = trim((string) ($tutorialStudent['path'] ?? ''));
$studentConfiguredPath = trim((string) ($tutorialStudent['configured_path'] ?? ''));
$studentIsDefault = !empty($tutorialStudent['is_default']);

$teacherSourceText = $teacherIsDefault
    ? 'Sumber saat ini: file bawaan sistem.'
    : ($teacherConfiguredPath !== '' ? 'Sumber saat ini: file kustom dari pengaturan.' : 'Sumber saat ini: file bawaan sistem.');
$studentSourceText = $studentIsDefault
    ? 'Sumber saat ini: file bawaan sistem.'
    : ($studentConfiguredPath !== '' ? 'Sumber saat ini: file kustom dari pengaturan.' : 'Sumber saat ini: file bawaan sistem.');
?>

<section class="card">
  <h2 style="margin:0 0 6px">Panduan Tutorial</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Unggah PDF baru untuk mengganti panduan. Jika tidak diunggah, sistem memakai file bawaan pada aplikasi.
  </p>

  <form method="post" action="/admin/settings" enctype="multipart/form-data">
    <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
    <?php if ($embed): ?>
      <input type="hidden" name="embed" value="1">
    <?php endif; ?>
    <input type="hidden" name="setting_group" value="tutorial">

    <div class="row wrap gap" style="align-items:stretch">
      <section class="card" style="flex:1; min-width:260px; margin:0">
        <h3 style="margin:0 0 8px">Panduan untuk Guru</h3>
        <p class="muted tiny" style="margin:0 0 6px"><?= esc($teacherSourceText) ?></p>
        <?php if ($teacherPath !== ''): ?>
          <p class="muted tiny" style="margin:0 0 8px">Berkas aktif: <?= esc(basename($teacherPath)) ?></p>
        <?php endif; ?>
        <?php if ($teacherUrl !== ''): ?>
          <a
            href="#"
            class="btn tiny"
            data-preview-url="<?= esc($teacherUrl) ?>"
            data-preview-title="Panduan untuk Guru"
          >
            Lihat Panduan
          </a>
        <?php else: ?>
          <p class="muted tiny" style="margin:0 0 8px">File panduan guru belum tersedia.</p>
        <?php endif; ?>

        <label style="margin-top:10px">Ganti file PDF</label>
        <input type="file" name="tutorial_teacher_file" accept="application/pdf,.pdf">
        <label class="muted tiny" style="display:flex; align-items:center; gap:8px; margin-top:8px">
          <input type="checkbox" name="tutorial_teacher_reset" value="1" style="width:auto; margin:0; padding:0">
          Gunakan kembali file bawaan aplikasi
        </label>
      </section>

      <section class="card" style="flex:1; min-width:260px; margin:0">
        <h3 style="margin:0 0 8px">Panduan untuk Siswa</h3>
        <p class="muted tiny" style="margin:0 0 6px"><?= esc($studentSourceText) ?></p>
        <?php if ($studentPath !== ''): ?>
          <p class="muted tiny" style="margin:0 0 8px">Berkas aktif: <?= esc(basename($studentPath)) ?></p>
        <?php endif; ?>
        <?php if ($studentUrl !== ''): ?>
          <a
            href="#"
            class="btn tiny"
            data-preview-url="<?= esc($studentUrl) ?>"
            data-preview-title="Panduan untuk Siswa"
          >
            Lihat Panduan
          </a>
        <?php else: ?>
          <p class="muted tiny" style="margin:0 0 8px">File panduan siswa belum tersedia.</p>
        <?php endif; ?>

        <label style="margin-top:10px">Ganti file PDF</label>
        <input type="file" name="tutorial_student_file" accept="application/pdf,.pdf">
        <label class="muted tiny" style="display:flex; align-items:center; gap:8px; margin-top:8px">
          <input type="checkbox" name="tutorial_student_reset" value="1" style="width:auto; margin:0; padding:0">
          Gunakan kembali file bawaan aplikasi
        </label>
      </section>
    </div>

    <p class="muted tiny" style="margin:10px 0 0">
      Format yang didukung: PDF. Ukuran maksimal file 20MB.
    </p>
    <button type="submit" class="ok" style="margin-top:10px">Simpan Tutorial</button>
  </form>
</section>
