<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Menunggu Sesi Dimulai</h1>
<p>Belum ada sesi aktif. Minta admin untuk klik <b>Mulai Sesi</b>.</p>

<form method="post" action="/login/student" class="card" style="max-width:520px">
  <h2>Info kamu</h2>
  <input type="hidden" name="student_name" value="<?= esc($student_name ?? '') ?>">
  <input type="hidden" name="class_name" value="<?= esc($class_name ?? '') ?>">
  <input type="hidden" name="device_label" value="<?= esc($device_label ?? '') ?>">
  <p><b><?= esc($student_name ?? '') ?></b> (<?= esc($class_name ?? '') ?>)</p>
  <button type="submit">Coba gabung lagi</button>
</form>

<script>
  setTimeout(() => document.forms[0].submit(), 5000);
</script>
<?= $this->endSection() ?>
