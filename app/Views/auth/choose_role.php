<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Masuk</h1>
<div class="grid2">
  <section class="card">
    <h2>Siswa</h2>
    <form method="post" action="/login/student">
      <label>Nama lengkap</label>
      <input name="student_name" required>
      <label>Kelas</label>
      <input name="class_name" required>
      <label>Nama komputer (opsional)</label>
      <input name="device_label" placeholder="PC-01" value="<?= esc($device_label ?? '') ?>">
      <?php if (!empty($device_label ?? '')): ?>
        <p class="muted tiny" style="margin:6px 0 0">
          Terdeteksi IP <?= esc($client_ip ?? '-') ?>, otomatis diisi: <?= esc($device_label) ?>.
        </p>
      <?php else: ?>
        <p class="muted tiny" style="margin:6px 0 0">
          IP terdeteksi: <?= esc($client_ip ?? '-') ?>. Jika tidak otomatis, isi manual.
        </p>
      <?php endif; ?>
      <button type="submit">Gabung sesi</button>
    </form>
    <p class="muted">Jika belum ada sesi aktif, kamu akan masuk mode menunggu.</p>
  </section>

  <section class="card">
    <h2>Admin</h2>
    <form method="post" action="/login/admin">
      <label>Username</label>
      <input name="username" required value="admin">
      <label>Password</label>
      <input name="password" type="password" required value="admin123">
      <button type="submit">Masuk admin</button>
    </form>
    <p class="muted">Default password hanya untuk dev. Ganti setelah jalan.</p>
  </section>
</div>
<?= $this->endSection() ?>
