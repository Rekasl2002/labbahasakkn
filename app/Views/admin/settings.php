<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<header class="pageHead">
  <div>
    <h1 style="margin:0">Pengaturan Admin</h1>
    <p class="muted" style="margin:6px 0 0">
      Atur auto-deteksi komputer dan ubah password admin.
    </p>
  </div>
</header>

<div class="grid2">
  <section class="card">
    <h2 style="margin:0 0 6px">Auto-Deteksi Komputer Siswa</h2>
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
      <button type="submit" class="ok" style="margin-top:10px">Simpan Pengaturan</button>
    </form>
  </section>

  <section class="card">
    <h2 style="margin:0 0 6px">Ganti Password Admin</h2>
    <p class="muted tiny" style="margin:0 0 10px">
      Gunakan password kuat dan simpan dengan aman.
    </p>
    <form method="post" action="/admin/settings/password">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <label>Password sekarang</label>
      <input name="current_password" type="password" autocomplete="current-password" required>
      <label>Password baru</label>
      <input name="new_password" type="password" autocomplete="new-password" required>
      <label>Konfirmasi password baru</label>
      <input name="confirm_password" type="password" autocomplete="new-password" required>
      <button type="submit" class="ok" style="margin-top:10px">Simpan Password</button>
    </form>
  </section>
</div>

<div style="margin-top:12px">
  <a href="/admin" class="btn">Kembali</a>
</div>

<?= $this->endSection() ?>
