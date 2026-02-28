<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="auth-choose">
  <h1 class="auth-hidden-title">Masuk</h1>
  <div class="auth-grid">
    <section class="card auth-card">
      <div class="auth-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#233" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3"></circle><path d="M6 20c0-3 3-5 6-5s6 2 6 5"></path></svg>
      </div>
      <div class="auth-title">Siswa</div>

      <form method="post" action="/login/student">
        <div class="form-row">
          <label>Nama lengkap</label>
          <input class="form-input" name="student_name" placeholder="Nama lengkap" required>
        </div>

        <div class="form-row">
          <label>Kelas</label>
          <input class="form-input" name="class_name" placeholder="X IPA / XII IPS" required>
        </div>

        <div class="form-row">
          <label>Nama komputer (opsional)</label>
          <input class="form-input" name="device_label" placeholder="Komputer-01" value="<?= esc($device_label ?? '') ?>">
        </div>

        <?php if (!empty($device_label ?? '')): ?>
          <p class="muted tiny hint-muted">Terdeteksi IP <?= esc($client_ip ?? '-') ?>, otomatis diisi: <?= esc($device_label) ?>.</p>
        <?php else: ?>
          <p class="muted tiny hint-muted">IP terdeteksi: <?= esc($client_ip ?? '-') ?>. Jika tidak otomatis, isi manual.</p>
        <?php endif; ?>

        <button class="btn-green" type="submit">Gabung sesi</button>
      </form>

      <p class="muted hint-muted">Jika belum ada sesi aktif, kamu akan masuk mode menunggu.</p>
    </section>

    <section class="card auth-card">
      <div class="auth-avatar auth-avatar-admin">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"></path><path d="M8 21h8"></path></svg>
      </div>
      <div class="auth-title">Guru</div>
      <form method="post" action="/login/admin">
        <div class="form-row">
          <label>Nama pengguna</label>
          <input class="form-input" name="username" required value="admin">
        </div>
        <div class="form-row">
          <label>Kata sandi</label>
          <input class="form-input" name="password" type="password" required value="admin123">
        </div>
        <button class="btn-green" type="submit">Masuk sebagai guru</button>
      </form>
      <p class="muted hint-muted">Kata sandi bawaan hanya untuk percobaan. Ubah setelah siap dipakai.</p>
    </section>

  </div>
</div>
<?= $this->endSection() ?>

