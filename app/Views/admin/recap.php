<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h1>Rekap Sesi</h1>

<div class="card">
  <p><b><?= esc($session['name']) ?></b></p>
  <p>Mulai: <?= esc($session['started_at'] ?? '-') ?> | Selesai: <?= esc($session['ended_at'] ?? '-') ?></p>
  <p>Durasi: <?= floor($durationSec/60) ?> menit <?= $durationSec%60 ?> detik</p>
  <p>Jumlah peserta: <?= count($participants) ?></p>
  <p>Total chat: <?= (int)$messagesCount ?></p>
  <p>Jumlah materi digunakan: <?= (int)$materialsUsed ?></p>
</div>

<div class="card">
  <h2>Attendance</h2>
  <div class="tableWrap">
  <table class="table">
    <thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th>Komputer</th><th>IP</th><th>Join</th><th>Last seen</th></tr></thead>
    <tbody>
      <?php foreach ($participants as $i => $p): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= esc($p['student_name']) ?></td>
          <td><?= esc($p['class_name']) ?></td>
          <td><?= esc($p['device_label'] ?? '-') ?></td>
          <td><?= esc($p['ip_address'] ?? '-') ?></td>
          <td><?= esc($p['joined_at'] ?? '-') ?></td>
          <td><?= esc($p['last_seen_at'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<a class="btn" href="/admin">Kembali ke Dashboard</a>
<?= $this->endSection() ?>
