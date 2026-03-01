<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="auth-choose">
  <h1 class="auth-hidden-title">Masuk</h1>
  <div class="auth-grid">
    <section class="card auth-card">
      <div class="auth-avatar">
        <img
          src="<?= base_url('assets/img/auth-student.png') ?>"
          alt="Ikon siswa"
          class="auth-avatar-img"
        >
      </div>
      <div class="auth-title">Siswa</div>

      <form class="auth-login-form" method="post" action="/login/student">
        <div class="auth-form-box">
          <div class="form-row">
            <label>Nama lengkap</label>
            <input class="form-input" name="student_name" placeholder="Nama lengkap" required>
          </div>

          <div class="form-row">
            <label>Kelas</label>
            <input class="form-input" name="class_name" placeholder="X IPA / XII IPS" required>
          </div>

          <div class="form-row">
            <label>Nama/Nomor komputer (opsional)</label>
            <input class="form-input" name="device_label" placeholder="Komputer-01" value="<?= esc($device_label ?? '') ?>">
          </div>

          <p class="muted hint-muted">Jika belum ada sesi aktif, kamu akan masuk halaman menunggu.</p>
        </div>

        <div class="auth-form-actions">
          <button class="btn-green" type="submit">Gabung Sesi</button>
        </div>
      </form>
    </section>

    <section class="card auth-card">
      <div class="auth-avatar auth-avatar-admin">
        <img
          src="<?= base_url('assets/img/auth-teacher.avif') ?>"
          alt="Ikon guru"
          class="auth-avatar-img"
        >
      </div>
      <div class="auth-title">Guru</div>
      <form class="auth-login-form" method="post" action="/login/admin">
        <div class="auth-form-box">
          <div class="form-row">
            <label>Nama pengguna</label>
            <input class="form-input" name="username" autocomplete="username" required>
          </div>
          <div class="form-row">
            <label>Kata sandi</label>
            <input class="form-input" name="password" type="password" autocomplete="current-password" required>
          </div>
        </div>

        <div class="auth-form-actions">
          <button class="btn-green" type="submit">Masuk sebagai Guru</button>
        </div>
      </form>
    </section>

  </div>
</div>
<?= $this->endSection() ?>

