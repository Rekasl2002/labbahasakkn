<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Menunggu Sesi Dimulai</h1>
<p>Belum ada <b>sesi yang aktif</b> atau sesi sebelumnya sudah selesai.</p>

<form method="post" action="/login/student" class="card" style="max-width:560px">
  <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>

  <?php if (!empty($status_message ?? '')): ?>
    <?php $statusType = (string) ($status_type ?? 'ok'); ?>
    <div class="alert <?= $statusType === 'error' ? 'alert-error' : 'alert-ok' ?>"><?= esc((string) $status_message) ?></div>
  <?php endif; ?>

  <h2 style="margin:0 0 6px">Profil Siswa</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Ubah data langsung di sini atau lewat menu pengaturan. Gabung sesi dilakukan manual dengan tombol "Gabung Lagi".
  </p>

  <label>Nama lengkap</label>
  <input
    name="student_name"
    maxlength="60"
    required
    placeholder="Nama lengkap"
    value="<?= esc($student_name ?? session('student_name') ?? '') ?>"
  >

  <label>Kelas</label>
  <input
    name="class_name"
    maxlength="60"
    required
    placeholder="X IPA / XII IPS"
    value="<?= esc($class_name ?? session('class_name') ?? '') ?>"
  >

  <label>Nama Komputer (opsional)</label>
  <input
    name="device_label"
    maxlength="60"
    placeholder="Komputer-01"
    value="<?= esc($device_label ?? session('device_label') ?? '') ?>"
  >

  <div class="row gap wrap" style="margin-top:10px">
    <button type="submit" formaction="/waiting/profile" class="btn ok">Simpan Profil Terbaru</button>
    <a href="/logout/student" class="btn danger" onclick="return confirm('Keluar dari mode menunggu sesi?');">Keluar Siswa</a>
    <button type="submit" class="btn">Coba Masuk Lagi</button>
  </div>
</form>
<?= $this->endSection() ?>

