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
      <input name="device_label" placeholder="PC-01">
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
