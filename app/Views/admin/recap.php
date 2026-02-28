<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php
$sessionName = trim((string) ($session['name'] ?? ''));
$sessionName = $sessionName !== '' ? $sessionName : 'Sesi tanpa nama';
$isActive = (int) ($session['is_active'] ?? 0) === 1;
$endedAtText = trim((string) ($session['ended_at'] ?? ''));
$endedAtText = $endedAtText !== '' ? $endedAtText : ($isActive ? 'Sedang berlangsung' : '-');
$durationLimitMinutes = (int) ($session['duration_limit_minutes'] ?? 0);
$extensionMinutes = (int) ($session['extension_minutes'] ?? 0);
$deadlineAtText = trim((string) ($session['deadline_at'] ?? ''));
$sessionId = (int) ($session['id'] ?? 0);
$limitText = '-';
if ($durationLimitMinutes > 0) {
    $limitText = $durationLimitMinutes . ' menit';
    if ($extensionMinutes > 0) {
        $limitText .= ' (+' . $extensionMinutes . ' menit)';
    }
}
?>
<h1>Rekap Sesi</h1>
<?php if ($sessionId > 0): ?>
  <div class="row gap wrap" style="margin:0 0 12px">
    <a class="btn tiny" href="/admin/session/<?= $sessionId ?>/report/excel">Unduh Excel</a>
    <a class="btn tiny" href="/admin/session/<?= $sessionId ?>/report/pdf">Unduh PDF</a>
  </div>
<?php endif; ?>

<div class="card">
  <div class="row between wrap gap" style="align-items:flex-start">
    <p style="margin:0"><b><?= esc($sessionName) ?></b></p>
    <?php if ($isActive): ?>
      <span class="badge ok">SESI AKTIF</span>
    <?php else: ?>
      <span class="badge">SESI SELESAI</span>
    <?php endif; ?>
  </div>
  <p>Mulai: <?= esc($session['started_at'] ?? '-') ?> | Selesai: <?= esc($endedAtText) ?></p>
  <p>Batas sesi: <?= esc($limitText) ?> | Deadline: <?= esc($deadlineAtText !== '' ? $deadlineAtText : '-') ?></p>
  <p>Durasi: <?= floor($durationSec/60) ?> menit <?= $durationSec%60 ?> detik</p>
  <p>Jumlah peserta: <?= count($participants) ?></p>
  <p>Total pesan: <?= (int)$messagesCount ?></p>
  <p>Jumlah materi digunakan: <?= (int)$materialsUsed ?></p>
</div>

<div class="card">
  <h2>Kehadiran</h2>
  <div class="tableWrap">
  <table class="table">
    <thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th>Komputer</th><th>IP</th><th>Waktu Masuk</th><th>Terakhir Aktif</th></tr></thead>
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

<a class="btn" href="/admin">Kembali ke Beranda</a>
<?= $this->endSection() ?>

